<?php
namespace Wormvc\Wormvc;

/**
 * Class Query
 *
 * @author		Eduardo Lazaro Rodriguez <eduzroco@gmail.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */
 
class Query
{
	/** @var string */
    const ORDER_ASCENDING = 'ASC';
   
	/** @var string */
    const ORDER_DESCENDING = 'DESC';
   
	/** @var integer */
    protected $limit = 0;

    /** @var integer */
    protected $offset = 0;
    
	/** @var array */
    protected $where = array();
    /**
     * @var string
     */
    protected $sort_by = 'id';
    /**
     * @var string
     */
    protected $order = 'ASC';
    /**
     * @var string|null
     */
    protected $search_term = null;
    /**
     * @var array
     */
    protected $search_fields = array();
    /**
     * @var string
     */
    protected $model;
    /**
     * @var string
     */
    protected $primary_key;
    /**
     * @param string $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }
    /**
     * Return the string representation of the query.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->composeQuery();
    }
    /**
     * Set the fields to include in the search.
     *
     * @param  array $fields
     */
    public function setSearchableFields(array $fields)
    {
        $this->search_fields = $fields;
    }
    /**
     * Set the primary key column.
     *
     * @param string $primary_key
     */
    public function setPrimaryKey($primary_key)
    {
        $this->primary_key = $primary_key;
        $this->sort_by     = $primary_key;
    }
    /**
     * Set the maximum number of results to return at once.
     *
     * @param  integer $limit
     * @return self
     */
    public function limit($limit)
    {
        $this->limit = (int) $limit;
        return $this;
    }
    /**
     * Set the offset to use when calculating results.
     *
     * @param  integer $offset
     * @return self
     */
    public function offset($offset)
    {
        $this->offset = (int) $offset;
        return $this;
    }
    /**
     * Set the column we should sort by.
     *
     * @param  string $sort_by
     * @return self
     */
    public function sortBy($sort_by)
    {
        $this->sort_by = $sort_by;
        return $this;
    }
    /**
     * Set the order we should sort by.
     *
     * @param  string $order
     * @return self
     */
    public function order($order)
    {
        $this->order = $order;
        return $this;
    }

    /**---------------------------------------------------------------
     * Add a variable clause to the search query.
     * ---------------------------------------------------------------
     * @param mixed $args,... An set of condition, operator and value,
	 * or anarray of sets
     * @return self
     */	
	 
	 

    public function where(...$args)
    {
		/*
        if ($args[0] instanceof Closure) {
            $args[0]($query = $this->model->query());
            $this->query->addNestedWhereQuery($query->getQuery(), $boolean);
        }
		*/
		if (!is_array($args[0])) $args = array($args);		
		foreach ($args as $arg) {
			if (count($arg) == 2) {
				$arg[2] = $arg[1];
				$arg[1] = '=';
			}
			if(!isset($arg[3])) $arg[3] = 'AND';
			$this->where[] = array('type' => 'where', 'column' => $arg[0], 'operator' => $arg[1], 'value' => $arg[2], 'boolean' => $arg[3]);
		}
		return $this;
    }

    /**---------------------------------------------------------------
     * Add a conditional variable clause to the search query.
     * ---------------------------------------------------------------
     * @param mixed $args,... An set of condition, operator and value,
	 * or anarray of sets
     * @return self
     */		
    public function orWhere(...$args)
    {
		if (!is_array($args[0])) $args = array($args);		
		foreach ($args as $arg) {
			if (count($arg) == 2) {
				$arg[3] = $arg[2];
				$arg[2] = '=';
			}
			if(!isset($arg[3])) $arg[3] = 'OR';
			$this->where[] = array('type' => 'orwhere', 'column' => $arg[0], 'operator' => $arg[1], 'value' => $arg[2], 'boolean' => $arg[3]);
		}
		return $this;
    }		
	
    public function whereRaw($query, $replace = null, $symbol = 'AND')
    {
		if (isset($replace)) $query = preg_replace('/(^\? )|( \? )/', $replace, $query); 
        $this->where[] = array('type' => 'raw', 'query' => $query, 'boolean' => $symbol);
        return $this;
    }
	
    public function orWhereRaw($query, $replace = null, $symbol = 'OR')
    {
		if (isset($replace)) $query = preg_replace('/(^\? )|( \? )/', $replace, $query); 
        $this->where[] = array('type' => 'raw', 'query' => $query, 'boolean' => $symbol);
        return $this;
    }	

	
	
	
	
