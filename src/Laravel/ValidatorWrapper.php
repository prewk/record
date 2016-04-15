<?php
/**
 * Wraps the Laravel validator to the ValidatorInterface
 *
 * @author Oskar Thornblad
 */

namespace Prewk\Laravel;

use Prewk\Record\ValidatorInterface;
use Illuminate\Validation\Factory;

class ValidatorWrapper implements ValidatorInterface
{
    /**
     * @var Factory
     */
    private $validator;

    /**
     * ValidatorWrapper constructor
     *
     * @param Factory $validator
     */
    public function __construct(Factory $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validates a value against a rule
     *
     * @param mixed $value
     * @param mixed $rule
     * @return bool
     */
    public function validate($value, $rule)
    {
        return !$this->validator->make(
            ["validatee" => $value],
            ["validatee" => $rule]
        )->fails();
    }
}