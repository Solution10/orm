<?php

namespace Solution10\ORM\Tests\ActiveRecord;

use Solution10\ORM\ActiveRecord\Query;
use PHPUnit_Framework_TestCase;
use Solution10\ORM\ConnectionManager;

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

    public function testFromWithAlias()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        // With alias
        $query->from('users', 'u');
        $this->assertEquals(['users' => 'u'], $query->from());
    }

    /**
     * @expectedException       \Solution10\ORM\ActiveRecord\Exception\QueryException
     * @expectedExceptionCode   \Solution10\ORM\ActiveRecord\Exception\QueryException::MISSING_ALIAS
     */
    public function testFromWithoutAlias()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $query->from('noalias');
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

    public function testGroupBy()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $clause = 'username';
        $this->assertEquals(null, $query->groupBy());
        $this->assertEquals($query, $query->groupBy($clause));
        $this->assertEquals($clause, $query->groupBy());
    }

    public function testHaving()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $clause = 'city = "London"';
        $this->assertEquals(null, $query->having());
        $this->assertEquals($query, $query->having($clause));
        $this->assertEquals($clause, $query->having());
    }

    public function testConnection()
    {
        $c = new ConnectionManager();
        $c->registerInstance();
        ConnectionManager::instance()->registerConnection('default', [
            'driver' => 'pdo_sqlite',
            'path' => __DIR__.'/../tests.db',
        ]);

        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $conn = ConnectionManager::instance()->connection('default');

        $this->assertNull($query->connection());
        $this->assertEquals($query, $query->connection($conn));
        $this->assertEquals($conn, $query->connection());
    }

    /**
     * @expectedException       \Solution10\ORM\ActiveRecord\Exception\QueryException
     * @expectedExceptionCode   \Solution10\ORM\ActiveRecord\Exception\QueryException::CONNECTION_MISSING
     */
    public function testSQLNoConnection()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $query
            ->select(['id', 'username', 'password'])
            ->from('users', 'u')
            ->where('username', '=', 'Alex')
            ->limit(1)
        ;
        $query->sql();
    }

    public function testSQL()
    {
        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $query
            ->select(['id', 'username', 'password'])
            ->from('users', 'u')
            ->join('u', 'packages', 'p', 'u.id = p.user_id')
            ->where('username', '=', 'Alex')
            ->orWhere(function (Query $query) {
                $query->where('username', '=', 'Lucie');
                $query->where('location', '=', 'Toronto');
            })
            ->limit(1)
        ;

        // We need a connection to generate the SQL. Thanks Doctrine.
        $c = new ConnectionManager();
        $c->registerInstance();
        ConnectionManager::instance()->registerConnection('default', [
                'driver' => 'pdo_sqlite',
                'path' => __DIR__.'/../tests.db',
            ]);

        $query->connection(ConnectionManager::instance()->connection('default'));

        $sql = $query->sql();
        $this->assertEquals(
            'SELECT id, username, password FROM users u '.
            'INNER JOIN packages p ON u.id = p.user_id'.
            ' WHERE username = :dcValue1 OR (username = :dcValue2 AND location = :dcValue3) LIMIT 1',
            $sql
        );
    }

    public function testToString()
    {
        // I'm extremely aware this query is totally contrived and not realistic.
        // I just want to check the SQL being spat out.

        $query = new Query('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $query
            ->select(['id', 'username', 'password', 'COUNT(d.id) as num'])
            ->from('users', 'u')
            ->join('u', 'packages', 'p', 'u.id = p.user_id')
            ->join('u', 'departments', 'd', 'u.id = d.user_id', 'LEFT')
            ->join('u', 'invoices', 'i', 'u.id = i.user_id', 'RIGHT')
            ->where('username', '=', 'Alex')
            ->orWhere(function (Query $query) {
                $query->where('username', '=', 'Lucie');
                $query->where('location', '=', 'Toronto');
            })
            ->orderBy('username', 'DESC')
            ->groupBy('d.id')
            ->having('num > 20')
            ->limit(1)
            ->offset(20)
        ;

        // We need a connection to generate the SQL. Thanks Doctrine.
        $c = new ConnectionManager();
        $c->registerInstance();
        ConnectionManager::instance()->registerConnection('default', [
                'driver' => 'pdo_sqlite',
                'path' => __DIR__.'/../tests.db',
            ]);

        $query->connection(ConnectionManager::instance()->connection('default'));

        $this->assertEquals(
            'SELECT id, username, password, COUNT(d.id) as num FROM users u '.
            'INNER JOIN packages p ON u.id = p.user_id LEFT JOIN departments d ON u.id = d.user_id '.
            'RIGHT JOIN invoices i ON u.id = i.user_id '.
            'WHERE username = :dcValue1 OR (username = :dcValue2 AND location = :dcValue3) '.
            'GROUP BY d.id HAVING num > 20 ORDER BY username DESC LIMIT 1 OFFSET 20',
            (string)$query
        );
    }
}
