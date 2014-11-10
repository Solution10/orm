<?php

namespace Solution10\ORM\Tests\ActiveRecord;

use Solution10\ORM\ActiveRecord\Query;
use Solution10\ORM\ConnectionManager;
use PHPUnit_Framework_TestCase;

class QueryTest extends PHPUnit_Framework_TestCase
{
    protected $conn;

    public function testConstructorValidModel()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $this->assertInstanceOf('Solution10\ORM\ActiveRecord\Query', $query);
    }

    /**
     * @expectedException       \Solution10\ORM\ActiveRecord\Exception\QueryException
     * @expectedExceptionCode   \Solution10\ORM\ActiveRecord\Exception\QueryException::UNKNOWN_MODEL
     */
    public function testConstructorInvalidModel()
    {
        new Query('My\Unknown\Model');
    }

    public function testGetSetModel()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $this->assertEquals('Solution10\ORM\Tests\ActiveRecord\Stubs\User', $query->model());

        $this->assertEquals($query, $query->model('Solution10\ORM\Tests\ActiveRecord\Stubs\BlogPost'));
        $this->assertEquals('Solution10\ORM\Tests\ActiveRecord\Stubs\BlogPost', $query->model());
    }

    /**
     * @expectedException       \Solution10\ORM\ActiveRecord\Exception\QueryException
     * @expectedExceptionCode   \Solution10\ORM\ActiveRecord\Exception\QueryException::UNKNOWN_MODEL
     */
    public function testSetUnknownModel()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $query->model('Unknown\Model');
    }

    public function testSelect()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        // No alias
        $this->assertEquals($query, $query->select('id'));
        $this->assertEquals(['id'], $query->select());

        // Aliasing
        $query->select(['id' => 'my_id']);
        $this->assertEquals(['id' => 'my_id'], $query->select());

        // Array, without aliasing
        $query->select(['id', 'username', 'password']);
        $this->assertEquals(['id', 'username', 'password'], $query->select());

        // Array with aliasing
        $query->select(['id' => 'my_id', 'username' => 'my_username']);
        $this->assertEquals(['id' => 'my_id', 'username' => 'my_username'], $query->select());
    }

    public function testFromNoAlias()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        // No alias
        $this->assertEquals($query, $query->from('users'));
        $this->assertEquals(['users'], $query->from());
    }

    public function testFromWithAlias()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        // With alias
        $query->from('users', 'u');
        $this->assertEquals(['users' => 'u'], $query->from());
    }

    public function testFromMixedAlias()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        // With mixture:
        $query->from('users', 'u');
        $query->from('usernames');
        $this->assertEquals(['users' => 'u', 'usernames'], $query->from());
    }

    /**
     * Testing where() in it's most basic form field, operator, value
     */
    public function testWhereBasic()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $this->assertEquals($query, $query->where('name', '=', 'Alex'));
        print_r($query->where());
    }
}
