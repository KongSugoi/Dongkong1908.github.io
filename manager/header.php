<?php

// Lấy tên người quản lý an toàn
$managerName = htmlspecialchars($_SESSION["manager"], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Quản lý | Hệ thống quản lý thiết bị</title>

    <!-- Bootstrap & CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/nprogress.css" rel="stylesheet">
    <link href="css/custom.min.css" rel="stylesheet">
</head>

<body class="nav-md">
<div class="container body">
    <div class="main_container">

        <!-- Sidebar trái -->
        <div class="col-md-3 left_col">
            <div class="left_col scroll-view">
                
                <!-- Logo hệ thống -->
                <div class="navbar nav_title" style="border: 0;">
                    <a href="index.php" class="site_title">
                        <i class="fa fa-book"></i> <span>Hệ thống QLTB</span>
                    </a>
                </div>

                <div class="clearfix"></div>

                <!-- Thông tin người dùng -->
                <div class="profile clearfix">
                    <div class="profile_pic">
                        <img src="images/img.jpg" alt="Ảnh đại diện" class="img-circle profile_img">
                    </div>
                    <div class="profile_info">
                        <span>Chào mừng,</span>
                        <h2><?= $managerName ?></h2>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <!-- /Thông tin người dùng -->

                <br/>

                <!-- Menu sidebar -->
                <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                    <div class="menu_section">
                        <h3>Tuỳ chọn</h3>
                        <ul class="nav side-menu">
                            <li>
                                <a href="display_user_info.php">
                                    <i class="fa fa-users"></i> Danh sách người dùng
                                </a>
                            </li>
                            <li>
                                <a href="display_device.php">
                                    <i class="fa fa-desktop"></i> Danh sách thiết bị
                                </a>
                            </li>
                            <li>
                                <a href="device_details_with_user.php">
                                    <i class="fa fa-table"></i> Thiết bị theo người dùng
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- /Menu sidebar -->
            </div>
        </div>
        <!-- /Sidebar trái -->

        <!-- Thanh top navigation -->
        <div class="top_nav">
            <div class="nav_menu">
                <nav>
                    <div class="nav toggle">
                        <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                    </div>

                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <img src="images/img.jpg" alt="Ảnh đại diện"> <?= $managerName ?>
                                <span class="fa fa-angle-down"></span>
                            </a>
                            <ul class="dropdown-menu dropdown-usermenu pull-right">
                                <li>
                                    <a href="logout.php">
                                        <i class="fa fa-sign-out pull-right"></i> Đăng xuất
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <!-- /Thanh top navigation -->
