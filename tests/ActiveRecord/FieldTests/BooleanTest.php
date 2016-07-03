<?php

namespace Solution10\ORM\Tests\ActiveRecord\FieldTests;

use Solution10\ORM\ActiveRecord\Model;
use Solution10\ORM\Tests\ActiveRecord\FieldTests;
use Solution10\ORM\ActiveRecord\Field\Boolean;

class BooleanTests extends FieldTests
{
    protected function fieldClass()
    {
        return 'Solution10\\ORM\\ActiveRecord\\Field\\Boolean';
    }

    protected function fieldInstance()
    {
        return new Boolean();
    }

    public function testSet()
    {
        $m = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $b = new Boolean();
        $this->assertTrue(is_integer($b->set($m, 'bool', 1)));
        $this->assertEquals(1, $b->set($m, 'bool', 1));

        $this->assertTrue(is_integer($b->set($m, 'bool', false)));
        $this->assertEquals(0, $b->set($m, 'bool', false));
    }

    public function testSetWeirdCasting()
    {
        $m = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $b = new Boolean();

        $this->assertEquals(1, $b->set($m, 'bool', 1));
        $this->assertEquals(1, $b->set($m, 'bool', true));
        $this->assertEquals(1, $b->set($m, 'bool', 'truthy'));

        $this->assertEquals(0, $b->set($m, 'bool', 0));
        $this->assertEquals(0, $b->set($m, 'bool', false));
        $this->assertEquals(0, $b->set($m, 'bool', null));
    }

    public function testDatabaseToPHP()
    {
        $m = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $b = new Boolean();
        $this->assertTrue(is_bool($b->databaseToPHP($m, 'bool', 1)));
        $this->assertTrue($b->databaseToPHP($m, 'bool', 1));

        $this->assertTrue(is_bool($b->databaseToPHP($m, 'bool', 0)));
        $this->assertFalse($b->databaseToPHP($m, 'bool', 0));
    }
}
