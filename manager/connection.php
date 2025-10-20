<?php
//Cấu hình kết nối CSDL
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "quan_ly_thiet_bi";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $link = new mysqli($db_host, $db_user, $db_pass, $db_name);

    // Thiết lập charset UTF-8 để tránh lỗi tiếng Việt
    $link->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    // Ghi log lỗi
    error_log("Database connection error: " . $e->getMessage());
    // Hiển thị thông báo 
    die("Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
}
?>
