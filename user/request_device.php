<?php
session_start();
require_once "connection.php";

// --- Kiểm tra đăng nhập ---
if (empty($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}

// --- Lấy thông tin người dùng ---
$username = $_SESSION["username"];
$stmt = $link->prepare("SELECT * FROM user_registration WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// --- Tạo CSRF Token ---
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

include "header.php";
?>

<!-- page content area main -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>Yêu cầu mượn thiết bị</h3>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="row" style="min-height:500px">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Gửi yêu cầu mượn thiết bị</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <!-- Form gửi yêu cầu -->
                    <form action="" method="post">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>">

                        <div class="form-group">
                            <label>Chọn thiết bị muốn mượn</label>
                            <select name="device_name" class="form-control" required>
                                <option value="">-- Chọn thiết bị --</option>
                                <?php
                                $res = $link->query("SELECT device_name, available_qty FROM add_device WHERE available_qty > 0 ORDER BY device_name ASC");
                                while ($row = $res->fetch_assoc()):
                                ?>
                                    <option value="<?= htmlspecialchars($row['device_name']) ?>">
                                        <?= htmlspecialchars($row['device_name']) ?> (Còn lại: <?= intval($row['available_qty']) ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Số lượng muốn mượn</label>
                            <input type="number" name="quantity" class="form-control" placeholder="Nhập số lượng" min="1" required>
                        </div>

                        <div class="form-group">
                            <label>Ngày yêu cầu</label>
                            <input type="text" name="request_date" class="form-control" value="<?= date('Y-m-d') ?>" readonly>
                        </div>

                        <button type="submit" name="submit_request" class="btn btn-primary">
                            Gửi yêu cầu
                        </button>
                    </form>

                    <?php
                    if (isset($_POST["submit_request"])) {
                        // --- Xác thực CSRF Token ---
                        if (empty($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
                            echo "<div class='alert alert-danger mt-3'>Yêu cầu không hợp lệ (CSRF token sai).</div>";
                        } else {
                            // --- Lấy và kiểm tra dữ liệu đầu vào ---
                            $device_name = trim($_POST["device_name"]);
                            $quantity = intval($_POST["quantity"]);
                            $request_date = date('Y-m-d');

                            // Kiểm tra thiết bị còn không
                            $stmt = $link->prepare("SELECT available_qty FROM add_device WHERE device_name = ? LIMIT 1");
                            $stmt->bind_param("s", $device_name);
                            $stmt->execute();
                            $device = $stmt->get_result()->fetch_assoc();
                            $stmt->close();

                            if (!$device) {
                                echo "<div class='alert alert-danger mt-3'>Thiết bị không tồn tại!</div>";
                            } elseif ($quantity > $device["available_qty"]) {
                                echo "<div class='alert alert-danger mt-3'>Số lượng yêu cầu vượt quá số lượng có sẵn!</div>";
                            } else {
                                // Kiểm tra trùng yêu cầu đang chờ duyệt
                                $stmt = $link->prepare("
                                    SELECT id FROM issue_device 
                                    WHERE user_username = ? AND device_name = ? AND status = 'Chờ duyệt mượn'
                                ");
                                $stmt->bind_param("ss", $username, $device_name);
                                $stmt->execute();
                                $exists = $stmt->get_result()->num_rows > 0;
                                $stmt->close();

                                if ($exists) {
                                    echo "<div class='alert alert-warning mt-3'>Bạn đã gửi yêu cầu mượn thiết bị này, vui lòng chờ duyệt.</div>";
                                } else {
                                    // Ghi yêu cầu vào CSDL
                                    $stmt = $link->prepare("
                                        INSERT INTO issue_device 
                                        (user_enrollment, user_name, user_email, user_contact, device_name, device_issue_date, quantity, device_return_date, user_username, status)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, '', ?, 'Chờ duyệt mượn')
                                    ");
                                    $full_name = $user['firstname'] . " " . $user['lastname'];
                                    $stmt->bind_param("ssssssis", 
                                        $user['enrollment'], $full_name, $user['email'], $user['contact'],
                                        $device_name, $request_date, $quantity, $username
                                    );

                                    if ($stmt->execute()) {
                                        echo "<div class='alert alert-success mt-3'>Yêu cầu đã được gửi thành công! Vui lòng chờ admin duyệt.</div>";
                                    } else {
                                        echo "<div class='alert alert-danger mt-3'>Lỗi khi gửi yêu cầu: " . htmlspecialchars($stmt->error) . "</div>";
                                    }

                                    $stmt->close();
                                }
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /page content -->

<?php
$link->close();
include "footer.php";
?>
