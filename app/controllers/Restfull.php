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
 * determinate the name of the Model to load {@link get_model_name()}
 *
 */
abstract class RestfullController extends ApplicationController
{
    private $_resource;

    private $_resource_collection;

    private $_index_url;
    
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
     * By default the uri to show a resource is /module/controller/show/id/1 
     *
     * @param int $id The id of resource to load
     */
    public function showAction($id)
    {
        $resource = $this->getResource($id);
        
        if (null === $resource) {
            return $this->forwardTo404();
        } else {
            $this->getView()->assign('resource', $resource);
        }
    }

    public function newAction()
    {
        $model = $this->get_model_name();
        $resource = new $model();
        $this->get_index_url();
        $this->getView()->assign('resource', $resource);
    }

    public function createAction()
    {
        $model = $this->get_model_name();

        $resource = new $model($this->get_resource_params($model));

        if ($resource->save()) {
            $this->redirect($this->get_index_url());
            return false;
        } else {
            Yaf\Dispatcher::getInstance()->disableView();
            echo $this->render('new', array('resource'=>$resource, 
                'index_url'=>$this->get_index_url()));
            return false;
        }
    }

    public function editAction($id)
    {

        $resource = $this->getResource($id);
        
        if (null === $resource) {
            return $this->forwardTo404();
        } else {
            $this->get_index_url();
            $this->getView()->assign('resource', $resource);
        }
    }

    public function updateAction($id)
    {
        $resource = $this->getResource($id);
        
        if (null === $resource) {
            return $this->forwardTo404();
        } 
        
        $model = $this->get_model_name();

        if ($resource->updateAttributes($this->get_resource_params($model))) {
            $this->redirect($this->get_index_url());
            return false;
        } else {
            Yaf\Dispatcher::getInstance()->disableView();
            echo $this->render('edit', array('resource'=>$resource, 
                'index_url'=>$this->get_index_url()));
            return false;
        }
    }

    public function deleteAction()
    {
        $resource = $this->getResource();
        
        if (null === $resource) {
            return $this->forwardTo404();
        }

        $this->resource->destroy();

        $this->redirect($this->get_index_url());
        return false;
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
        $model = $this->get_model_name();

        return $model::find()->fetchAll();
    }

    /**
     * Creates a resource by a given id.
     *
     * By default it finds the resource for the given id.
     * Overload this method if you want to load a resource with additional 
     * criteria.
     *
     * @param int $id The id of resource to load.
     */
    public function getResource($id)
    {
        if (null === $this->_resource) {
            $model = $this->get_model_name();
            $this->_resource = $model::findById($id)
                ->fetch();
        }
        return $this->_resource;
    }

    protected function get_model_name()
    {
        return Inflect::singularize($this->getRequest()->getControllerName())
            . "Model";       
    }

    protected function get_resource_params($model)
    {
        $param_name = Inflect::underscore(str_replace('Model', '', $model));
        $post = $this->getRequest()->getPost();

        return $post[$param_name];
    }

    protected function get_index_url()
    {

        if (null !== $this->_index_url) {
            return $this->_index_url;
        }

        $module = $this->getRequest()->getModuleName();
        
        $this->_index_url = "/"
            . ('index' == strtolower($module) ? null : $module. "/")
            . Inflect::underscore($this->getRequest()->getControllerName()) 
            . "/index";
        
        $this->getView()->assign('index_url', $this->_index_url);
        return $this->_index_url;
    }
}
