<?php

namespace Solution10\ORM\Tests\ActiveRecord\ModelTests;

use Solution10\ORM\ActiveRecord\Model;
use Solution10\ORM\ConnectionManager;
use PHPUnit_Framework_TestCase;
use Solution10\ORM\Tests\ActiveRecord\Stubs\User;
use Solution10\ORM\Tests\ActiveRecord\Stubs\UserValidated;

/**
 * These tests focus on the behaviours that DO require a database connection.
 * The reason for splitting out the tests is just to keep the execution runtime
 * down; we don't need a DB Connection for every test, so don't make one.
 *
 * This file doesn't make use of PHPUnit's database testing tools in the name of
 * speed, ease of use and laziness.
 */
class DatabaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var     \Doctrine\DBAL\Connection
     */
    protected $conn;

    /**
     * Creates the SQLite database
     */
    public function setUp()
    {
        $c = new ConnectionManager();
        $c->registerInstance();

        ConnectionManager::instance()->registerConnection('default', [
            'driver' => 'pdo_sqlite',
            'path' => __DIR__.'/../tests.db',
        ]);

        // Clear the database and create our test tables:
        $this->conn = ConnectionManager::instance()->connection('default');
        $this->conn->query('DROP TABLE IF EXISTS users');
        $this->conn->query('DROP TABLE IF EXISTS orders');

        $this->conn->query('
            CREATE TABLE `users` (
              `id` INTEGER PRIMARY KEY,
              `name` varchar(32) NOT NULL
            );
        ');

        $this->conn->query('
            CREATE TABLE `orders` (
              `id` INTEGER PRIMARY KEY,
              `user_id` INTEGER,
              `total` FLOAT
            );
        ');
    }

    public function testSimpleCreate()
    {
        $user = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $user->set('name', 'Alex');
        $this->assertEquals($user, $user->save());

        // Make sure that there is a user:
        $result = $this->conn->fetchAll('SELECT * FROM users');
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('Alex', $result[0]['name']);
    }

    public function testFindByID()
    {
        $user = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $user->set('name', 'Alex');
        $user->save();

        $savedUser = User::findById(1);
        $this->assertTrue($savedUser->isLoaded());
        $this->assertEquals(1, $savedUser->get('id'));
        $this->assertEquals('Alex', $savedUser->get('name'));
    }

    public function testFindByIDFails()
    {
        $unknownUser = User::findById(27);
        $this->assertFalse($unknownUser->isLoaded());
    }

    public function testSimpleUpdate()
    {
        $user = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $user->set('name', 'Alex');
        $user->save();

        $savedUser = User::findById(1);
        $savedUser->set('name', 'AlexAlex');
        $this->assertEquals($savedUser, $savedUser->save());

        // Make sure that the update succeeded
        $result = $this->conn->fetchAll('SELECT * FROM users');
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('AlexAlex', $result[0]['name']);
    }

    public function testUpdateNoChanges()
    {
        $user = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $user->set('name', 'Alex');
        $user->save();

        // Make no changes, just save again.
        $savedUser = User::findById(1);
        $savedUser->save();

        $result = $this->conn->fetchAll('SELECT * FROM users');
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('Alex', $result[0]['name']);
    }

    public function testDeletingPresentItem()
    {
        $user = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $user->set('name', 'Alex');
        $user->save();

        // Delete the user we just created.
        $savedUser = User::findById(1);
        $this->assertEquals($savedUser, $savedUser->delete());

        // Verify it's gone:
        $result = $this->conn->fetchAll('SELECT * FROM users');
        $this->assertCount(0, $result);
    }

    public function testDeletingNonSavedItem()
    {
        $user = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $this->assertEquals($user, $user->delete());
    }

    public function testQueryingForItems()
    {
        $this->conn->insert('users', ['name' => 'Alex']);
        $this->conn->insert('users', ['name' => 'Lucie']);
        $this->conn->insert('users', ['name' => 'Archibald']);

        $results = User::query('SELECT * FROM users');

        $this->assertInstanceOf('Solution10\\ORM\\ActiveRecord\\Resultset', $results);
        $this->assertCount(3, $results);

        $this->assertInstanceOf('Solution10\ORM\Tests\ActiveRecord\Stubs\User', $results[0]);
        $this->assertEquals('Alex', $results[0]->get('name'));
        $this->assertEquals('Lucie', $results[1]->get('name'));
        $this->assertEquals('Archibald', $results[2]->get('name'));
    }

    /**
     * ------------------- Relationship Testing ----------------------
     */

    public function testReadHasMany()
    {
        $this->conn->insert('users', ['name' => 'Alex']);
        $this->conn->insert('orders', ['user_id' => 1, 'total' => 27.5]);
        $this->conn->insert('orders', ['user_id' => 1, 'total' => 5.55]);
        $this->conn->insert('orders', ['user_id' => 27, 'total' => 127.99]);

        $object = UserValidated::findById(1);

        $orders = $object->fetchRelated('orders');
        $this->assertCount(2, $orders);
        $this->assertEquals(27.5, $orders[0]->get('total'));
        $this->assertEquals(5.55, $orders[1]->get('total'));
    }
}
