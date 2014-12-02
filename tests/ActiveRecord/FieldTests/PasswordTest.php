<?php

namespace Solution10\ORM\Tests\ActiveRecord\FieldTests;

use Solution10\ORM\ActiveRecord\Model;
use Solution10\ORM\Tests\ActiveRecord\FieldTests;
use Solution10\ORM\ActiveRecord\Field\Password;

class PasswordTests extends FieldTests
{
    protected function fieldClass()
    {
        return 'Solution10\\ORM\\ActiveRecord\\Field\\Password';
    }

    protected function fieldInstance()
    {
        return new Password([
            'hashWith' => function ($model, $field, $value) {
                return sha1($value);
            }
        ]);
    }

    /**
     * @expectedException       \Solution10\ORM\ActiveRecord\Exception\FieldException
     * @expectedExceptionCode   \Solution10\ORM\ActiveRecord\Exception\FieldException::MISSING_REQUIRED_OPTIONS
     */
    public function testConstructMissingHashWith()
    {
        new Password();
    }

    public function testSet()
    {
        $m = Model::factory('Solution10\ORM\Tests\ActiveRecord\Stubs\User');
        $p = $this->fieldInstance();
        $this->assertEquals('de5f7d71dda25c2e9957be163800502b1abe95cb', $p->set($m, 'password', 'MyAwesomePassword'));
    }
}
