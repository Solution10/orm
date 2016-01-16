<?php

namespace Solution10\ORM\Tests\ActiveRecord;

use Solution10\ORM\ActiveRecord\Meta;
use Solution10\ORM\ActiveRecord\Field\Text;

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

    public function testTableSingular()
    {
        $meta = new Meta('MyApp\\Models\\User');
        $meta->table('users');

        $this->assertEquals('user', $meta->tableSingular());
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
        $field = new Text();
        $meta = new Meta('MyApp\\Models\\User');

        $this->assertEquals($meta, $meta->field('username', $field));
        $this->assertEquals($field, $meta->field('username'));
    }

    public function testGetUnknownField()
    {
        $meta = new Meta('MyApp\\Models\\User');
        $this->assertNull($meta->field('unknown'));
    }

    public function testSetGetRelationships()
    {
        $meta = new Meta('MyApp\\Models\\User');
        $this->assertEquals([], $meta->relationships());
        $this->assertEquals(null, $meta->relationship('posts'));

        $this->assertEquals($meta, $meta->relationship('posts', 'hasMany', 'BlogPost', ['query' => 'blah']));
        $this->assertEquals([
            'type' => 'hasMany',
            'name' => 'posts',
            'model' => 'BlogPost',
            'options' => ['query' => 'blah']
        ], $meta->relationship('posts'));
    }

    /**
     * @expectedException       \Solution10\ORM\ActiveRecord\Exception\MetaException
     * @expectedExceptionCode   \Solution10\ORM\ActiveRecord\Exception\MetaException::UNKNOWN_RELATIONSHIP
     */
    public function testBadRelationshipType()
    {
        $meta = new Meta('MyApp\\Models\\User');
        $meta->relationship('bad', 'hasAll', 'BlogPost');
    }
}
