<?php

namespace Solution10\ORM\Tests\ActiveRecord;

use Solution10\ORM\ActiveRecord\Query;
use PHPUnit_Framework_TestCase;

class QueryTest extends PHPUnit_Framework_TestCase
{
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
}
