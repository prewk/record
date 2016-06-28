<?php
/**
 * Wraps the Laravel validator to the ValidatorInterface
 *
 * @author Oskar Thornblad
 */

namespace Prewk\Record\Laravel;

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

    /**
     * Don't serialize the validator
     */
    public function __sleep()
    {
        return [];
    }

    /**
     * When deserializing - re-create the validator with the IoC container
     */
    public function __wakeup()
    {
        $this->validator = app(Factory::class);
    }
}