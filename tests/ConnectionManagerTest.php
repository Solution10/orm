<?php

namespace Solution10\ORM\Tests\ActiveRecord;

use Solution10\ORM\ConnectionManager;

class ConnectionManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceCreates()
    {
        $c = new ConnectionManager();
        $c->registerInstance();

        $i = ConnectionManager::instance();
        $this->assertInstanceOf('Solution10\\ORM\\ConnectionManager', $i);
    }

    public function testRegisteringConnections()
    {
        $i = new ConnectionManager();

        $params = [
            'driver' => 'pdo_sqlite',
            'path' => __DIR__.'/tests.db',
        ];

        $this->assertEquals($i, $i->registerConnection('default', $params));
        $this->assertTrue(in_array('default', array_keys($i->registeredConnections())));
    }

    public function testBuildingConnections()
    {
        $i = new ConnectionManager();

        $params = [
            'driver' => 'pdo_sqlite',
            'path' => __DIR__.'/tests.db',
        ];

        $i->registerConnection('default', $params);
        $conn = $i->connection('default');
        $this->assertInstanceOf('Doctrine\\DBAL\\Connection', $conn);
    }

    public function testConnectionReuse()
    {
        $i = new ConnectionManager();

        $i->registerConnection('default', [
                'driver' => 'pdo_sqlite',
                'path' => __DIR__.'/tests.db',
            ]);

        $conn = $i->connection('default');
        $conn->mark = 'green';
        $conn2 = $i->connection('default');
        $this->assertEquals($conn, $conn2);
        $this->assertEquals('green', $conn2->mark);
    }

    /**
     * @expectedException       \Solution10\ORM\Exception\ConnectionException
     * @expectedExceptionCode   \Solution10\ORM\Exception\ConnectionException::UNKNOWN_CONNECTION
     */
    public function testUnknownConnection()
    {
        $i = new ConnectionManager();
        $i->connection('unknown');
    }
}
