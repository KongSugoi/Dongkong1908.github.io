<?php
// --- Khởi động session an toàn ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Kiểm tra quyền admin ---
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// --- Kết nối cơ sở dữ liệu ---
require_once "connection.php";

// --- Kiểm tra CSRF token ---
if (!isset($_GET['token']) || !isset($_SESSION['csrf_token']) || $_GET['token'] !== $_SESSION['csrf_token']) {
    die("Yêu cầu không hợp lệ (CSRF token không khớp).");
}

// --- Kiểm tra và lọc đầu vào ---
if (!isset($_GET['id'], $_GET['action'])) {
    die("Thiếu tham số id hoặc action.");
}

$id = intval($_GET['id']); // chống SQLi
$action = $_GET['action'];

// --- Xử lý hành động ---
if ($action === "approve") {
    // --- Lấy thông tin phiếu mượn ---
    $stmt = $link->prepare("SELECT device_name, quantity FROM issue_device WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($device_name, $quantity);
    if (!$stmt->fetch()) {
        die("Không tìm thấy yêu cầu mượn hợp lệ.");
    }
    $stmt->close();

    // --- Kiểm tra số lượng khả dụng ---
    $stmt = $link->prepare("SELECT available_qty FROM add_device WHERE device_name = ?");
    $stmt->bind_param("s", $device_name);
    $stmt->execute();
    $stmt->bind_result($available_qty);
    if (!$stmt->fetch()) {
        die("Không tìm thấy thiết bị trong kho.");
    }
    $stmt->close();

    if ($available_qty < $quantity) {
        die("Không đủ thiết bị khả dụng để duyệt yêu cầu mượn này.");
    }

    // --- Cập nhật trạng thái & trừ số lượng ---
    $status = "Đã duyệt mượn";
    $stmt1 = $link->prepare("UPDATE issue_device SET status = ? WHERE id = ?");
    $stmt1->bind_param("si", $status, $id);
    $stmt1->execute();
    $stmt1->close();

    $stmt2 = $link->prepare("UPDATE add_device SET available_qty = available_qty - ? WHERE device_name = ?");
    $stmt2->bind_param("is", $quantity, $device_name);
    $stmt2->execute();
    $stmt2->close();

    $message = "Duyệt mượn thành công.";

} elseif ($action === "reject") {
    $status = "Từ chối cho mượn";
    $stmt = $link->prepare("UPDATE issue_device SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();

    $message = "Từ chối yêu cầu mượn thành công.";
} else {
    die("Hành động không hợp lệ.");
}

// --- Xóa token sau khi xử lý xong ---
unset($_SESSION['csrf_token']);
?>

<script>
    alert("<?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>");
    window.location = "issue_device.php";
</script>
