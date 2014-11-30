<?php

namespace Solution10\ORM\Tests\ActiveRecord;

use PHPUnit_Framework_TestCase;
use Solution10\ORM\ActiveRecord\Exception\ValidationException;
use Solution10\ORM\ActiveRecord\Model;

/**
 * Class FieldTests
 *
 * Base abstract test class that all field tests can inherit from to
 * cover their basic functionality
 *
 * @package Solution10\ORM\Tests\ActiveRecord
 */
abstract class FieldTests extends PHPUnit_Framework_TestCase
{
    /**
     * @return string
     */
    abstract protected function fieldClass();

    /**
     * @return \Solution10\ORM\ActiveRecord\Field
     */
    abstract protected function fieldInstance();

    public function testConstruct()
    {
        $instance = $this->fieldInstance();
        $this->assertInstanceOf($this->fieldClass(), $instance);
        $this->assertInstanceOf('Solution10\\ORM\\ActiveRecord\\Field', $instance);
    }

    public function testGet()
    {
        // For most fields, there's no transform so this is a straight pass back
        $m = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $i = $this->fieldInstance();
        $this->assertNull($i->get($m, 'name', null));
        $this->assertEquals('Alex', $i->get($m, 'name', 'Alex'));
        $this->assertEquals(27.5, $i->get($m, 'age', 27.5));
    }

    public function testSet()
    {
        // For most fields, there's no transform so this is a straight pass back
        $m = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $i = $this->fieldInstance();
        $this->assertNull($i->set($m, 'name', null));
        $this->assertEquals('Alex', $i->set($m, 'name', 'Alex'));
        $this->assertEquals(27.5, $i->set($m, 'age', 27.5));
    }

    public function testSave()
    {
        // For most fields, there's no transform so this is a straight pass back
        $m = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $i = $this->fieldInstance();
        $this->assertNull($i->save($m, 'name', null));
        $this->assertEquals('Alex', $i->save($m, 'name', 'Alex'));
        $this->assertEquals(27.5, $i->save($m, 'age', 27.5));
    }
}
