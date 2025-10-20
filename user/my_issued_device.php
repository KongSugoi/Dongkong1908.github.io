<?php
session_start();
require_once "connection.php";

// --- Kiểm tra người dùng đã đăng nhập ---
if (empty($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}

include "header.php";

// --- Lấy danh sách thiết bị đã mượn của người dùng ---
$username = $_SESSION["username"];

$stmt = $link->prepare("
    SELECT user_name, device_name, quantity, device_issue_date, device_return_date, status 
    FROM issue_device 
    WHERE user_username = ?
    ORDER BY device_issue_date DESC
");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- page content area main -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>Thiết bị đã mượn</h3>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="row" style="min-height:500px">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Danh sách thiết bị mà bạn đã mượn</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <?php if ($result->num_rows > 0): ?>
                        <table class="table table-bordered table-striped">
                            <thead  style="background-color:#2a3f54; color:white;">
                                <tr>
                                    <th>Tên người dùng</th>
                                    <th>Tên thiết bị</th>
                                    <th>Số lượng</th>
                                    <th>Ngày đăng ký mượn</th>
                                    <th>Ngày trả thiết bị</th>
                                    <th>Tình trạng duyệt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row["user_name"]) ?></td>
                                        <td><?= htmlspecialchars($row["device_name"]) ?></td>
                                        <td><?= htmlspecialchars($row["quantity"]) ?></td>
                                        <td><?= htmlspecialchars($row["device_issue_date"]) ?></td>
                                        <td>
                                            <?= $row["device_return_date"] 
                                                ? htmlspecialchars($row["device_return_date"]) 
                                                : "<span class='text-muted'>Chưa trả</span>" ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status = $row["status"];
                                            if (empty($status)) {
                                                echo "<span class='label label-default'>Chờ duyệt</span>";
                                            } elseif ($status === "Đã duyệt mượn") {
                                                echo "<span class='label label-success'>Đã duyệt mượn</span>";
                                            } elseif ($status === "Từ chối cho mượn") {
                                                echo "<span class='label label-danger'>Từ chối cho mượn</span>";
                                            } else {
                                                echo "<span class='label label-info'>" . htmlspecialchars($status) . "</span>";
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Bạn chưa đăng ký mượn thiết bị nào.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /page content -->

<?php
$stmt->close();
$link->close();
include "footer.php";
?>
