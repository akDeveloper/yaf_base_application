<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Lycan\Validations\Validators;
use Lycan\Validations\Validators\Each;

class Format extends Each
{
    public function validateEach($record, $attribute, $value)
    {
        if (isset($this->options['with'])) {

            $regexp = $this->_option_call($record, 'with');
            if (!(preg_match($regexp, $value))) {
                $record->errors()->add(
                    $attribute, 
                    ':invalid', 
                    array_merge($this->options, array('with'=>$value))
                );
            }
        } elseif (isset($this->options['without'])) {
            $regexp = $this->_option_call($record, 'without');
            if (preg_match($regexp, $value))
                $record->errors()->add(
                    $attribute, 
                    ':invalid', 
                    array_merge($this->options, array('without'=>$value))
                );
        }
    }

    private function _option_call($record, $name)
    {
        $option = $this->options[$name];
        return is_callable($option) ? $option($record) : $option;
    }

    protected function check_validity()
    {
        if (!(array_key_exists('with', $this->options) 
            xor array_key_exists('without',$this->options))
        ) {
            throw new \InvalidArgumentException('Either `with` or `without` must be supplied (but not both)'); 
        }
    }

}
