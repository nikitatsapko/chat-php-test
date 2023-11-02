<?php
declare(strict_types=1);

require 'vendor/autoload.php';

class System {
    function db() {
        return new mysqli(db_host, db_user, db_password, db_basename);
    }
    function remote_db($host, $user, $password, $basename) {
        return new mysqli($host, $user, $password, $basename);
    }
    function auth() {
        if (!isset($_COOKIE['id']) || !isset($_COOKIE['usid'])){
            return false;
        }
        $id = trim($_COOKIE['id']);
        $usid = trim($_COOKIE['usid']);
        $db = $this->db();
        $query = $db->query("SELECT * FROM `users_session` WHERE `id` = '$id' AND `usid` = '$usid'");
        $query2 = $db->query("SELECT * FROM `users` WHERE `id` = '$id'");
        $result = $query->fetch_assoc();
        $result2 = $query2->fetch_assoc();
        $usid_f = $result['usid'];
        if($usid !== $usid_f) {
            return false;
        }
        if($query->num_rows == 1 && $query2->num_rows == 0) {
            $db->query("DELETE FROM `users_session` WHERE `id`=". $id .";");
        }
        if ($query->num_rows == 1 && $query2->num_rows == 1)
            return true;
        else
            return false;
    }
    function userinfo($id = false) {
        if(!isset($_COOKIE['id']))
            return false;
        $db = $this->db();
        if (empty($id))
            $id = trim($_COOKIE['id']);
        $query = $db->query("SELECT * FROM `users` WHERE `id` = '$id'");
        return $query->num_rows == 1 ? $query->fetch_assoc() : false;
    }
    function printError($error) {
        include __DIR__ . "/template/errors/" . $error . '.php';
        die();
    }
}

function res($code, $text = false) {
    if ($text)
        exit(json_encode(["result" => $code, "text" => $text]));
    else
        exit(json_encode(["result" => $code]));
}

function Location($location) {
    header('Location: ' . $location);
    exit();
}

function RandomString($length) {
    $keys = array_merge(
        range(0,9),
        array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z')
    );

    $key = '';

    for($i=0; $i < $length; $i++) {
        $key .= $keys[mt_rand(0, count($keys) - 1)];
    }

    return $key;
}

function getNameRole($id) {
    switch($id) {
        case 0:
            return "Пользователь";
            break;
        case 1:
            return "Модератор";
            break;
        case 2:
            return "Администратор";
            break;
    }
}