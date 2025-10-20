<?php
session_start();
require_once "connection.php";

// Bắt buộc đăng nhập admin
if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

// Khởi tạo CSRF token nếu chưa có
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Lấy id thiết bị và kiểm tra hợp lệ
$id = $_GET["id"] ?? null;
$id = filter_var($id, FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    $_SESSION["flash_error"] = "ID thiết bị không hợp lệ.";
    header("Location: display_device.php");
    exit;
}

// Lấy thông tin thiết bị từ DB
$stmt = $link->prepare("SELECT * FROM add_device WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $_SESSION["flash_error"] = "Không tìm thấy thiết bị.";
    header("Location: display_device.php");
    exit;
}
$row = $result->fetch_assoc();
$stmt->close();

include "header.php";
?>

<!-- page content area main -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>Chỉnh sửa thông tin thiết bị</h3>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
    <div class="row" style="min-height:500px">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Thông tin thiết bị</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <form method="post" enctype="multipart/form-data" class="col-lg-6">

                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                        <div class="form-group">
                            <label>Tên thiết bị</label>
                            <input type="text" name="device_name" class="form-control"
                                   value="<?= htmlspecialchars($row['device_name']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Hình ảnh hiện tại</label><br>
                            <img src="<?= htmlspecialchars('../public_image/' . basename($row['device_image'])) ?>"
                                 width="120" height="120" alt="Hình thiết bị"><br><br>
                            <label>Thay ảnh mới (nếu có)</label>
                            <input type="file" name="device_image" class="form-control"
                                   accept=".jpg,.jpeg,.png,.gif">
                        </div>

                        <div class="form-group">
                            <label>Tình trạng thiết bị</label>
                            <input type="text" name="device_status" class="form-control"
                                   value="<?= htmlspecialchars($row['device_status']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Số lượng thiết bị</label>
                            <input type="number" name="device_qty" class="form-control"
                                   value="<?= htmlspecialchars($row['device_qty']) ?>" required min="0">
                        </div>

                        <div class="form-group">
                            <label>Số lượng thiết bị có sẵn</label>
                            <input type="number" name="available_qty" class="form-control"
                                   value="<?= htmlspecialchars($row['available_qty']) ?>" required min="0">
                        </div>

                        <div class="form-group">
                            <label>Nhân viên quản lý</label>
                            <input type="text" name="admin_username" class="form-control"
                                   value="<?= htmlspecialchars($row['admin_username']) ?>" readonly>
                        </div>

                        <button type="submit" name="submit1" class="btn btn-success">Cập nhật thông tin</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /page content -->

<?php
// Khi người dùng submit form
if (isset($_POST["submit1"])) {

    // Kiểm tra CSRF token
    $token = $_POST["csrf_token"] ?? '';
    if (empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $_SESSION['flash_error'] = "Yêu cầu không hợp lệ (CSRF).";
        header("Location: display_device.php");
        exit;
    }

    // Lấy dữ liệu input
    $device_name    = trim($_POST['device_name']);
    $device_status  = trim($_POST['device_status']);
    $device_qty     = (int)$_POST['device_qty'];
    $available_qty  = (int)$_POST['available_qty'];

    // Kiểm tra upload ảnh mới (nếu có)
    $update_image = false;
    $dst_db = $row['device_image']; // mặc định giữ ảnh cũ

    if (!empty($_FILES['device_image']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($_FILES['device_image']['tmp_name']);
        $file_size = $_FILES['device_image']['size'];

        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['flash_error'] = "Định dạng ảnh không hợp lệ.";
            header("Location: edit_device.php?id=" . urlencode($id));
            exit;
        }

        if ($file_size > 2 * 1024 * 1024) { // Giới hạn 2MB
            $_SESSION['flash_error'] = "Kích thước ảnh quá lớn (tối đa 2MB).";
            header("Location: edit_device.php?id=" . urlencode($id));
            exit;
        }

        $v3 = md5(uniqid((string)rand(), true));
        $fnm = basename($_FILES['device_image']['name']);
        $dst = "../public_image/" . $v3 . "_" . $fnm;
        if (move_uploaded_file($_FILES['device_image']['tmp_name'], $dst)) {
            $dst_db = $dst;
            $update_image = true;
        } else {
            $_SESSION['flash_error'] = "Tải ảnh lên thất bại.";
            header("Location: edit_device.php?id=" . urlencode($id));
            exit;
        }
    }

    // Cập nhật DB
    if ($update_image) {
        $stmt = $link->prepare("UPDATE add_device 
                                SET device_name=?, device_image=?, device_status=?, device_qty=?, available_qty=? 
                                WHERE id=?");
        $stmt->bind_param("sssiii", $device_name, $dst_db, $device_status, $device_qty, $available_qty, $id);
    } else {
        $stmt = $link->prepare("UPDATE add_device 
                                SET device_name=?, device_status=?, device_qty=?, available_qty=? 
                                WHERE id=?");
        $stmt->bind_param("ssiii", $device_name, $device_status, $device_qty, $available_qty, $id);
    }

    if ($stmt->execute()) {
        $_SESSION['flash_success'] = "Cập nhật thiết bị thành công!";
    } else {
        $_SESSION['flash_error'] = "Cập nhật thất bại: " . $stmt->error;
    }

    $stmt->close();
   echo "<script>alert('Cập nhật thông tin thiết bị thành công!'); window.location='display_device.php';</script>";
exit;
}

include "footer.php";
?>
