<?php

namespace Solution10\ORM\ActiveRecord;

/**
 * Class Select
 *
 * Override of the S10\SQL\Select class that adds some additional functionality
 * such as fetch(), fetchAll() and caching
 *
 * @package     Solution10\ORM\ActiveRecord
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
class Select extends \Solution10\SQL\Select
{
    const CACHE_NEVER = -1;
    const CACHE_FOREVER = 0;

    /**
     * @var     int     How long to cache this query for.
     */
    protected $cacheLifetime = self::CACHE_NEVER;

    /**
     * @var     string  Which cache to use for this query.
     */
    protected $cacheName = 'default';

    /**
     * @var     string|false  The cache key to use for this query.
     */
    protected $cacheKey = false;

    /**
     * Returns the cache lifetime, in seconds for this query.
     * Can also return "special" values for NEVER and FOREVER. Use the
     * constants on this class to work that out.
     *
     * @return  int
     */
    public function getCacheLifetime()
    {
        return $this->cacheLifetime;
    }

    /**
     * How long to cache this query for. Either a positive integer in seconds,
     * or one of the special CACHE_ constants on this class:
     *
     *  - Select::CACHE_NEVER: query will never be cached (default)
     *  - Select::CACHE_FOREVER: query will be cached indefinitely
     *
     * @param   int     $cacheLifetime
     * @return  $this
     */
    public function setCacheLifetime($cacheLifetime)
    {
        $this->cacheLifetime = (int)$cacheLifetime;
        return $this;
    }

    /**
     * Returns the name of the cache (registered with the Connection) to be used
     * with this query. Defaults to 'default'
     *
     * @return  string
     */
    public function getCacheName()
    {
        return $this->cacheName;
    }

    /**
     * Sets the cache name (registered with the Connection instance) to be used
     * for this query.
     *
     * @param   string  $cacheName
     * @return  $this
     */
    public function setCacheName($cacheName)
    {
        $this->cacheName = (string)$cacheName;
        return $this;
    }

    /**
     * Returns the cache key we're using with this query.
     *
     * @return  string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * Sets the cache key to use with this query.
     *
     * @param   string  $cacheKey
     * @return  $this
     */
    public function setCacheKey($cacheKey)
    {
        $this->cacheKey = (string)$cacheKey;
        return $this;
    }

    /**
     * This shortcut method ties together the cache functions into a single
     * call, making it easier and more fluent to set this information.
     *
     * You should usually use this to set cache info on a query.
     *
     * @param   int     $cacheLifetime
     * @param   string  $cacheKey
     * @param   string  $cacheName
     * @return  $this
     */
    public function cacheFor($cacheLifetime, $cacheKey, $cacheName = 'default')
    {
        return $this
            ->setCacheLifetime($cacheLifetime)
            ->setCacheKey($cacheKey)
            ->setCacheName($cacheName);
    }

    /**
     * Fetches all rows of a resultset
     *
     * @return  Model[]
     */
    public function fetchAll()
    {
        $this->flag('fetch', 'all');
        $model = $this->flag('model');
        return $model::fetchQuery($this);
    }

    /**
     * Fetches the first row of this query
     *
     * @return  Model
     */
    public function fetch()
    {
        $this->flag('fetch', 'one');
        $model = $this->flag('model');
        return $model::fetchQuery($this);
    }

    /**
     * Counts the results of a query and returns.
     *
     * @return  int
     */
    public function count()
    {
        $this->flag('count', true);
        $model = $this->flag('model');
        return $model::fetchCount($this);
    }
}
