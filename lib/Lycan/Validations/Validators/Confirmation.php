<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Lycan\Validations\Validators;

class Confirmation extends \Lycan\Validations\Validators\Each
{
    public function validateEach($record, $attribute, $value)
    {
        $confirmed = $record->readAttributeForValidation("{$attribute}_confirmation");
        if ($value != $confirmed) {
            $record->errors()->add($attribute, ':confirmation', $this->options);
        }
    }
}
