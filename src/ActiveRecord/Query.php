<?php

namespace Solution10\ORM\ActiveRecord;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Solution10\ORM\ActiveRecord\Exception\QueryException;

class Query
{
    /**
     * @var     string
     */
    protected $model;

    /**
     * @var     QueryBuilder
     */
    protected $query;

    /**
     * Starts a brand new query
     *
     * @param   Connection  $conn   Database connection we're using
     * @param   string  $model  The Model class that this query is for
     * @throws  QueryException
     */
    public function __construct(Connection $conn, $model)
    {
        $this->model($model);
        $this->query = $conn->createQueryBuilder();
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

    /**
     * ------------------- Query Building Passthroughs -------------------
     */

    /**
     * Get/Set the Select columns.
     *
     * @param   string|array|null   $columns
     * @return  $this|array
     */
    public function select($columns = null)
    {
        if ($columns === null) {
            return $this->query->getQueryPart('select');
        }

        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $this->query->select($columns);
        return $this;
    }

    /**
     * Get/Set Table to pull from.
     *
     * @param   string|null     $table      String to set, NULL to return
     * @param   string|null     $alias
     * @return  $this|string|array
     */
    public function from($table = null, $alias = null)
    {
        if ($table === null) {
            $parts = $this->query->getQueryPart('from');
            if ($parts[0]['alias'] === null) {
                return $parts[0]['table'];
            } else {
                return [$parts[0]['table'] => $parts[0]['alias']];
            }
        }

        // Reset the from:
        $this->query->resetQueryPart('from');
        $this->query->from($table, $alias);
        return $this;
    }
}
