<?php

if (!file_exists('config.json')) {
    header('HTTP/1.1 503 Service Unavailable');
    echo 'Please setup OpenHub by copying config.sample.json to config.json and editing it appropriately.';
    exit;
}

require 'vendor/autoload.php';

$config = json_decode(file_get_contents('config.json'), TRUE);
$routes = json_decode(file_get_contents('routes.json'), TRUE);

use Aura\Router\Map;
use Aura\Router\DefinitionFactory;
use Aura\Router\RouteFactory;
use Aura\Web\Context;
use Aura\Web\Accept;
use Aura\Web\Response;
use Aura\Web\Signal;
use Aura\Web\Renderer\None as Renderer;

$router_map = new Map(new DefinitionFactory, new RouteFactory);

foreach ($routes as $name => $params) {
    array_unshift($params, $name);
    call_user_func_array(array($router_map, 'add'), $params);
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$route = $router_map->match($path, $_SERVER);
if (!$route) {
    header('HTTP/1.1 404 Not Found');
    echo "No application route was found for that URI path.";
    exit();
}

if (isset($route->values['controller'])) {
    $controller = $route->values['controller'];
} else {
    $controller = 'Index';
}

$controller = 'OpenHub\\Web\\' . $controller;

if (isset($route->values['action'])) {
    $action = $route->values['action'];
} else {
    $action = 'root';
}

$params = $route->values;
$params['action'] = $action;
$params['repositories'] = $config['repositories'];

$page = new $controller(
    new Context($GLOBALS),
    new Accept($_SERVER),
    new Response,
    new Signal,
    new Renderer,
    $params
);

$response = $page->exec();
echo $response->getContent();
