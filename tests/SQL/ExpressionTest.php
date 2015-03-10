<?php

namespace Solution10\ORM\Tests\SQL;

use PHPUnit_Framework_TestCase;
use Solution10\ORM\SQL\Expression;

class ExpressionTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $e = new Expression('COUNT(*)');
        $this->assertInstanceOf('Solution10\\ORM\\SQL\\Expression', $e);
    }

    public function testToString()
    {
        $e = new Expression('COUNT(*)');
        $this->assertEquals('COUNT(*)', (string)$e);
    }
}
