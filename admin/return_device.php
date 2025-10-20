<?php
// --- Khởi động session an toàn ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Tạo token CSRF nếu chưa có ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- Kiểm tra quyền admin ---
if (!isset($_SESSION['admin'])) {
    header("Location: ../user/login.php");
    exit;
}

require_once "connection.php";
include "header.php";
?>

<!-- page content area main -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>Duyệt yêu cầu trả thiết bị</h3>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="row" style="min-height:500px">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Danh sách yêu cầu trả thiết bị</h2>
                        <div class="clearfix"></div>
                    </div>

                    <div class="x_content">
                        <table class="table table-bordered table-striped">
                            <thead style="background-color:#2a3f54; color:white;">
                                <tr>   
                                    <th>Mã người dùng</th>                                 
                                    <th>Tên người dùng</th>
                                    <th>Tên thiết bị</th>
                                    <th>Số lượng</th>
                                    <th>Ngày mượn</th>
                                    <th>Ngày yêu cầu trả</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // --- Truy vấn an toàn ---
                                $status = 'Yêu cầu trả';
                                $stmt = $link->prepare("
                                    SELECT id, user_name, user_enrollment, device_name, quantity, device_issue_date, device_return_date 
                                    FROM issue_device 
                                    WHERE status = ?
                                    ORDER BY id DESC
                                ");
                                $stmt->bind_param("s", $status);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows === 0) {
                                    echo "<tr><td colspan='7' class='text-center text-muted'>Không có yêu cầu trả thiết bị nào.</td></tr>";
                                } else {
                                    while ($row = $result->fetch_assoc()) {
                                        // --- Escape dữ liệu chống XSS ---
                                        $id = (int)$row['id'];
                                        $enrollment = htmlspecialchars($row['user_enrollment'], ENT_QUOTES, 'UTF-8');
                                        $user_name = htmlspecialchars($row['user_name'], ENT_QUOTES, 'UTF-8');
                                        $device_name = htmlspecialchars($row['device_name'], ENT_QUOTES, 'UTF-8');
                                        $quantity = htmlspecialchars($row['quantity'], ENT_QUOTES, 'UTF-8');
                                        $issue_date = htmlspecialchars($row['device_issue_date'], ENT_QUOTES, 'UTF-8');
                                        $return_date = htmlspecialchars($row['device_return_date'], ENT_QUOTES, 'UTF-8');

                                        // --- Hiển thị từng dòng ---
                                        echo "<tr>
                                            <td>{$enrollment}</td>                                           
                                            <td>{$user_name}</td>
                                            <td>{$device_name}</td>
                                            <td>{$quantity}</td>
                                            <td>{$issue_date}</td>
                                            <td>{$return_date}</td>
                                            <td>
                                                <a href='approve_return.php?action=approve&id={$id}&token={$_SESSION['csrf_token']}' 
                                                   class='btn btn-success btn-sm' 
                                                   onclick='return confirm(\"Xác nhận duyệt yêu cầu trả thiết bị?\");'>
                                                   Duyệt
                                                </a>
                                                <a href='approve_return.php?action=reject&id={$id}&token={$_SESSION['csrf_token']}' 
                                                   class='btn btn-danger btn-sm' 
                                                   onclick='return confirm(\"Bạn có chắc muốn từ chối yêu cầu này?\");'>
                                                   Từ chối
                                                </a>
                                            </td>
                                        </tr>";
                                    }
                                }

                                $stmt->close();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>
