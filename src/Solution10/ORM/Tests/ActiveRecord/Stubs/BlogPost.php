<?php

namespace Solution10\ORM\Tests\ActiveRecord\Stubs;

use Solution10\ORM\ActiveRecord\Model;

class BlogPost extends Model
{
    public function initialize()
    {
        $this
            ->table('posts');
    }
}
