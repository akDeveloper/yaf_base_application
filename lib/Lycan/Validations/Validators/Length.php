<?php 

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Lycan\Validations\Validators;

/**
 * Length Validator,
 *
 * Checks for the right length of the given property of a class.
 *
 *
 *
 */
class Length extends \Lycan\Validations\Validators\Each
{
    protected $messages = array('is'=>':wrong_length', 'min' => ':too_short', 'max'=>':too_long');
    protected $checks = array('is'=>'==', 'min'=>'>=', 'max' => '<=');

    private $_reserved_options = array('min'=>null, 'max'=>null, 'within'=>null, 'is'=>null, 'tokenizer'=>null, 'too_short'=>null, 'too_long'=>null);
    public function __construct($options)
    {

        $range = isset($options['in']) 
            ? $options['in'] 
            : (isset($options['within']) ? $options['within'] : null);
        if ($range) {
            if (!is_array($range))
                throw new \InvalidArgumentException('`in` and `within` must be an array');
            $options['min'] = min($range);
            $options['max'] = max($range);
        }
        
        parent::__construct($options);
    }

    public function validateEach($record, $attribute, $value)
    {
        $value = $this->_tokenize($value);
        $value_length = is_array($value) ? count($value) : strlen($value);
        
        foreach ($this->checks as $key => $operator) {

            if (!isset($this->options[$key])) continue;
            
            $check_value = $this->options[$key];
            if ($this->_check_value($check_value, $value_length, $operator)) continue;

            $error_options = array_diff_key($this->options, $this->_reserved_options);

            $error_options['count'] = $check_value;
            $default_message = isset($this->options[$this->messages[$key]])
                ? $this->options[$this->messages[$key]]
                : null;

            if ($default_message)
                $error_options['message'] = $error_options['message'] ?: $default_message;

            $record->errors()->add($attribute, $this->messages[$key], $error_options);
        }
    }

    private function _tokenize($value)
    {
        if (isset($this->options['tokenizer']) && is_string($value)) {
            $tokenizer = $this->options['tokenizer'];
            if (is_callable($tokenizer))
                return $tokenizer($value);
        }
        return $value;
    }

    private function _check_value($check_value,$value_length, $operator)
    {
        switch ($operator) {
            case '==':
                return $value_length == $check_value;
                break;
            case '>=':
                return $value_length >= $check_value;
                break;
            case '<=':
                return $value_length <= $check_value;
                break;
        }
    }
}
