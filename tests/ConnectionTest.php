<?php

namespace Solution10\ORM\Tests;

use Solution10\ORM\PHPUnit\BasicDatabase;
use Solution10\ORM\Logger;
use Solution10\SQL\Select;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    use BasicDatabase;

    public function testSetGetLogger()
    {
        // Make use of the connection BasicDatabase sets up.
        $logger = new Logger();
        $this->assertNull($this->conn->logger());
        $this->assertEquals($this->conn, $this->conn->setLogger($logger));
        $this->assertEquals($logger, $this->conn->logger());
    }

    public function testInsert()
    {
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $this->assertEquals(1, $this->conn->insert('users', [
            'name' => 'Alex'
        ]));

        // verify the database:
        $stmt = $this->conn->prepare('SELECT * FROM users');
        $stmt->execute();
        $users = $stmt->fetchAll();

        $this->assertCount(1, $users);
        $this->assertEquals(1, $users[0]['id']);
        $this->assertEquals('Alex', $users[0]['name']);

        // verify the logger:
        $events = $logger->events();
        $this->assertCount(1, $events);
        $this->assertContains('INSERT INTO', $events[0]['sql']);
        $this->assertEquals(['Alex'], $events[0]['parameters']);
        $this->assertInternalType('float', $events[0]['time']);
    }

    public function testUpdate()
    {
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $this->conn->insert('users', [
            'name' => 'Alex'
        ]);

        // Do the update:
        $this->assertEquals($this->conn, $this->conn->update('users', [
            'name' => 'Alexander'
        ], ['id' => 1]));

        // verify the database
        $stmt = $this->conn->prepare('SELECT * FROM users');
        $stmt->execute();
        $users = $stmt->fetchAll();

        $this->assertCount(1, $users);
        $this->assertEquals(1, $users[0]['id']);
        $this->assertEquals('Alexander', $users[0]['name']);

        // verify the logger:
        $events = $logger->events();
        $this->assertCount(2, $events); // offset as the INSERT logs too
        $this->assertContains('UPDATE', $events[1]['sql']);
        $this->assertEquals(['Alexander', 1], $events[1]['parameters']);
        $this->assertInternalType('float', $events[1]['time']);
    }

    public function testDelete()
    {
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $this->conn->insert('users', [
            'name' => 'Alex'
        ]);
        $this->conn->insert('users', [
            'name' => 'Lucie'
        ]);

        $this->assertEquals($this->conn, $this->conn->delete('users', ['id' => 1]));

        // verify the database
        $stmt = $this->conn->prepare('SELECT * FROM users');
        $stmt->execute();
        $users = $stmt->fetchAll();

        $this->assertCount(1, $users);
        $this->assertEquals('Lucie', $users[0]['name']);

        // verify the logger:
        $events = $logger->events();
        $this->assertCount(3, $events); // offset as the INSERT logs too
        $this->assertContains('DELETE', $events[2]['sql']);
        $this->assertEquals([1], $events[2]['parameters']);
        $this->assertInternalType('float', $events[2]['time']);
    }

    public function testFetch()
    {
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $this->conn->insert('users', [
            'name' => 'Alex'
        ]);
        $this->conn->insert('users', [
            'name' => 'Lucie'
        ]);

        $users = $this->conn->fetch('SELECT * FROM users');

        $stmt = $this->conn->prepare('SELECT * FROM users');
        $stmt->execute();
        $actualUsers = $stmt->fetch();

        $this->assertEquals($actualUsers['id'], $users['id']);
        $this->assertEquals($actualUsers['name'], $users['name']);
    }

    public function testFetchEmpty()
    {
        $this->assertFalse($this->conn->fetch('SELECT * FROM users'));
    }

    public function testFetchAll()
    {
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $this->conn->insert('users', [
            'name' => 'Alex'
        ]);
        $this->conn->insert('users', [
            'name' => 'Lucie'
        ]);

        $users = $this->conn->fetchAll('SELECT * FROM users');

        $stmt = $this->conn->prepare('SELECT * FROM users');
        $stmt->execute();
        $actualUsers = $stmt->fetchAll();

        $this->assertCount(count($actualUsers), $users);
        $this->assertEquals($actualUsers[0]['id'], $users[0]['id']);
        $this->assertEquals($actualUsers[0]['name'], $users[0]['name']);

        $this->assertEquals($actualUsers[1]['id'], $users[1]['id']);
        $this->assertEquals($actualUsers[1]['name'], $users[1]['name']);
    }

    public function testFetchAllEmpty()
    {
        $this->assertEquals([], $this->conn->fetchAll('SELECT * FROM users'));
    }

    public function testExecuteQuery()
    {
        $logger = new Logger();
        $this->conn->setLogger($logger);

        $this->conn->insert('users', [
            'name' => 'Alex'
        ]);
        $this->conn->insert('users', [
            'name' => 'Lucie'
        ]);

        $stmt = $this->conn->executeQuery(
            (new Select())
            ->select('*')
            ->from('users')
        );
        $users = $stmt->fetchAll();

        $stmt = $this->conn->prepare('SELECT * FROM users');
        $stmt->execute();
        $actualUsers = $stmt->fetchAll();

        $this->assertEquals($users, $actualUsers);
    }
}
