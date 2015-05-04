<?php

namespace Solution10\ORM\Tests\Repository\Database;

use \PDO;
use Solution10\ORM\Tests\Repository\Stubs\User;
use Solution10\ORM\Tests\Repository\Stubs\UsersDatabaseRepo;

class RepoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var     PDO
     */
    protected $conn;

    public function setup()
    {
        $this->conn = new \PDO('sqlite::memory:');

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

    protected function fetchOne($sql, array $params = [])
    {
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $name => $value) {
            $stmt->bindParam($name, $value);
        }
        $stmt->execute();
        return $stmt->fetch();
    }

    protected function fetchAll($sql, array $params = [])
    {
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $name => $value) {
            $stmt->bindParam($name, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function testConstruct()
    {
        $uRepo = new UsersDatabaseRepo($this->conn);
        $this->assertInstanceOf('Solution10\ORM\Tests\Repository\Stubs\UsersDatabaseRepo', $uRepo);
    }

    public function testQuery()
    {
        $this->assertTrue(true);
    }

    public function testBasicCreate()
    {
        $item = new User([
            'name' => 'Alex'
        ]);

        $uRepo = new UsersDatabaseRepo($this->conn);

        $this->assertTrue($uRepo->store($item));

        // Check that it actually went in:
        $rows = $this->fetchAll('SELECT * FROM users');
        $this->assertCount(1, $rows);
        $this->assertEquals('Alex', $rows[0]['name']);
    }

    public function testBasicUpdate()
    {
        $item = new User([
            'name' => 'Alex'
        ]);
        $uRepo = new UsersDatabaseRepo($this->conn);
        $uRepo->store($item);

        // Make a change and then re-store it:
        $item->set('name', 'Alexander');
        $this->assertTrue($uRepo->store($item));

        // Verify the database:
        $rows = $this->fetchAll('SELECT * FROM users');
        $this->assertCount(1, $rows);
        $this->assertEquals('Alexander', $rows[0]['name']);
    }

    public function testBasicDelete()
    {
        $item = new User([
            'name' => 'Alex'
        ]);
        $uRepo = new UsersDatabaseRepo($this->conn);
        $uRepo->store($item);

        $this->assertEquals(1, $uRepo->remove($item));

        // Verify the database:
        $rows = $this->fetchAll('SELECT * FROM users');
        $this->assertCount(0, $rows);
    }
}
