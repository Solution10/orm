<?php

namespace Solution10\ORM\Tests\SQL;

use PHPUnit_Framework_TestCase;
use Solution10\ORM\SQL\NestedWhere;

class WhereTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return \Solution10\ORM\SQL\Where
     */
    protected function whereObject()
    {
        return $this->getMockForTrait('Solution10\\ORM\\SQL\\Where', []);
    }

    public function testNoWhere()
    {
        $w = $this->whereObject();
        $this->assertEquals('', $w->buildWhereSQL());

        // Test return type:
        $this->assertEquals([], $w->where());
    }

    public function testSimpleWhere()
    {
        $w = $this->whereObject();
        $this->assertEquals($w, $w->where('name', '=', 'Alex'));
        $this->assertEquals('WHERE name = ?', $w->buildWhereSQL());
        $this->assertEquals(['Alex'], $w->getWhereParams());

        // Test return type:
        $where = $w->where();
        $this->assertCount(1, $where);
        $this->assertEquals([
            'join' => 'AND',
            'field' => 'name',
            'operator' => '=',
            'value' => 'Alex'
        ], $where[0]);
    }

    public function testSimpleOR()
    {
        $w = $this->whereObject();
        $w->where('name', '=', 'Alex')
            ->orWhere('name', '=', 'Alexander');

        $this->assertEquals('WHERE name = ? OR name = ?', $w->buildWhereSQL());
        $this->assertEquals(['Alex', 'Alexander'], $w->getWhereParams());

        // Test return type:
        $where = $w->where();
        $this->assertCount(2, $where);
        $this->assertEquals([
            'join' => 'AND',
            'field' => 'name',
            'operator' => '=',
            'value' => 'Alex'
        ], $where[0]);

        $this->assertEquals([
            'join' => 'OR',
            'field' => 'name',
            'operator' => '=',
            'value' => 'Alexander'
        ], $where[1]);
    }

    public function testSimpleGroup()
    {
        $w = $this->whereObject();
        $w->where('name', '=', 'Alex');
        $w->where(function (NestedWhere $q) {
            $q->where('city', '=', 'London');
            $q->orWhere('city', '=', 'Toronto');
        });

        $this->assertEquals('WHERE name = ? AND (city = ? OR city = ?)', $w->buildWhereSQL());
        $this->assertEquals(['Alex', 'London', 'Toronto'], $w->getWhereParams());

        $where = $w->where();
        $this->assertEquals([
            ['join' => 'AND', 'field' => 'name', 'operator' => '=', 'value' => 'Alex'],
            [
                'join' => 'AND',
                'sub' => [
                    ['join' => 'AND', 'field' => 'city', 'operator' => '=', 'value' => 'London'],
                    ['join' => 'OR', 'field' => 'city', 'operator' => '=', 'value' => 'Toronto']
                ]
            ]
        ], $where);
    }

    /**
     * Time for a final, large and complex query to test the where() and orWhere() clauses.
     */
    public function testWhereComplex()
    {
        $w = $this->whereObject();

        $w
            ->where('name', '=', 'Alex')
            ->orWhere('name', '=', 'Lucie')
            ->where(function (NestedWhere $query) {
                $query
                    ->where('city', '=', 'London')
                    ->where('country', '=', 'GB');
            })
            ->orWhere(function (NestedWhere $query) {
                $query
                    ->where('city', '=', 'Toronto')
                    ->where('country', '=', 'CA')
                    ->orWhere(function (NestedWhere $query) {
                        $query->where('active', '!=', true);
                    });
            });

        $this->assertEquals(
            'WHERE name = ? OR name = ? AND (city = ? AND country = ?) OR (city = ? AND country = ? OR (active != ?))',
            $w->buildWhereSQL()
        );
        $this->assertEquals(
            ['Alex', 'Lucie', 'London', 'GB', 'Toronto', 'CA', true],
            $w->getWhereParams()
        );

        // Check the return types:
        $where = $w->where();
        $this->assertCount(4, $where);
        $this->assertEquals([
            ['join' => 'AND', 'field' => 'name', 'operator' => '=', 'value' => 'Alex'],
            ['join' => 'OR', 'field' => 'name', 'operator' => '=', 'value' => 'Lucie'],
            [
                'join' => 'AND',
                'sub' => [
                    ['join' => 'AND', 'field' => 'city', 'operator' => '=', 'value' => 'London'],
                    ['join' => 'AND', 'field' => 'country', 'operator' => '=', 'value' => 'GB']
                ]
            ],
            [
                'join' => 'OR',
                'sub' => [
                    ['join' => 'AND', 'field' => 'city', 'operator' => '=', 'value' => 'Toronto'],
                    ['join' => 'AND', 'field' => 'country', 'operator' => '=', 'value' => 'CA'],
                    [
                        'join' => 'OR',
                        'sub' => [
                            ['join' => 'AND', 'field' => 'active', 'operator' => '!=', 'value' => true],
                        ]
                    ]
                ]
            ]
        ], $where);
    }
}
