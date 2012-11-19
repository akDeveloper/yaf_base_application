<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Lycan\Validations;

abstract class Validator 
{
    protected $options;
    protected $kind;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function kind()
    {
        if (null == $this->kind) {
            $name = explode('\\',get_class($this));
            $this->kind = strtolower(array_pop($name));
        }

        return $this->kind;
    }

    abstract public function validate($record);


}
