<?php

function main() {
    global $system, $system_user_id, $_user;
    if (!$system->auth())
        Location("/auth");
    $content = '../core/template/chat/chat.php';
    include '../core/template/default.php';
}

function login() {
    global $system, $system_user_id, $_user;
    if ($system->auth())
        Location("/");
    include '../core/template/auth/login.php';
}

function profile_password() {
    global $system, $system_user_id, $_user;
    if (!$system->auth())
        Location("/");
    $content = '../core/template/profile/password.php';
    include '../core/template/default.php';
}

function admin_users() {
    global $system, $system_user_id, $_user;
    if (!$system->auth() or $_user['user_type'] != 2)
        $system->printError(403);
    $db = $system->db();
    $query = $db->query("SELECT * FROM `users`;");
    $content = '../core/template/admin/users.php';
    include '../core/template/default.php';
}

function admin_users_edit($args) {
    global $system, $system_user_id, $_user;
    if (!$system->auth() or $_user['user_type'] != 2)
        $system->printError(403);
    $user_id = !empty(intval($args['id'])) ? intval($args['id']) : Location("/admin/users");
    if (!$user = $system->userinfo($user_id))
        Location("/admin/users");
    $content = '../core/template/admin/user.php';
    include '../core/template/default.php';
}

// ================ API ================ \\

function api_login() {
    global $system, $system_user_id, $_user;
    if ($system->auth())
        res(3);
    $db = $system->db();
    $db->set_charset("utf8");
    $login = $db->real_escape_string($_REQUEST['login']);
    $password = $db->real_escape_string($_REQUEST['password']);
    $query = $db->query("SELECT * FROM `users` WHERE `login` = '$login'");
    $result = $query->fetch_assoc();

    $id = $result['id'];
    if(!$id)
        res(0);
    if(!password_verify($password, $result['password']))
        res(0);

    $solt = bin2hex(openssl_random_pseudo_bytes(20, $cstrong));
    if($id != 0) {
        $query = $db->query("DELETE FROM `users_session` WHERE `id` = '$id'");
        $query = $db->query("INSERT INTO `users_session` (`id`, `usid`) VALUES ('$id', '$solt')");
        setcookie("id", $id, time()+(60*60*24*7), "/");
        setcookie("usid", $solt, time()+(60*60*24*7), "/");
    }
    res(1);
}

function logout() {
    global $system, $system_user_id, $_user;
    if (!$system->auth())
        Location("/");
    $db = $system->db();
    $db->set_charset("utf8");
    $id = trim($_COOKIE['id']);
    $usid = trim($_COOKIE['usid']);
    $db->query("DELETE FROM `users_session` WHERE `id` = '$id' AND `usid` = '$usid'");
    setcookie("id", $id, time()-1, "/");
    setcookie("usid", $solt, time()-1, "/");
    Location("/");
}

function api_moderation_ban() {
    global $system, $system_user_id, $_user;
    if (!$system->auth() or $_user['user_type'] < 1)
        res(0, "Нет прав");
    $db = $system->db();
    $login = $db->real_escape_string($_REQUEST['login']);
    $query = $db->query("SELECT * FROM `users` WHERE `login`='$login'");
    if ($query->num_rows != 1)
        res(0, "Ошибка в поиске пользователей");
    $result = $query->fetch_assoc();
    if ($result['blocked'] == 1)
        res(0, "Пользователь " . $login . " уже заблокирован.");
    $query = $db->query("UPDATE `users` SET `blocked` = '1' WHERE `users`.`login` = '$login'");
    res(1, "Пользователь " . $login . " успешно заблокирован!");
}

function api_users_edit() {
    global $system, $system_user_id, $_user;
    if (!$system->auth() or $_user['user_type'] < 2)
        res(0, "Нет прав");
    $user_id = !empty(intval($_POST['id'])) ? intval($_POST['id']) : res(0, "Ошибка");
    if (!$user = $system->userinfo($user_id))
        res(0, "Ошибка");
    if(!is_numeric($_POST['role']))
        res(4, "Выберите роль и попробуйте снова");
    $role = !empty(intval($_POST['role'])) || $_POST['role'] < 0 ? intval($_POST['role']) : 0;
    $user_role = $system->userinfo()['user_type'];
    $banned = !empty(intval($_POST['banned'])) ? intval($_POST['banned']) - 1 : res(0, "Ошибка");
    $db = $system->db();
    $db->set_charset("utf8");
    $login = $db->real_escape_string($_POST['login']);
    $password = $db->real_escape_string(trim($_POST['password']));
    if (empty($password))
        $db->query("UPDATE `users` SET `login` = '$login', `user_type` = '$role', `blocked` = '$banned' WHERE `id` = '$user_id'");
    else {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $db->query("UPDATE `users` SET `login` = '$login', `password` = '$password', `user_type` = '$role', `blocked` = '$banned' WHERE `id` = '$user_id'");
    }
    res(1, "Данные пользователя успешно обновлены");
}

function api_user_changepassword() {
    global $system, $system_user_id, $_user;
    if (!$system->auth())
        Location("/");
    if(empty($_REQUEST['password']))
        res(0, "No password error");
    $user_id = $_user['id'];
    $db = $system->db();
    $db->set_charset("utf8");
    $password = $db->real_escape_string(trim($_REQUEST['password']));
    $password = password_hash($password, PASSWORD_DEFAULT);
    $db->query("UPDATE `users` SET `password` = '$password' WHERE `id` = '$user_id'");
    $id = trim($_COOKIE['id']);
    $db->query("DELETE FROM `users_session` WHERE `id` = '$id'");
    setcookie("id", $id, time()-1, "/");
    setcookie("usid", 0, time()-1, "/");
    res(1, "Пароль успешно изменен! Переавторизируйтесь на сайте, текущая сессия закрыта.");
}