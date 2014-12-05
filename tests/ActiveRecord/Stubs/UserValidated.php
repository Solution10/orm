<?php

namespace Solution10\ORM\Tests\ActiveRecord\Stubs;

use Solution10\ORM\ActiveRecord\Model;
use Solution10\ORM\ActiveRecord\Meta;
use Solution10\ORM\ActiveRecord\Field;

class UserValidated extends Model
{
    public static function init(Meta $meta)
    {
        return $meta
            ->table('users')
            ->field('name', new Field\String([
                'rules' => [
                    ['required'],
                    ['lengthMin', 2]
                ]
            ]))
            ->field('about', new Field\Text)

            ->relationship('posts', 'hasMany', 'BlogPost')
            ->relationship('orders', 'hasMany', 'Solution10\\ORM\\Tests\\ActiveRecord\\Stubs\\Order')
        ;
    }
}
