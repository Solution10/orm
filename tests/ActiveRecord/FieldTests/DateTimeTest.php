<?php

namespace Solution10\ORM\Tests\ActiveRecord\FieldTests;

use Solution10\ORM\ActiveRecord\Model;
use Solution10\ORM\Tests\ActiveRecord\FieldTests;
use Solution10\ORM\ActiveRecord\Field\DateTime;
use DateTime as NativeDateTime;

class DateTimeTests extends FieldTests
{
    protected function fieldClass()
    {
        return 'Solution10\\ORM\\ActiveRecord\\Field\\DateTime';
    }

    protected function fieldInstance()
    {
        return new DateTime();
    }

    public function testSet()
    {
        $m = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $d = new DateTime();

        // Testing with timestamp:
        $now = time();
        $this->assertEquals($now, $d->set($m, 'created', $now));

        // String:
        $this->assertEquals(1404300707, $d->set($m, 'created', '2014-07-02 12:31:47'));

        // DateTime Object:
        $ndt = new NativeDateTime('2014-07-02 12:31:47');
        $this->assertEquals(1404300707, $d->set($m, 'created', $ndt));
    }

    public function testSetWithFormat()
    {
        $m = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $d = new DateTime([
            'format' => 'Y-m-d H:i:s'
        ]);

        // Testing with timestamp:
        $now = time();
        $this->assertEquals(date('Y-m-d H:i:s'), $d->set($m, 'created', $now));

        // String:
        $this->assertEquals('2014-07-02 12:31:47', $d->set($m, 'created', '2014-07-02 12:31:47'));

        // DateTime Object:
        $ndt = new NativeDateTime('2014-07-02 12:31:47');
        $this->assertEquals('2014-07-02 12:31:47', $d->set($m, 'created', $ndt));
    }

    public function testGet()
    {
        $m = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');


    }
}
