<?php

namespace Solution10\ORM\Tests\ActiveRecord;

use PHPUnit_Framework_TestCase;
use Solution10\ORM\ActiveRecord\Model;
use Solution10\ORM\ActiveRecord\Resultset;

class ResultsetTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $c = new Resultset();
        $this->assertInstanceOf('Solution10\\ORM\ActiveRecord\\Resultset', $c);
        $this->assertInstanceOf('Solution10\\Collection\\Collection', $c);
    }

    public function testResultModel()
    {
        $c = new Resultset();
        $m = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');

        $this->assertNull($c->resultModel());
        $this->assertEquals($c, $c->resultModel($m));
        $this->assertEquals($m, $c->resultModel());
    }

    public function testInstancesReturnedLooping()
    {
        $results = [
            ['id' => 1, 'name' => 'Alex', 'email' => 'alex@solution10.com'],
            ['id' => 2, 'name' => 'Lucie', 'email' => null],
        ];

        $c = new Resultset($results);
        $m = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $c->resultModel($m);

        foreach ($c as $idx => $item) {
            $this->assertInstanceOf('Solution10\ORM\Tests\ActiveRecord\Stubs\User', $item);
            $this->assertEquals($results[$idx]['id'], $item->get('id'));
            $this->assertEquals($results[$idx]['name'], $item->get('name'));
            $this->assertEquals($results[$idx]['email'], $item->get('email'));
        }
    }

    public function testInstancesReturnedArrayAccess()
    {
        $results = [
            ['id' => 1, 'name' => 'Alex', 'email' => 'alex@solution10.com'],
            ['id' => 2, 'name' => 'Lucie', 'email' => null],
        ];

        $c = new Resultset($results);
        $m = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $c->resultModel($m);

        $item = $c[1];
        $this->assertInstanceOf('Solution10\ORM\Tests\ActiveRecord\Stubs\User', $item);
        $this->assertEquals($results[1]['id'], $item->get('id'));
        $this->assertEquals($results[1]['name'], $item->get('name'));
        $this->assertEquals($results[1]['email'], $item->get('email'));

        $item = $c[0];
        $this->assertInstanceOf('Solution10\ORM\Tests\ActiveRecord\Stubs\User', $item);
        $this->assertEquals($results[0]['id'], $item->get('id'));
        $this->assertEquals($results[0]['name'], $item->get('name'));
        $this->assertEquals($results[0]['email'], $item->get('email'));
    }

    public function testInstancesCollectionMagic()
    {
        $results = [
            ['id' => 1, 'name' => 'Alex', 'email' => 'alex@solution10.com'],
            ['id' => 2, 'name' => 'Lucie', 'email' => null],
            ['id' => 3, 'name' => 'Archibald', 'email' => 'archie@google.com']
        ];

        $c = new Resultset($results);
        $m = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $c->resultModel($m);

        $ranged = $c['1:2'];
        $this->assertCount(2, $ranged);
        foreach ($ranged as $idx => $item) {
            $this->assertInstanceOf('Solution10\ORM\Tests\ActiveRecord\Stubs\User', $item);
            $this->assertEquals($results[$idx+1]['id'], $item->get('id'));
            $this->assertEquals($results[$idx+1]['name'], $item->get('name'));
            $this->assertEquals($results[$idx+1]['email'], $item->get('email'));
        }
    }
}
