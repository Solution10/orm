<?php

namespace Solution10\ORM\Tests\SQL;

use PHPUnit_Framework_TestCase;
use Solution10\ORM\SQL\TableName;

class TableNameTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return TableName
     */
    protected function traitObject()
    {
        return $this->getMockForTrait('Solution10\\ORM\\SQL\\TableName');
    }

    public function testSetGet()
    {
        $t = $this->traitObject();
        $this->assertNull($t->table());
        $this->assertEquals($t, $t->table('users'));
        $this->assertEquals('users', $t->table());
    }
}
