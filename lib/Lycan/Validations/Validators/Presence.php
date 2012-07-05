<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Lycan\Validations\Validators;

class Presence extends \Lycan\Validations\Validators\Each
{
    public function validate($record)
    {
        $if = $this->validates_if($record);
        if (false == $if) return true;

        $record->errors()->addOnEmpty($this->attributes, $this->options);
    }

    protected function validateEach($record, $attribute, $value)
    {
        
    }
}
