<?php
/**
 * Unit test for Immutable Record
 *
 * @author Oskar Thornblad
 */

declare(strict_types=1);

namespace Prewk;

use PHPUnit\Framework\TestCase;
use Prewk\Record\ValidatorInterface;
use stdClass;

class TestWithDefaultsRecord extends Record
{
    /**
     * Get fields
     * @return array
     */
    protected function getFields(): array
    {
        return ["foo", "bar", "baz"];
    }

    /**
     * Get defaults
     * @return array
     */
    protected function getDefaults(): array
    {
        return ["foo" => 123, "bar" => null, "baz" => 456];
    }

    /**
     * Get rules
     * @return array
     */
    protected function getRules(): array
    {
        return ["foo" => "rule1", "bar" => "rule2", "baz" => "rule3"];
    }
}

class TestWithoutDefaultsRecord extends Record
{
    /**
     * Get fields
     * @return array
     */
    protected function getFields(): array
    {
        return ["foo", "bar", "baz"];
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

    public function validate($value, $rule): bool
    {
        $this->validated[] = [$value, $rule];

        return $this->answer;
    }

    public function setAnswer($answer)
    {
        $this->answer = $answer;
    }
}

class RecordTest extends TestCase
{
    private $validator;

    public function setUp()
    {
        parent::setUp();

        $this->validator = new MockValidator();
    }

    public function test_that_the_record_sets_returns_new_records()
    {
        $record1 = new TestWithDefaultsRecord;

        $record2 = $record1->set("foo", "Foo");

        $this->assertEquals(123, $record1->foo);
        $this->assertEquals("Foo", $record2->foo);
    }

    public function test_that_the_record_updates_returns_new_records()
    {
        $record1 = new TestWithDefaultsRecord;

        $record2 = $record1->update("foo", function($value) {
            return $value . "Foo";
        });

        $this->assertEquals(123, $record1->foo);
        $this->assertEquals("123Foo", $record2->foo);
    }

    /**
     * @expectedException \Exception
     */
    public function test_that_updating_unset_required_fields_throws()
    {
        $record1 = new TestWithoutDefaultsRecord;

        $record2 = $record1->update("foo", function() {});
    }

    /**
     * @expectedException \Exception
     */
    public function test_that_the_record_doesnt_magically_set()
    {
        $record = new TestWithDefaultsRecord;

        $record->foo = "Foo";
    }

    public function test_that_has_works_on_an_unset_non_default_field()
    {
        $record = new TestWithoutDefaultsRecord;

        $this->assertFalse($record->has("foo"));
    }

    public function test_that_the_record_defaults_correctly()
    {
        $record = new TestWithDefaultsRecord;

        $record = $record->set("foo", "Foo");

        $this->assertEquals("Foo", $record->foo);
        $this->assertNull($record->bar);
        $this->assertEquals(456, $record->baz);
    }

