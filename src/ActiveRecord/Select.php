<?php

namespace Solution10\ORM\ActiveRecord;

class Select extends \Solution10\SQL\Select
{
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
