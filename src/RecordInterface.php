<?php
/**
 * Describes a validatable immutable record
 *
 * @author Oskar Thornblad
 */

declare(strict_types=1);

namespace Prewk;

use ArrayAccess;
use Closure;
use Countable;
use Exception;
use Iterator;
use JsonSerializable;

/**
 * Describes a record
 */
interface RecordInterface extends JsonSerializable, ArrayAccess, Iterator, Countable
{
    /**
     * Immutable set method
     *
     * @param string $name Key
     * @param mixed $value Value
     * @return static
     * @throws Exception if field name or value is invalid
     */
    public function set(string $name, $value);

    /**
     * Immutable update method
     *
     * @param string $name Key
     * @param Closure $updater
     * @return static
     */
    public function update(string $name, Closure $updater);

    /**
     * Non-magic getter
     *
     * @param $name string
     * @return mixed
     */
    public function get(string $name);

    /**
     * Does the record have a set value for the given field?
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Returns a new record
     *
     * @param array|ArrayAccess $init Initial record data
     * @return static
     */
    public function make($init = []);

    /**
     * Merge the record with another structure
     *
     * @param mixed $mergee
     * @return static
     * @throws Exception
     */
    public function merge($mergee);

    /**
     * Compare two records by content
     *
     * @param Record $comparee
     * @return bool
     */
    public function equals(Record $comparee): bool;

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray();
}