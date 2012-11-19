<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

use eYaf\Request;
use eYaf\Layout;

class Bootstrap extends \Yaf\Bootstrap_Abstract 
{

    public function _initErrorHandler(Yaf\Dispatcher $dispatcher)
    {
        $dispatcher->setErrorHandler(array(get_class($this),'error_handler'));
    }

    public function _initConfig(Yaf\Dispatcher $dispatcher)
    {
        $this->config = Yaf\Application::app()->getConfig();
    }
    
    public function _initRequest(Yaf\Dispatcher $dispatcher)
    {
        $dispatcher->setRequest(new Request());
    }

    public function _initDatabase(Yaf\Dispatcher $dispatcher)
    {

    }

    public function _initPlugins(Yaf\Dispatcher $dispatcher)
    {
        $dispatcher->registerPlugin(new LogPlugin());
        
        $dispatcher->registerPlugin(new AuthTokenPlugin());
    }

    public function _initLoader(Yaf\Dispatcher $dispatcher) 
    {
    }

    public function _initRoute(Yaf\Dispatcher $dispatcher)
    {
        $config = new Yaf\Config\Ini(APP_PATH . '/config/routing.ini');
        $dispatcher->getRouter()->addConfig($config);
    }

    /**
     * Custom init file for modules.
     *
     * Allows to load extra settings per module, like routes etc.
     */
    public function _initModules(Yaf\Dispatcher $dispatcher)
    {
        $app = $dispatcher->getApplication();

        $modules = $app->getModules();
        foreach ($modules as $module) {
            if ('index' == strtolower($module)) continue;

            require_once $app->getAppDirectory() . "/modules" . "/$module" . "/_init.php"; 
        } 
    } 

    public function _initLayout(Yaf\Dispatcher $dispatcher)
    {
        $layout = new Layout($this->config->application->layout->directory);
        $dispatcher->setView($layout);
    }
    
    /**
     * Custom error handler.
     *
     * Catches all errors (not exceptions) and creates an ErrorException.
     * ErrorException then can caught by Yaf\ErrorController.
     *
     * @param integer $errno   the error number.
     * @param string  $errstr  the error message.
     * @param string  $errfile the file where error occured.
     * @param integer $errline the line of the file where error occured.
     *
     * @throws ErrorException
     */
    public static function error_handler($errno, $errstr, $errfile, $errline)
    {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
}
