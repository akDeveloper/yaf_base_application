<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Lycan\Validations\Validators;

abstract class Clusivity extends Each
{
    protected function is_include($record, $value)
    {
        $delimiter = $this->options['in'];
        $exclusions = is_callable($delimiter) ? $delimiter($record) : $delimiter;

        return in_array($value, $exclusions);
    }

    protected function check_validity()
    {
        if (!is_array($this->options['in']) && !is_callable($this->options['in']))
            throw new \InvalidArgumentException("`in` must be an array or a function");
    }   

    protected function filtered_options($value)
    {
        $options = array_diff_key($this->options, array('in'=>null));
        $options['value'] =  $value;
        return $options;
    }
}

