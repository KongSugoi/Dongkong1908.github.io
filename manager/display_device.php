<?php
session_start();
require "connection.php";

if (!isset($_SESSION["manager"])) {
    header("Location: login.php");
    exit;
}

$search = "";
if (isset($_POST["submit"])) {
    $search = trim($_POST["search"]);
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

<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>Trang chủ</h3>
            </div>
        </div>

        <div class="clearfix"></div>
        <div class="row" style="min-height:500px">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Danh sách thiết bị</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <form method="post">
                            <div class="form-group">
                                <label for="search">Tìm kiếm thiết bị:</label>
                                <input type="text" name="search" id="search" class="form-control"
                                    placeholder="Nhập tên thiết bị" value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <input type="submit" name="submit" value="Tìm" class="btn btn-default">
                        </form>

                        <table class="table table-bordered">
                            <thead  style="background-color:#2a3f54; color:white;">
                            <tr>
                                <th>Tên thiết bị</th>
                                <th>Hình ảnh</th>
                                <th>Tình trạng</th>
                                <th>Số lượng</th>
                                <th>Số lượng có sẵn</th>
                                <th>Nhân viên quản lý</th>                                
                            </tr>
                            </thead>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['device_name']) ?></td>
                                    <td><img src="<?= htmlspecialchars('../public_image/' . basename($row['device_image'])) ?>" width="120" height="120"></td>
                                    <td><?= htmlspecialchars($row['device_status']) ?></td>
                                    <td><?= htmlspecialchars($row['device_qty']) ?></td>
                                    <td><?= htmlspecialchars($row['available_qty']) ?></td>
                                    <td><?= htmlspecialchars($row['admin_username']) ?></td>                                    
                                </tr>
                            <?php endwhile; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
if (isset($stmt)) $stmt->close();
$link->close();
include "footer.php";
?>
