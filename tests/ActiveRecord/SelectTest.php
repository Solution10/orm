<?php

namespace Solution10\ORM\Tests\ActiveRecord;

use PHPUnit\Framework\TestCase;
use Solution10\ORM\ActiveRecord\Select;

class SelectTest extends TestCase
{
    public function testGetSetCacheKey()
    {
        $s = new Select();
        $this->assertFalse($s->getCacheKey());
        $this->assertEquals($s, $s->setCacheKey('my_query'));
        $this->assertEquals('my_query', $s->getCacheKey());

        // Verify the casting:
        $s->setCacheKey(27);
        $this->assertInternalType('string', $s->getCacheKey());
        $this->assertEquals('27', $s->getCacheKey());
    }

    public function testGetSetCacheLifetime()
    {
        $s = new Select();
        $this->assertEquals(Select::CACHE_NEVER, $s->getCacheLifetime());
        $this->assertEquals($s, $s->setCacheLifetime(27));
        $this->assertEquals(27, $s->getCacheLifetime());

        // Verify special values:
        $s->setCacheLifetime(Select::CACHE_NEVER);
        $this->assertEquals(Select::CACHE_NEVER, $s->getCacheLifetime());

        $s->setCacheLifetime(Select::CACHE_FOREVER);
        $this->assertEquals(Select::CACHE_FOREVER, $s->getCacheLifetime());

        // Verify casting:
        $s->setCacheLifetime(27.27);
        $this->assertEquals(27, $s->getCacheLifetime());
    }

    public function testGetSetCacheName()
    {
        $s = new Select();
        $this->assertEquals('default', $s->getCacheName());
        $this->assertEquals($s, $s->setCacheName('other_cache'));
        $this->assertEquals('other_cache', $s->getCacheName());
    }

    public function testCacheFor()
    {
        $s = new Select();
        $this->assertEquals(
            $s,
            $s->cacheFor(27, 'my_cache_key')
        );
        $this->assertEquals(27, $s->getCacheLifetime());
        $this->assertEquals('my_cache_key', $s->getCacheKey());

        $s = new Select();
        $this->assertEquals(
            $s,
            $s->cacheFor(Select::CACHE_FOREVER, 'my_cache_key_2', 'other_cache')
        );
        $this->assertEquals(Select::CACHE_FOREVER, $s->getCacheLifetime());
        $this->assertEquals('my_cache_key_2', $s->getCacheKey());
        $this->assertEquals('other_cache', $s->getCacheName());
    }
}
