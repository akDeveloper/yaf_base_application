<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Lycan\Validations\Validators;

abstract class Each extends \Lycan\Validations\Validator
{
    /**
     * @var array $attributes Class attributes to validates.
     */
    protected $attributes = array();

    public function __construct($options)
    {
        $this->attributes = isset($options['attributes'])
            ? (
                !is_array($options['attributes']) 
                ? array($options['attributes']) 
                : $options['attributes']
            )
            : array();

        if (empty($this->attributes)) {
            
            throw new \Exception('attributes cannot be empty');
        }

        if (isset($options['attributes'])) {
            unset($options['attributes']);
        }

        parent::__construct($options);
        
        $this->check_validity();
    }

    public function validate($record)
    {
        $if = $this->validates_if($record);
        if (false == $if) {

            return true;
        }

        foreach ($this->attributes as $attribute) {
            
            $value = $record->readAttributeForValidation($attribute);
            
            if (   (null === $value && isset($this->options['allow_null'])
                && true == $this->options['allow_null'])
                || (empty($value) && isset($this->options['allow_empty']) 
                && true == $this->options['allow_empty'])
            ) {
                continue;
            }

            $this->validateEach($record, $attribute, $value);
        }
    }

    protected function validates_if($record)
    {
        if (isset($this->options['if'])) {
            $if = $this->options['if'];
            if (is_callable($if)) {
                return $if($record);
            } else if (method_exists($record, $if)) {
                return $record->$if();
            }
        }
        return true;
    }

    abstract protected function validateEach($record, $attribute, $value);

    protected function check_validity()
    {
    
    }


    
}
