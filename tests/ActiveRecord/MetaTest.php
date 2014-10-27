<?php

namespace Solution10\ORM\Tests\ActiveRecord;

use Solution10\ORM\ActiveRecord\Meta;
use Solution10\ORM\ActiveRecord\Field\String;

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

    public function testGetSetStringPk()
    {
        $meta = new Meta('MyApp\\Models\\User');
        $this->assertEquals('id', $meta->primaryKey());
        $this->assertEquals($meta, $meta->primaryKey('user_id'));
        $this->assertEquals('user_id', $meta->primaryKey());
    }

    public function testGetSetArrayPk()
    {
        $meta = new Meta('MyApp\\Models\\User');

        $pk = ['id', 'email']; // Don't do this for real...

        $this->assertEquals('id', $meta->primaryKey());
        $this->assertEquals($meta, $meta->primaryKey($pk));
        $this->assertEquals($pk, $meta->primaryKey());
    }

    public function testSetGetField()
    {
        $field = new String();
        $meta = new Meta('MyApp\\Models\\User');

        $this->assertEquals($meta, $meta->field('username', $field));
        $this->assertEquals($field, $meta->field('username'));
    }

    public function testGetUnknownField()
    {
        $meta = new Meta('MyApp\\Models\\User');
        $this->assertNull($meta->field('unknown'));
    }
}
