# Immutable Validatable Record [![Build Status](https://travis-ci.org/prewk/record.svg)](https://travis-ci.org/prewk/record) [![Coverage Status](https://coveralls.io/repos/github/prewk/record/badge.svg?branch=master&)](https://coveralls.io/github/prewk/record?branch=master)


Validatable records with an API inspired by [Immutable Record](http://facebook.github.io/immutable-js/docs/#/Record) (but without the memory efficiency..)

## Installation

`composer require prewk/record`

## Simple usage

1. Extend `\Prewk\Record`
2. Define fields by implementing `getFields()`
3. Define (optional) defaults by implementing `getDefaults()`
4. Construct a base record
5. Create new records from that base record

````php
<?php

class FooRecord extends \Prewk\Record
{
    /**
     * Get record fields
     * @return array
     */
    protected function getFields()
    {
        return ["foo", "bar", "baz"];
    }

    /**
     * Get defaults
     * @return array
     */
    protected function getDefaults()
    {
        return ["foo" => 123, "bar" => null, "baz" => 456];
    }
}

$fooRecord = new FooRecord;

// Create a FooRecord
$record1 = $fooRecord->make(["foo" => 777, "bar" => 888]);
print_r($record1->asArray());
// -> ["foo" => 777, "bar" => 888, "baz" => 456]

// Immutibility
$record1->set("foo", "This value will disappear into the void");
print_r($record1->asArray());
// -> ["foo" => 777, "bar" => 888, "baz" => 456]

$record2 = $record1->set("foo", "This value will end up in record2");
print_r($record2->asArray());
// -> ["foo" => "Yay", "bar" => 888, "baz" => 456]

````

## Validation

1. Implement a validator class that extends `\Prewk\Record\ValidatorInterface`
2. Define rules on your record by implementing `getRules()`
3. Every mutation on your record will be routed through your validator

````php
class MyValidator implements \Prewk\Record\ValidatorInterface
{
   /**
     * Validates a value against a rule 
     * @param mixed $value
     * @param mixed $rule
     * @return bool
     */
    public function validate($value, $rule)
    {
        switch ($rule) {
            case "numeric":
                return is_numeric($value);
            default:
                throw new \Exception("Invalid rule!");
        }
    }
    
class FooRecord extends \Prewk\Record
{
    /**
     * Get record fields
     * @return array
     */
    protected function getFields()
    {
        return ["foo", "bar", "baz"];
    }

    /**
     * Get defaults
     * @return array
     */
    protected function getDefaults()
    {
        return ["foo" => 123, "bar" => null, "baz" => 456];
    }
    
    /**
     * Get rules
     * @return array
     */
    protected function getRules()
    {
        return ["foo" => "numeric"];
    }
}

$fooRecord = new FooRecord(new MyValidator);

$record1 = $fooRecord->make(["foo" => 100]);
print_r($record1->asArray());
// -> ["foo" => 777, "bar" => 888, "baz" => 456]

$record2 = $fooRecord->make(["foo" => "Will throw exception"]);
// -> throws exception "Field name foo didn't validate according to its rules"

````

## Injectable Laravel validated record

````php
<?php
class FooRecord extends \Prewk\Record\Laravel\Record
{
    protected function getFields()
    {
        return ["foo", "bar"];
    }
    
    protected function getRules()
    {
        return ["foo" => "in:1,2,3", "bar" => "numeric"];
    }
}

class FooController extends BaseController
{
    private $fooRecord;
    
    public function __construct(FooRecord $fooRecord)
    {
        $this->fooRecord = $fooRecord;
    }
    
    public function create(FooRequest $request)
    {
        $record = $this->fooRecord->make($request->all());
    }
}
````

# API

````php
// Make a new record from an existing record
$record->make(["foo" => "bar"]);

// Make a new record from setting
$newRecord = $record->set("foo", "bar");

// Check if a field has a value (if value has a default value this returns true)
$fooIsSet = $record->has("foo");

// Merge with an array
$newRecord = $record->merge(["baz" => "qux"]);
// ..or with an existing record
$newRecord = $record->merge($record2);
````

# License

MIT

