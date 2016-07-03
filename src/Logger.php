<?php

namespace Solution10\ORM;

/**
 * Logger
 *
 * Implementation of the Logger interface for capturing queries sent through the S10 ORM.
 *
 * @package     Solution10\ORM
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
class Logger implements LoggerInterface
{
    /**
     * @var     array
     */
    protected $events = [];

    /**
     * Sent when a query has executed
     *
     * @param   string     $sql
     * @param   array|null $parameters
     * @param   float      $time
     * @return  $this
     */
    public function onQuery($sql, $parameters, $time)
    {
        $this->events[] = [
            'sql' => $sql,
            'parameters' => $parameters,
            'time' => $time,
        ];
        return $this;
    }

    /**
     * Returns the total number of queries executed
     *
     * @return  int
     */
    public function totalQueries()
    {
        return count($this->events);
    }

    /**
     * Returns the total time taken by all events (ms)
     *
     * @return  float
     */
    public function totalTime()
    {
        $total = 0.0;
        foreach ($this->events as $e) {
            $total += $e['time'];
        }
        return $total;
    }

    /**
     * Returns all the events we've put in
     *
     * @return  array
     */
    public function events()
    {
        return $this->events;
    }
}
