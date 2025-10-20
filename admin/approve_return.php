<?php
// --- Khởi động session an toàn ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Kiểm tra quyền admin ---
if (empty($_SESSION['admin'])) {
    header("Location: ../user/login.php");
    exit;
}

require_once "connection.php";

// --- Kiểm tra tham số bắt buộc ---
if (!isset($_GET['id'], $_GET['action'], $_GET['token'])) {
    die("Thiếu tham số yêu cầu.");
}

// --- Kiểm tra CSRF token ---
if (!hash_equals($_SESSION['csrf_token'], $_GET['token'])) {
    die("CSRF token không hợp lệ.");
}

// --- Làm sạch dữ liệu đầu vào ---
$id = intval($_GET['id']);
$action = $_GET['action'];

// --- Lấy thông tin yêu cầu ---
$stmt = $link->prepare("SELECT device_name, quantity FROM issue_device WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Không tìm thấy yêu cầu với ID này.");
}

$row = $res->fetch_assoc();
$device_name = $row['device_name'];
$quantity = intval($row['quantity']);
$stmt->close();

if ($action === 'approve') {
    // --- Cập nhật trạng thái 'Đã duyệt trả' ---
    $status = "Đã duyệt trả";
    $stmt1 = $link->prepare("UPDATE issue_device SET status=? WHERE id=?");
    $stmt1->bind_param("si", $status, $id);
    $stmt1->execute();

    // --- Tăng số lượng thiết bị có sẵn ---
    $stmt2 = $link->prepare("UPDATE add_device SET available_qty = available_qty + ? WHERE device_name = ?");
    $stmt2->bind_param("is", $quantity, $device_name);
    $stmt2->execute();

    $stmt1->close();
    $stmt2->close();

    echo "<script>alert('✅ Đã duyệt yêu cầu trả thiết bị thành công!'); window.location='return_device.php';</script>";

} elseif ($action === 'reject') {
    // --- Cập nhật trạng thái 'Từ chối cho trả' ---
    $status = "Từ chối cho trả";
    $stmt3 = $link->prepare("UPDATE issue_device SET status=? WHERE id=?");
    $stmt3->bind_param("si", $status, $id);
    $stmt3->execute();
    $stmt3->close();

    echo "<script>alert('🚫 Đã từ chối yêu cầu trả thiết bị.'); window.location='return_device.php';</script>";

} else {
    die("Hành động không hợp lệ.");
}
?>
