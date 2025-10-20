<?php
session_start();
require_once "connection.php";
require_once "header.php";


// --- Kiểm tra quyền truy cập ---
if (!isset($_SESSION["manager"])) {
    header("Location: login.php");
    exit;
}

// --- Lấy danh sách thiết bị ---
$stmt = $link->prepare("SELECT device_name, device_image, device_qty, available_qty FROM add_device");
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- page content area main -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>Thông tin thiết bị</h3>
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
                        <?php if ($result->num_rows === 0): ?>
                            <div class="alert alert-info">Hiện chưa có thiết bị nào trong hệ thống.</div>
                        <?php else: ?>
                            <table class="table table-bordered text-center">
                                <tr>
                                    <?php
                                    $i = 0;
                                    while ($row = $result->fetch_assoc()):
                                        $i++;
                                        $device_name = htmlspecialchars($row["device_name"]);
                                        $device_qty = intval($row["device_qty"]);
                                        $available_qty = intval($row["available_qty"]);
                                        $device_image = htmlspecialchars($row["device_image"]);
                                    ?>
                                        <td style="vertical-align:top;">
                                            <img src="<?php echo '../public_image/' . $device_image; ?>" 
                                                 alt="<?php echo $device_name; ?>" 
                                                 height="100" width="100"
                                                 style="object-fit:cover; border-radius:8px;">
                                            <br>
                                            <b><?php echo $device_name; ?></b><br>
                                            <b>Số lượng: <?php echo $device_qty; ?></b><br>
                                            <b>Khả dụng: <?php echo $available_qty; ?></b><br>
                                            <a href="all_user_of_this_device.php?device_name=<?php echo urlencode($device_name); ?>" 
                                               class="btn btn-link text-danger" 
                                               style="font-weight:bold;">Người dùng đang sử dụng thiết bị</a>
                                        </td>
                                    <?php
                                        if ($i % 4 == 0) echo "</tr><tr>";
                                    endwhile;
                                    ?>
                                </tr>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /page content -->

<?php
$stmt->close();
require_once "footer.php";
?>


Warning: session_regenerate_id(): Session ID cannot be regenerated after headers have already been sent in D:\Xampp\htdocs\qltb\manager\device_details_with_user.php on line 7
