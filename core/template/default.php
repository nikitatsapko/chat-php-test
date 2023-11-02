<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Online Chat</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/dashboard.css?ver=2">
    <meta name="viewport" content="initial-scale=1, width=device-width, maximum-scale=1, minimum-scale=1, user-scalable=no">
    <script src="/assets/js/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/assets/css/jquery.datetimepicker.css">
    <script src="/assets/js/jquery.datetimepicker.full.min.js"></script>
    <script src="/assets/js/sweetalert2.js"></script>
    <script src="https://www.kryogenix.org/code/browser/sorttable/sorttable.js"></script>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/chat.css">
    <link rel="SHORTCUT ICON" href="/assets/img/logo_logs_notepad.ico" type="image/x-icon">
    <link rel="stylesheet" href="/assets/css/ips_framework.css" media="all">
    <link rel="stylesheet" href="/assets/css/ips_core.css" media="all">
    <!--<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">-->
</head>
<body>
<div class="wrapper">
    <header>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="menu" style="cursor: pointer;">
                        <a href="/" class="menu-btn logo">
                            <span><img src="/assets/img/logo.png"/></span>
                            <p></p>
                        </a>
                    </div>
                </div>
                <div class="col-12">
                    <a></a>
                    <div class="profile">
                        <p class="username" onclick="profileUi();"><?php echo $_user['login'];?></p>
                        <div class="avatar" onclick="profileUi();">
                            <img src="<?php echo $_user['avatar'];?>"/>
                        </div>
                        <div class="box">
                            <p class="box-title">Меню</p>
                            <?php
                                echo '<a href="/profile/password" class="nav-link">Изменить пароль</a>';
                                if ($_user['user_type'] == 2)
                                    echo '<a href="/admin/users" class="nav-link">Управление пользователями</a>';
                                echo '<hr>';
                            ?>
                            <a href="/logout" class="nav-link">Выход</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <main>
        <?php include $content;?>
    </main>
    <footer>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <!--<b>По тех. вопросам — <a href="https://vk.com/nightday13">Nikita Tsapko</a>.</b>-->
                </div>
            </div>
        </div>
    </footer>
</div>
<script>
    function profileUi(){
        if($('header .profile .box').hasClass('active')){
            $('header .profile .box').removeClass('active');
        } else {
            $('header .profile .box').addClass('active');
        }
    }
</script>
</body>
</html>
