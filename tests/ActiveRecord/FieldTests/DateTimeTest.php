<?php

namespace Solution10\ORM\Tests\ActiveRecord\FieldTests;

use Solution10\ORM\ActiveRecord\Model;
use Solution10\ORM\Tests\ActiveRecord\FieldTests;
use Solution10\ORM\ActiveRecord\Field\DateTime;
use DateTime as NativeDateTime;
use DateTimeZone;

class DateTimeTests extends FieldTests
{
    protected function fieldClass()
    {
        return 'Solution10\\ORM\\ActiveRecord\\Field\\DateTime';
    }

    protected function fieldInstance()
    {
        return new DateTime([
            'timezone' => new DateTimeZone('Europe/London')
        ]);
    }

    /**
     * @expectedException       \Solution10\ORM\ActiveRecord\Exception\FieldException
     * @expectedExceptionCode   \Solution10\ORM\ActiveRecord\Exception\FieldException::MISSING_REQUIRED_OPTIONS
     */
    public function testConstructMissingTimezone()
    {
        new DateTime();
    }

    public function testSet()
    {
        $m = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $d = new DateTime([
            'timezone' => new DateTimeZone('Europe/London')
        ]);

        // Testing with timestamp:
        $now = time();
        $this->assertEquals($now, $d->set($m, 'created', $now));

        // String:
        $this->assertEquals(1404300707, $d->set($m, 'created', '2014-07-02 12:31:47'));

        // DateTime Object:
        $ndt = new NativeDateTime('2014-07-02 12:31:47', new DateTimeZone('Europe/London'));
        $this->assertEquals(1404300707, $d->set($m, 'created', $ndt));
    }

    public function testSetWithFormat()
    {
        $m = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $d = new DateTime([
            'format' => 'Y-m-d H:i:s',
            'timezone' => new DateTimeZone('Europe/London')
        ]);

        // Testing with timestamp:
        $now = time();
        $expected = (new NativeDateTime(null, new DateTimeZone('Europe/London')))
            ->setTimeStamp($now)
            ->format('Y-m-d H:i:s');
        $this->assertEquals($expected, $d->set($m, 'created', $now));

        // String:
        $this->assertEquals('2014-07-02 12:31:47', $d->set($m, 'created', '2014-07-02 12:31:47'));

        // DateTime Object:
        $ndt = new NativeDateTime('2014-07-02 12:31:47', new DateTimeZone('Europe/London'));
        $this->assertEquals('2014-07-02 12:31:47', $d->set($m, 'created', $ndt));
    }

    public function testGet()
    {
        $m = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $zone = new DateTimeZone('Europe/London');

        $d = new DateTime([
            'timezone' => $zone
        ]);

        // Testing with a timestamp:
        $now = time();
        $expected = (new NativeDateTime(null, $zone))->setTimestamp($now);
        $this->assertInstanceOf('\DateTime', $d->get($m, 'created', $now));
        $this->assertEquals($expected->format('Y-m-d H:i:s'), $d->get($m, 'created', $now)->format('Y-m-d H:i:s'));

        // Testing with a date style format from a db:
        $date = '2014-07-02';
        $this->assertInstanceOf('\DateTime', $d->get($m, 'created', $date));
        $this->assertEquals($date, $d->get($m, 'created', $date)->format('Y-m-d'));

        // Testing with a datetime style format from a db:
        $date = '2014-07-02 08:45:32';
        $this->assertInstanceOf('\DateTime', $d->get($m, 'created', $date));
        $this->assertEquals($date, $d->get($m, 'created', $date)->format('Y-m-d H:i:s'));
    }
}
