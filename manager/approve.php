<?php
session_start();
require_once "connection.php";

if (!isset($_SESSION["manager"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"], $_GET["csrf_token"]) || $_GET["csrf_token"] !== $_SESSION["csrf_token"]) {
    die("Yêu cầu không hợp lệ (CSRF detected).");
}

$id = intval($_GET["id"]);

$stmt = $link->prepare("UPDATE user_registration SET status='yes' WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: display_user_info.php");
exit;
?>
