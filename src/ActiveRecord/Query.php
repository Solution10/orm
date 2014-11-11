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
     * @var     QueryBuilder
     */
    protected $query;

    /**
     * @var     array   Holds the query in an array form before we use the QueryBuilder to do it for real.
     */
    protected $parts = [];

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
            return (array_key_exists('SELECT', $this->parts))? $this->parts['SELECT'] : [];
        }

        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $this->parts['SELECT'] = $columns;
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
            return (array_key_exists('FROM', $this->parts))? $this->parts['FROM'] : [];
        }

        ($alias !==  null)? $this->parts['FROM'][$table] = $alias : $this->parts['FROM'][] = $table;

        return $this;
    }

    /**
     * Get/Set an "AND WHERE" clause on the query. You can either pass a simple
     * comparison ('name', '=', 'Alex') or a function to append multiple queries
     * in a group:
     *
     *  $query->where(function($query) {
     *          $query
     *              ->where('user', '=', 'Alex')
     *              ->where('country', '=', 'GB');
     *      })
     *      ->orWhere(function($query) {
     *          $query->where('user', '=', 'Lucie');
     *          $query->where('country', '=', 'CA');
     *      });
     *
     * Would generate:
     *
     *  WHERE (name = 'Alex' AND country = 'GB')
     *  OR (name = 'Lucie' AND country = 'CA')
     *
     * @param   string|\Closure|null    $field      Fieldname|callback for group|to return
     * @param   string|null             $operator   Operator (=, !=, <>, <= etc)
     * @param   mixed|null              $value      Value to test against
     * @return  $this|array                         $this on set, array on get
     */
    public function where($field = null, $operator = null, $value = null)
    {
        return $this->applyWhere('AND', $field, $operator, $value);
    }

    /**
     * Adds a new 'OR ' predicate to the query. Same rules for types as where() so check
     * the docs there.
     *
     * @param   string|\Closure|null    $field      Fieldname|callback for group|to return
     * @param   string|null             $operator   Operator (=, !=, <>, <= etc)
     * @param   mixed|null              $value      Value to test against
     * @return  $this|array                         $this on set, array on get
     */
    public function orWhere($field = null, $operator = null, $value = null)
    {
        return $this->applyWhere('OR', $field, $operator, $value);
    }

    /**
     * Actually applies the where() clause. See docs on where() for field descriptions.
     *
     * @param   string                  $join       AND or OR
     * @param   string|\Closure|null    $field      Fieldname|callback for group|to return
     * @param   string|null             $operator   Operator (=, !=, <>, <= etc)
     * @param   mixed|null              $value      Value to test against
     * @return  $this|array                         $this on set, array on get
     */
    protected function applyWhere($join, $field = null, $operator = null, $value = null)
    {
        if ($field === null) {
            return (array_key_exists('WHERE', $this->parts))? $this->parts['WHERE'] : [];
        }

        if ($field instanceof \Closure) {
            // Return and merge the result of these queries
            $subQuery = new self($this->model);
            $field($subQuery);
            $this->parts['WHERE'][] = [
                'join' => $join,
                'sub' => $subQuery->where()
            ];
        } else {
            $this->parts['WHERE'][] = [
                'join' => $join,
                'field' => $field,
                'operator' => $operator,
                'value' => $value
            ];
        }

        return $this;
    }

    /**
     * Get/Set the ORDER BY component of the query.
     *
     * @param   string|array|null       $field      Field name or an array of field => direction. Null to get
     * @param   string|null             $direction  ASC by default
     * @return  $this|array
     */
    public function orderBy($field = null, $direction = 'ASC')
    {
        if ($field === null) {
            return (array_key_exists('ORDER BY', $this->parts))? $this->parts['ORDER BY'] : [];
        }

        if (!is_array($field)) {
            $field = [$field => $direction];
        }

        foreach ($field as $f => $d) {
            $this->parts['ORDER BY'][$f] = $d;
        }

        return $this;
    }

    /**
     * Get/Set the limit of the query
     *
     * @param   int|null    $limit  Int to set, null to get
     * @return  $this|int
     */
    public function limit($limit = null)
    {
        if ($limit === null) {
            return (array_key_exists('LIMIT', $this->parts))? $this->parts['LIMIT'] : null;
        }

        $this->parts['LIMIT'] = (int)$limit;
        return $this;
    }

    /**
     * Get/Set the offset of the query
     *
     * @param   int|null    $offset     Int to set, null to get
     * @return  $this|int
     */
    public function offset($offset = null)
    {
        if ($offset === null) {
            return (array_key_exists('OFFSET', $this->parts))? $this->parts['OFFSET'] : 0;
        }

        $this->parts['OFFSET'] = (int)$offset;
        return $this;
    }
}
