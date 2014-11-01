<?php

namespace Solution10\ORM\Tests\ActiveRecord\ModelTests;

use Solution10\ORM\ActiveRecord\Model;

/**
 * These tests focus on the behaviours that don't require a database connection.
 * The reason for splitting out the tests is just to keep the execution runtime
 * down; we don't need a DB Connection for every test, so don't make one.
 */
class NonDatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException       \Solution10\ORM\ActiveRecord\Exception\ModelException
     * @expectedExceptionCode   \Solution10\ORM\ActiveRecord\Exception\ModelException::NO_INIT
     */
    public function testNoInitThrows()
    {
        Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\NoInitModel');
    }

    public function testSetGetSingle()
    {
        $model = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\BlogPost');
        $this->assertEquals($model, $model->set('title', 'Post Title'));
        $this->assertEquals('Post Title', $model->get('title'));
    }

    public function testSetGetMulti()
    {
        $model = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\BlogPost');
        $this->assertEquals($model, $model->set([
            'title' => 'Post Title',
            'body' => 'Post Body',
        ]));
        $this->assertEquals('Post Title', $model->get('title'));
        $this->assertEquals('Post Body', $model->get('body'));
    }

    public function testGetNotset()
    {
        $model = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\BlogPost');
        $this->assertNull($model->get('unknown'));
    }

    public function testGetDefault()
    {
        $model = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\BlogPost');
        $this->assertEquals('Rebecca', $model->get('author', 'Rebecca'));
    }

    public function testIsValueSet()
    {
        $object = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $this->assertFalse($object->isValueSet('name'));
        $object->set('name', 'Alex');
        $this->assertTrue($object->isValueSet('name'));
    }

    public function testSetAsSaved()
    {
        $object = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $object->set('name', 'Alex');

        // Test that name remains after save:
        $this->assertEquals($object, $object->setAsSaved());
        $this->assertEquals('Alex', $object->get('name'));
    }

    public function testChanges()
    {
        $object = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $object->set('name', 'Alex');

        $this->assertTrue($object->hasChanges());
        $this->assertEquals([
                'name' => 'Alex'
            ], $object->changes());
    }

    public function testSaveClearsChanges()
    {
        $object = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $object->set(['name' => 'Alex', 'city' => 'London']);

        $this->assertTrue($object->hasChanges());
        $object->setAsSaved();
        $this->assertFalse($object->hasChanges());
        $this->assertEquals([], $object->changes());
    }

    public function testset()
    {
        $object = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $object->set([
                'name' => 'Alex',
                'city' => 'London',
            ]);
        $object->setAsSaved();

        // That function should load everything into original immediately
        // and not report as changes:
        $this->assertFalse($object->hasChanges());
        $this->assertEquals([], $object->changes());

        // check that the values did actually save
        $this->assertEquals('Alex', $object->get('name'));
        $this->assertEquals('London', $object->get('city'));
    }

    public function testOriginal()
    {
        $object = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $object->set([
                'name' => 'Alex',
                'city' => 'London',
            ]);
        $object->setAsSaved();

        $object->set('name', 'Jake');
        $this->assertEquals('Alex', $object->original('name'));
        $this->assertEquals('London', $object->original('city'));
    }

    public function testGetOriginalPostSave()
    {
        $object = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $object->set([
                'name' => 'Alex',
                'city' => 'London',
            ]);

        $object->set('name', 'Jake');
        $object->setAsSaved();

        $this->assertEquals('Jake', $object->original('name'));
        $this->assertEquals('London', $object->original('city'));
    }

    public function testIsLoaded()
    {
        $object = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $this->assertFalse($object->isLoaded());
        $object->set('name', 'Alex');
        $object->setAsSaved();
        $this->assertTrue($object->isLoaded());

        // And check if loaded from resource:
        $loadedObject = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $loadedObject->set([
                'id' => 1,
                'name' => 'Alex',
            ]);
        $loadedObject->setAsSaved();
        $this->assertTrue($loadedObject->isLoaded());
    }
}
