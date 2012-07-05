<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Orm\Mysql;

class ResultIterator implements \Iterator, \Countable, \ArrayAccess
{
    
    protected $result_set;
    
    private $_index=-1;

    public function __construct(\Traversable $result_set)
    {
        $this->result_set  = $result_set;
    }

    public function current()
    {
        $this->result_set->data_seek($this->_index);
        return $this->result_set->fetch_assoc();
    }

    public function key()
    {
        return $this->_index; 
    }

    public function next()
    {
        $this->_index++;
    }

    public function rewind()
    {
        $this->_index = 0;
    }

    public function valid()
    {
        return $this->key() < $this->result_set->num_rows; 
    }

    public function count()
    {
        return $this->result_set->num_rows;
    }

    public function offsetExists($key)
    {
        return $key < $this->result_set->num_rows;
    }

    public function offsetGet($key)
    {
        $this->result_set->data_seek($key);
        return $this->result_set->fetch_assoc();
    }

    public function offsetSet($key, $value)
    {
        return false;
    }

    public function offsetUnset($key)
    {
        return false;
    }

    public function __destruct()
    {
        $this->result_set->free();
    }
}
