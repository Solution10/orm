<?php

namespace Solution10\ORM\Tests\ActiveRecord\ModelTests;

use Doctrine\Common\Cache\ArrayCache;
use Solution10\ORM\ActiveRecord\Model;
use Solution10\ORM\Logger;
use Solution10\ORM\PHPUnit\BasicDatabase;
use PHPUnit_Framework_TestCase;
use Solution10\ORM\Tests\ActiveRecord\Stubs\User;

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
    use BasicDatabase;

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

        $results = User::query()->fetchAll();

        $this->assertInstanceOf('Solution10\\ORM\\ActiveRecord\\Resultset', $results);
        $this->assertCount(3, $results);

        $this->assertInstanceOf('Solution10\ORM\Tests\ActiveRecord\Stubs\User', $results[0]);
        $this->assertEquals('Alex', $results[0]->get('name'));
        $this->assertEquals('Lucie', $results[1]->get('name'));
        $this->assertEquals('Archibald', $results[2]->get('name'));
    }

    public function testCountQueries()
    {
        $this->conn->insert('users', ['name' => 'Alex']);
        $this->conn->insert('users', ['name' => 'Lucie']);
        $this->conn->insert('users', ['name' => 'Archibald']);

        $result = User::query()->count();
        $this->assertEquals(3, $result);

        $result = User::query()->where('name', '=', 'Alex')->count();
        $this->assertEquals(1, $result);

        $result = User::query()->where('name', '=', 'George')->count();
        $this->assertEquals(0, $result);
    }

    /* --------------- Cached Query Tests ------------------- */

    public function testFetchAllCached()
    {
        $this->conn->insert('users', ['name' => 'Alex']);

        // Set up the cache:
        $cache = new ArrayCache();
        $this->conn->setCache($cache);

        // Grab a logger so we can count the queries easily:
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $query = User::query()
            ->where('name', '=', 'Alex')
            ->cacheFor(10, 'find_alex');

        $results1 = $query->fetchAll();
        $this->assertCount(1, $results1);
        $this->assertEquals('Alex', $results1[0]->get('name'));

        // Verify the cache:
        $this->assertTrue($cache->contains('find_alex'));

        $results2 = $query->fetchAll();
        $this->assertCount(1, $results2);
        $this->assertEquals('Alex', $results2[0]->get('name'));

        // Verify the query count:
        $this->assertEquals(1, $logger->totalQueries());
    }

    public function testFetchAllEmptyCached()
    {
        // Set up the cache:
        $cache = new ArrayCache();
        $this->conn->setCache($cache);

        // Grab a logger so we can count the queries easily:
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $query = User::query()
            ->where('name', '=', 'Alex')
            ->cacheFor(10, 'find_alex');

        $results1 = $query->fetchAll();
        $this->assertCount(0, $results1);

        // Verify the cache:
        $this->assertTrue($cache->contains('find_alex'));

        $results2 = $query->fetchAll();
        $this->assertCount(0, $results2);

        // Verify the query count:
        $this->assertEquals(1, $logger->totalQueries());
    }

    public function testFetchCached()
    {
        $this->conn->insert('users', ['name' => 'Alex']);

        // Set up the cache:
        $cache = new ArrayCache();
        $this->conn->setCache($cache);

        // Grab a logger so we can count the queries easily:
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $query = User::query()
            ->where('name', '=', 'Alex')
            ->cacheFor(10, 'find_alex');

        $results1 = $query->fetch();
        $this->assertEquals('Alex', $results1->get('name'));

        // Verify the cache:
        $this->assertTrue($cache->contains('find_alex'));

        $results2 = $query->fetch();
        $this->assertEquals('Alex', $results2->get('name'));

        // Verify the query count:
        $this->assertEquals(1, $logger->totalQueries());
    }

    public function testFetchEmptyCached()
    {
        // Set up the cache:
        $cache = new ArrayCache();
        $this->conn->setCache($cache);

        // Grab a logger so we can count the queries easily:
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $query = User::query()
            ->where('name', '=', 'Alex')
            ->cacheFor(10, 'find_alex');

        $results1 = $query->fetch();
        $this->assertFalse($results1->isLoaded());

        // Verify the cache:
        $this->assertTrue($cache->contains('find_alex'));

        $results2 = $query->fetch();
        $this->assertFalse($results2->isLoaded());

        // Verify the query count:
        $this->assertEquals(1, $logger->totalQueries());
    }

    public function testCountCached()
    {
        $this->conn->insert('users', ['name' => 'Alex']);
        $this->conn->insert('users', ['name' => 'Lucie']);
        $this->conn->insert('users', ['name' => 'Archibald']);

        // Set up the cache:
        $cache = new ArrayCache();
        $this->conn->setCache($cache);

        // Grab a logger so we can count the queries easily:
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $query = User::query()
            ->cacheFor(10, 'count_users');

        $results1 = $query->count();
        $this->assertEquals(3, $results1);

        // Verify the cache:
        $this->assertTrue($cache->contains('count_users__count'));

        $results2 = $query->count();
        $this->assertEquals(3, $results2);

        // Verify the query count:
        $this->assertEquals(1, $logger->totalQueries());
    }

    public function testCountEmptyCached()
    {
        // Set up the cache:
        $cache = new ArrayCache();
        $this->conn->setCache($cache);

        // Grab a logger so we can count the queries easily:
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $query = User::query()
            ->cacheFor(10, 'count_users');

        $results1 = $query->count();
        $this->assertEquals(0, $results1);

        // Verify the cache:
        $this->assertTrue($cache->contains('count_users__count'));

        $results2 = $query->count();
        $this->assertEquals(0, $results2);

        // Verify the query count:
        $this->assertEquals(1, $logger->totalQueries());
    }

    public function testStackingFetchCountCache()
    {
        $this->conn->insert('users', ['name' => 'Alex']);
        $this->conn->insert('users', ['name' => 'Lucie']);
        $this->conn->insert('users', ['name' => 'Archibald']);

        // Set up the cache:
        $cache = new ArrayCache();
        $this->conn->setCache($cache);

        // Grab a logger so we can count the queries easily:
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $query = User::query()
            ->cacheFor(10, 'all_users');

        $results = $query->fetchAll();
        $this->assertCount(3, $results);

        $count = $query->count();
        $this->assertEquals(3, $count);

        // Verify the cache:
        $this->assertTrue($cache->contains('all_users'));
        $this->assertTrue($cache->contains('all_users__count'));

        // Verify the query count:
        $this->assertEquals(2, $logger->totalQueries());
    }
}
