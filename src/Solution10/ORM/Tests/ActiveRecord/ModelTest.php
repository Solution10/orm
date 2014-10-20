<?php

namespace Solution10\ORM\Tests\ActiveRecord;

use Solution10\ORM\Tests\ActiveRecord\Stubs\BlogPost;
use Solution10\ORM\Tests\ActiveRecord\Stubs\User;

class ModelTest extends \PHPUnit_Framework_TestCase
{
    public function testTable()
    {
        $model = new BlogPost();
        $this->assertEquals('posts', $model->table());

        $model = new User();
        $this->assertEquals('users', $model->table());
    }
}
