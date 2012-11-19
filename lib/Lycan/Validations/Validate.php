<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Lycan\Validations;
/**
 * Abstract class Validate.
 *
 * To allow validation to a class extend the class with this Validate class.
 * Implement the abstract method {@link validations()} by adding the validations
 * you want to pass the class. 
 *
 * <code>
 * class MyClass
 * {
 *      use Lycan\Validations\Validate;
 *
 *      protected function validations()
 *      {
 *          $this->validates('property', array('Validator'=>$options);
 *      }
 *
 * }
 * </code>
 */
trait Validate
{
    protected $default_keys = array('if', 'on', 'allow_empty', 'allow_null');

    private $_errors;

    /**
     * Returns errors that occured after the validation of the class.
     *
     * @see \Lycan"validations\Errors
     *
     * @return \ArrayIterator 
     */ 
    public function errors()
    {
        $this->_errors = $this->_errors ?: new Errors();
        return $this->_errors;
    }

    public function getErrors()
    {
        return $this->errors();
    }

    /**
     * Checks if a class is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        $this->errors()->clear();
        $this->run_validations();
        return $this->errors()->isEmpty(); 
    }

    /**
     * Checks if a class is invalid
     *
     * @return boolean
     */
    public function isInvalid()
    {
        return !$this->isValid(); 
    }

    /**
     * Validates properties of a class against validators.
     *
     * validations array must contain the name of Validator class without the 
     * namespace and the options for this validator.
     * 
     * <code>
     *  $validations = array('Validator' => array('message'=>'my error message'));
     *  
     *  $validations = array('Validator' => true); // you must pass true if 
     *                                                options are not exist.
     * </code>
     *
     * On examples above the Lycan\Validations\Validators\Validator will be 
     * called.
     *
     * @params string|array $attrs       the properties of class to validate
     * @params array        $validations an array of Validator name class and 
     *                                   its options.
     * 
     * @throws \Exception if a Validator class does not exist.
     *
     * @return void
     */
    final public function validates($attrs, array $validations)
    {
        foreach ($validations as $key=>$options) {

            $validator = "\\Lycan\\Validations\\Validators\\" . $key;
            
            if (!class_exists($validator)) {
                throw new \Exception("Unknown validator: {$key}");
            }

            $defaults = $this->_parse_validates_options($options);
            $defaults['attributes'] = $attrs;
            $vtor = new $validator($defaults);
            $vtor->validate($this);
        }
    }

    public function readAttributeForValidation($attribute)
    {
        return isset($this->$attribute) ? $this->$attribute : null;
    }

    protected function validations()
    {
        return true;
    }

    protected function run_validations()
    {
        $this->validations();

        return $this->errors()->count() == 0;
    }

    private function _parse_validates_options($options)
    {
        if (is_array($options)) {

            return $options;
        } elseif(is_bool($options)) {

            return array();
        } else {

            return array('with' => $options);
        }
    }
} 
