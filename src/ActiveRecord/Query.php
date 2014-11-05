<?php

namespace Solution10\ORM\ActiveRecord;

use Doctrine\DBAL\Query\QueryBuilder;
use Solution10\ORM\ActiveRecord\Exception\QueryException;

class Query
{
    /**
     * @var     string
     */
    protected $model;

    /**
     * Starts a brand new query
     *
     * @param   string  $model  The Model class that this query is for
     * @throws  QueryException
     */
    public function __construct($model)
    {
        $this->model($model);
    }

    /**
     * Gets/Sets the model name for this query
     *
     * @param   string|null     $model  NULL for get, string for set
     * @return  $this|string
     * @throws  QueryException
     */
    public function model($model = null)
    {
        if ($model === null) {
            return $this->model;
        }

        if (!class_exists($model)) {
            throw new QueryException(
                'Model provided for query "'.$model.'" does not exist!',
                QueryException::UNKNOWN_MODEL
            );
        }
        $this->model = $model;

        return $this;
    }
}
