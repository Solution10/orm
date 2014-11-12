<?php

namespace Solution10\ORM\Tests\ActiveRecord;

use Solution10\ORM\ActiveRecord\Query;
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

        $where = $query->where();
        $this->assertCount(1, $where);
        $this->assertEquals([
                'join' => 'AND',
                'field' => 'name',
                'operator' => '=',
                'value' => 'Alex'
            ], $where[0]);
    }

    public function testOrWhereBasic()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $this->assertEquals($query, $query->orWhere('name', '=', 'Alex'));

        $where = $query->where();
        $this->assertCount(1, $where);
        $this->assertEquals([
                'join' => 'OR',
                'field' => 'name',
                'operator' => '=',
                'value' => 'Alex',
            ], $where[0]);
    }

    public function testWhereGroupBasic()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $this->assertEquals(
            $query,
            $query->where(function (Query $query) {
                $query->where('name', '=', 'Alex');
            })
        );

        $this->assertEquals([[
                'join' => 'AND',
                'sub' => [
                    [
                        'join' => 'AND',
                        'field' => 'name',
                        'operator' => '=',
                        'value' => 'Alex',
                    ]
                ],
            ]], $query->where());
    }

    public function testOrWhereGroupBasic()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $this->assertEquals(
            $query,
            $query->orWhere(function (Query $query) {
                $query->orWhere('name', '=', 'Alex');
            })
        );

        $this->assertEquals([[
                'join' => 'OR',
                'sub' => [
                    [
                        'join' => 'OR',
                        'field' => 'name',
                        'operator' => '=',
                        'value' => 'Alex',
                    ]
                ],
            ]], $query->where());
    }

    /**
     * Time for a final, large and complex query to test the where() and orWhere() clauses.
     */
    public function testWhereComplex()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $query
            ->where('name', '=', 'Alex')
            ->orWhere('name', '=', 'Lucie')
            ->where(function (Query $query) {
                $query
                    ->where('city', '=', 'London')
                    ->where('country', '=', 'GB');
            })
            ->orWhere(function (Query $query) {
                $query
                    ->where('city', '=', 'Toronto')
                    ->where('country', '=', 'CA')
                    ->orWhere(function (Query $query) {
                        $query->where('active', '!=', true);
                    });
            });

        $where = $query->where();
        $this->assertCount(4, $where);
        $this->assertEquals([
            ['join' => 'AND', 'field' => 'name', 'operator' => '=', 'value' => 'Alex'],
            ['join' => 'OR', 'field' => 'name', 'operator' => '=', 'value' => 'Lucie'],
            [
                'join' => 'AND',
                'sub' => [
                    ['join' => 'AND', 'field' => 'city', 'operator' => '=', 'value' => 'London'],
                    ['join' => 'AND', 'field' => 'country', 'operator' => '=', 'value' => 'GB']
                ]
            ],
            [
                'join' => 'OR',
                'sub' => [
                    ['join' => 'AND', 'field' => 'city', 'operator' => '=', 'value' => 'Toronto'],
                    ['join' => 'AND', 'field' => 'country', 'operator' => '=', 'value' => 'CA'],
                    [
                        'join' => 'OR',
                        'sub' => [
                            ['join' => 'AND', 'field' => 'active', 'operator' => '!=', 'value' => true],
                        ]
                    ]
                ]
            ]
        ], $where);
    }

    public function testOrderBySingle()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $this->assertEquals($query, $query->orderBy('name', 'DESC'));
        $this->assertEquals(['name' => 'DESC'], $query->orderBy());

        // And test the default:
        $query->orderBy('name');
        $this->assertEquals(['name' => 'ASC'], $query->orderBy());
    }

    public function testOrderByArray()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $order = [
            'name' => 'ASC',
            'age' => 'DESC',
        ];

        $this->assertEquals($query, $query->orderBy($order));
        $this->assertEquals($order, $query->orderBy());
    }

    public function testLimit()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $this->assertEquals($query, $query->limit(10));
        $this->assertEquals(10, $query->limit());

        $query->limit('20');
        $this->assertEquals(20, $query->limit());
    }

    public function testOffset()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $this->assertEquals($query, $query->offset(10));
        $this->assertEquals(10, $query->offset());

        $query->offset('20');
        $this->assertEquals(20, $query->offset());
    }

    public function testJoinDefault()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $this->assertEquals($query, $query->join('users', 'posts', 'p', 'users.id = p.user_id'));
        $this->assertEquals([[
                'type' => 'INNER',
                'left' => 'users',
                'right' => 'posts',
                'rightAlias' => 'p',
                'predicate' => 'users.id = p.user_id'
            ]], $query->join());
    }

    public function testJoinExplicitType()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $this->assertEquals($query, $query->join('users', 'posts', 'p', 'users.id = p.user_id', 'LEFT'));
        $this->assertEquals([[
                'type' => 'LEFT',
                'left' => 'users',
                'right' => 'posts',
                'rightAlias' => 'p',
                'predicate' => 'users.id = p.user_id'
            ]], $query->join());
    }

    public function testMultiJoin()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $query
            ->join('users', 'posts', 'p', 'users.id = p.user_id')
            ->join('users', 'comments', 'c', 'users.id = c.user_id', 'LEFT')
        ;
        $this->assertEquals([
            [
                'type' => 'INNER',
                'left' => 'users',
                'right' => 'posts',
                'rightAlias' => 'p',
                'predicate' => 'users.id = p.user_id'
            ],
            [
                'type' => 'LEFT',
                'left' => 'users',
                'right' => 'comments',
                'rightAlias' => 'c',
                'predicate' => 'users.id = c.user_id'
            ]
        ], $query->join());
    }

    /**
     * @expectedException       \Solution10\ORM\ActiveRecord\Exception\QueryException
     * @expectedExceptionCode   \Solution10\ORM\ActiveRecord\Exception\QueryException::BAD_JOIN_TYPE
     */
    public function testJoinBadType()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $query->join('users', 'posts', 'p', 'users.id = p.user_id', 'MOO');
    }

    public function testValues()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $values = [
            'username' => 'Alex',
            'city' => 'London',
        ];

        $this->assertEquals([], $query->values());
        $this->assertEquals($query, $query->values($values));
        $this->assertEquals($values, $query->values());
    }
}