	//whereNot(sadsa, '', asdasd)
	//orWhereNot(sadsa, '', asdasd)
	
	
	
    /**
     * Add a `!=` clause to the search query.
     *
     * @param  string $column
     * @param  string $value
     * @return self
     */
    public function whereNot($column, $value)
    {
        $this->where[] = array('type' => 'not', 'column' => $column, 'value' => $value);
        return $this;
    }
    /**
     * Add a `LIKE` clause to the search query.
     *
     * @param  string $column
     * @param  string $value
     * @return self
     */
    public function whereLike($column, $value)
    {
        $this->where[] = array('type' => 'like', 'column' => $column, 'value' => $value);
        return $this;
    }
    /**
     * Add a `NOT LIKE` clause to the search query.
     *
     * @param  string $column
     * @param  string $value
     * @return self
     */
    public function whereNotLike($column, $value)
    {
        $this->where[] = array('type' => 'not_like', 'column' => $column, 'value' => $value);
        return $this;
    }
    /**
     * Add a `<` clause to the search query.
     *
     * @param  string $column
     * @param  string $value
     * @return self
     */
    public function whereLess($column, $value)
    {
		$this->where[] = array('type' => 'orwhere', 'column' => $arg[0], 'operator' => '<', 'value' => $arg[2]);
        return $this;
    }
    /**
     * Add a `<=` clause to the search query.
     *
     * @param  string $column
     * @param  string $value
     * @return self
     */
    public function whereLessEqual($column, $value)
    {
       $this->where[] = array('type' => 'orwhere', 'column' => $arg[0], 'operator' => '<=', 'value' => $arg[2]);
       return $this;
    }
    /**
     * Add a `>` clause to the search query.
     *
     * @param  string $column
     * @param  string $value
     * @return self
     */
    public function whereMore($column, $value)
    {
        $this->where[] = array('type' => 'orwhere', 'column' => $arg[0], 'operator' => '>', 'value' => $arg[2]);
        return $this;
    }
    /**
     * Add a `>=` clause to the search query.
     *
     * @param  string $column
     * @param  string $value
     * @return self
     */
    public function whereMoreEqual($column, $value)
    {
        $this->where[] = array('type' => 'orwhere', 'column' => $arg[0], 'operator' => '>=', 'value' => $arg[2]);
        return $this;
    }
    /**
     * Add an `IN` clause to the search query.
     *
     * @param  string $column
     * @param  array  $value
     * @return self
     */
    public function whereIn($column, array $in)
    {
        $this->where[] = array('type' => 'in', 'column' => $column, 'value' => $in);
        return $this;
    }
    /**
     * Add a `NOT IN` clause to the search query.
     *
     * @param  string $column
     * @param  array  $value
     * @return self
     */
    public function whereNotIn($column, array $not_in)
    {
        $this->where[] = array('type' => 'not_in', 'column' => $column, 'value' => $not_in);
        return $this;
    }
    /**
     * Add an OR statement to the where clause (e.g. (var = foo OR var = bar OR
     * var = baz)).
     *
     * @param  array $where
     * @return self
     */
    public function whereAny(array $where)
    {
        $this->where[] = array('type' => 'any', 'where' => $where);
        return $this;
    }
    /**
     * Add an AND statement to the where clause (e.g. (var1 = foo AND var2 = bar
     * AND var3 = baz)).
     *
     * @param  array $where
     * @return self
     */
    public function whereAll(array $where)
    {
        $this->where[] = array('type' => 'all', 'where' => $where);
        return $this;
    }
    /**
     * Get models where any of the designated fields match the given value.
     *
     * @param  string $search_term
     * @return self
     */
    public function search($search_term)
    {
        $this->search_term = $search_term;
        return $this;
    }
    /**
     * Runs the same query as find, but with no limit and don't retrieve the
     * results, just the total items found.
     *
     * @return integer
     */
    public function totalCount()
    {
        return $this->get(true);
    }
	
    /**
     * Get first result.
     *
     * @param  boolean $only_count Whether to only return the row count
     * @return array
     */
    public function first($only_count = false)
    {
		$results = $this->get($only_count);
		if (is_array($results) && count($results)) return $results[0];
		return $results;
	}
	
