<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Lycan\Validations;

class Errors extends \ArrayIterator
{
    protected $base;

    public function __construct($base, $array=array(), $flags=0)
    {
        $this->base = $base;
        parent::__construct($array, $flags);
    }

    public function clear()
    {
        foreach ($this as $v) unset($v);
    }

    public function isEmpty()
    {
        return $this->count() == 0; 
    }

    public function contains($attribute)
    {
        return $this->offsetExists($attribute);
    }

    public function add($attribute, $message=null, $options=array())
    {
        $message = $this->_normalize_message($attribute, $message, $options);
        !$this->offsetExists($attribute) ? $this->offsetSet($attribute, array()) : null;
        $val = $this->offsetGet($attribute);
        array_push($val, $message);
        $this->offsetSet($attribute, $val);
    }

    public function addOnEmpty($attributes, $options=array())
    {
        foreach ($attributes as $attribute) {
            $value = $this->base->$attribute;
            $is_empty = is_object($value) && method_exists($value, 'isEmpty') ? $value->isEmpty() : empty($value);
            if($is_empty) $this->add($attribute, ':empty', $options);
        }
    }

    public function addOnNull($attributes, $options=array())
    {
        foreach ($attributes as $attribute) {
            $value = $this->base->$attribute;
            $is_null = is_object($value) && method_exists($value, 'isNull') ? $value->isNull() : is_null($value);
            if($is_null) $this->add($attribute, ':null', $options);
        }
    }

    private function _normalize_message($attribute, $message, $options)
    {
        $message = $message ?: ':invalid';

        if ( 0 === strpos($message, ':') ) {
            return $this->generateMessage($attribute, $message, $options);
        } elseif (is_callable($message)) {
            return $message();
        } else {
            return $message;
        }
    }

    public function generateMessage($attribute, $type=':invalid', $options=array())
    {
        // TODO: i18n
        $message = null; 
        if (isset($options['message'])) {
            if (0 === strpos($options['message'], ':'))
                $type = $options['message'];
            else
                $message = $options['message'];
        }

        return $message ?: $type;
    }
}
