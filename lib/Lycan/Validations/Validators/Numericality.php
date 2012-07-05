<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Lycan\Validations\Validators;

class Numericality extends \Lycan\Validations\Validators\Each
{
    protected $checks = array(
        'greater_than' => '>', 
        'greater_than_or_equal_to' => '>=',
        'equal_to' => '==',
        'less_than' => '==',
        'less_than_or_equal_to' => '<=',
        'odd' => 'odd',
        'even' => 'even',
        'other_than' => '!='
    );

    private $_reserved_options = array(
        'greater_than' => '>', 
        'greater_than_or_equal_to' => '>=',
        'equal_to' => '==',
        'less_than' => '<',
        'less_than_or_equal_to' => '<=',
        'odd' => 'odd',
        'even' => 'even',
        'other_than' => '!=',
        'only_integer' => null
    );

    protected function validateEach($record, $attribute, $value)
    {
        if (array_key_exists('allow_null',$this->options)) {
            if ($this->options['allow_null'] && null === $value)
                return;
        }

        if (array_key_exists('only_integer',$this->options)) {
            if ($this->options['only_integer']) { 
                if (!($value = $this->parse_value_as_integer($value))) {
                    $record->errors()->add($attribute, ':not_an_integer', $this->filtered_options($value));
                    return;
                }
            }
        }
        $options = array_intersect_key($this->options, $this->checks);
        foreach ($options as $option => $option_value) {
            switch ($option) {
                case 'odd':
                    if ( 1 !== (1 & $value))
                        $record->errors()->add($attribute, ":$option", $this->filtered_options($value));
                    break;
                case 'even':
                    if ( 0 !== (1 & $value))
                        $record->errors()->add($attribute, ":$option", $this->filtered_options($value));
                    break;
                default:
                    
                    if ( is_callable($option_value)) $option_value = $option_value($record);

                    if ( false === $this->_check_value($value, $option_value, $this->checks[$option])) {
                        $o = $this->filtered_options($value);
                        $o['count'] = $option_value;
                        $record->errors()->add($attribute, ":$option", $o);
                    }
                    break;
            }
        }

    }
    
    protected function check_validity()
    {
        $options = array_intersect_key($this->options, $this->checks);
        foreach ($options as $option=>$value) {
            if ($option == 'odd' || $option == 'even' || is_numeric($value) || is_callable($value)) continue;
            throw new \InvalidArgumentException("{$option} must be a number or a function");
        }
    }

    protected function parse_value_as_number($value)
    {
        if ( is_numeric($value)) {
            if ( is_float($value) ) return (float) $value;
            if ( is_int($value) ) return (int) $value;
        }
    }

    protected function parse_value_as_integer($value)
    {
        if ( is_numeric($value) && is_int($value))
            return (int) $value;
        else
            return null;
    }

    protected function filtered_options($value)
    {
        $options = array_diff_key($this->options, $this->_reserved_options);
        $options['value'] =  $value;
        return $options;
    }

    private function _check_value($record_value, $check_value, $operator)
    {
        switch ($operator) {
            case '>':
                return $record_value > $check_value;
                break;
            case '<':
                return $record_value < $check_value;
                break;
            case '==':
                return $record_value == $check_value;
                break;
            case '>=':
                return $record_value >= $check_value;
                break;
            case '<=':
                return $record_value <= $check_value;
                break;
        }
    }
}
