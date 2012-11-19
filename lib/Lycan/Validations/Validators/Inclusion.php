<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Lycan\Validations\Validators;

class Inclusion extends \Lycan\Validations\Validators\Clusivity
{
    public function validateEach($record, $attribute, $value)
    {
        if (!$this->is_include($record, $value)) {
            $record->errors()->add($attribute, ':inclusion', $this->filtered_options($value));
        }
    }
}
