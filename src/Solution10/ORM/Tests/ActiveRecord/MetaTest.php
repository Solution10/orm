<?php

namespace Solution10\ORM\Tests\ActiveRecord;

use Solution10\ORM\ActiveRecord\Meta;

class MetaTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSetTable()
    {
        $meta = new Meta('MyApp\\Models\\User');
        $this->assertEquals('users', $meta->table());

        $meta = new Meta('MyApp\\Models\\BlogPosts');
        $this->assertEquals($meta, $meta->table('posts'));
        $this->assertEquals('posts', $meta->table());
    }

    public function testGetSetConnection()
    {
        $meta = new Meta('MyApp\\Models\\User');
        $this->assertEquals('default', $meta->connection());
        $this->assertEquals($meta, $meta->connection('unittests'));
        $this->assertEquals('unittests', $meta->connection());
    }
}
