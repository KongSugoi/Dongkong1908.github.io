
<?php
$adminName = htmlspecialchars($_SESSION['admin'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhân viên | Hệ thống quản lý thiết bị</title>

    <!-- CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/nprogress.css">
    <link rel="stylesheet" href="css/custom.min.css">
</head>

<body class="nav-md">
<div class="container body">
    <div class="main_container">
        <!-- Sidebar -->
        <div class="col-md-3 left_col">
            <div class="left_col scroll-view">
                <div class="navbar nav_title" style="border: 0;">
                    <a href="#" class="site_title">
                        <i class="fa fa-book"></i> <span>Hệ thống QLTB</span>
                    </a>
                </div>

                <div class="clearfix"></div>

                <!-- Profile Info -->
                <div class="profile clearfix">
                    <div class="profile_pic">
                        <img src="images/img.jpg" alt="Avatar" class="img-circle profile_img">
                    </div>
                    <div class="profile_info">
                        <span>Chào mừng,</span>
                        <h2><?= $adminName ?></h2>
                    </div>
                </div>
                <!-- /Profile Info -->

                <br/>

                <!-- Sidebar Menu -->
                <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                    <div class="menu_section">
                        <h3>Tùy chọn</h3>
                        <ul class="nav side-menu">
                            <li><a href="add_device.php"><i class="fa fa-edit"></i> Thêm thiết bị</a></li>
                            <li><a href="display_device.php"><i class="fa fa-desktop"></i> Danh sách thiết bị</a></li>
                            <li><a href="issue_device.php"><i class="fa fa-table"></i> Danh sách mượn thiết bị</a></li>
                            <li><a href="return_device.php"><i class="fa fa-table"></i> Danh sách trả thiết bị</a></li>
                            <li><a href="device_details_with_user.php"><i class="fa fa-users"></i> Thiết bị theo người dùng</a></li>
                        </ul>
                    </div>
                </div>
                <!-- /Sidebar Menu -->
            </div>
        </div>
        <!-- /Sidebar -->

        <!-- Top Navigation -->
        <div class="top_nav">
            <div class="nav_menu">
                <nav>
                    <div class="nav toggle">
                        <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                    </div>

                    <ul class="nav navbar-nav navbar-right">
                        <li class="">
                            <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <img src="images/img.jpg" alt=""><?= $adminName ?>
                                <span class="fa fa-angle-down"></span>
                            </a>
                            <ul class="dropdown-menu dropdown-usermenu pull-right">
                                <li><a href="logout.php"><i class="fa fa-sign-out pull-right"></i> Đăng xuất</a></li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <!-- /Top Navigation -->