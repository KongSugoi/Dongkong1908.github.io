<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

require_once "connection.php";
require_once "header.php";

//  Tạo CSRF token nếu chưa có
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- page content area main -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>Trang chủ</h3>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
    <div class="row" style="min-height:500px">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Thêm thiết bị</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <table class="table table-bordered">
                        <tr>
                            <td>
                                <form name="form1" action="" method="post" class="col-lg-6" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label>Nhập tên thiết bị</label>
                                        <input type="text" name="device_name" class="form-control" 
                                               placeholder="Nhập tên thiết bị" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Hình ảnh thiết bị</label>
                                        <input type="file" name="device_image" class="form-control" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Tình trạng thiết bị</label>
                                        <input type="text" name="device_status" class="form-control" 
                                               placeholder="Nhập tình trạng thiết bị" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Số lượng thiết bị</label>
                                        <input type="number" name="device_qty" class="form-control" 
                                               placeholder="Nhập số lượng thiết bị" required min="1">
                                    </div>

                                    <div class="form-group">
                                        <label>Số lượng thiết bị có sẵn</label>
                                        <input type="number" name="available_qty" class="form-control" 
                                               placeholder="Nhập số lượng thiết bị có sẵn" required min="0">
                                    </div>

                                    <div class="form-group">
                                        <label>Nhân viên quản lý</label>
                                        <input type="text" name="admin_username" class="form-control" 
                                               value="<?php echo htmlspecialchars($_SESSION['admin'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                    </div>

                                    <!--  CSRF Token -->
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                                    <input type="submit" name="submit1" value="Thêm thiết bị" 
                                           class="btn btn-default" style="background-color: #2a3f54; color: white;">
                                </form>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /page content -->

<?php
// ✅ Xử lý POST dữ liệu an toàn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["submit1"])) {

    // ✅ Kiểm tra CSRF Token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("<script>alert('CSRF token không hợp lệ!'); window.location='add_device.php';</script>");
    }

    // ✅ Lọc và kiểm tra dữ liệu nhập
    $device_name     = trim($_POST['device_name']);
    $device_status   = trim($_POST['device_status']);
    $device_qty      = (int)$_POST['device_qty'];
    $available_qty   = (int)$_POST['available_qty'];
    $admin_username  = $_SESSION['admin'];

    // ✅ Kiểm tra dữ liệu hợp lệ cơ bản
    if ($device_qty < 1 || $available_qty < 0) {
        echo "<script>alert('Số lượng không hợp lệ!'); window.location='add_device.php';</script>";
        exit;
    }

    // ✅ Xử lý upload file an toàn
    if (isset($_FILES["device_image"]) && $_FILES["device_image"]["error"] === UPLOAD_ERR_OK) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($_FILES["device_image"]["name"], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_ext)) {
            echo "<script>alert('Chỉ cho phép upload file ảnh (jpg, png, gif)!'); window.location='add_device.php';</script>";
            exit;
        }

        $unique_name = md5(uniqid(rand(), true)) . "." . $file_ext;
        $upload_path = "../public_image/" . $unique_name;

        if (!move_uploaded_file($_FILES["device_image"]["tmp_name"], $upload_path)) {
            echo "<script>alert('Không thể upload ảnh.'); window.location='add_device.php';</script>";
            exit;
        }

        // ✅ Thêm dữ liệu vào DB bằng Prepared Statement (chống SQLi)
        $stmt = $link->prepare("
            INSERT INTO add_device (device_name, device_image, device_status, device_qty, available_qty, admin_username)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        if ($stmt) {
            $stmt->bind_param("sssdds", 
                $device_name, 
                $upload_path, 
                $device_status, 
                $device_qty, 
                $available_qty, 
                $admin_username
            );
            $stmt->execute();
            $stmt->close();

            echo "<script>alert('Thiết bị đã được thêm thành công!'); window.location='add_device.php';</script>";
        } else {
            echo "<script>alert('Lỗi kết nối cơ sở dữ liệu.'); window.location='add_device.php';</script>";
        }
    } else {
        echo "<script>alert('Vui lòng chọn hình ảnh hợp lệ.'); window.location='add_device.php';</script>";
    }
}
?>

<?php include "footer.php"; ?>
