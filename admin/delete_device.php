<?php
session_start();
require_once "connection.php";

// Chỉ cho phép admin thực hiện
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: display_device.php');
    exit;
}

// Kiểm tra CSRF token
$token = $_POST['csrf_token'] ?? '';
if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$token)) {
    $_SESSION['flash_error'] = 'Yêu cầu không hợp lệ (CSRF).';
    header('Location: display_device.php');
    exit;
}

// Validate id
$id = $_POST['id'] ?? '';
$id = filter_var($id, FILTER_VALIDATE_INT);
if ($id === false || $id <= 0) {
    $_SESSION['flash_error'] = 'ID thiết bị không hợp lệ.';
    header('Location: display_device.php');
    exit;
}

// Kiểm tra tồn tại thiết bị
$check = $link->prepare("SELECT id, device_image FROM add_device WHERE id = ?");
if (!$check) {
    error_log("Prepare failed (check): " . $link->error);
    $_SESSION['flash_error'] = 'Lỗi hệ thống.';
    header('Location: display_device.php');
    exit;
}
$check->bind_param('i', $id);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    $check->close();
    $_SESSION['flash_error'] = 'Không tìm thấy thiết bị.';
    header('Location: display_device.php');
    exit;
}
$check->bind_result($found_id, $device_image);
$check->fetch();
$check->close();

// Xóa record (và file ảnh nếu bạn muốn)
$del = $link->prepare("DELETE FROM add_device WHERE id = ?");
if (!$del) {
    error_log("Prepare failed (delete): " . $link->error);
    $_SESSION['flash_error'] = 'Lỗi hệ thống khi xóa.';
    header('Location: display_device.php');
    exit;
}
$del->bind_param('i', $id);
if (!$del->execute()) {
    error_log("Execute failed (delete): " . $del->error);
    $_SESSION['flash_error'] = 'Xóa thất bại.';
    $del->close();
    header('Location: display_device.php');
    exit;
}
$del->close();

// (Tùy chọn) Xóa file ảnh vật lý an toàn nếu file nằm trong folder cho phép
if (!empty($device_image)) {
    // chỉ xóa file trong thư mục được phép
    $baseDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'public_image' . DIRECTORY_SEPARATOR;
    $filePath = realpath($baseDir . basename($device_image));
    if ($filePath && str_starts_with($filePath, $baseDir) && is_file($filePath)) {
        @unlink($filePath); // supress errors
    }
}

$_SESSION['flash_success'] = 'Xóa thiết bị thành công.';
header('Location: display_device.php');
exit;
