<?php
session_start();

// --- Ngăn chặn session fixation ---
session_regenerate_id(true);

// --- Kiểm tra đăng nhập ---
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}

require_once "connection.php";
require_once "header.php";

// --- Tạo và kiểm tra CSRF token ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- page content area main -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>Gửi yêu cầu trả thiết bị</h3>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="row" style="min-height:500px">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Danh sách thiết bị đang mượn hoặc bị từ chối trả</h2>
                        <div class="clearfix"></div>
                    </div>

                    <div class="x_content">
                        <form method="post" autocomplete="off">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                            <table class="table table-bordered">
                                <thead  style="background-color:#2a3f54; color:white;">
                                <tr>
                                    <th>Chọn</th>
                                    <th>Tên thiết bị</th>
                                    <th>Số lượng</th>
                                    <th>Ngày mượn</th>
                                    <th>Trạng thái</th>
                                </tr>
                                </thead>
                                <?php
                                $username = mysqli_real_escape_string($link, $_SESSION["username"]);

                                // ✅ Lấy thiết bị Đã duyệt mượn hoặc Từ chối cho trả (chưa được trả xong)
                                $stmt = $link->prepare("
                                                SELECT id, device_name, quantity, device_issue_date, status 
                                                FROM issue_device 
                                                WHERE user_username = ? 
                                                AND (
                                                    (status = 'Đã duyệt mượn' AND (device_return_date IS NULL OR device_return_date = ''))
                                                    OR status = 'Từ chối cho trả'
                                                )
                                            ");
                                $stmt->bind_param("s", $username);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows === 0) {
                                    echo "<tr><td colspan='5'>Bạn không có thiết bị nào đang mượn hoặc bị từ chối trả.</td></tr>";
                                } else {
                                    while ($row = $result->fetch_assoc()) {
                                        $safeName = htmlspecialchars($row['device_name']);
                                        $safeStatus = htmlspecialchars($row['status']);
                                        $statusLabel = '';

                                        // Màu trạng thái hiển thị dễ nhìn hơn
                                        if ($row['status'] === 'Đã duyệt mượn') {
                                            $statusLabel = "<span class='label label-success'>Đã duyệt mượn</span>";
                                        } elseif ($row['status'] === 'Từ chối cho trả') {
                                            $statusLabel = "<span class='label label-danger'>Từ chối cho trả</span>";
                                        } else {
                                            $statusLabel = "<span class='label label-default'>{$safeStatus}</span>";
                                        }

                                        echo "<tr>";
                                        echo "<td><input type='radio' name='device_id' value='".intval($row['id'])."'></td>";
                                        echo "<td>{$safeName}</td>";
                                        echo "<td>".intval($row['quantity'])."</td>";
                                        echo "<td>".htmlspecialchars($row['device_issue_date'])."</td>";
                                        echo "<td>{$statusLabel}</td>";
                                        echo "</tr>";
                                    }
                                }
                                $stmt->close();
                                ?>
                            </table>

                            <input type="submit" name="submit" value="Gửi yêu cầu trả thiết bị" class="btn btn-primary">
                        </form>

                        <?php
                        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {
                            // --- Kiểm tra CSRF token ---
                            if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
                                echo '<div class="alert alert-danger">Yêu cầu không hợp lệ (CSRF detected).</div>';
                                exit;
                            }

                            // --- Kiểm tra người dùng có chọn thiết bị hay chưa ---
                            if (empty($_POST["device_id"])) {
                                echo '<div class="alert alert-warning" style="margin-top:10px;">Vui lòng chọn một thiết bị để gửi yêu cầu trả.</div>';
                            } else {
                                $device_id = intval($_POST["device_id"]);
                                $device_return_date = date("Y-m-d");

                                // --- Cập nhật trạng thái nếu thiết bị hợp lệ ---
                                $stmt = $link->prepare("
                                    UPDATE issue_device 
                                    SET status='Yêu cầu trả', device_return_date=? 
                                    WHERE id=? AND user_username=? AND (status='Đã duyệt mượn' OR status='Từ chối cho trả')
                                ");
                                $stmt->bind_param("sis", $device_return_date, $device_id, $username);

                                if ($stmt->execute() && $stmt->affected_rows > 0) {
                                    echo '<div class="alert alert-success" style="margin-top:10px;">Yêu cầu trả thiết bị đã được gửi thành công. Vui lòng chờ admin duyệt.</div>';
                                } else {
                                    echo '<div class="alert alert-warning" style="margin-top:10px;">Không thể gửi yêu cầu. Có thể thiết bị đã được trả hoặc không hợp lệ.</div>';
                                }
                                $stmt->close();
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>
