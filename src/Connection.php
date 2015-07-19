<?php

namespace Solution10\ORM;

use Solution10\SQL\Dialect\ANSI;
use Solution10\SQL\Dialect\MySQL;

/**
 * Connection
 *
 * A simple subclass of PDO that adds a couple of needed features, mostly
 * around dialects
 *
 * @package     Solution10
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
class Connection extends \PDO
{
    /**
     * Returns the correct Solution10\SQL\DialectInterface instance for this connection
     *
     * @return  \Solution10\SQL\DialectInterface
     */
    public function dialect()
    {
        $driver = $this->getAttribute(self::ATTR_DRIVER_NAME);
        return ($driver === 'mysql')? new MySQL() : new ANSI();
    }

    /**
     * A basic fetchAll implementation. Not to be used in production.
     *
     * @param   string  $sql
     * @param   array   $params
     * @return  array
     */
    public function fetchAll($sql, array $params = null)
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
