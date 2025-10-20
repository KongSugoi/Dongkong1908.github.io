<?php
session_start();
include "connection.php";

// --- Kiểm tra quyền ---
if (!isset($_SESSION["admin"])) {
    echo "<script>window.location='../user/login.php';</script>";
    exit;
}

// --- Kiểm tra tham số id hợp lệ ---
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    echo "<script>alert('❌ ID không hợp lệ!'); window.location='return_device.php';</script>";
    exit;
}

$id = intval($_GET["id"]);
$return_date = date("Y-m-d");

// --- Lấy thông tin thiết bị ---
$stmt = $link->prepare("SELECT device_name, quantity FROM issue_device WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('❌ Không tìm thấy yêu cầu mượn thiết bị!'); window.location='return_device.php';</script>";
    exit;
}

$row = $result->fetch_assoc();
$device_name = $row["device_name"];
$quantity = (int)$row["quantity"];
$stmt->close();

// --- Cập nhật ngày trả và trạng thái ---
$stmt = $link->prepare("UPDATE issue_device SET device_return_date = ?, status = 'Đã trả' WHERE id = ?");
$stmt->bind_param("si", $return_date, $id);
$stmt->execute();
$stmt->close();

// --- Cập nhật số lượng thiết bị ---
$stmt = $link->prepare("UPDATE add_device SET available_qty = available_qty + ? WHERE device_name = ?");
$stmt->bind_param("is", $quantity, $device_name);
$stmt->execute();
$stmt->close();

echo "<script>alert('✅ Trả thiết bị thành công!'); window.location='return_device.php';</script>";
exit;
?>
