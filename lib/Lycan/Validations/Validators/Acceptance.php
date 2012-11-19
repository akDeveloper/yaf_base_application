<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Lycan\Validations\Validators;

/**
 * Acceptance validator
 *
 * Will validate the value of a property to be equal with the accept option 
 * value. By default accept value is '1' so can easy compared with the value of 
 * an HTML checkbox.
 *
 * Options:
 *   + message    - The error message to display. Default 'must be accepted'.
 *   + accept     - The accept value to compare againt. Default '1'.
 *   + allow_null - Skip validation if property value is null. Default true. 
 *                  Allow true|false
 *   + if           Set a condition to executed and if it is true then 
 *                  validation will executed. 'if' option can be a string with 
 *                  the name of a callable method of the class to be validated 
 *                  or Closure object (anonymous function). If Closure object used 
 *                  then an instance of class will be passed as parameter to 
 *                  this Closure object.
 *
 * <code>
 *      $options = array('Acceptance' => true); 
 *      $this->validates('terms_of_service', $options);
 *
 *      $options = array('Acceptance' => array(
 *             'message'=> 'accept the terms or else ...',
 *             'if' => function($class){
 *                  return !$class->isNeedToAccept;
 *             }
 *      ));
 *
 *      $this->validates('terms_of_service',$options);
 * </code>
 *
 * @vendor  Lycan
 * @package Validations
 * @author  Andreas Kollaros <php@andreaskollaros.com> 
 * @license MIT {@link http://opensource.org/licenses/mit-license.php}
 */
class Acceptance extends Each
{
    protected $message = 'must be accepted';

    public function __construct($options)
    {
        $options = array_merge($options, array('allow_null'=>true));
        if (!isset($options['accept'])) {
            $options['accept'] = '1';
        }
        parent::__construct($options);
    }

    public function validateEach($record, $attribute, $value)
    {
        if ($value != $this->options['accept']) {
            unset($this->options['allow_null']);
            unset($this->options['accept']);
            $record->errors()->add($attribute, $this->message, $this->options);
        }
    }
}
