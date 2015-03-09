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
        // No alias
        $query = new Select;
        $this->assertEquals($query, $query->select('id'));
        $this->assertEquals(['id'], $query->select());

        // Aliasing
        $query = new Select;
        $query->select(['id' => 'my_id']);
        $this->assertEquals(['id' => 'my_id'], $query->select());

        // Array, without aliasing
        $query = new Select;
        $query->select(['id', 'username', 'password']);
        $this->assertEquals(['id', 'username', 'password'], $query->select());

        // Array with aliasing
        $query = new Select;
        $query->select(['id' => 'my_id', 'username' => 'my_username']);
        $this->assertEquals(['id' => 'my_id', 'username' => 'my_username'], $query->select());
    }

    public function testSelectSQL()
    {
        $query = new Select;
        $this->assertEquals('', $query->buildSelectSQL());

        $query = new Select;
        $query->select('name');
        $this->assertEquals('SELECT "name"', $query->buildSelectSQL());

        $query = new Select;
        $query->select(['name', 'age']);
        $this->assertEquals('SELECT "name", "age"', $query->buildSelectSQL());

        $query = new Select;
        $query->select(['name' => 'myname']);
        $this->assertEquals('SELECT "name" AS "myname"', $query->buildSelectSQL());

        $query = new Select;
        $query->select(['name' => 'myname', 'age' => 'years_alive']);
        $this->assertEquals('SELECT "name" AS "myname", "age" AS "years_alive"', $query->buildSelectSQL());
    }

    public function testResetSelect()
    {
        $query = new Select;
        $query
            ->select('name')
            ->select('age');

        $this->assertEquals(['name', 'age'], $query->select());

        $this->assertEquals($query, $query->resetSelect());
        $this->assertEquals([], $query->select());
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
        $this->assertEquals('FROM "users"', $query->buildFromSQL());

        $query = new Select;
        $query->from('users', 'u');
        $this->assertEquals('FROM "users" "u"', $query->buildFromSQL());

        $query = new Select;
        $query
            ->from('users', 'u')
            ->from('roles', 'r');

        $this->assertEquals('FROM "users" "u", "roles" "r"', $query->buildFromSQL());
    }

    public function testResetFrom()
    {
        $query = new Select;
        $query
            ->from('users')
            ->from('comments');

        $this->assertEquals(['users' => null, 'comments' => null], $query->from());

        $this->assertEquals($query, $query->resetFrom());
        $this->assertEquals([], $query->from());
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
        $this->assertEquals('JOIN "posts" ON "users"."id" = "posts"."user_id"', $query->buildJoinSQL());

        $query = new Select;
        $query->join('users', 'posts', 'users.id = posts.user_id', 'LEFT');
        $this->assertEquals('LEFT JOIN "posts" ON "users"."id" = "posts"."user_id"', $query->buildJoinSQL());

        $query = new Select;
        $query->join('users', 'posts', 'users.id = posts.user_id', 'RIGHT');
        $this->assertEquals('RIGHT JOIN "posts" ON "users"."id" = "posts"."user_id"', $query->buildJoinSQL());

        $query = new Select;
        $query->join('users', 'posts', 'users.id = posts.user_id');
        $query->join('users', 'comments', 'users.id = comments.user_id', 'LEFT');
        $this->assertEquals(
            'JOIN "posts" ON "users"."id" = "posts"."user_id"'."\n"
            .'LEFT JOIN "comments" ON "users"."id" = "comments"."user_id"',
            $query->buildJoinSQL()
        );
    }

    public function testResetJoins()
    {
        $query = new Select;
        $query->join('users', 'posts', 'users.id = posts.user_id');
        $query->join('users', 'comments', 'users.id = comments.user_id', 'LEFT');

        $this->assertCount(2, $query->join());

        $this->assertEquals($query, $query->resetJoins());
        $this->assertEquals([], $query->join());
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
        $this->assertEquals('GROUP BY "name"', $query->buildGroupBySQL());

        $query = new Select;
        $query
            ->groupBy('name')
            ->groupBy('age');

        $this->assertEquals('GROUP BY "name", "age"', $query->buildGroupBySQL());

        $query = new Select;
        $query->groupBy(['name', 'age']);
        $this->assertEquals('GROUP BY "name", "age"', $query->buildGroupBySQL());
    }

    public function testResetGroupBy()
    {
        $query = new Select;
        $query
            ->groupBy('name')
            ->groupBy('age');

        $this->assertEquals(['name', 'age'], $query->groupBy());

        $this->assertEquals($query, $query->resetGroupBy());
        $this->assertEquals([], $query->groupBy());
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
        $this->assertEquals('ORDER BY "name" ASC', $query->buildOrderBySQL());

        $query = new Select;
        $query->orderBy('name', 'ASC');
        $query->orderBy('age', 'DESC');
        $this->assertEquals('ORDER BY "name" ASC, "age" DESC', $query->buildOrderBySQL());

        $query = new Select;
        $query->orderBy([
            'name'  => 'ASC',
            'age'   => 'DESC'
        ]);
        $this->assertEquals('ORDER BY "name" ASC, "age" DESC', $query->buildOrderBySQL());
    }

    public function testResetOrderBy()
    {
        $query = new Select;
        $query
            ->orderBy('name', 'ASC')
            ->orderBy('age', 'DESC');

        $this->assertEquals(['name' => 'ASC', 'age' => 'DESC'], $query->orderBy());

        $this->assertEquals($query, $query->resetOrderBy());
        $this->assertEquals([], $query->orderBy());
    }

    /*
     * ------------------ SQL string testing ------------------
     */

    public function testBasicStatement()
    {
        $q = new Select();
        $q->select('*')
            ->from('users');

        $this->assertEquals(
            'SELECT * FROM "users"',
            (string)$q
        );
        $this->assertEquals([], $q->params());
    }

    public function testStatementWithWhere()
    {
        $q = new Select();
        $q->select('*')
            ->from('users')
            ->where('name', '=', 'Alex');

        $this->assertEquals(
            'SELECT * FROM "users" WHERE "name" = ?',
            (string)$q
        );
        $this->assertEquals(['Alex'], $q->params());
    }

    public function testStatementWithOrder()
    {
        $q = new Select();
        $q->select('*')
            ->from('users')
            ->where('name', '=', 'Alex')
            ->orderBy('created', 'DESC')
        ;

        $this->assertEquals(
            'SELECT * FROM "users" WHERE "name" = ? ORDER BY "created" DESC',
            (string)$q
        );
        $this->assertEquals(['Alex'], $q->params());
    }

    public function testStatementWithLimit()
    {
        $q = new Select();
        $q->select('*')
            ->from('users')
            ->where('name', '=', 'Alex')
            ->orderBy('created', 'DESC')
            ->limit(10)
        ;

        $this->assertEquals(
            'SELECT * FROM "users" WHERE "name" = ? ORDER BY "created" DESC LIMIT 10',
            (string)$q
        );
        $this->assertEquals(['Alex'], $q->params());
    }

    public function testStatementWithLimitAndOffset()
    {
        $q = new Select();
        $q->select('*')
            ->from('users')
            ->where('name', '=', 'Alex')
            ->orderBy('created', 'DESC')
            ->limit(10)
            ->offset(5)
        ;

        $this->assertEquals(
            'SELECT * FROM "users" WHERE "name" = ? ORDER BY "created" DESC LIMIT 5, 10',
            (string)$q
        );
        $this->assertEquals(['Alex'], $q->params());
    }

    public function testStatementWithJoins()
    {
        $q = new Select();
        $q->select('*')
            ->from('users')
            ->where('name', '=', 'Alex')
            ->orderBy('created', 'DESC')
            ->limit(10)
            ->offset(5)
            ->join('users', 'comments', 'users.id = comments.user_id', 'LEFT')
        ;

        $this->assertEquals(
            'SELECT * FROM "users" LEFT JOIN "comments" ON "users"."id" = "comments"."user_id" '
            .'WHERE "name" = ? ORDER BY "created" DESC LIMIT 5, 10',
            (string)$q
        );
        $this->assertEquals(['Alex'], $q->params());
    }

    public function testStatementWithGroupBy()
    {
        $q = new Select();
        $q->select('*')
            ->from('users')
            ->where('name', '=', 'Alex')
            ->orderBy('created', 'DESC')
            ->limit(10)
            ->offset(5)
            ->join('users', 'comments', 'users.id = comments.user_id', 'LEFT')
            ->groupBy('comments.user_id')
        ;

        $this->assertEquals(
            'SELECT * FROM "users" LEFT JOIN "comments" ON "users"."id" = "comments"."user_id" '
            .'WHERE "name" = ? GROUP BY "comments"."user_id" ORDER BY "created" DESC LIMIT 5, 10',
            (string)$q
        );
        $this->assertEquals(['Alex'], $q->params());
    }

    public function testStatementWithHaving()
    {
        $q = new Select();
        $q->select('*')
            ->from('users')
            ->where('name', '=', 'Alex')
            ->orderBy('created', 'DESC')
            ->limit(10)
            ->offset(5)
            ->join('users', 'comments', 'users.id = comments.user_id', 'LEFT')
            ->groupBy('comments.user_id')
            ->having('COUNT(comments.user_id)', '>', 10)
        ;

        $this->assertEquals(
            'SELECT * FROM "users" LEFT JOIN "comments" ON "users"."id" = "comments"."user_id" '
            .'WHERE "name" = ? GROUP BY "comments"."user_id" HAVING COUNT("comments"."user_id") > ? '
            .'ORDER BY "created" DESC LIMIT 5, 10',
            (string)$q
        );
        $this->assertEquals(['Alex', 10], $q->params());
    }

    public function testFullReset()
    {
        $q = new Select();
        $q->select('*')
            ->from('users')
            ->where('name', '=', 'Alex')
            ->orderBy('created', 'DESC')
            ->limit(10)
            ->offset(5)
            ->join('users', 'comments', 'users.id = comments.user_id', 'LEFT')
            ->groupBy('comments.user_id')
            ->having('COUNT(comments.user_id)', '>', 10)
        ;

        $this->assertEquals($q, $q->reset());
        $this->assertEquals('', $q->sql());
        $this->assertEquals([], $q->params());
    }
}
