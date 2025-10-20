<?php
session_start();
require_once "connection.php";

if (!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}

// Tạo CSRF token nếu chưa có
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Flash messages (nếu có)
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Xử lý tìm kiếm an toàn
$search = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit_search"])) {
    $search = trim($_POST["search"] ?? "");
    $stmt = $link->prepare("SELECT * FROM add_device WHERE device_name LIKE ?");
    $searchTerm = "%$search%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $link->query("SELECT * FROM add_device");
}

include "header.php";
?>
<!-- page content area main -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left"><h3>Trang chủ</h3></div>
        </div>

        <div class="clearfix"></div>
        <div class="row" style="min-height:500px">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title"><h2>Danh sách thiết bị</h2><div class="clearfix"></div></div>
                    <div class="x_content">

                        <!-- Hiển thị flash -->
                        <?php if ($flash_success): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($flash_success) ?></div>
                        <?php endif; ?>
                        <?php if ($flash_error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($flash_error) ?></div>
                        <?php endif; ?>

                        <form action="" method="post" class="form-inline mb-3">
                            <div class="form-group">
                                <label for="search" class="mr-2">Tìm kiếm thiết bị:</label>
                                <input type="text" name="search" id="search" class="form-control"
                                       placeholder="Nhập tên thiết bị" value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <button type="submit" name="submit_search" class="btn btn-default ml-2">Tìm</button>
                        </form>

                        <table class="table table-bordered table-hover">
                            <thead style="background-color:#2a3f54; color:white;">
                                <tr>
                                    <th>Tên thiết bị</th>
                                    <th>Hình ảnh</th>
                                    <th>Tình trạng</th>
                                    <th>Số lượng</th>
                                    <th>Số lượng có sẵn</th>
                                    <th>Nhân viên quản lý</th>
                                    <th>Xoá thiết bị</th>
                                    <th>Chỉnh sửa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['device_name']) ?></td>
                                            <td>
                                                <img src="<?= htmlspecialchars('../public_image/' . basename($row['device_image'])) ?>"
                                                     width="120" height="120" alt="Hình ảnh thiết bị">
                                            </td>
                                            <td><?= htmlspecialchars($row['device_status']) ?></td>
                                            <td><?= htmlspecialchars($row['device_qty']) ?></td>
                                            <td><?= htmlspecialchars($row['available_qty']) ?></td>
                                            <td><?= htmlspecialchars($row['admin_username']) ?></td>
                                            <td>
                                                <!-- Form POST kèm CSRF token -->
                                                <form method="post" action="delete_device.php" style="display:inline"
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn xoá thiết bị này?');">
                                                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Xoá</button>
                                                </form>
                                            </td>
                                            <td>
                                                <a href="edit_device.php?id=<?= urlencode($row['id']) ?>"
                                                   class="btn btn-primary btn-sm">Chỉnh sửa</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" class="text-center">Không tìm thấy thiết bị nào.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /page content -->

<?php
if (isset($stmt)) $stmt->close();
$link->close();
include "footer.php";
?>
