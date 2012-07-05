<?php
define("APP_PATH",  realpath(dirname(__FILE__) . "/../"));
define("PUBLIC_PATH",  dirname(__FILE__));

$app  = new \Yaf\Application(APP_PATH . "/config/application.ini");
$app->bootstrap() //call bootstrap methods defined in Bootstrap.php
    ->run();
?>
