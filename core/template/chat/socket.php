<?php

// socket library from https://github.com/iabhinavr/php-socket-chat/blob/master/app/public/functions.php

$address = '0.0.0.0';
$port = 8920;
$null = NULL;

include 'functions.php';
include '../../config.php';
include '../../functions.php';

$system = new System();

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($sock, $address, $port);
socket_listen($sock);

$members = [];
$connections = [];
$connections[] = $sock;

echo "Listening for new connections on port $port: " . "\n";

// cached messages for them loading when user have joined or rebooted
$messages = [];

while(true) {

    $reads = $writes = $exceptions = $connections;
    socket_select($reads, $writes, $exceptions, 0);

    // creating connection
    if(in_array($sock, $reads)) {
        $new_connection = socket_accept($sock);
        $header = socket_read($new_connection, 1024);
        handshake($header, $new_connection, $address, $port);
        $connections[] = $new_connection;
        $reply = [
            "type" => "join",
            "sender" => "Server",
            "text" => "enter name to join... \n"
        ];
        $reply = pack_data(json_encode($reply));
        $firstIndex = array_search($sock, $reads);
        unset($reads[$firstIndex]);
    }

    // working with the connections
    foreach ($reads as $key => $value) {
        $data = socket_read($value, 1024);
        if(!empty($data)) {
            $message = unmask($data);
            $decoded_message = json_decode($message, true);
            if ($decoded_message) {
                if(isset($decoded_message['text']) or isset($decoded_message['mid'])){
                    // establishing the connection session & check CMS session and admin righths
                    if($decoded_message['type'] === 'join') {
                        // has user a rightly session?
                        if (!isset($decoded_message['session'])) continue;
                        $session = $decoded_message['session'];
                        $query = $system->db()->query("SELECT * FROM `users_session` WHERE `usid` = '".$session."'");
                        $result = $query->fetch_assoc();
                        if($query->num_rows == 0) continue;

                        // creating members list and sending the information about connection
                        $query = $system->db()->query("SELECT * FROM `users` WHERE `id` = '".$result['id']."'");
                        $result_a = $query->fetch_assoc();
                        $members[$key] = [
                            'uid' => $result['id'],
                            'login' => $result_a['login'],
                            'role' => $result_a['user_type'],
                            'name' => $decoded_message['sender'],
                            'session' => $session,
                            'idconn' => $key,
                            'connection' => $value
                        ];
                        $reply = [
                            "type" => "join",
                            "sender" => "Server",
                            "text" => "Your ID Connection: " . $members[$key]['idconn'] . ", Role: " . $members[$key]['role'],
                            "messages" => json_encode($messages)
                        ];
                        $reply = pack_data(json_encode($reply));
                        socket_write($members[$key]['connection'], $reply, strlen($reply));
                    }

                    // processing messages with blocking banned users
                    else if($decoded_message['type'] === 'normal') {
                        // has user a rightly session?
                        $session = $members[$key]['session'];
                        $query = $system->db()->query("SELECT * FROM `users_session` WHERE `usid` = '".$session."'");
                        $result = $query->fetch_assoc();
                        if($query->num_rows == 0) continue;

                        // user is blocked?
                        $query = $system->db()->query("SELECT * FROM `users` WHERE `id` = '".$result['id']."'");
                        $result_a = $query->fetch_assoc();
                        if($result_a['blocked'] == 1) continue;

                        // sending message to the clients
                        $decoded_message['sender'] = $members[$key]['login'];
                        $decoded_message['uid'] = $members[$key]['uid'];
                        $decoded_message['idmess'] = sizeof($messages);
                        array_push($messages, $decoded_message);
                        foreach ($members as $mkey => $mvalue) {
                            $maskedMessage = pack_data(json_encode($decoded_message));
                            socket_write($mvalue['connection'], $maskedMessage, strlen($maskedMessage));
                        }
                    }

                    // deleting messages with checking admin rights
                    else if($decoded_message['type'] === 'delete') {
                        // has user a rightly session?
                        $session = $members[$key]['session'];
                        $query = $system->db()->query("SELECT * FROM `users_session` WHERE `usid` = '".$session."'");
                        $result = $query->fetch_assoc();
                        if($query->num_rows == 0) continue;

                        // has user mod & admin rights?
                        $query = $system->db()->query("SELECT * FROM `users` WHERE `id` = '".$result['id']."'");
                        $result_a = $query->fetch_assoc();
                        if($result_a['user_type'] < 1) continue;

                        // sending delete event
                        $messages[$decoded_message['mid']] = [];
                        $maskedMessage = pack_data(json_encode($decoded_message));
                        foreach ($members as $mkey => $mvalue) {
                            $maskedMessage = pack_data(json_encode($decoded_message));
                            socket_write($mvalue['connection'], $maskedMessage, strlen($maskedMessage));
                        }
                    }
                }
            }
        }

        // a client has been disconnected
        else if($data === '')  {
            echo "disconnected " . $key . " \n";
            unset($connections[$key]);
            if(array_key_exists($key, $members)) {
                unset($members[$key]);
            }
            socket_close($value);
        }
    }

}

socket_close($sock);