    public function test_that_the_non_magic_set_get_works_correctly()
    {
        $record = new TestWithDefaultsRecord;

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
    public function test_that_getting_a_non_defaulted_field_without_a_value_throws()
    {
        $record = new TestWithoutDefaultsRecord;

        $record->get("foo");
    }

    public function test_that_it_makes_with_a_stdClass()
    {
        $withoutDefaults = new TestWithoutDefaultsRecord;
        $obj = new stdClass;
        $obj->foo = 123;
        $obj->bar = null;
        $obj->baz = 456;

        $record = $withoutDefaults->make($obj);

        $this->assertEquals(123, $record->get("foo"));
        $this->assertEquals(null, $record->get("bar"));
        $this->assertEquals(456, $record->get("baz"));
    }

    public function test_that_equals_works()
    {
        $withDefaults = new TestWithDefaultsRecord;
        $withoutDefaults = new TestWithoutDefaultsRecord;

        $record = $withoutDefaults->make([
            "foo" => 123,
            "bar" => null,
            "baz" => 456,
        ]);

        $this->assertTrue($withDefaults->equals($record));

        $record1 = (new TestWithoutDefaultsRecord)->make([
            "foo" => [],
            "bar" => [
                "lorem" => "ipsum",
                "dolor" => "amet",
            ],
            "baz" => 456,
        ]);

        $record2 = (new TestWithoutDefaultsRecord)->make([
            "foo" => [],
            "bar" => [
                "lorem" => "ipsum",
                "dolor" => "amet",
            ],
            "baz" => 456,
        ]);

        $this->assertTrue($record1->equals($record2));
        $this->assertFalse($record1->equals($record2->set("baz", 789)));
    }

    public function test_that_it_merges_with_an_array()
    {
        $record = new TestWithDefaultsRecord;

        $record = $record->merge([
            "foo" => "Foo",
            "bar" => "Bar",
        ]);

        $this->assertEquals("Foo", $record->get("foo"));
        $this->assertEquals("Bar", $record->get("bar"));
        $this->assertEquals(456, $record->get("baz"));
    }

    public function test_that_merging_filters_invalid_keys()
    {
        $record = new TestWithDefaultsRecord;

        $record = $record->merge([
            "foo" => "Foo",
            "bar" => "Bar",
            "qux" => "IGNORE",
        ]);

        $this->assertEquals("Foo", $record->get("foo"));
        $this->assertEquals("Bar", $record->get("bar"));
        $this->assertEquals(456, $record->get("baz"));
    }

    public function test_that_it_merges_with_another_record()
    {
        $record = new TestWithDefaultsRecord;

        $mergee = $record->make([
            "foo" => "Foo",
            "bar" => "Bar",
        ]);

        $record = $record->merge($mergee);

        $this->assertEquals("Foo", $record->get("foo"));
        $this->assertEquals("Bar", $record->get("bar"));
        $this->assertEquals(456, $record->get("baz"));
    }

    /**
     * @expectedException \Exception
     */
    public function test_that_getting_invalid_field_names_throws_exception()
    {
        $record = new TestWithDefaultsRecord;

        $record->get("qux");
    }

    /**
     * @expectedException \Exception
     */
    public function test_that_setting_invalid_fields_throws_exception()
    {
        $record = new TestWithDefaultsRecord;

        $record->set("qux", "Qux");
    }

    public function test_valid_setting_with_rules()
    {
        $record = new TestWithDefaultsRecord($this->validator);
        $this->validator->setAnswer(true);

        $record = $record->set("foo", 789);

        $this->assertEquals($this->validator->validated[0], [789, "rule1"]);
    }

    /**
     * @expectedException \Exception
     */
    public function test_invalid_setting_with_rules()
    {
        $record = new TestWithDefaultsRecord($this->validator);
        $this->validator->setAnswer(false);

        $record->set("foo", 789);
    }


    public function test_arrayable_interface()
    {
        $record = new TestWithDefaultsRecord;

        $array = $record->set("foo", "Foo")->toArray();

        $this->assertEquals("Foo", $array["foo"]);
        $this->assertNull($array["bar"]);
        $this->assertEquals(456, $array["baz"]);
    }

    public function test_recursive_arrayable_interface()
    {
        $record = new TestWithDefaultsRecord;
        $array = $record->set("foo", $record)->toArray();

        $this->assertEquals($record->toArray(), $array["foo"]);
    }

    /**
     * @expectedException \Exception
     */
    public function test_that_it_doesnt_unset()
    {
        $record = new TestWithDefaultsRecord;

        unset($record["foo"]);
    }

    public function test_jsonable_interface()
    {
        $record = new TestWithDefaultsRecord;

        $json = json_encode($record);
        $obj = json_decode($json);

        $this->assertEquals(123, $obj->foo);
        $this->assertNull($obj->bar);
        $this->assertEquals(456, $obj->baz);
    }

    public function test_array_access_interface_get()
    {
        $record = new TestWithDefaultsRecord;

        $this->assertEquals(123, $record->foo);
        $this->assertEquals(123, $record["foo"]);
    }
    public function test_array_access_interface_isset()
    {
        $withDefaults = new TestWithDefaultsRecord;
        $withoutDefaults  = new TestWithoutDefaultsRecord;

        $this->assertTrue(isset($withDefaults["foo"]));
        $this->assertFalse(isset($withDefaults["qux"]));
        $this->assertFalse(isset($withoutDefaults["foo"]));
    }

    /**
     * @expectedException \Exception
     */
    public function test_that_array_access_interface_set_throws()
    {
        $record = new TestWithDefaultsRecord;

        $record["foo"] = 123;
    }

    /**
     * @expectedException \Exception
     */
    public function test_that_array_access_interface_throws_exception()
    {
        $record = new TestWithDefaultsRecord;
        $test = $record["qux"];
    }

    public function test_iterator_interface()
    {
        $record = new TestWithDefaultsRecord;

        $this->assertEquals(3, count($record));
        $keys = [];
        $values = [];
        foreach ($record as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
            if ($key === "foo") {
                $this->assertEquals(123, $value);
            } elseif ($key === "bar") {
                $this->assertNull($value);
            } elseif ($key === "baz") {
                $this->assertEquals(456, $value);
            }
        }

        $this->assertEquals(["foo", "bar", "baz"], $keys);
        $this->assertEquals([123, null, 456], $values);
    }
}