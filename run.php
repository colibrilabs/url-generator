<?php

use Subapp\Http\Request;
use Subapp\Router\Pattern;
use Subapp\Router\Route;
use Subapp\Router\Router;
use Subapp\UrlGenerator\UrlBuilder;

include_once __DIR__ . '/vendor/autoload.php';

$router = new Router();

$router->path('/user/:id', ['controller' => 'Index', 'action' => 'GetUser',]);
$router->path('/search/:hash', ['controller' => 'Index', 'action' => 'Search',]);

$route = new Route(['controller' => 'Babe', 'action' => 'profile',],
    Pattern::of('path', '/babe/id-:id.html'), Pattern::of('domain', ':name.domain.com'));

$router->addRoute($route);

$builder = new UrlBuilder($router, new Request());

var_dump(
    $builder->create('Babe:profile', ['id' => 123, 'name' => 'miss-alice-18'])
);
