<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Lycan\Validations;

interface ValidatableInterface
{
    /**
     * Checks if class is valid. If not, creates an array with errors or an 
     * ArrayIterator object.
     *
     * @return boolean true if class is valid, false if it is invalid.
     */
    public function isValid();

    /**
     * Returns an array or an ArrayIterator object with errors.
     *
     * The format should be:
     * <code>
     *      Array(
     *          'class_attribute_name' => Array(
     *              [0] => 'Message',
     *              [1] => 'Message',
     *          ),
     *          'another_class_attribute_name' => Array(
     *              [0] => 'Message',
     *          ),
     *      )
     * </code>
     *
     * @retrun array|ArrayIterator
     */
    public function getErrors();
}
