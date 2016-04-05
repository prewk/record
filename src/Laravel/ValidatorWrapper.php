<?php
/**
 * Wraps the Laravel validator to the ValidatorInterface
 *
 * @author Oskar Thornblad
 */

namespace Prewk\Laravel;

use Prewk\Record\ValidatorInterface;
use Illuminate\Contracts\Validation\Validator;

class ValidatorWrapper implements ValidatorInterface
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * ValidatorWrapper constructor
     *
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
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
        return !$this->validate()->make(
            ["validatee" => $value],
            ["validatee" => $rule]
        )->fails();
    }
}