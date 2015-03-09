<?php

namespace Solution10\ORM\SQL;

use Solution10\ORM\SQL\Dialect\ANSI;

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
    use Having;
    use Paginate;

    /**
     * @var     DialectInterface
     */
    protected $dialect;

    /**
     * @var     array
     */
    protected $selectColumns = [];

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
    protected $groupBy = [];

    /**
     * @var     array
     */
    protected $orderBy = [];

    /**
     * Pass in a dialect, otherwise it'll assume ANSI SQL.
     *
     * @param   DialectInterface|null    $dialect
     */
    public function __construct(DialectInterface $dialect = null)
    {
        $this->dialect = ($dialect === null)? new ANSI() : $dialect;
    }

    /*
     * ------------------ SELECT ---------------------
     */

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

        $this->selectColumns = array_merge($this->selectColumns, $columns);
        return $this;
    }

    /**
     * Builds the SQL for the SELECT component of the query
     *
     * @return  string
     */
    public function buildSelectSQL()
    {
        if (empty($this->selectColumns)) {
            return '';
        }

        $parts = [];
        foreach ($this->selectColumns as $key => $value) {
            if (is_integer($key)) {
                $parts[] = $this->dialect->quoteField($value);
            } else {
                $parts[] = $this->dialect->quoteField($key).' AS '.$this->dialect->quoteField($value);
            }
        }
        return 'SELECT '.implode(', ', $parts);
    }

    /**
     * Resets the SELECT portion of this query to empty.
     *
     * @return  $this
     */
    public function resetSelect()
    {
        $this->selectColumns = [];
        return $this;
    }

    /*
     * ----------------- FROM --------------------
     */

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
            if ($value === null) {
                $parts[] = $this->dialect->quoteTable($key);
            } else {
                $parts[] = $this->dialect->quoteTable($key).' '.$this->dialect->quoteTable($value);
            }
        }
        return 'FROM '.implode(', ', $parts);
    }

    /**
     * Resets the FROM portion of this query to empty.
     *
     * @return  $this
     */
    public function resetFrom()
    {
        $this->fromTables = [];
        return $this;
    }

    /*
     * ------------------ JOIN -----------------------
     */

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
            $join .= $this->dialect->quoteTable($j['right']).' ON '.$this->dialect->quoteField($j['predicate']);
            $joins[] = $join;
        }

        return trim(implode("\n", $joins));
    }

    /**
     * Resets the JOIN portion of this query to empty.
     *
     * @return  $this
     */
    public function resetJoins()
    {
        $this->joins = [];
        return $this;
    }

    /*
     * ------------------ GROUP BY -------------------
     */

    /**
     * Set/Get the group by clause
     *
     * @param   string|array|null     $clause     String or array to set, null to get
     * @return  $this|string|array
     */
    public function groupBy($clause = null)
    {
        if ($clause === null) {
            return $this->groupBy;
        }

        $clause = (is_array($clause))? $clause : [$clause];
        $this->groupBy = array_merge($this->groupBy, $clause);

        return $this;
    }

    /**
     * Builds the SQL for a GROUP BY statement
     *
     * @return  string
     */
    public function buildGroupBySQL()
    {
        if (empty($this->groupBy)) {
            return '';
        }

        $parts = [];
        foreach ($this->groupBy as $p) {
            $parts[] = $this->dialect->quoteField($p);
        }

        return 'GROUP BY '.implode(', ', $parts);
    }

    /**
     * Resets the GROUP BY portion of this query to empty.
     *
     * @return  $this
     */
    public function resetGroupBy()
    {
        $this->groupBy = [];
        return $this;
    }

    /*
     * ---------------- ORDER BY ----------------------
     */

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
            $parts[] = $this->dialect->quoteField($field).' '.$direction;
        }

        return 'ORDER BY '.implode(', ', $parts);
    }

    /**
     * Resets the ORDER BY portion of this query to empty.
     *
     * @return  $this
     */
    public function resetOrderBy()
    {
        $this->orderBy = [];
        return $this;
    }

    /*
     * --------------- Generating SQL --------------------
     */

    /**
     * Generates the full SQL statement for this query with all the composite parts.
     * Note: there is no guarantee that this will be valid SQL! Obviously the parts
     * you've given will come out good, but if you forget to add a FROM or something,
     * this class won't automatically guess one for you!
     *
     * @return  string
     */
    public function sql()
    {
        $candidateParts = [
            $this->buildSelectSQL(),
            $this->buildFromSQL(),
            $this->buildJoinSQL(),
            $this->buildWhereSQL($this->dialect),
            $this->buildGroupBySQL(),
            $this->buildHavingSQL($this->dialect),
            $this->buildOrderBySQL(),
            $this->buildPaginateSQL()
        ];

        $realParts = [];
        foreach ($candidateParts as $p) {
            if ($p != '') {
                $realParts[] = $p;
            }
        }

        return implode(" ", $realParts);
    }

    /**
     * Serves as a shortcut for sql()
     *
     * @return  string
     */
    public function __toString()
    {
        return $this->sql();
    }

    /**
     * Returns all the parameters, in the correct order, to pass into PDO.
     *
     * @return  array
     */
    public function params()
    {
        return array_merge($this->getWhereParams(), $this->getHavingParams());
    }

    /**
     * Resets the entire query by calling each sections resetXXX() function.
     *
     * @return  $this
     */
    public function reset()
    {
        return $this
            ->resetSelect()
            ->resetFrom()
            ->resetJoins()
            ->resetWhere()
            ->resetGroupBy()
            ->resetHaving()
            ->resetOrderBy()
            ->resetLimit()
            ->resetOffset();
    }
}
