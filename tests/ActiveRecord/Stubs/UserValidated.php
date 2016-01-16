<?php

namespace Solution10\ORM\Tests\ActiveRecord\Stubs;

use Solution10\ORM\ActiveRecord\Model;
use Solution10\ORM\ActiveRecord\Meta;
use Solution10\ORM\ActiveRecord\Field;
use Valitron\Validator;

class UserValidated extends Model
{
    public static function init(Meta $meta)
    {
        return $meta
            ->table('users')
            ->field('name', new Field\Text([
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

    public static function validatorHook(Validator $v)
    {
        $v->addRule('testHook', function ($field, $value, array $params) {
            return false;
        });
        return $v;
    }
}
