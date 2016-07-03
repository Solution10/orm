<?php

namespace Solution10\ORM\Tests;

use Solution10\ORM\Logger;

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnEvent()
    {
        $l = new Logger();
        $this->assertEquals($l, $l->onQuery(
            'SELECT * FROM users WHERE id = ?',
            [1],
            0.25
        ));
    }

    public function testTotalQueries()
    {
        $l = new Logger();
        $l->onQuery(
            'SELECT * FROM users WHERE id = ?',
            [1],
            0.25
        );
        $l->onQuery(
            'SELECT * FROM users WHERE id = ?',
            [2],
            0.35
        );

        $this->assertEquals(2, $l->totalQueries());
    }

    public function testTotalTime()
    {
        $l = new Logger();
        $l->onQuery(
            'SELECT * FROM users WHERE id = ?',
            [1],
            0.25
        );
        $l->onQuery(
            'SELECT * FROM users WHERE id = ?',
            [2],
            0.35
        );

        $this->assertEquals(0.60, $l->totalTime());
    }

    public function testEvents()
    {
        $l = new Logger();
        $l->onQuery(
            'SELECT * FROM users WHERE id = ?',
            [1],
            0.25
        );
        $l->onQuery(
            'SELECT * FROM users WHERE id = ?',
            [2],
            0.35
        );

        $this->assertEquals([
            [
                'sql' => 'SELECT * FROM users WHERE id = ?',
                'parameters' => [1],
                'time' => 0.25
            ],
            [
                'sql' => 'SELECT * FROM users WHERE id = ?',
                'parameters' => [2],
                'time' => 0.35
            ],
        ], $l->events());
    }
}
