<?php

namespace Solution10\ORM;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Solution10\ORM\Exception\ConnectionException;

abstract class ConnectionManager
{
    protected static $connections;
    protected static $builtConnections = [];

    /**
     * Registers a connection with the manager.
     *
     * @param   string          $name       Connection name
     * @param   array           $params     Connection parameters
     * @param   Configuration   $config     Connection Configuration object
     * @return  void
     */
    public static function registerConnection($name, array $params, Configuration $config = null)
    {
        if ($config === null) {
            $config = new Configuration();
        }

        self::$connections[$name] = [
            'params' => $params,
            'config' => $config,
        ];
    }

    /**
     * Returns a connection by a given name.
     *
     * @param   string  $name   Connection name
     * @return  Connection
     * @throws  Exception\ConnectionException
     */
    public static function connection($name)
    {
        if (!array_key_exists($name, self::$connections)) {
            throw new ConnectionException(
                'Unknown connection "'.$name.'"',
                ConnectionException::UNKNOWN_CONNECTION
            );
        }

        if (!array_key_exists($name, self::$builtConnections)) {
            self::$builtConnections[$name] = DriverManager::getConnection(
                self::$connections[$name]['params'],
                self::$connections[$name]['config']
            );
        }

        return self::$builtConnections[$name];
    }
}
