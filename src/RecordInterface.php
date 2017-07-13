<?php
/**
 * Describes a validatable immutable record
 *
 * @author Oskar Thornblad
 */

namespace Prewk;

use ArrayAccess;
use Countable;
use Exception;
use Iterator;
use JsonSerializable;

interface RecordInterface extends JsonSerializable, ArrayAccess, Iterator, Countable
{
    /**
     * Immutable set method
     *
     * @param string $name Key
     * @param mixed $value Value
     * @return Record
     * @throws Exception if field name or value is invalid
     */
    public function set($name, $value);

    /**
     * Non-magic getter
     *
     * @param $name mixed
     * @return mixed
     */
    public function get($name);

    /**
     * Does the record have a set value for the given field?
     *
     * @param string $name
     * @return bool
     */
    public function has($name);

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
    public function equals(Record $comparee);

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray();
}