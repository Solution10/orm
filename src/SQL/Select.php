<?php

namespace Solution10\ORM\SQL;

/**
 * Select
 *
 * Runs a SELECT SQL query against the database.
 *
 * @package     Solution10\ORM\SQL
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
class Select
{
    use Where;
    use Paginate;

    /**
     * @var     array
     */
    protected $selectColumns = ['*'];

    /**
     * @var     array
     */
    protected $fromTables = [];

    /**
     * @var     array
     */
    protected $joins = [];

    /**
     * @var     array
     */
    protected $orderBy = [];

    /**
     * Set/Get the Select columns.
     *
     * @param   string|array|null   $columns
     * @return  $this|array
     */
    public function select($columns = null)
    {
        if ($columns === null) {
            return $this->selectColumns;
        }

        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $this->selectColumns = $columns;
        return $this;
    }

    /**
     * Builds the SQL for the SELECT component of the query
     *
     * @return  string
     */
    public function buildSelectSQL()
    {
        $parts = [];
        foreach ($this->selectColumns as $key => $value) {
            $parts[] = (is_integer($key))? $value : $key.' AS '.$value;
        }
        return 'SELECT '.implode(', ', $parts);
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
            return $this->fromTables;
        }
        $this->fromTables[$table] = $alias;
        return $this;
    }

    /**
     * Builds the SQL for the FROM component of the query
     *
     * @return  string
     */
    public function buildFromSQL()
    {
        if (empty($this->fromTables)) {
            return '';
        }

        $parts = [];
        foreach ($this->fromTables as $key => $value) {
            $parts[] = (is_null($value))? $key : $key.' '.$value;
        }
        return 'FROM '.implode(', ', $parts);
    }

    /**
     * Sets/Gets a JOIN.
     *
     * @param   string|null     $left       String to set the left table (or the alias in from()), null to get
     * @param   string|null     $right      Name of the right table
     * @param   string|null     $predicate  String expression of how to make the join (u.id = p.u_id)
     * @param   string          $type       Type (LEFT, RIGHT, INNER)
     * @return  $this|array                 $this on set, array on get
     * @throws  \InvalidArgumentException   On unknown $type
     */
    public function join($left = null, $right = null, $predicate = null, $type = 'INNER')
    {
        if ($left === null) {
            return $this->joins;
        }

        if (!in_array($type, ['LEFT', 'RIGHT', 'INNER'])) {
            throw new \InvalidArgumentException('Unknown join type "'.$type.'"');
        }

        $this->joins[] = [
            'type' => $type,
            'left' => $left,
            'right' => $right,
            'predicate' => $predicate,
        ];

        return $this;
    }

    /**
     * Builds the SQL for a JOIN statement
     *
     * @return  string
     */
    public function buildJoinSQL()
    {
        if (empty($this->joins)) {
            return '';
        }

        $joins = [];
        foreach ($this->joins as $j) {
            $join = ($j['type'] != 'INNER')? $j['type'].' ' : '';
            $join .= 'JOIN ';
            $join .= $j['right'].' ON '.$j['predicate'];
            $joins[] = $join;
        }

        return trim(implode(' ', $joins));
    }

    /**
     * Set/Get the ORDER BY component of the query.
     *
     * @param   string|array|null       $field      Field name or an array of field => direction. Null to get
     * @param   string|null             $direction  ASC by default
     * @return  $this|array
     */
    public function orderBy($field = null, $direction = 'ASC')
    {
        if ($field === null) {
            return $this->orderBy;
        }

        if (!is_array($field)) {
            $field = [$field => $direction];
        }

        foreach ($field as $f => $d) {
            $this->orderBy[$f] = $d;
        }

        return $this;
    }

    /**
     * Builds the SQL for the ORDER BY part of the query
     *
     * @return  string
     */
    public function buildOrderBySQL()
    {
        if (empty($this->orderBy)) {
            return '';
        }

        $parts = [];
        foreach ($this->orderBy as $field => $direction) {
            $parts[] = $field.' '.$direction;
        }

        return 'ORDER BY '.implode(', ', $parts);
    }
}
