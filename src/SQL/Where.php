<?php

namespace Solution10\ORM\SQL;

/**
 * Where
 *
 * Adds in where(), orWhere() and groupings
 * into the SQL builder.
 *
 *      $query->where(function($query) {
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
 *      WHERE (name = 'Alex' AND country = 'GB')
 *      OR (name = 'Lucie' AND country = 'CA')
 *
 * @package     Solution10\ORM\SQL
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
trait Where
{
    /**
     * @var     array   Where parts
     */
    protected $whereParts = [];

    /**
     * @var     array   Where params (used as a shortcut)
     */
    protected $whereParams = [];

    /**
     * Get/Set an "AND WHERE" clause on the query. You can either pass a simple
     * comparison ('name', '=', 'Alex') or a function to append multiple queries
     * in a group.
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
     * Adds a new 'OR ' predicate to the query.
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
            return $this->whereParts;
        }

        if ($field instanceof \Closure) {
            // Return and merge the result of these queries
            $subQuery = new NestedWhere();
            $field($subQuery);
            $this->whereParts[] = [
                'join' => $join,
                'sub' => $subQuery->where()
            ];
            $this->whereParams = array_merge($this->whereParams, $subQuery->getWhereParams());
        } else {
            $this->whereParts[] = [
                'join' => $join,
                'field' => $field,
                'operator' => $operator,
                'value' => $value
            ];
            $this->whereParams[] = $value;
        }

        return $this;
    }

    /**
     * Returns the SQL string for the WHERE portion of the query
     *
     * @return  string
     */
    public function buildWhereSQL()
    {
        if (empty($this->whereParts)) {
            return '';
        }

        return 'WHERE '.$this->buildWhere($this->whereParts);
    }

    /**
     * Where is a complex, recursive beast, so this builds up each layer of the query.
     *
     * @param   array   $parts
     * @return  string
     */
    protected function buildWhere(array $parts = array())
    {
        $where = '';
        foreach ($parts as $c) {
            $where .= ' '.$c['join'].' ';
            if (array_key_exists('sub', $c)) {
                $where .= '(';
                $where .= $this->buildWhere($c['sub']);
                $where .= ')';
            } else {
                $where .= $c['field'].' '.$c['operator'].' ?'; //.$builder->createNamedParameter($c['value']);
            }
        }
        $where = trim(preg_replace('/^(AND|OR) /', '', trim($where)));
        return $where;
    }

    /**
     * Returns an array of all the parameter that have been passed to where()
     * ready to be thrown at PDO.
     *
     * @return  array
     */
    public function getWhereParams()
    {
        return $this->whereParams;
    }
}
