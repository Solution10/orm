<?php

namespace Solution10\ORM;

use PDO;
use Solution10\ManagedInstance\ManagedInstance;
use Solution10\ORM\Exception\ConnectionException;

class ConnectionManager
{
    use ManagedInstance;

    /**
     * @var     PDO[]
     */
    protected $connections = [];

    /**
     * Registers a connection with the manager.
     *
     * @param   string          $name   Name of this connection
     * @param   Connection      $conn   PDO instance of the connection
     * @return  $this
     */
    public function registerConnection($name, Connection $conn)
    {
        $this->connections[$name] = $conn;
        return $this;
    }

    /**
     * Returns a list of registered connections with their params and config objects.
     *
     * @return  array
     */
    public function registeredConnections()
    {
        return $this->connections;
    }

    /**
     * Returns a connection by a given name.
     *
     * @param   string  $name   Connection name
     * @return  Connection
     * @throws  Exception\ConnectionException
     */
    public function connection($name)
    {
        if (!array_key_exists($name, $this->connections)) {
            throw new ConnectionException(
                'Unknown connection "'.$name.'"',
                ConnectionException::UNKNOWN_CONNECTION
            );
        }

        return $this->connections[$name];
    }
}
