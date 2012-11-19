<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Lycan\Validations;

class Errors extends \ArrayIterator
{
    public function __construct($array=array(), $flags=0)
    {
        parent::__construct($array, $flags);
    }

    public function clear()
    {
        $array = $this->getArrayCopy();

        foreach ($array as $k=>$v) $this->offsetUnset($k);
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
        $message = null;
        $type = substr($type, 1);

        if (isset($options['message'])) {
            if (0 === strpos($options['message'], ':'))
                $type = substr($options['message'], 1);
            else
                $message = $options['message'];
        }

        return $message ?: $type;
    }
}
