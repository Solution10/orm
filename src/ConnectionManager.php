<?php

namespace Solution10\ORM;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Solution10\ORM\Exception\ConnectionException;

class ConnectionManager
{
    protected $connections = [];
    protected $builtConnections = [];

    /**
     * @var     ConnectionManager   Holds the current instance of the connection manager
     */
    protected static $instance;

    /**
     * Gets the current instance of the connection manager, or creates a new one if none
     * are registered.
     *
     * @param   ConnectionManager   $manager    Sets the current manager instance
     * @return  ConnectionManager
     */
    public static function instance(ConnectionManager $manager = null)
    {
        if ($manager !== null) {
            self::$instance = $manager;
        }

        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Registers a connection with the manager.
     *
     * @param   string          $name       Connection name
     * @param   array           $params     Connection parameters
     * @param   Configuration   $config     Connection Configuration object
     * @return  $this
     */
    public function registerConnection($name, array $params, Configuration $config = null)
    {
        if ($config === null) {
            $config = new Configuration();
        }

        $this->connections[$name] = [
            'params' => $params,
            'config' => $config,
        ];

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

        if (!array_key_exists($name, $this->builtConnections)) {
            $this->builtConnections[$name] = DriverManager::getConnection(
                $this->connections[$name]['params'],
                $this->connections[$name]['config']
            );
        }

        return $this->builtConnections[$name];
    }
}
