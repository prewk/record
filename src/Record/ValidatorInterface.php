<?php
/**
 * Validator interface for the immutable records
 *
 * @author Oskar Thornblad
 */

namespace Prewk\Record;

/**
 * Describes something validatable
 */
interface ValidatorInterface
{
    /**
     * Validates a value against a rule
     * 
     * @param mixed $value
     * @param mixed $rule
     * @return bool
     */
    public function validate($value, $rule);
}