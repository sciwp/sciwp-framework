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
    protected $skip = 0;
    
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
        $this->primary_key = $model::primaryKey();
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
     * Set the skip to use when calculating results.
     *
     * @param  integer $skip
     * @return self
     */
    public function skip($skip)
    {
        $this->skip = (int) $skip;
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



    public function open($boolean = 'AND')
    {
        $this->where[] = array('type' => 'open', 'boolean' => $boolean);
		return $this;
    }

    public function close()
    {
        $this->where[] = array('type' => 'close');
		return $this;
    }

    public function andOpen()
    {
        $this->open('AND');
		return $this;
    }
    
    public function orOpen()
    {
        $this->open('OR');
		return $this;
    }

    public function group(...$args)
    {
        $this->where[] = array('type' => 'open', 'boolean' => 'AND');
        foreach ($args as $query) {
            echo("<pre>");
            print_r($query);echo("</pre>");
            call_user_func_array([$this, $query[0]], (array) $query[1]);
        }
        $this->where[] = array('type' => 'close');
        return $this;
    }


    /**
     * Add a where clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of condition, operator and value,
     * @param array $options An array of options
     * @return self
     */
    private function addWhere($args, $options = []) {
        if (!is_array($args)) $args = array($args);
        if (!is_array($args[0])) $args = array($args);
        
        if (!isset($options['negation'])) $options['negation'] = '';
        if (!isset($options['boolean'])) $options['boolean'] = 'AND';
        if (!isset($options['many'])) $options['many'] = 'OR';

		foreach ($args as $key => $arg) {
            // If only a value is present
            if (count($args[$key]) == 1) {
                $args[$key][1] = $args[$key][0];
                $args[$key][0] = $this->primary_key;
			}
            // If only a key and a value is present
			if (count($args[$key]) == 2) {
				$args[$key][2] = $args[$key][1];
				$args[$key][1] = '=';
			}
            // If only key, value and operator is present
			if (count($args[$key]) == 3) {
				$args[$key][3] = $options['boolean'];
			}
            // If only key, value, operator and (AND|OR) is present, add empty for negation
			if (count($args[$key]) == 4) {
				$args[$key][4] = $options['negation'];
			}
		}
        foreach ($args as $arg) {
            $this->where[] = array('type' => 'where', 'column' => $arg[0], 'operator' => $arg[1], 'value' => $arg[2], 'boolean' => $arg[3], 'negation' => $arg[4], 'many' => $options['many']);
        }
		return $this;
    }

    /**
     * Add a and where variable clause to the search query
     *
     * @param mixed $args,... A set (or set of sets) of condition, operator and value,
     * @return self
     */
    public function where(...$args)
    {
        $this->addWhere($args);
		return $this;
    }

    /**
     * Add a and where variable clause to the search query
     *
     * @param mixed $args,... A set (or set of sets) of condition, operator and value,
     * @return self
     */
    public function whereAny(...$args)
    {
        $this->addWhere($args, ['many' => 'OR']);
		return $this;
    }

    /**
     * Add a and where variable clause to the search query
     *
     * @param mixed $args,... A set (or set of sets) of condition, operator and value,
     * @return self
     */
    public function whereAll(...$args)
    {
        $this->addWhere($args, ['many' => 'AND']);
		return $this;
    }

    /**
     * Add a where not variable clause to the search query
     *
     * @param mixed $args,... A set (or set of sets) of condition, operator and value,
     * @return self
     */
    public function whereNot(...$args)
    {
        $this->addWhere($args, ['negation' => 'NOT']);
        return $this;
    }
	
    /**
     * Add a or where variable clause to the search query
     *
     * @param mixed $args,... A set (or set of sets) of condition, operator and value,
     * @return self
     */
    public function orWhere(...$clauses)
    {
        $this->addWhere($clauses , ['negation' => '', 'boolean' => 'OR']);
		return $this;
    }
    
    /**
     * Add a or where not variable clause to the search query
     *
     * @param mixed $args,... A set (or set of sets) of condition, operator and value,
     * @return self
     */
    public function orWhereNot(...$args)
    {   
        $this->addWhere($args, ['negation' => 'NOT', 'boolean' => 'OR']);
		return $this;
    }	

    /**
     * Add a where raw clause to the search query.
     *
     * @param string $query,... The raw query,
     * @param array $options An array of options
     * @return self
     */
    private function addWhereRaw($query, $options = [])
    {
        if (!isset($options['negation'])) $options['negation'] = '';
        if (!isset($options['boolean'])) $options['boolean'] = 'AND';
        if (!isset($options['replace'])) $options['replace'] = false;
        
        if ($options['replace']) $query = preg_replace('/(^\? )|( \? )/', $replace, $query); 

        $this->where[] = array('type' => 'raw', 'query' => $query, 'boolean' => $options['boolean'], 'negation' => $options['negation']);
		return $this;
    }

    /**
     * Add a where raw clause to the search query
     *
     * @param string $query The raw query,
     * @param boolean $replace Replace dangerous characters
     * @param string $symbol Use and or OR symbol
     * @return self
     */
    public function whereRaw($query, $replace = false, $symbol = 'AND')
    {
        $this->addWhereRaw($query, ['boolean' => $symbol, 'negation' => '', 'replace' => $replace]);
        return $this;
    }
  
    /**
     * Add a where not raw clause to the search query
     *
     * @param string $query The raw query,
     * @param boolean $replace Replace dangerous characters
     * @param string $symbol Use and or OR symbol
     * @return self
     */
    public function whereNotRaw($query, $replace = false, $symbol = 'AND')
    {
        $this->addWhereRaw($query, ['boolean' => $symbol, 'negation' => 'NOT', 'replace' => $replace]);
        return $this;
    }

    /**
     * Add a or where raw clause to the search query
     *
     * @param string $query The raw query,
     * @param boolean $replace Replace dangerous characters
     * @return self
     */
    public function orWhereRaw($query, $replace = null)
    {
        $this->addWhereRaw($query, ['boolean' => 'OR', 'negation' => '', 'replace' => $replace]);
        return $this;
    }

    /**
     * Add a or where not raw clause to the search query
     *
     * @param string $query The raw query,
     * @param boolean $replace Replace dangerous characters
     * @return self
     */
    public function orWhereNotRaw($query, $replace = null)
    {
        $this->addWhereRaw($query, ['boolean' => 'OR', 'negation' => 'NOT', 'replace' => $replace]);
        return $this;
    }

    /**
     * Add a where like clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of condition and value,
     * @param array $options An array of options
     * @return self
     */
    private function addWhereOp($args, $operator, $options = []) {
        if (!is_array($args[0])) $args = array($args);
        if (!isset($options['negation'])) $options['negation'] = '';
        if (!isset($options['boolean'])) $options['boolean'] = 'AND';
        if (!isset($options['type'])) $options['type'] = 'where';

		foreach ($args as $key => $arg) {
            // If only key, value and operator is present
			if (count($args[$key]) == 2) {
				$args[$key][2] = $options['boolean'];
			}
            // If only key, value, operator and (AND|OR) is present, add empty for negation
			if (count($args[$key]) == 3) {
				$args[$key][3] = $options['negation'];
			}
		}
        foreach ($args as $arg) {
            $this->where[] = array('type' => $options['type'], 'column' => $arg[0], 'operator' => $operator, 'value' => $arg[1], 'boolean' => $arg[2], 'negation' => $arg[3]);
        }
		return $this;
    }

    /**
     * Add a '<' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereLess(...$args)
    {
        $this->addWhereOp($args, '<');
		return $this; 
    }

    /**
     * Add a `<=` clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereLessEqual(...$args)
    {
        $this->addWhereOp($args, '<=');
		return $this;
    }

    /**
     * Add a `>` clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereMore(...$args)
    {
        $this->addWhereOp($args, '>');
		return $this;
    }

    /**
     * Add a `>=` clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereMoreEqual(...$args)
    {
        $this->addWhereOp($args, '>=');
		return $this;
    }

    /**
     * Add a `in` clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereIn(...$args)
    {
        $this->addWhereOp($args, 'IN', ['type' => 'in']);
		return $this;
    }

    /**
     * Add a `not in` clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotIn(...$args)
    {
        $this->addWhereOp($args, 'NOT IN', ['type' => 'in']);
        return $this;
    }
    
    /**
     * Add a where like clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereLike(...$args)
    {   
        $this->addWhereOp($args, 'LIKE');
		return $this;
    }	

    /**
     * Add a where not like clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotLike(...$args)
    {
        $this->addWhereOp($args, 'NOT LIKE');
		return $this; 
    }
    
    /**
     * Add a or where like clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereLike(...$args)
    {
        $this->addWhereOp($args, 'LIKE', ['boolean' => 'OR']);
		return $this; 
    }

    /**
     * Add a or where not like clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotLike(...$args)
    {
        $this->addWhereOp($args, 'NOT LIKE', ['boolean' => 'OR']);
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
        $table  = $model::table();
        $where  = '';
        $order  = '';
        $limit  = '';
        $skip = '';
        // Search
        if (!empty($this->search_term)) {
            $where .= ' AND (';
            foreach ($this->search_fields as $field) {
                $where .= '`' . $field . '` LIKE "%' . esc_sql($this->search_term) . '%" OR ';
            }
            $where = substr($where, 0, -4) . ')';
        }

        // Where clauses
        $initial = true;
        foreach ($this->where as $wherex) {
            if ($initial) {
                $wherex['boolean'] = '';
                $initial = false;
            }
			switch ($wherex['type']) {
				case 'where':
					if(!is_array($wherex['value'])) {
						$where .= ' '.esc_sql(strtoupper($wherex['boolean'])).' '. $wherex['negation'] .' ( `' . $wherex['column'] . '` '.esc_sql($wherex['operator']).' "' . esc_sql($wherex['value']) . '"  )';
                    }
					else {
						$where .= ' '.esc_sql( strtoupper($wherex['boolean'])) . ' ' .strtoupper($wherex['negation']) . ' (';
						$count = 0;
                        $associative = array_keys((array)$wherex['value']) !== range(0, count((array)$wherex['value']) - 1) ? true : false;
                        foreach ($wherex['value'] as $key => $value) {
                            // If assocaitive, use the arr keys as the columns to check
                            $column = $associative ? $key : $wherex['column'];                            
							if($count) $where .= ' ' . $wherex['many'] . ' `' . $column . '` '.esc_sql($wherex['operator']).' "' . esc_sql($value) . '"';
							else $where .= ' `' . $column . '` '.esc_sql($wherex['operator']).' "' . esc_sql($value) . '"';
                            $count++;
                        }
						$where .= ' )';
					}
					break;
				case 'raw':
					$where .= ' '.strtoupper($wherex['boolean']). ' ' . strtoupper($wherex['negation']) . ' ' . $wherex['query'];  
                    break;
				case 'in':
					$where .= ' '.esc_sql(strtoupper($wherex['boolean'])).' '. $wherex['negation'] .' `' . $wherex['column'] . '` '.$wherex['operator'].' (';
					foreach ((array)$wherex['value'] as $value) {
						$where .= '"' . esc_sql($value) . '",';
					}
					$where = substr($where, 0, -1) . ')';
                    break;
				case 'open':
					$where .= ' '.strtoupper($wherex['boolean']).' (';
					$initial = true;
                    break;
				case 'close':
					$where .= ')';
                    break;	
				default:
					break;				
			}
        }

        if (!empty($where)) $where = ' WHERE ' . $where;
        

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
        // skip
        if ($this->skip > 0) {
            $skip = ' OFFSET ' . $this->skip;
            if ($this->limit == 0) {
                $limit = ' LIMIT 9999999999';
            }
        }
        

       echo("<br>"); print_r(apply_filters('wporm_count_query', "SELECT COUNT(*) FROM `{$table}`{$where}", $this->model));
        // Query
        if ($only_count) {
            return apply_filters('wporm_count_query', "SELECT COUNT(*) FROM `{$table}`{$where}", $this->model);
        }
        return apply_filters('wporm_query', "SELECT * FROM `{$table}`{$where}{$order}{$limit}{$skip}", $this->model);
    }
}