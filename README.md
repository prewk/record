# Immutable Validatable Record

## Installation

`composer require prewk/record`

## Simple usage

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

## Laravel related

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

class FooRecordServiceProvider extends Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->bind(FooRecord::class, function() {
            return new FooRecord($this->app->make(\Prewk\Record\Laravel\ValidatorWrapper));
        });
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

# License

MIT
