<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Orm\Mysql;

class Collection extends \IteratorIterator implements \Countable, \ArrayAccess
{

    public static $BATCH_SIZE = 1000;

    private $_page_size    = 0;
    private $_current_page = 0;

    /**
     * Deleted elements from result set
     *
     *
     * @var array
     */
    private $_deleted = array();

    /**
     * The class name of the model which execute query.
     *
     * @var string
     */
    protected $model;

    /**
     * The result set of query.
     *
     * An array that contains elements for iteration.
     *
     * @var array
     */
    protected $results = array();


    public function __construct(\ArrayAccess $iterator, $model=null)
    {
        parent::__construct($iterator);
        $this->model = $model;
        $this->_fetch();

    }

    public function current()
    {
        return current($this->results);
    }

    public function key()
    {
        return key($this->results);
    }

    public function next()
    {
        if ($this->key() >= ($this->_current_page + 1) * static::$BATCH_SIZE ) {
            $this->_current_page++;
            $this->_fetch();
        }
        next($this->results);

    }

    public function rewind()
    {
        if ($this->_current_page !== 0 )
            $this->_fetch();
        $this->_current_page = 0;
        reset($this->results);
    }

    public function valid()
    {
        return (current($this->results) !== false);
    }

    public function count()
    {
        return count($this->results);
    }    
    
    public function offsetExists($key)
    {
        if ($key > $this->_current_max_offset()) {
            $this->_current_page = floor($key/static::$BATCH_SIZE);
            $this->_fetch();
        }
        return isset($this->results[$key]);
    }

    public function offsetGet($key)
    {
        if ($key > $this->_current_max_offset() 
            || $key < $this->_current_max_offset() - static::$BATCH_SIZE) 
        {
            $this->_current_page = floor($key/static::$BATCH_SIZE);
            $this->_fetch();
        }
        return $this->results[$key];
    }

    public function offsetSet($key, $value)
    {
        if ($key === null) {
            $k = array_search($key, $this->_deleted);
            if (false!==$k) unset($this->_deleted[$k]);
            return $this->results[] = $value;
        } else {
            return $this->results[$key] = $value;
        }       
    }

    public function offsetUnset($key)
    {
        $this->_deleted[] = $key;
        unset($this->results[$key]); 
    }
    /**
     * Returns first result in set
     */
    public function first()
    {
        return reset($this->results) ?: null;
    }

    /**
     * Returns last result in set
     */
    public function last()
    {
        return end($this->results) ?: null;
    }

    public function isEmpty()
    {
        return empty($this->results);
    }

    public function map(\Closure $block) 
    {
        return new self(array_map($block, array_keys($this->results), $this->results));
    }

    public function each_with_index(\Closure $block) 
    {
        foreach ($this->results as $key => $value) {
            $block($key, $this->results[$key]);
        }
    }

    public function each(\Closure $block)
    {
        foreach ($this->results as $key => $value) {
            $block($this->results[$key]);
        }
    }

    public function toArray($keyColumn=null, $valueColumn=null)
    {
        // Both empty
        if (null === $keyColumn && null === $valueColumn) {
            $return = $this->results;

            // Key column name
        } elseif (null !== $keyColumn && null === $valueColumn) {
            $return = array();
            foreach ($this->results as $k=>$row) {
                if (isset($row->$keyColumn))
                    $return[$k] = $row->$keyColumn;
            }

            // Both key and value columns filled in
        } else {
            $return = array();
            foreach ($this->results as $row) {
                $return[$row->$keyColumn] = $row->$valueColumn;
            }
        }

        return $return;
    }

    public function toJson()
    {

    }

    public function select($search_value, $field_value)
    {
        $array = array(null);
        $array = array_filter($this->results, function($row) use ($search_value, $field_value){
            return $search_value == $row->$field_value;
        });
        return new static(new \ArrayIterator(array_values($array)), $this->model);
    }

    public function detect($search_value, $field_value)
    {
        $array = array(null);
        $array = array_filter($this->results, function($row) use ($search_value, $field_value){
            return $search_value == $row->$field_value;
        });
        return reset($array);
    }

    public function delete($search_value, $field_value)
    {
        $array = array();
        $array = array_filter($this->results, function($row) use ($search_value, $field_value){
            return $search_value == $row->$field_value;
        });
        if (!empty($array)) {
            $key = key($array);
            unset($this->results[$key]);
        }
    }

    private function _current_max_offset()
    {
        return ($this->_current_page + 1) * static::$BATCH_SIZE;
    }

    // Should be called once per page
    private function _fetch()
    {
        $model  = $this->model;
        $rows   = $this->getInnerIterator();
        $offset = $this->_current_page * static::$BATCH_SIZE;
        $this->results = array();
        for ( $i=$offset; $i <= ($this->_current_page + 1) * static::$BATCH_SIZE; $i++ ) {
            if (in_array($i, $this->_deleted)) continue;
            if ($rows->offsetExists($i)) {
                $this->results[$i] = ($rows[$i] instanceof \AbstractModel) 
                    ? $rows[$i]
                    : new $model($rows[$i], false);
            }
        }
    }

}
