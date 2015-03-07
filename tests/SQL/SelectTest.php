<?php

namespace Solution10\ORM\Tests\SQL;

use PHPUnit_Framework_TestCase;
use Solution10\ORM\SQL\Select;

class SelectTest extends PHPUnit_Framework_TestCase
{
    /*
     * ------------------- SELECT testing -------------------------
     */
    public function testSelect()
    {
        $query = new Select;

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

    public function testSelectSQL()
    {
        $query = new Select;
        $this->assertEquals('SELECT *', $query->buildSelectSQL());

        $query->select('name');
        $this->assertEquals('SELECT name', $query->buildSelectSQL());

        $query->select(['name', 'age']);
        $this->assertEquals('SELECT name, age', $query->buildSelectSQL());

        $query->select(['name' => 'myname']);
        $this->assertEquals('SELECT name AS myname', $query->buildSelectSQL());

        $query->select(['name' => 'myname', 'age' => 'years_alive']);
        $this->assertEquals('SELECT name AS myname, age AS years_alive', $query->buildSelectSQL());
    }

    /*
     * ------------------ FROM testing -----------------------------
     */

    public function testFromWithAlias()
    {
        $query = new Select;

        // With alias
        $query->from('users', 'u');
        $this->assertEquals(['users' => 'u'], $query->from());
    }

    public function testFromWithoutAlias()
    {
        $query = new Select;
        $query->from('noalias');

        $this->assertEquals(['noalias' => null], $query->from());
    }

    public function testFromSQL()
    {
        $query = new Select;
        $this->assertEquals('', $query->buildFromSQL());

        $query = new Select;
        $query->from('users');
        $this->assertEquals('FROM users', $query->buildFromSQL());

        $query = new Select;
        $query->from('users', 'u');
        $this->assertEquals('FROM users u', $query->buildFromSQL());

        $query = new Select;
        $query
            ->from('users', 'u')
            ->from('roles', 'r');

        $this->assertEquals('FROM users u, roles r', $query->buildFromSQL());
    }

    /*
     * ------------------- JOIN testing ----------------------------
     */

    public function testJoinDefault()
    {
        $query = new Select;

        $this->assertEquals($query, $query->join('users', 'posts', 'users.id = posts.user_id'));
        $this->assertEquals([[
            'type' => 'INNER',
            'left' => 'users',
            'right' => 'posts',
            'predicate' => 'users.id = posts.user_id'
        ]], $query->join());
    }

    public function testJoinExplicitType()
    {
        $query = new Select;

        $this->assertEquals($query, $query->join('users', 'posts', 'users.id = posts.user_id', 'LEFT'));
        $this->assertEquals([[
            'type' => 'LEFT',
            'left' => 'users',
            'right' => 'posts',
            'predicate' => 'users.id = posts.user_id'
        ]], $query->join());
    }

    public function testMultiJoin()
    {
        $query = new Select;

        $query
            ->join('users', 'posts', 'users.id = posts.user_id')
            ->join('users', 'comments', 'users.id = comments.user_id', 'LEFT')
        ;
        $this->assertEquals([
            [
                'type' => 'INNER',
                'left' => 'users',
                'right' => 'posts',
                'predicate' => 'users.id = posts.user_id'
            ],
            [
                'type' => 'LEFT',
                'left' => 'users',
                'right' => 'comments',
                'predicate' => 'users.id = comments.user_id'
            ]
        ], $query->join());
    }

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionMessage    Unknown join type "MOO"
     */
    public function testJoinBadType()
    {
        $query = new Select;
        $query->join('users', 'posts', 'users.id = p.user_id', 'MOO');
    }

    public function testJoinSQL()
    {
        $query = new Select;
        $this->assertEquals('', $query->buildJoinSQL());

        $query = new Select;
        $query->join('users', 'posts', 'users.id = posts.user_id');
        $this->assertEquals('JOIN posts ON users.id = posts.user_id', $query->buildJoinSQL());

        $query = new Select;
        $query->join('users', 'posts', 'users.id = posts.user_id', 'LEFT');
        $this->assertEquals('LEFT JOIN posts ON users.id = posts.user_id', $query->buildJoinSQL());

        $query = new Select;
        $query->join('users', 'posts', 'users.id = posts.user_id', 'RIGHT');
        $this->assertEquals('RIGHT JOIN posts ON users.id = posts.user_id', $query->buildJoinSQL());

        $query = new Select;
        $query->join('users', 'posts', 'users.id = posts.user_id');
        $query->join('users', 'comments', 'users.id = comments.user_id', 'LEFT');
        $this->assertEquals(
            'JOIN posts ON users.id = posts.user_id LEFT JOIN comments ON users.id = comments.user_id',
            $query->buildJoinSQL()
        );
    }

    /*
     * ------------------ GROUP BY testing ---------------------
     */

    public function testGroupBy()
    {
        $query = new Select;
        $this->assertEquals([], $query->groupBy());

        $query = new Select;
        $this->assertEquals($query, $query->groupBy('name'));
        $this->assertEquals(['name'], $query->groupBy());

        $query = new Select;
        $query
            ->groupBy('name')
            ->groupBy('age');

        $this->assertEquals(['name', 'age'], $query->groupBy());

        $query = new Select;
        $query->groupBy(['name', 'age']);
        $this->assertEquals(['name', 'age'], $query->groupBy());
    }

    public function testGroupBySQL()
    {
        $query = new Select;
        $this->assertEquals('', $query->buildGroupBySQL());

        $query = new Select;
        $query->groupBy('name');
        $this->assertEquals('GROUP BY name', $query->buildGroupBySQL());

        $query = new Select;
        $query
            ->groupBy('name')
            ->groupBy('age');

        $this->assertEquals('GROUP BY name, age', $query->buildGroupBySQL());

        $query = new Select;
        $query->groupBy(['name', 'age']);
        $this->assertEquals('GROUP BY name, age', $query->buildGroupBySQL());
    }

    /*
     * ------------------ ORDER BY testing -------------------------
     */

    public function testOrderBySingle()
    {
        $query = new Select;

        $this->assertEquals($query, $query->orderBy('name', 'DESC'));
        $this->assertEquals(['name' => 'DESC'], $query->orderBy());

        // And test the default:
        $query->orderBy('name');
        $this->assertEquals(['name' => 'ASC'], $query->orderBy());
    }

    public function testOrderByArray()
    {
        $query = new Select;

        $order = [
            'name' => 'ASC',
            'age' => 'DESC',
        ];

        $this->assertEquals($query, $query->orderBy($order));
        $this->assertEquals($order, $query->orderBy());
    }

    public function testOrderBySQL()
    {
        $query = new Select;
        $this->assertEquals('', $query->buildOrderBySQL());

        $query = new Select;
        $query->orderBy('name', 'ASC');
        $this->assertEquals('ORDER BY name ASC', $query->buildOrderBySQL());

        $query = new Select;
        $query->orderBy('name', 'ASC');
        $query->orderBy('age', 'DESC');
        $this->assertEquals('ORDER BY name ASC, age DESC', $query->buildOrderBySQL());

        $query = new Select;
        $query->orderBy([
            'name'  => 'ASC',
            'age'   => 'DESC'
        ]);
        $this->assertEquals('ORDER BY name ASC, age DESC', $query->buildOrderBySQL());
    }
}
