<?php

namespace Solution10\ORM;

use Solution10\SQL\Delete;
use Solution10\SQL\Dialect\ANSI;
use Solution10\SQL\Dialect\MySQL;
use Solution10\SQL\Insert;
use Solution10\SQL\Query;
use Solution10\SQL\Update;

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
     * @var     LoggerInterface
     */
    protected $logger = null;

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
     * Sets the connection logger to use.
     *
     * @param   LoggerInterface     $logger
     * @return  $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Returns the logger on this instance.
     *
     * @return  LoggerInterface
     */
    public function logger()
    {
        return $this->logger;
    }

    /**
     * Basic insert into a table.
     *
     * @param   string  $tableName
     * @param   array   $data
     * @return  int     Insert ID.
     */
    public function insert($tableName, array $data)
    {
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $q = new Insert($this->dialect());
        $q->table($tableName);
        $q->values($data);
        $stmt = $this->prepare((string)$q);
        $this->doQuery($stmt, $q->params());
        return $this->lastInsertId();
    }

    /**
     * Basic update
     *
     * @param   string  $tableName
     * @param   array   $data
     * @param   array   $where
     * @return  $this
     */
    public function update($tableName, array $data, array $where)
    {
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $q = new Update($this->dialect());
        $q
            ->table($tableName)
            ->values($data);

        foreach ($where as $k => $v) {
            $q->where($k, '=', $v);
        }

        $stmt = $this->prepare((string)$q);
        $this->doQuery($stmt, $q->params());
        return $this;
    }

    /**
     * Deletes a row from the database
     *
     * @param   string  $tableName
     * @param   array   $where
     * @return  $this
     */
    public function delete($tableName, array $where)
    {
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $q = new Delete($this->dialect());
        $q->table($tableName);
        foreach ($where as $k => $v) {
            $q->where($k, '=', $v);
        }

        $stmt = $this->prepare((string)$q);
        $this->doQuery($stmt, $q->params());
        return $this;
    }

    /**
     * A basic fetchAll implementation.
     *
     * @param   string  $sql
     * @param   array   $params
     * @return  array
     */
    public function fetchAll($sql, array $params = null)
    {
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $stmt = $this->prepare($sql);
        $stmt = $this->doQuery($stmt, $params);
        $result = $stmt->fetchAll();
        $result = $this->cleanResult($result);
        return $result;
    }

    /**
     * Fetches a single row of the result.
     *
     * @param   string  $sql
     * @param   array   $params
     * @return  array
     */
    public function fetch($sql, array $params = null)
    {
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $stmt = $this->prepare($sql);
        $stmt = $this->doQuery($stmt, $params);
        $result = $stmt->fetch();
        $result = $this->cleanResult($result);
        return $result;
    }

    /**
     * Runs any query against the database
     *
     * @param   Query   $query
     * @return  \PDOStatement
     */
    public function executeQuery(Query $query)
    {
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $stmt = $this->prepare((string)$query);
        $stmt = $this->doQuery($stmt, $query->params());
        return $stmt;
    }

    /**
     * Executes a query, that we'll log and monitor against.
     *
     * @param   \PDOStatement       $stmt
     * @param   array|null          $params
     * @return  \PDOStatement
     */
    public function doQuery(\PDOStatement $stmt, array $params = null)
    {
        $start = microtime(true);
        $stmt->execute($params);
        $end = microtime(true);

        if ($this->logger) {
            $this->logger->onQuery($stmt->queryString, $params, ($end - $start) * 1000);
        }

        return $stmt;
    }

    /**
     * Removes the annoying numeric keys that PDO puts in query results.
     *
     * @param   array   $result
     * @return  array
     */
    protected function cleanResult(array $result = array())
    {
        if (count($result) == 0) {
            return $result;
        }

        // Are we in a single result, or multi-result?
        $single = false;
        if (!is_array($result[0])) {
            $result = [$result];
            $single = true;
        }

        foreach ($result as &$row) {
            foreach ($row as $key => $value) {
                if (is_numeric($key)) {
                    unset($row[$key]);
                }
            }
        }

        return ($single)? $result[0] : $result;
    }
}
