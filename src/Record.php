<?php
/**
 * Immutable validatable record
 *
 * @author Oskar Thornblad
 */

namespace Prewk;

use ArrayAccess;
use Closure;
use Exception;
use Prewk\Record\ValidatorInterface;

/**
 * Class Record
 * @package PagePicnic\Services\Records
 */
abstract class Record implements RecordInterface
{
    /**
     * @var array
     */
    private $_recordData = [];

    /**
     * @var int
     */
    private $_recordIteratorIndex = 0;

    /**
     * @var ValidatorInterface
     */
    private $_validator;

    /**
     * Record constructor.
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator = null)
    {
        $this->_validator = $validator;
    }

    /**
     * Array key exists
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return
            array_key_exists($offset, $this->getDefaults()) ||
            array_key_exists($offset, $this->_recordData);
    }

    /**
     * Array get key
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Array set key
     *
     * @param mixed $offset
     * @param mixed $value
     * @throws Exception when you try to set
     */
    public function offsetSet($offset, $value)
    {
        throw new Exception("You can't set on an immutable record as an array");
    }

    /**
     * Array unset key
     *
     * @param mixed $offset
     * @throws Exception when you try to unset
     */
    public function offsetUnset($offset)
    {
        throw new Exception("You can't unset an immutable record");
    }

    /**
     * Should return an array of record fields names
     *
     * @return array Fields
     */
    abstract protected function getFields();

    /**
     * Should return an associative array of validator rules, if a field is
     * omitted, that field will not be validated when set
     *
     * @codeCoverageIgnore
     * @return array
     */
    protected function getRules()
    {
        return [];
    }

    /**
     * Should return an associative array of record defaults, if a field is
     * omitted an exception will be thrown when accessing it before setting it
     *
     * @codeCoverageIgnore
     * @return array Defaults
     */
    protected function getDefaults()
    {
        return [];
    }

    /**
     * Disallow sets
     * @throws Exception if someone sets
     */
    final public function __set($name, $value)
    {
        throw new Exception("On an immutable record you must use the set() method");
    }

    /**
     * Immutable set method
     *
     * @param string $name Key
     * @param mixed $value Value
     * @return Record
     * @throws Exception if field name or value is invalid
     */
    public function set($name, $value)
    {
        $recordData = $this->validate($name, $value)->_recordData;

        $recordData[$name] = $value;
        $record = new static($this->_validator);
        $record->force($recordData);

        return $record;
    }

    /**
     * Immutable update method
     *
     * @param $name Key
     * @param Closure $updater
     * @return Record
     */
    public function update($name, Closure $updater)
    {
        return $this->set($name, $updater($this->get($name)));
    }

    /**
     * Validate a field
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     * @throws Exception if the field doesn't validate
     */
    private function validate($name, $value) {
        if (!in_array($name, $this->getFields())) {
            throw new Exception("Field name $name invalid in " . get_class($this));
        } elseif (
            array_key_exists($name, $this->getRules()) &&
            !is_null($this->_validator) &&
            !$this->_validator->validate($value, $this->getRules()[$name])
        ) {
            throw new Exception("Field name $name didn't validate according to its rules in "  . get_class($this));
        }

        return $this;
    }

    /**
     * Force set record data without validation
     *
     * @param array|ArrayAccess $recordData
     */
    public function force($recordData)
    {
        $this->_recordData = (array)$recordData;
    }

    /**
     * Non-magic getter
     *
     * @param $name mixed
     * @return mixed
     */
    public function get($name)
    {
        return $this->$name;
    }

    /**
     * Does the record have a set value for the given field?
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return
            array_key_exists($name, $this->getDefaults()) ||
            array_key_exists($name, $this->_recordData);
    }

    /**
     * Magic getter, throws an exception if the field name is invalid, or is unset without defaults
     *
     * @param $name
     * @return mixed
     * @throws Exception if field name is invalid for this record or if unset without defaults
     */
    final public function __get($name)
    {
        if (!in_array($name, $this->getFields())) {
            throw new Exception("Field name $name invalid in this record");
        } elseif (!array_key_exists($name, $this->_recordData)) {
            $defaults = $this->getDefaults();
            if (array_key_exists($name, $defaults)) {
                return $defaults[$name];
            } else {
                throw new Exception("Field name $name isn't set and lacks default value in this record");
            }
        } else {
            return $this->_recordData[$name];
        }
    }

    /**
     * Returns a new record
     *
     * @param array|ArrayAccess $init Initial record data
     * @return static
     */
    public function make($init = []) {
        $record = new static($this->_validator);

        foreach ($init as $key => $value) {
            $record->validate($key, $value);
        }

        $record->force($init);

        return $record;
    }

    /**
     * Merge the record with another structure
     *
     * @param mixed $mergee
     * @return static
     * @throws Exception
     */
    public function merge($mergee) {
        $data = $this->_recordData;
        $fields = $this->getFields();
        $record = new static($this->_validator);

        foreach ($mergee as $key => $value) {
            if (in_array($key, $fields)) {
                $record->validate($key, $value);
                $data[$key] = $value;
            }
        }

        $record->force($data);

        return $record;
    }

    /**
     * Compare two records by content
     *
     * @param Record $comparee
     * @return bool
     */
    public function equals(Record $comparee)
    {
        return $this === $comparee || $this->toArray() == $comparee->toArray();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_reduce($this->getFields(), function($carry, $current) {
            $value = $this->get($current);

            if (!is_string($value) && method_exists($value, "toArray")) {
                $carry[$current] = $value->toArray();
            } else {
                $carry[$current] = $value;
            }

            return $carry;
        }, []);
    }

    /**
     * JSON compatibility
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Iterator compatibility
     */
    public function rewind()
    {
        $this->_recordIteratorIndex = 0;
    }

    /**
     * Iterator compatibility
     *
     * @return array
     */
    public function current()
    {
        return $this->get($this->key());
    }

    /**
     * Iterator compatibility
     *
     * @return int
     */
    public function key()
    {
        return $this->getFields()[$this->_recordIteratorIndex];
    }

    /**
     * Iterator compatibility
     */
    public function next()
    {
        ++$this->_recordIteratorIndex;
    }

    /**
     * Iterator compatibility
     *
     * @return bool
     */
    public function valid()
    {
        if (count($this) <= $this->_recordIteratorIndex) {
            return false;
        } else {
            return $this->has($this->key());
        }
    }

    /**
     * Countable compatibility
     *
     * @return int
     */
    public function count()
    {
        return array_reduce($this->getFields(), function($carry, $field) {
            return $this->has($field) ? $carry + 1 : $carry;
        }, 0);
    }
}