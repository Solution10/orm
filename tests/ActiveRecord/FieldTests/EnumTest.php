<?php

namespace Solution10\ORM\Tests\ActiveRecord\FieldTests;

use Solution10\ORM\Tests\ActiveRecord\FieldTests;
use Solution10\ORM\ActiveRecord\Field\Enum;

class EnumTests extends FieldTests
{
    protected function fieldClass()
    {
        return 'Solution10\\ORM\\ActiveRecord\\Field\\Enum';
    }

    protected function fieldInstance()
    {
        return new Enum([
            'choices' => ['a', 'b', 'c']
        ]);
    }

    /**
     * @expectedException       \Solution10\ORM\ActiveRecord\Exception\FieldException
     * @expectedExceptionCode   \Solution10\ORM\ActiveRecord\Exception\FieldException::MISSING_REQUIRED_OPTIONS
     */
    public function testConstructMissingChoices()
    {
        new Enum();
    }

    public function testChoicesAreAddedToValidation()
    {
        $e = $this->fieldInstance();
        $v = $e->validation();

        $this->assertCount(1, $v);
        $this->assertEquals(['in', ['a', 'b', 'c']], $v[0]);
    }
}
