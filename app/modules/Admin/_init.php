<?php
$dis=Yaf\Dispatcher::getInstance();

//Initialize Routes for Admin module
$routes = new Yaf\Config\Ini(__DIR__ . "/config" . "/routes.ini");
$dis->getRouter()->addConfig($routes->admin);
