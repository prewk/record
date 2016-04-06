<?php
/**
 * Unit test for Immutable Record
 *
 * @author Oskar Thornblad
 */

namespace Prewk;

use PHPUnit_Framework_TestCase;
use Prewk\Record\ValidatorInterface;
use TestCase;

class TestRecord extends Record
{
    /**
     * Get fields
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
        return ["foo" => "rule1", "bar" => "rule2", "baz" => "rule3"];
    }
}

/**
 * Class MockValidator
 * @package PagePicnic\Services\Records
 */
class MockValidator implements ValidatorInterface
{
    public $answer = true;
    public $validated = [];
    
    public function validate($value, $rule)
    {
        $this->validated[] = [$value, $rule];

        return $this->answer;
    }
    
    public function setAnswer($answer)
    {
        $this->answer = $answer;
    }
}

class RecordTest extends PHPUnit_Framework_TestCase
{
    private $validator;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->validator = new MockValidator();
    }

    public function test_that_the_record_is_immutable()
    {
        $record1 = new TestRecord();

        $record2 = $record1->set("foo", "Foo");

        $this->assertEquals(123, $record1->foo);
        $this->assertEquals("Foo", $record2->foo);
    }

    public function test_that_the_record_defaults_correctly()
    {
        $record = new TestRecord();

        $record = $record->set("foo", "Foo");

        $this->assertEquals("Foo", $record->foo);
        $this->assertNull($record->bar);
        $this->assertEquals(456, $record->baz);
    }

    public function test_that_the_non_magic_set_get_works_correctly()
    {
        $record = new TestRecord();

        $record = $record
            ->set("foo", "Foo")
            ->set("bar", "Bar")
            ->set("baz", "Baz");

        $this->assertEquals("Foo", $record->get("foo"));
        $this->assertEquals("Bar", $record->get("bar"));
        $this->assertEquals("Baz", $record->get("baz"));
    }

    /**
     * @expectedException \Exception
     */
    public function test_that_getting_invalid_field_names_throws_exception()
    {
        $record = new TestRecord();

        $record->get("qux");
    }

    /**
     * @expectedException \Exception
     */
    public function test_that_setting_invalid_fields_throws_exception()
    {
        $record = new TestRecord();

        $record->set("qux", "Qux");
    }

    public function test_valid_setting_with_rules()
    {
        $record = new TestRecord($this->validator);
        $this->validator->setAnswer(true);

        $record = $record->set("foo", 789);

        $this->assertEquals($this->validator->validated[0], [789, "rule1"]);
    }

    /**
     * @expectedException \Exception
     */
    public function test_invalid_setting_with_rules()
    {
        $record = new TestRecord($this->validator);
        $this->validator->setAnswer(false);

        $record->set("foo", 789);
    }


    public function test_arrayable_interface()
    {
        $record = new TestRecord();

        $array = $record->set("foo", "Foo")->toArray();

        $this->assertEquals("Foo", $array["foo"]);
        $this->assertNull($array["bar"]);
        $this->assertEquals(456, $array["baz"]);
    }

    public function test_recursive_arrayable_interface()
    {
        $record = new TestRecord();
        $array = $record->set("foo", $record)->toArray();

        $this->assertEquals($record->toArray(), $array["foo"]);
    }

    public function test_jsonable_interface()
    {
        $record = new TestRecord();

        $json = json_encode($record);
        $obj = json_decode($json);

        $this->assertEquals(123, $obj->foo);
        $this->assertNull($obj->bar);
        $this->assertEquals(456, $obj->baz);
    }

    public function test_array_access_interface()
    {
        $record = new TestRecord();

        $this->assertEquals(123, $record->foo);
        $this->assertEquals(123, $record["foo"]);
    }

    /**
     * @expectedException \Exception
     */
    public function test_that_array_access_interface_throws_exception()
    {
        $record = new TestRecord();
        $test = $record["qux"];
    }

    public function test_iterator_interface()
    {
        $record = new TestRecord();

        $this->assertEquals(3, count($record));
        foreach ($record as $key => $value) {
            if ($key === "foo") {
                $this->assertEquals(123, $value);
            } elseif ($key === "bar") {
                $this->assertNull($value);
            } elseif ($key === "baz") {
                $this->assertEquals(456, $value);
            }
        }
    }
}