<?php
// Bật session với cấu hình cookie an toàn
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$httponly = true;
$samesite = 'Lax'; // hoặc 'Strict' nếu phù hợp
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',      // để mặc định; chỉnh nếu cần
    'secure' => $secure,
    'httponly' => $httponly,
    'samesite' => $samesite
]);
session_start();

require_once "connection.php"; 
mysqli_set_charset($link, "utf8");

// Cấu hình giới hạn thử (basic, dựa trên session)
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['first_attempt_time'] = time();
}
// reset attempts sau 15 phút
if (time() - ($_SESSION['first_attempt_time'] ?? 0) > 120) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['first_attempt_time'] = time();
}

$error = null;
$blocked = false;
if ($_SESSION['login_attempts'] >= 5) {
    $blocked = true;
    $error = "Quá nhiều lần thử. Vui lòng thử lại sau 2 phút.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit1']) && !$blocked) {
    // Lấy và làm sạch input
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Kiểm tra sơ bộ
    if ($username === '' || $password === '') {
        $error = "Vui lòng nhập tên đăng nhập và mật khẩu.";
    } else {
        // Prepared statement: lấy hashed password và status cho username
        $stmt = $link->prepare("SELECT id, password, status FROM manager_registration WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($user_id, $db_password_hash, $status);
                $stmt->fetch();

                // Kiểm tra tài khoản đã được kích hoạt (status = 'yes')
                if ($status !== 'yes') {
                    // Không cho biết chính xác là chưa kích hoạt hay username sai
                    $error = "Không hợp lệ: Sai tên đăng nhập hoặc mật khẩu hoặc tài khoản chưa được cấp quyền.";
                    $_SESSION['login_attempts']++;
                } else {
                    // So khớp password
                   if (password_verify($password, $db_password_hash)) {
                    // Đăng nhập thành công

                    // Kiểm tra xem có cần rehash lại không (khi PHP cập nhật thuật toán)
                    if (password_needs_rehash($db_password_hash, PASSWORD_DEFAULT)) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);

                        $rehashStmt = $link->prepare("UPDATE manager_registration SET password = ? WHERE id = ?");
                        if ($rehashStmt) {
                            $rehashStmt->bind_param("si", $newHash, $user_id);
                            $rehashStmt->execute();
                            $rehashStmt->close();
                        }
                    }

                        // Đăng nhập thành công: bảo mật session
                        session_regenerate_id(true);
                        // Lưu thông tin cơ bản vào session (không lưu mật khẩu)
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['manager'] = $username;
                        // reset attempts
                        $_SESSION['login_attempts'] = 0;
                        $_SESSION['first_attempt_time'] = time();

                        // Chuyển hướng an toàn
                        header("Location: plain_page.php");
                        exit;
                    } else {
                        $error = "Không hợp lệ: Sai tên đăng nhập hoặc mật khẩu hoặc tài khoản chưa được cấp quyền.";
                        $_SESSION['login_attempts']++;
                    }
                }
            } else {
                // Không tiết lộ user không tồn tại
                $error = "Không hợp lệ: Sai tên đăng nhập hoặc mật khẩu hoặc tài khoản chưa được cấp quyền.";
                $_SESSION['login_attempts']++;
            }
            $stmt->close();
        } else {
            // Không hiển thị lỗi DB cho user
            $error = "Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau.";
            // ở log server: error_log("Prepare failed: " . $link->error);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý đăng nhập</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/animate.min.css" rel="stylesheet">
    <link href="css/custom.min.css" rel="stylesheet">
</head>
<body class="login">

<div class="col-lg-12 text-center">
    <h1 style="font-family:Lucida Console">Hệ thống quản lý thiết bị</h1>
</div>

<div class="login_wrapper">
    <section class="login_content">
        <form method="post" autocomplete="off" novalidate>
            <h1>Quản lý đăng nhập</h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger col-lg-6 col-lg-push-3">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div>
                <input type="text" name="username" class="form-control" placeholder="Tên đăng nhập" required />
            </div>
            <div>
                <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required />
            </div>
            <div>
                <input class="btn btn-default submit" type="submit" name="submit1" value="Đăng nhập" <?= $blocked ? 'disabled' : '' ?>>
                <a class="reset_pass" href="#">Quên mật khẩu?</a>
            </div>

            <div class="clearfix"></div>
`    
        </form>
    </section>
</div>

</body>
</html>
