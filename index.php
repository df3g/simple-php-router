<?php

use Df3g\Router\Request;
use Df3g\Router\Router;

require __DIR__ . '/vendor/autoload.php';

// Create a new router instance
$router = new Router();

// Define routes
$router->addRoute('GET', '/users', function(Request $request){
    echo "User Index";
});

$router->addRoute('GET', '/users/{name}/{profile?}', function(Request $request){
    if($request->getParam('profile')){
        echo "Showing ". $request->getParam('profile'). " profile for ". $request->getParam('name');
    } else {
        echo "Showing default profile for: ". $request->getParam('name');
    }
});

// Dispatch the current request
$router->dispatch();