<?php

namespace Solution10\ORM\Tests\SQL;

use PHPUnit_Framework_TestCase;

class PaginateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return \Solution10\ORM\SQL\Paginate
     */
    protected function paginateObject()
    {
        return $this->getMockForTrait('Solution10\\ORM\\SQL\\Paginate');
    }

    public function testNoPaginateSet()
    {
        $p = $this->paginateObject();
        $this->assertEquals(null, $p->limit());
        $this->assertEquals(0, $p->offset());
        $this->assertEquals('', $p->buildPaginateSQL());
    }

    public function testLimitOnly()
    {
        $p = $this->paginateObject();

        $this->assertEquals($p, $p->limit(10));
        $this->assertEquals(10, $p->limit());

        $this->assertEquals('LIMIT 10', $p->buildPaginateSQL());
    }

    public function testLimitAndOffset()
    {
        $p = $this->paginateObject();

        $p->limit(10);
        $this->assertEquals($p, $p->offset(100));
        $this->assertEquals(100, $p->offset());

        $this->assertEquals('LIMIT 100, 10', $p->buildPaginateSQL());
    }
}
