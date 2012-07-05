<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * The restfull controller.
 *
 */
abstract class RestfullController extends ApplicationController
{
    private $_resource;

    private $_resource_collection;
    
    /**
     * Loads and displays all items from a resource collection.
     *
     * @return void
     */
    public function indexAction()
    {
        $collection = $this->getResourceCollection();
        $this->getView()->assign('collection',$collection);
    }

    public function showAction()
    {
        $resource = $this->getResource();
        
        if (null === $resource) {
            return $this->forwardTo404();
        } else {
            $this->getView()->assign('resource',$resource);
        }
    }

    public function newAction()
    {
        $model = $this->_get_model_name();
        $resource = new $model();
        $this->getView()->assign('resource',$resource);
    }

    public function createAction()
    {
        $model = $this->_get_model_name();
        $param_name = Inflect::underscore(str_replace('Model','',$model));
        $post = $this->getRequest()->getPost();
        $resource = new $model($post[$param_name]);

        if ($resource->save()) {
            $url = "/" 
                . Inflect::underscore($this->getRequest()->getControllerName()) 
                . "/index";
            $this->redirect($url);
        } else {
            $this->render('new', array('resource'=>$resource));
        }
    }

    public function editAction()
    {

    }

    public function updateAction()
    {

    }

    public function deleteAction()
    {

    }

    public function getResourceCollection()
    {
        $model = $this->_get_model_name();

        return $model::find()->fetchAll();
    }

    public function getResource()
    {
        if (null === $this->_resource) {
            $model = $this->_get_model_name();
            $this->_resource = $model::findById($this->getRequest()->getParam('id'))
                ->fetch();
        }
        return $this->_resource;
    }

    private function _get_model_name()
    {
        return Inflect::singularize($this->getRequest()->getControllerName())
            . "Model";       
    }
}
