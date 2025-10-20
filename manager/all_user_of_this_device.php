<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION["manager"])) {
    header("Location: login.php");
    exit;
}

require_once "connection.php";
require_once "header.php";
?>

<!-- page content area main -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>Danh sách người dùng mượn thiết bị</h3>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row" style="min-height: 500px;">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Danh sách người dùng mượn thiết bị</h2>
                        <div class="clearfix"></div>
                    </div>

                    <div class="x_content">
                        <?php
                        if (isset($_GET['device_name']) && !empty($_GET['device_name'])) {
                            $device_name = $_GET['device_name'];

                            // Truy vấn chỉ lấy các bản ghi có status thể hiện thiết bị vẫn đang được giữ
                            $stmt = $link->prepare("
                                SELECT user_name, user_enrollment, user_email, user_contact, 
                                       device_name, quantity, device_issue_date, status
                                FROM issue_device
                                WHERE device_name = ?
                                  AND (status = 'Đã duyệt mượn' 
                                       OR status = 'Yêu cầu trả' 
                                       OR status = 'Từ chối cho trả')
                            ");
                            $stmt->bind_param("s", $device_name);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                echo "<table class='table table-bordered'>";
                                echo "<thead style='background-color:#2a3f54; color:white;'>
                                        <tr>
                                            <th>Tên người dùng</th>
                                            <th>Mã người dùng</th>
                                            <th>Email</th>
                                            <th>Số điện thoại</th>
                                            <th>Tên thiết bị</th>
                                            <th>Số lượng</th>
                                            <th>Ngày mượn</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                      </thead>";
                                echo "<tbody>";

                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row["user_name"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["user_enrollment"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["user_email"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["user_contact"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["device_name"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["quantity"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["device_issue_date"]) . "</td>";
                                    echo "<td><span class='label label-info'>" . htmlspecialchars($row["status"]) . "</span></td>";
                                    echo "</tr>";
                                }

                                echo "</tbody></table>";
                            } else {
                                echo "<div class='alert alert-info'>Không có người dùng nào đang mượn thiết bị này.</div>";
                            }

                            $stmt->close();
                        } else {
                            echo "<div class='alert alert-warning'>Thiếu tham số tên thiết bị.</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /page content -->

<?php include "footer.php"; ?>
