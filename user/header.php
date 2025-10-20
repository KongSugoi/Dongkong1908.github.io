<?php

$displayName = htmlspecialchars($_SESSION["username"], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Người dùng | Hệ thống quản lý thiết bị</title>


    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/nprogress.css" rel="stylesheet">
    <link href="css/custom.min.css" rel="stylesheet">
</head>

<body class="nav-md">
<div class="container body">
    <div class="main_container">
        
        <div class="col-md-3 left_col">
            <div class="left_col scroll-view">
                <div class="navbar nav_title" style="border: 0;">
                    <a href="#" class="site_title">
                        <i class="fa fa-book"></i> 
                        <span>Hệ thống QLTB</span>
                    </a>
                </div>

                <div class="clearfix"></div>
                
                <div class="profile clearfix">
                    <div class="profile_pic">
                        <img src="images/img.jpg" alt="Ảnh đại diện" class="img-circle profile_img">
                    </div>
                    <div class="profile_info">
                        <span>Chào mừng,</span>
                        <h2><?= $displayName ?></h2>
                    </div>
                </div>
                <br/>

                <!-- Menu sidebar -->
                <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                    <div class="menu_section">
                        <h3>Tùy chọn</h3>
                        <ul class="nav side-menu">
                            <li>
                                <a href="display_device.php">
                                    <i class="fa fa-desktop"></i> Danh sách thiết bị
                                </a>
                            </li>
                            <li>
                                <a href="my_issued_device.php">
                                    <i class="fa fa-list"></i> Thiết bị đã mượn
                                </a>
                            </li>
                            <li>
                                <a href="request_device.php">
                                    <i class="fa fa-plus-square"></i> Mượn thiết bị
                                </a>
                            </li>
                            <li>
                                <a href="return_device.php">
                                    <i class="fa fa-undo"></i> Trả thiết bị
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- /sidebar menu -->
            </div>
        </div>
        <!-- /Sidebar trái -->
        
        <!-- top navigation bar -->
        <div class="top_nav">
            <div class="nav_menu">
                <nav>
                    <div class="nav toggle">
                        <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                    </div>

                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <img src="images/img.jpg" alt=""><?= $displayName ?>
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
        <!-- /top navigation bar -->
