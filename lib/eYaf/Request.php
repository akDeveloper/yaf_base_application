<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace eYaf;

/**
 * Filter user input data.
 * 
 * Filter quotes and double quotes from  user input data by applying 
 * htmlspecioalchars function with parametes ENT_QUOTES and UTF-8.
 * 
 * @return array the user data filtered,
 */ 
class Request extends \Yaf\Request\Http
{
    private $_posts;
    private $_params;
    private $_query;

    public function getPost()
    {
        if ($this->_posts) {
            return $this->_posts;
        }

        $this->_posts = $this->filter_params(parent::getPost());
        return $this->_posts;
    }

    public function getParams()
    {
        if ($this->_params) {
            return $this->_params;
        }

        $this->_params = $this->filter_params(parent::getParams());
        return $this->_params;

    }

    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = $this->filter_params(parent::getQuery());
        return $this->_query;

    }

    private function filter_params($params)
    {
        if (!empty($params)) {
            array_walk_recursive($params, function(&$value, $key){
                $value=htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            });
        }

        return $params;
    }
}
