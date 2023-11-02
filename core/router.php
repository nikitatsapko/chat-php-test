<?php
header('Access-Control-Allow-Origin: *');
$system = new System();

require __DIR__ . '/vendor/autoload.php';

$_user = $system->userinfo();
$system_user_id = $system->userinfo()['id'];

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', 'main');
    $r->addRoute('GET', '/auth', 'login');
    $r->addRoute('GET', '/logout', 'logout');
    $r->addRoute('GET', '/profile/avatar', 'profile_avatar');
    $r->addRoute('GET', '/profile/password', 'profile_password');
    $r->addRoute('GET', '/admin/users', 'admin_users');
    $r->addRoute('GET', '/admin/users/{id:\d+}', 'admin_users_edit');
    //*** API ***\\
    $r->addRoute('POST', '/api/login', 'api_login');
    $r->addRoute('POST', '/api/moderation/ban', 'api_moderation_ban');
    $r->addRoute('POST', '/api/user/edit', 'api_users_edit');
    $r->addRoute('POST', '/api/user/changepassword', 'api_user_changepassword');
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

include __DIR__ . "/handlers.php";

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        $system->printError(404);
        die();
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        $system->printError(405);
        die();
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        print $handler($vars);
        break;
}