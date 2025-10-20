<?php
session_start();

// --- Ngăn chặn session fixation ---
session_regenerate_id(true);

// --- Kiểm tra quyền truy cập ---
if (!isset($_SESSION["manager"])) {
    header("Location: login.php");
    exit;
}

require_once "connection.php";
require_once "header.php";

// --- Tạo CSRF token để xác thực thao tác nhạy cảm (duyệt/hủy xác thực) ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- page content area main -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>Danh sách người dùng</h3>
            </div>                    
        </div>

        <div class="clearfix"></div>

        <div class="row" style="min-height:500px">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Thông tin người dùng</h2>
                        <div class="clearfix"></div>
                    </div>

                    <div class="x_content">
                        <?php
                        // --- Lấy danh sách người dùng bằng Prepared Statement ---
                        $stmt = $link->prepare("SELECT id, firstname, lastname, username, email, contact, enrollment, status FROM user_registration ORDER BY id DESC");
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows === 0) {
                            echo "<div class='alert alert-info'>Không có người dùng nào trong hệ thống.</div>";
                        } else {
                            echo "<table class='table table-bordered'>";
                            echo "<thead  style=\"background-color:#2a3f54; color:white;\"><tr>
                                    <th>Họ</th>
                                    <th>Tên</th>
                                    <th>Tên đăng nhập</th>
                                    <th>Email</th>
                                    <th>Số điện thoại</th>
                                    <th>Mã người dùng</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                  </tr></thead><tbody>";

                            while ($row = $result->fetch_assoc()) {
                                $statusLabel = ($row["status"] === "no") ? "❌ Chưa xác thực" : "✅ Đã xác thực";
                                $id = intval($row["id"]);
                                $csrf = $_SESSION['csrf_token'];

                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row["firstname"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["lastname"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["username"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["contact"]) . "</td>";
                                echo "<td>" . htmlspecialchars($row["enrollment"]) . "</td>";
                                echo "<td>" . $statusLabel . "</td>";
                                echo "<td style='white-space:nowrap'>
                                        <a href='approve.php?id={$id}&csrf_token={$csrf}' class='btn btn-success btn-sm'>Duyệt</a>
                                        <a href='not_approve.php?id={$id}&csrf_token={$csrf}' class='btn btn-danger btn-sm'>Huỷ</a>
                                      </td>";
                                echo "</tr>";
                            }
                            echo "</tbody></table>";
                        }

                        $stmt->close();
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /page content -->

<?php require_once "footer.php"; ?>
