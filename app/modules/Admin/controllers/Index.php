<?php

class IndexController extends ApplicationController 
{
    protected $layout = 'admin';

    public function init() 
    {
        parent::init();

        $this->getView()->setLayoutPath(
            $this->getConfig()->application->directory 
            . "/modules" . "/Admin" . "/views" . "/layouts"
        );
    }

    public function indexAction() 
    {
        $this->heading = "Dashboard";
    }
}
