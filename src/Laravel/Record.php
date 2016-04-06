<?php
/**
 * Laravel-arrayable record
 *
 * @author Oskar Thornblad
 */

namespace Prewk\Laravel;

use Prewk\Record as BaseRecord;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Laravel-arrayable record
 */
abstract class Record extends BaseRecord implements Arrayable
{
    public function __construct(ValidatorWrapper $validator)
    {
        parent::__construct($validator);
    }
}