<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Lycan\Validations\Validators;

class Presence extends Each
{
    protected function validateEach($record, $attribute, $value)
    {
        $value = $record->readAttributeForValidation($attribute);

        $is_empty = is_object($value) && method_exists($value, 'isEmpty') 
            ? $value->isEmpty() 
            : empty($value);
        
        if ($is_empty) {
            $record->errors()->add($attribute, ':not_empty', $this->options);
        }
    }
}
