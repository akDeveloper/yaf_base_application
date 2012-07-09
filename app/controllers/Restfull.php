<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * The restfull controller.
 *
 * Restfull controller helps to easy create crud actions for a model.
 * Assuming that the Controller has the same name with your model. If you have 
 * a PostsController then Restfull controller will assume that your resource is 
 * the PostModel. 
 *
 * Notice that controller name is defined as `Posts`, plural, and model is 
 * defind as `Post` singular. RestfullController uses Inflect class to 
 * determinate the name of the Model to load {@link _get_model_name()}
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
        $this->getView()->assign('collection', $collection);
    }

    /**
     * Loads and displays a single resource asccording to Request::getParams() 
     * values. 
     *
     * By default the uri to load a resource is /module/controller/show/id/1 
     * showAction uses
     */
    public function showAction($id)
    {
        $resource = $this->getResource($id);
        
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

        $resource = new $model($this->_get_resource_params($model));

        if ($resource->save()) {
            $this->redirect($this->_get_index_url());
        } else {
            $this->render('new', array('resource'=>$resource));
        }
    }

    public function editAction()
    {

        $resource = $this->getResource();
        
        if (null === $resource) {
            return $this->forwardTo404();
        } else {
            $this->getView()->assign('resource',$resource);
        }
    }

    public function updateAction()
    {
        $resource = $this->getResource();
        
        if (null === $resource) {
            return $this->forwardTo404();
        } 
        
        $model = $this->_get_model_name();

        if ($resource->updateAttributes($this->_get_resource_params($model))) {
            $this->redirect($this->_get_index_url());
        } else {
            $this->render('edit', array('resource'=>$resource));
        }
    }

    public function deleteAction()
    {
        $resource = $this->getResource();
        
        if (null === $resource) {
            return $this->forwardTo404();
        }

        $this->resource->destroy();

        $this->redirect($this->_get_index_url());
    }

    /**
     * Creates a resource collection.
     *
     * By default it finds all elements of the resource. You can overload this 
     * methods to return custom queries of the resource like pagination and/or 
     * search.
     *
     * @return Orm\Mysql\Collection A collection object handling elements of 
     *                              the resource
     */
    public function getResourceCollection()
    {
        $model = $this->_get_model_name();

        return $model::find()->fetchAll();
    }

    public function getResource($id)
    {
        if (null === $this->_resource) {
            $model = $this->_get_model_name();
            $this->_resource = $model::findById($id)
                ->fetch();
        }
        return $this->_resource;
    }

    private function _get_model_name()
    {
        return Inflect::singularize($this->getRequest()->getControllerName())
            . "Model";       
    }

    private function _get_resource_params($model)
    {
        $param_name = Inflect::underscore(str_replace('Model','',$model));
        $post = $this->getRequest()->getPost();

        return $post[$param_name];
    }

    protected function get_index_url()
    {
        $module = $this->getRequest()->getModuleName();
        return "/"
            . ('index' == strtolower($module) ? null : $module. "/")
            . Inflect::underscore($this->getRequest()->getControllerName()) 
            . "/index";       
    }
}
