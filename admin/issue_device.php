<?php
session_start();
require_once "connection.php";
require_once "header.php";

// ======= TẠO CSRF TOKEN =======
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ======= KIỂM TRA QUYỀN ADMIN =======
if (empty($_SESSION["admin"])) {
    header("Location: ../user/login.php");
    exit;
}

// ======= XỬ LÝ DUYỆT / TỪ CHỐI =======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'], $_POST['csrf_token'])) {
    // Kiểm tra token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token không hợp lệ.");
    }

    $id = intval($_POST['id']);
    $action = $_POST['action'];

    // Lấy thông tin yêu cầu mượn
    $stmt = $link->prepare("SELECT * FROM issue_device WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();

    if ($row) {
        $device_name = $row['device_name'];
        $quantity = intval($row['quantity']);

        if ($action === 'approve') {
            // Lấy số lượng hiện có
            $stmt2 = $link->prepare("SELECT available_qty FROM add_device WHERE device_name = ?");
            $stmt2->bind_param("s", $device_name);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            $dev = $res2->fetch_assoc();

            if ($dev && $dev['available_qty'] >= $quantity) {
                // Cập nhật trạng thái và trừ số lượng
                $stmt3 = $link->prepare("UPDATE issue_device SET status='Đã duyệt mượn' WHERE id=?");
                $stmt3->bind_param("i", $id);
                $stmt3->execute();

                $stmt4 = $link->prepare("UPDATE add_device SET available_qty = available_qty - ? WHERE device_name=?");
                $stmt4->bind_param("is", $quantity, $device_name);
                $stmt4->execute();

                echo "<script>alert('✅ Đã duyệt yêu cầu mượn thiết bị thành công!'); window.location='issue_device.php';</script>";
            } else {
                echo "<script>alert('❌ Không đủ số lượng thiết bị để duyệt!'); window.location='issue_device.php';</script>";
            }
        } elseif ($action === 'reject') {
            $stmt5 = $link->prepare("UPDATE issue_device SET status='Từ chối cho mượn' WHERE id=?");
            $stmt5->bind_param("i", $id);
            $stmt5->execute();

            echo "<script>alert('🚫 Đã từ chối yêu cầu mượn thiết bị.'); window.location='issue_device.php';</script>";
        }
    }
}
?>

<!-- ========== GIAO DIỆN CHÍNH ========== -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>Duyệt yêu cầu mượn thiết bị</h3>
            </div>
        </div>

        <div class="clearfix"></div>
        <div class="row" style="min-height:500px">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Danh sách yêu cầu</h2>
                        <div class="clearfix"></div>
                    </div>

                    <div class="x_content">
                        <table class="table table-bordered table-striped">
                            <thead style="background-color:#2a3f54; color:white;">
                                <tr>                                    
                                    <th>Mã người dùng</th>
                                    <th>Tên người dùng</th>
                                    <th>Email</th>
                                    <th>Thiết bị</th>
                                    <th>Số lượng</th>
                                    <th>Ngày yêu cầu</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // 🔹 Chỉ lấy các yêu cầu có status = 'Chờ duyệt mượn'
                                $result = $link->query("
                                    SELECT * FROM issue_device 
                                    WHERE status = 'Chờ duyệt mượn' 
                                    ORDER BY id DESC
                                ");

                                if ($result->num_rows === 0) {
                                    echo "<tr><td colspan='8' class='text-center text-muted'>Không có yêu cầu mượn thiết bị nào.</td></tr>";
                                } else {
                                    while ($row = $result->fetch_assoc()):
                                        $status_label = "<span class='label label-warning'>Chờ duyệt mượn</span>";
                                ?>
                                    <tr>                                        
                                        <td><?= htmlspecialchars($row['user_enrollment']) ?></td>
                                        <td><?= htmlspecialchars($row['user_name']) ?></td>
                                        <td><?= htmlspecialchars($row['user_email']) ?></td>
                                        <td><?= htmlspecialchars($row['device_name']) ?></td>
                                        <td><?= htmlspecialchars($row['quantity']) ?></td>
                                        <td><?= htmlspecialchars($row['device_issue_date']) ?></td>
                                        <td><?= $status_label ?></td>
                                        <td>
                                            <form method="POST" style="display:inline-block">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Duyệt yêu cầu này?')">Duyệt</button>
                                            </form>

                                            <form method="POST" style="display:inline-block">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Từ chối yêu cầu này?')">Từ chối</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>