    /**
     * Compose & execute our query.
     *
     * @param  boolean $only_count Whether to only return the row count
     * @return array
     */
    public function get($only_count = false)
    {
        global $wpdb;
        $model = $this->model;
        // Query
        if ($only_count) {
            return (int) $wpdb->get_var($this->composeQuery(true));
        }
        $results = $wpdb->get_results($this->composeQuery(false));
        if ($results) {
            foreach ($results as $index => $result) {
                $results[$index] = $model::create((array) $result);
            }
        }
        return $results;
    }
    /**
     * Compose the actual SQL query from all of our filters and options.
     *
     * @param  boolean $only_count Whether to only return the row count
     * @return string
     */
    public function composeQuery($only_count = false)
    {
        $model  = $this->model;
        $table  = $model::getTable();
        $where  = '';
        $order  = '';
        $limit  = '';
        $offset = '';
        // Search
        if (!empty($this->search_term)) {
            $where .= ' AND (';
            foreach ($this->search_fields as $field) {
                $where .= '`' . $field . '` LIKE "%' . esc_sql($this->search_term) . '%" OR ';
            }
            $where = substr($where, 0, -4) . ')';
        }
        // Where clauses
        foreach ($this->where as $wherex) {
			switch ($wherex['type']) {
				case 'where':
					if(!is_array($wherex['value'])) {
						$where .= ' '.esc_sql(strtoupper($wherex['boolean'])).' `' . $wherex['column'] . '` '.esc_sql($wherex['operator']).' "' . esc_sql($wherex['value']) . '"';
					}
					else {
						$where .= ' '.esc_sql(strtoupper($wherex['boolean'])).' (';
						foreach ((array)$wherex['value'] as $key => $value) {
							if(!$key) $where .= ' `' . $wherex['column'] . '` '.esc_sql($wherex['operator']).' "' . esc_sql($wherex['value']) . '"';
							else $where .= ' OR `' . $wherex['column'] . '` '.esc_sql($wherex['operator']).' "' . esc_sql($wherex['value']) . '"';
						}
						$where .= ' )';
					}
					break;				
				case 'raw':
					$where .= ' AND ' . $wherex['value'];
					break;
				case 'orraw':
					$where .= ' OR ' . $wherex['value'];
					break;
				case 'not':
					$where .= ' AND `' . $wherex['column'] . '` != "' . esc_sql($wherex['value']) . '"';
					break;
				case 'like':
					$where .= ' AND `' . $wherex['column'] . '` LIKE "' . esc_sql($wherex['value']) . '"';
					break;
				case 'not_like':
					$where .= ' AND `' . $wherex['column'] . '` NOT LIKE "' . esc_sql($wherex['value']) . '"';
					break;
				case 'in':
					$where .= ' AND `' . $wherex['column'] . '` IN (';
					foreach ($wherex['value'] as $value) {
						$where .= '"' . esc_sql($value) . '",';
					}
					$where = substr($where, 0, -1) . ')';
					break;
				case 'not_in':
					$where .= ' AND `' . $wherex['column'] . '` NOT IN (';
					foreach ($wherex['value'] as $value) {
						$where .= '"' . esc_sql($value) . '",';
					}
					$where = substr($where, 0, -1) . ')';
					break;	
				case 'any':
					$where .= ' AND (';
					foreach ($wherex['where'] as $column => $value) {
						$where .= '`' . $column . '` = "' . esc_sql($value) . '" OR ';
					}
					$where = substr($where, 0, -5) . ')';
					break;	
				case 'all':
					$where .= ' AND (';
					foreach ($wherex['where'] as $column => $value) {
						$where .= '`' . $column . '` = "' . esc_sql($value) . '" AND ';
					}
					$where = substr($where, 0, -5) . ')';
					break;	
				default:
					break;				
			}
        }
        if (!empty($where)) $where = ' WHERE ' . substr($where, 5);

        // Order
        if (strstr($this->sort_by, '(') !== false && strstr($this->sort_by, ')') !== false) {
            // The sort column contains () so we assume its a function, therefore
            // don't quote it
            $order = ' ORDER BY ' . $this->sort_by . ' ' . $this->order;
        } else {
            $order = ' ORDER BY `' . $this->sort_by . '` ' . $this->order;
        }
        // Limit
        if ($this->limit > 0) {
            $limit = ' LIMIT ' . $this->limit;
        }
        // Offset
        if ($this->offset > 0) {
            $offset = ' OFFSET ' . $this->offset;
        }
        // Query
        if ($only_count) {
            return apply_filters('wporm_count_query', "SELECT COUNT(*) FROM `{$table}`{$where}", $this->model);
        }
        return apply_filters('wporm_query', "SELECT * FROM `{$table}`{$where}{$order}{$limit}{$offset}", $this->model);
    }
}