<?php
namespace MyPlugin\Sci;
use \Exception;
use \Closure;
/**
 * Class Query
 *
 * @author		Eduardo Lazaro Rodriguez <eduzroco@gmail.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
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
     * The columns that should be returned.
     *
     * @var array
     */
    public $columns;

    /**
     * @var string
     */
    protected $model;

    /**
     * @var string
     */
    protected $from;

    /**
     * @var array $bindings
     */
    protected $bindings = [
        'select' => [],
        'from'   => [],
        'join'   => [],
        'where'  => [],
    ];

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $primary_key;

    /**
     * @param string $table
     * @param string $primary_key
     */
    public function __construct($table = null, $primary_key = null)
    {
        if ($table) $this->table = $table;
        if ($primary_key) $this->setPrimaryKey($primary_key);
    }

    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * Add a binding to the query.
     *
     * @param  mixed   $value
     * @param  string  $type
     * @return $this
     */
    public function addBinding($type = 'where', $binding)
    {
        if (! array_key_exists($type, $this->bindings)) {
            throw new Exception("Invalid binding type: {$type}.");
        }
        $this->bindings[$type][] = $binding;

        return $this;
    }

    /**
     * Creates a subquery and parse it.
     *
     * @param  \Query|string $query
     * @return array
     */
    protected function createSubQuery($query)
    {
        if ($query instanceof Closure) {
            $callback = $query;
            $callback($query = new self());
        }
        if ($query instanceof self) {
            return [$query->composeQuery(), $query->getBindings()];
        } elseif (is_string($query)) {
            return [$query, []];
        }
        throw new Exception('A subquery must be a query instance, a Closure, or a string.');
    }

    /**
     * Set the model
     *
     * @param string $model
     */
    public function setModel($model)
    {
        $this->model = $model;
        $this->table = $model::table();
        $this->from = $model::table();
        $this->primary_key = $model::primaryKey();
    }

    /**
     * Set the table
     *
     * @param string $model
     */
    public function setTable($table)
    {
        $this->table = $table;
        $this->from = $table;
    }

    /**
     * Set the table which the query is targeting.
     *
     * @param  \MyPlugin\Sci\Query|string  $table
     * @param  string|null  $as
     * @return $this
     */
    public function from($table, $as = null)
    { 
        if ($table instanceof self || $table instanceof Closure) {
            return $this->fromSub($table, $as);
        }
        $this->from = $as ? "`{$table}` as `{$as}`" : $table;
        return $this;
    }
    
    /**
     * Makes "from" fetch from a subquery.
     *
     * @param  \Closure|\Query|string $query
     * @param  string  $as
     * @return \Query
     */
    public function fromSub($query, $as)
    {
        [$query, $bindings] = $this->createSubQuery($query);
        $this->addBinding('from', $bindings);
        $this->from = '('.$query.') as `'.$as.'` ';
        return $this;
    }

    /**
     * Add a raw from clause to the query.
     *
     * @param  string  $expression
     * @param  mixed   $bindings
     * @return \Query
     */
    public function fromRaw($expression, $bindings = [])
    {
        $this->from = $expression;
        if ($bindings) $this->addBinding('from', $bindings);
        return $this;
    }

    /**
     * Add a new select column to the query.
     *
     * @param  array|mixed  $column
     * @return $this
     */
    public function addSelect($column)
    {
        $columns = is_array($column) ? $column : func_get_args();
        foreach ($columns as $as => $column) {
            if (is_string($as) && ($column instanceof self || $column instanceof Closure)) {
                if (is_null($this->columns)) {
                    $this->select($this->from.'.*');
                }
                $this->selectSub($column, $as);
            } else {
                $this->columns[] = $column;
            }
        }
        return $this;
    }

    /**
     * Set the columns to be selected.
     *
     * @param  array|mixed  $columns
     * @return $this
     */
    public function select($columns = ['*'])
    {
        $this->columns = [];
        $columns = is_array($columns) ? $columns : func_get_args();
        foreach ($columns as $key => $column) {
            if (is_string($key) && ($column instanceof self || $column instanceof Closure)) {   
                $this->selectSub($column, $key);
            } else {
                if (is_integer($key)) {
                    $this->columns[] = $column;
                } else {
                    $this->columns[$key] = $column;
                }                
            }
        }
        return $this;
    }

    /**
     * Add a subselect expression to the query.
     *
     * @param  \Closure|\Query|string $query
     * @param  string  $as
     * @return $this
     */

    public function selectSub($query, $as)
    {
        [$query, $bindings] = $this->createSubQuery($query);
        $this->addSelect('('.$query.') as '.$as); //Expression
        $this->addBinding('select', $bindings);
        return $this;
    }

    /**
     * Add a new "raw" select expression to the query.
     *
     * @param  string  $expression
     * @param  array   $bindings
     * @return $this
     */
    public function selectRaw($expression, array $bindings = [])
    {
        $this->addSelect($expression);
        if ($bindings) $this->addBinding('select', $bindings);
        return $this;
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


    /**
     * Add an opening parenthesis to the query
     * @param $boolean 
     * @return self
     */
    public function open($append = 'AND')
    {        
        $this->addBinding('where', ['type' => 'open', 'boolean' => $append]);
		return $this;
    }
    
    /**
     * Add an opening parenthesis to the query
     * @return self
     */
    public function orOpen()
    {
        $this->open('OR');
		return $this;
    }

    /**
     * Add a closing parenthesis to the query
     * @return self
     */
    public function close()
    {
        $this->addBinding('where', ['type' => 'close']);
		return $this;
    }

    /*
    public function group(...$args)
    {
        $this->bindings['where'][] = array('type' => 'open', 'boolean' => 'AND');
        foreach ($args as $query) {
            echo("<pre>");
            print_r($query);echo("</pre>");
            call_user_func_array([$this, $query[0]], (array) $query[1]);
        }
        $this->bindings['where'][] = array('type' => 'close');
        return $this;
    }
    */

    /**
     * Add a where clause to the search query.
     *
     * @param mixed $args. An array of caluses
     * @param array $options An array of options
     * @return self
     */
    private function addWhere($args, $options = []) {
        if (!is_array($args)) $args = array($args);
        if (!is_array($args[0])) $args = array($args);
               
        if (!isset($options['boolean'])) $options['boolean'] = true;
        if (!isset($options['append'])) $options['append'] = 'AND';
        if (!isset($options['joint'])) $options['joint'] = 'AND';
        $negation = $options['boolean'] ? '' : 'NOT';

        $queries = array();
		foreach ($args as $query) {
            $isAssociative = array_keys((array)$query) !== range(0, count((array)$query) - 1) ? true : false;
            // Pairs of key and value
            if ($isAssociative) {
                foreach($query as $key => $value) {
                    $queries[] = ['column' => $key, 'operator' => '=', 'value' => $value, 'joint' => $options['joint']];
                }
            } else {
                // If only a value is present
                if (count($query) == 1) {
                    $query[1] = $query[0];
                    $query[0] = $this->primary_key;
                }
                // If only a key and a value is present
                if (count($query) == 2) {
                    $query[2] = $query[1];
                    $query[1] = '=';
                }

                $queries[] = ['column' => $query[0], 'operator' => $query[1], 'value' => $query[2], 'joint' => $options['joint']];            
            }
        }
        
        $this->addBinding('where', ['type' => 'where', 'append' => $options['append'], 'negation' => $negation, 'queries' => $queries]);
		return $this;
    }

    /**
     * Add a and where variable clause to the search query
     *
     * @param mixed $args,... A set (or set of sets) of condition, operator and value,
     * @return self
     */
    public function where(...$args){
        return $this->addWhere($args);
    }

    /**
     * Add a where not variable clause to the search query
     *
     * @param mixed $args,... A set (or set of sets) of condition, operator and value,
     * @return self
     */
    public function whereNot(...$args)
    {
        $this->addWhere($args, ['boolean' => false]);
        return $this;
    }

    /**
     * Add a and where variable clause to the search query
     *
     * @param mixed $args,... A set (or set of sets) of condition, operator and value,
     * @return self
     */
    public function orWhere(...$args)
    {
        $this->addWhere($args, ['append' => 'OR']);
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
        $this->addWhere($args, ['boolean' => false, 'append' => 'OR']);
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
        $this->addWhere($args, ['joint' => 'OR']);
		return $this;
    }

    /**
     * Add a and where variable clause to the search query
     *
     * @param mixed $args,... A set (or set of sets) of condition, operator and value,
     * @return self
     */
    public function whereNotAny(...$args)
    {
        $this->addWhere($args, ['boolean' => false, 'joint' => 'OR']);
		return $this;
    }

    /**
     * Add a and orWhereAny variable clause to the search query
     *
     * @param mixed $args,... A set (or set of sets) of condition, operator and value,
     * @return self
     */
    public function orWhereAny(...$args)
    {
        $this->addWhere($args, ['append' => 'OR', 'joint' => 'OR']);
		return $this;
    }

    /**
     * Add a and where variable clause to the search query
     *
     * @param mixed $args,... A set (or set of sets) of condition, operator and value,
     * @return self
     */
    public function orWhereNotAny(...$args)
    {
        $this->addWhere($args, ['boolean' => false, 'joint' => 'OR', 'append' => 'OR']);
		return $this;
    }

    /**
     * Add a where raw clause to the search query.
     *
     * @param string $args An array of clauses
     * @param array $options An array of options
     * @return self
     */
    private function addWhereRaw($args, $options = [])
    {
        if (!is_array($args)) $args = array($args);
        if (!is_array($args[0])) $args = array($args);
        if (!isset($options['append'])) $options['append'] = 'AND';
        if (!isset($options['joint'])) $options['joint'] = 'AND';
        if (!isset($options['boolean'])) $options['boolean'] = true;
        $negation = $options['boolean'] ? '' : 'NOT'; 

        $queries = array();
        foreach ($args as $query) { 
            // If only a query is provided, set replace to false
			if (count($query) == 1) $query[1] = false;

          // Default boolean to true
			if (count($query) == 2) $query[2] = 'AND';

            // Replace the dangerous symbols
            if ($query[1]) $query[0] = preg_replace('/(^\? )|( \? )/', $replace, $query[0]);
            
            $queries[] = ['query' =>  $query[0], 'joint' => $options['joint']];
        }
        
        $boolean = $options['boolean'] ? '' : 'NOT';
		$this->addBinding('where', ['type' => 'raw', 'append' => $options['append'], 'queries' => $queries, 'negation' => $negation]);
        return $this;
    }

    /**
     * Add a where raw clause to the search query
     *
     * @param string $query The raw query,
     * @param boolean $replace Replace dangerous characters
     * @return self
     */
    public function whereRaw(...$args)
    {
        $this->addWhereRaw($args);
        return $this;
    }
  
    /**
     * Add a where not raw clause to the search query
     *
     * @param string $query The raw query,
     * @param boolean $replace Replace dangerous characters
     * @return self
     */
    public function whereNotRaw(...$args)
    {
        $this->addWhereRaw($args, ['boolean' => false]);
        return $this;
    }

    /**
     * Add a where raw clause to the search query
     *
     * @param string $query The raw query,
     * @param boolean $replace Replace dangerous characters
     * @return self
     */
    public function whereRawAny(...$args)
    {
        $this->addWhereRaw($args, ['joint' => 'OR']);
        return $this;
    }
  
    /**
     * Add a where not raw clause to the search query
     *
     * @param string $query The raw query,
     * @param boolean $replace Replace dangerous characters
     * @return self
     */
    public function whereNotRawAny(...$args)
    {
        $this->addWhereRaw($args, ['boolean' => false, 'joint' => 'OR']);
        return $this;
    }

    /**
     * Add a where raw clause to the search query
     *
     * @param string $query The raw query,
     * @param boolean $replace Replace dangerous characters
     * @return self
     */
    public function orWhereRaw(...$args)
    {
        $this->addWhereRaw($args, ['append' => 'OR']);
        return $this;
    }
  
    /**
     * Add a where not raw clause to the search query
     *
     * @param string $query The raw query,
     * @param boolean $replace Replace dangerous characters
     * @return self
     */
    public function orWhereNotRaw(...$args)
    {
        $this->addWhereRaw($args, ['boolean' => false, 'append' => 'OR']);
        return $this;
    }

    /**
     * Add a where raw clause to the search query
     *
     * @param string $query The raw query,
     * @param boolean $replace Replace dangerous characters
     * @return self
     */
    public function orWhereRawAny(...$args)
    {
        $this->addWhereRaw($args, ['joint' => 'OR', 'append' => 'OR']);
        return $this;
    }
  
    /**
     * Add a where not raw clause to the search query
     *
     * @param string $query The raw query,
     * @param boolean $replace Replace dangerous characters
     * @return self
     */
    public function orWhereNotRawAny(...$args)
    {
        $this->addWhereRaw($args, ['boolean' => false, 'joint' => 'OR', 'append' => 'OR']);
        return $this;
    }

    /**
     * Add a where clause to the search query.
     *
     * @param mixed $args. An array of caluses
     * @param string $operator The operator
     * @param array $options An array of options
     * @return self
     */
    private function addWhereOp($args, $operator, $options = []) {
        if (!is_array($args)) $args = array($args);
        if (!is_array($args[0])) $args = array($args);
        if (!isset($options['boolean'])) $options['boolean'] = true;
        if (!isset($options['append'])) $options['append'] = 'AND';
        if (!isset($options['type'])) $options['type'] = 'where';
        if (!isset($options['joint'])) $options['joint'] = 'AND';
        $negation = $options['boolean'] ? '' : 'NOT';

        $queries = array();
		foreach ($args as $query) {
         // If only a value is present
            if (count($query) == 1) {
                $query[1] = $query[0];
                $query[0] = $this->primary_key;
			}

            $queries[] = ['column' => $query[0], 'operator' => $operator, 'value' => $query[1], 'joint' => $options['joint']];
		}
        $this->addBinding('where', ['type' => $options['type'], 'append' => $options['append'], 'negation' => $negation, 'queries' => $queries]);    
		return $this;  
    }

    /**
     * Add a '<' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereLt (...$args)
    {
        $this->addWhereOp($args, '<');
		return $this; 
    }

    /**
     * Add a negative '<' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotLt (...$args)
    {
        $this->addWhereOp($args, '<', ['boolean' => false]);
		return $this; 
    }

    /**
     * Add a '<' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereLtAny (...$args)
    {
        $this->addWhereOp($args, '<', ['joint' => 'OR']);
		return $this; 
    }

    /**
     * Add a negative '<' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotLtAny (...$args)
    {
        $this->addWhereOp($args, '<', ['boolean' => false, 'joint' => 'OR']);
		return $this; 
    }

    /**
     * Add a '<' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereLt (...$args)
    {
        $this->addWhereOp($args, '<', ['append' => 'OR']);
		return $this; 
    }

    /**
     * Add a negative '<' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotLt (...$args)
    {
        $this->addWhereOp($args, '<', ['boolean' => false, 'append' => 'OR']);
		return $this; 
    }

    /**
     * Add a '<' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereLtAny (...$args)
    {
        $this->addWhereOp($args, '<', ['joint' => 'OR', 'append' => 'OR']);
		return $this; 
    }

    /**
     * Add a negative '<' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotLtAny (...$args)
    {
        $this->addWhereOp($args, '<', ['boolean' => false, 'joint' => 'OR', 'append' => 'OR']);
		return $this; 
    }

    /**
     * Add a '<=' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereLte (...$args)
    {
        $this->addWhereOp($args, '<');
		return $this; 
    }

    /**
     * Add a negative '<=' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotLte (...$args)
    {
        $this->addWhereOp($args, '<', ['boolean' => false]);
		return $this; 
    }

    /**
     * Add a '<=' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereLteAny (...$args)
    {
        $this->addWhereOp($args, '<', ['joint' => 'OR']);
		return $this; 
    }

    /**
     * Add a negative '<=' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotLteAny (...$args)
    {
        $this->addWhereOp($args, '<', ['boolean' => false, 'joint' => 'OR']);
		return $this; 
    }

    /**
     * Add a '<=' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereLte (...$args)
    {
        $this->addWhereOp($args, '<', ['append' => 'OR']);
		return $this; 
    }

    /**
     * Add a negative '<=' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotLte (...$args)
    {
        $this->addWhereOp($args, '<', ['boolean' => false, 'append' => 'OR']);
		return $this; 
    }

    /**
     * Add a '<=' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereLteAny (...$args)
    {
        $this->addWhereOp($args, '<', ['joint' => 'OR', 'append' => 'OR']);
		return $this; 
    }

    /**
     * Add a negative '<=' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotLteAny (...$args)
    {
        $this->addWhereOp($args, '<', ['boolean' => false, 'joint' => 'OR', 'append' => 'OR']);
		return $this; 
    }

    /**
     * Add a '>' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereGt (...$args)
    {
        $this->addWhereOp($args, '<');
		return $this; 
    }

    /**
     * Add a negative '>' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotGt (...$args)
    {
        $this->addWhereOp($args, '<', ['boolean' => false]);
		return $this; 
    }

    /**
     * Add a '>' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereGtAny (...$args)
    {
        $this->addWhereOp($args, '<', ['joint' => 'OR']);
		return $this; 
    }

    /**
     * Add a negative '>' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotGtAny (...$args)
    {
        $this->addWhereOp($args, '<', ['boolean' => false, 'joint' => 'OR']);
		return $this; 
    }

    /**
     * Add a '>' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereGt (...$args)
    {
        $this->addWhereOp($args, '<', ['append' => 'OR']);
		return $this; 
    }

    /**
     * Add a negative '>' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotGt (...$args)
    {
        $this->addWhereOp($args, '<', ['boolean' => false, 'append' => 'OR']);
		return $this; 
    }

    /**
     * Add a '>' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereGtAny (...$args)
    {
        $this->addWhereOp($args, '<', ['joint' => 'OR', 'append' => 'OR']);
		return $this; 
    }

    /**
     * Add a negative '>' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotGtAny (...$args)
    {
        $this->addWhereOp($args, '<', ['boolean' => false, 'joint' => 'OR', 'append' => 'OR']);
		return $this; 
    }

    /**
     * Add a '>=' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereGte (...$args)
    {
        $this->addWhereOp($args, '<');
		return $this; 
    }

    /**
     * Add a negative '>=' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotGte (...$args)
    {
        $this->addWhereOp($args, '<', ['boolean' => false]);
		return $this; 
    }

    /**
     * Add a '>=' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereGteAny (...$args)
    {
        $this->addWhereOp($args, '<', ['joint' => 'OR']);
		return $this; 
    }

    /**
     * Add a negative '>=' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotGteAny (...$args)
    {
        $this->addWhereOp($args, '<', ['boolean' => false, 'joint' => 'OR']);
		return $this; 
    }

    /**
     * Add a '>=' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereGte (...$args)
    {
        $this->addWhereOp($args, '<', ['append' => 'OR']);
		return $this; 
    }

    /**
     * Add a negative '>=' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotGte (...$args)
    {
        $this->addWhereOp($args, '<', ['boolean' => false, 'append' => 'OR']);
		return $this; 
    }

    /**
     * Add a '>=' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereGteAny (...$args)
    {
        $this->addWhereOp($args, '<', ['joint' => 'OR', 'append' => 'OR']);
		return $this; 
    }

    /**
     * Add a negative '>=' clause clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotGteAny (...$args)
    {
        $this->addWhereOp($args, '<', ['boolean' => false, 'joint' => 'OR', 'append' => 'OR']);
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
        $this->addWhereOp($args, 'IN', ['type' => 'in', 'boolean' => false]);
        return $this;
    }


    /**
     * Add a `in` clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereInAny(...$args)
    {
        $this->addWhereOp($args, 'IN', ['type' => 'in', 'joint' => 'OR']);
		return $this;
    }

    /**
     * Add a `not in` clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotInAny(...$args)
    {
        $this->addWhereOp($args, 'IN', ['type' => 'in', 'boolean' => false, 'joint' => 'OR']);
        return $this;
    }

    /**
     * Add a `in` clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereIn(...$args)
    {
        $this->addWhereOp($args, 'IN', ['type' => 'in', 'append' => 'OR']);
		return $this;
    }

    /**
     * Add a `not in` clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotIn(...$args)
    {
        $this->addWhereOp($args, 'IN', ['type' => 'in', 'append' => 'OR', 'boolean' => false]);
        return $this;
    }

    /**
     * Add a `in` clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereInAny(...$args)
    {
        $this->addWhereOp($args, 'IN', ['type' => 'in', 'append' => 'OR', 'joint' => 'OR']);
		return $this;
    }

    /**
     * Add a `not in` clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotInAny(...$args)
    {
        $this->addWhereOp($args, 'IN', ['type' => 'in', 'append' => 'OR', 'boolean' => false, 'joint' => 'OR']);
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
        $this->addWhereOp($args, 'LIKE',  ['boolean' => false]);
		return $this; 
    }
    
    /**
     * Add a where like clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereLikeAny(...$args)
    {   
        $this->addWhereOp($args, 'LIKE', ['joint' => 'OR']);
		return $this;
    }	

    /**
     * Add a where not like clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotLikeAny(...$args)
    {
        $this->addWhereOp($args, 'LIKE',  ['boolean' => false, 'joint' => 'OR']);
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
        $this->addWhereOp($args, 'LIKE', ['append' => 'OR']);
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
        $this->addWhereOp($args, 'NOT LIKE', ['append' => 'OR', 'boolean' => false]);
		return $this; 
    }

    /**
     * Add a or where like clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereLikeAny(...$args)
    {
        $this->addWhereOp($args, 'LIKE', ['append' => 'OR', 'joint' => 'OR']);
		return $this; 
    }

    /**
     * Add a or where not like clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotLikeAny(...$args)
    {
        $this->addWhereOp($args, 'NOT LIKE', ['append' => 'OR', 'boolean' => false, 'joint' => 'OR']);
		return $this; 
    }

    /**
     * Add a search clause to the search query.
     *
     * @param mixed $args,... A set (or set of sets) of condition and value,
     * @param array $options An array of options
     * @return self
     */
    private function addSearch($args, $operation, $options = []) {
        if (!is_array($args)) $args = array($args);
        if (!is_array($args[0])) $args = array($args);
        if (!isset($options['boolean'])) $options['boolean'] = true;
        if (!isset($options['append'])) $options['append'] = 'AND';
        if (!isset($options['joint'])) $options['joint'] = 'AND';
        $negation = $options['boolean'] ? '' : 'NOT';

        $queries = array();
		foreach ($args as $query) {
            switch ($operation) {
                case 'contains':
                      $query[1] = '%'.$query[1].'%';
                      break;
                case 'containsAt':
                      $i = 1;
                      $underscores = '';
                      while($i <= $query[2]){
                        $underscores .= '_';
                        $i++;
                      }
                      $query[1] = $underscores.$query[1].'%';
                      break;
                case 'startsWith':
                      $query[1] = '%'.$query[1];
                      break;
                case 'endsWith':
                      $query[1] = $query[1].'%';
                      break;
            }

            $queries[] = ['column' => $query[0], 'value' => $query[1], 'joint' => $options['joint']];
		}  
		$this->addBinding('where', ['type' => 'like', 'append' => $options['append'], 'negation' => $negation, 'queries' => $queries]);
        return $this;  
    }

    /**
     * Add a contains (where like) clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereContains(...$args)
    {
        $this->addSearch($args, 'contains');
        return $this;
    }
    
    /**
     * Add a contains (where like) clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotContains(...$args)
    {
        $this->addSearch($args, 'contains', ['boolean' => false]);
        return $this;
    }

    /**
     * Add a contains (where like) clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereContainsAny(...$args)
    {
        $this->addSearch($args, 'contains', ['joint' => 'OR']);
        return $this;
    }

    /**
     * Add a contains (where like) clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotContainsAny(...$args)
    {
        $this->addSearch($args, 'contains', ['boolean' => false, 'joint' => 'OR']);
        return $this;
    }

    /**
     * Add a contains (where like) clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereContains(...$args)
    {
        $this->addSearch($args, 'contains', ['append' => 'OR']);
        return $this;
    }

    /**
     * Add a contains (where like) clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotContains(...$args)
    {
        $this->addSearch($args, 'contains', ['append' => 'OR', 'boolean' => false]);
        return $this;
    }

    /**
     * Add a contains (where like) clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereContainsAny(...$args)
    {
        $this->addSearch($args, 'contains', ['append' => 'OR', 'joint' => 'OR']);
        return $this;
    }

    /**
     * Add a contains (where like) clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotContainsAny(...$args)
    {
        $this->addSearch($args, 'contains', ['append' => 'OR', 'boolean' => false, 'joint' => 'OR']);
        return $this;
    }

    /**
     * Add a containsAt (where like) clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereContainsAt(...$args)
    {
        $this->addSearch($args, 'containsAt');
        return $this;
    }
    
    /**
     * Add a containsAt (where like) clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotContainsAt(...$args)
    {
        $this->addSearch($args, 'containsAt', ['boolean' => false]);
        return $this;
    }

    /**
     * Add a containsAt (where like) clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereContainsAtAny(...$args)
    {
        $this->addSearch($args, 'containsAt', ['joint' => 'OR']);
        return $this;
    }

    /**
     * Add a containsAt (where like) clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotContainsAtAny(...$args)
    {
        $this->addSearch($args, 'containsAt', ['boolean' => false, 'joint' => 'OR']);
        return $this;
    }

    /**
     * Add a containsAt (where like) clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereContainsAt(...$args)
    {
        $this->addSearch($args, 'containsAt', ['append' => 'OR']);
        return $this;
    }

    /**
     * Add a containsAt (where like) clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotContainsAt(...$args)
    {
        $this->addSearch($args, 'containsAt', ['append' => 'OR', 'boolean' => false]);
        return $this;
    }

    /**
     * Add a containsAt (where like) clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereContainsAtAny(...$args)
    {
        $this->addSearch($args, 'containsAt', ['append' => 'OR', 'joint' => 'OR']);
        return $this;
    }

    /**
     * Add a containsAt (where like) clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotContainsAtAny(...$args)
    {
        $this->addSearch($args, 'containsAt', ['append' => 'OR', 'boolean' => false, 'joint' => 'OR']);
        return $this;
    }

    /**
     * Add a startsWith clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereStartsWith(...$args)
    {
        $this->addSearch($args, 'startsWith');
        return $this;
    }
    
    /**
     * Add a startsWith clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotStartsWith(...$args)
    {
        $this->addSearch($args, 'startsWith', ['boolean' => false]);
        return $this;
    }

    /**
     * Add a startsWith clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereStartsWithAny(...$args)
    {
        $this->addSearch($args, 'startsWith', ['joint' => 'OR']);
        return $this;
    }

    /**
     * Add a startsWith clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotStartsWithAny(...$args)
    {
        $this->addSearch($args, 'startsWith', ['boolean' => false, 'joint' => 'OR']);
        return $this;
    }
    
    /**
     * Add a startsWith clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereStartsWith(...$args)
    {
        $this->addSearch($args, 'startsWith', ['append' => 'OR']);
        return $this;
    }

    /**
     * Add a startsWith clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotStartsWith(...$args)
    {
        $this->addSearch($args, 'startsWith', ['append' => 'OR', 'boolean' => false]);
        return $this;
    }
    
    /**
     * Add a startsWith clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereStartsWithAny(...$args)
    {
        $this->addSearch($args, 'startsWith', ['append' => 'OR', 'joint' => 'OR']);
        return $this;
    }
    
    /**
     * Add a startsWith clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotStartsWithAny(...$args)
    {
        $this->addSearch($args, 'startsWith', ['append' => 'OR', 'boolean' => false, 'joint' => 'OR']);
        return $this;
    }

    /**
     * Add a endsWith clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereEndsWith(...$args)
    {
        $this->addSearch($args, 'endsWith');
        return $this;
    }

    /**
     * Add a endsWith clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotEndsWith(...$args)
    {
        $this->addSearch($args, 'endsWith', ['boolean' => false]);
        return $this;
    }

    /**
     * Add a endsWith clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereEndsWithAny(...$args)
    {
        $this->addSearch($args, 'endsWith', ['joint' => 'OR']);
        return $this;
    }

    /**
     * Add a endsWith clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function whereNotEndsWithAny(...$args)
    {
        $this->addSearch($args, 'endsWith', ['boolean' => false, 'joint' => 'OR']);
        return $this;
    }

    /**
     * Add a endsWith clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereEndsWith(...$args)
    {
        $this->addSearch($args, 'endsWith', ['append' => 'OR']);
        return $this;
    }

    /**
     * Add a endsWith clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotEndsWith(...$args)
    {
        $this->addSearch($args, 'endsWith', ['append' => 'OR', 'boolean' => false]);
        return $this;
    }

    /**
     * Add a endsWith clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereEndsWithAny(...$args)
    {
        $this->addSearch($args, 'endsWith', ['append' => 'OR', 'joint' => 'OR']);
        return $this;
    }

    /**
     * Add a endsWith clause
     *
     * @param mixed $args,... A set (or set of sets) of column and value,
     * @return self
     */
    public function orWhereNotEndsWithAny(...$args)
    {
        $this->addSearch($args, 'endsWith', ['append' => 'OR', 'boolean' => false, 'joint' => 'OR']);
        return $this;
    }

    /**
     * Runs the same query as find, but with no limit and don't retrieve the
     * results, just the total items found.
     *
     * @return integer
     */
    public function count()
    {
        global $wpdb;
        return (int) $wpdb->get_var($this->composeQuery(true));
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
    public function get()
    {
        global $wpdb;
        return $wpdb->get_results($this->composeQuery(false));
    }
    
    
    /**
     * Compose the actual SQL query from all of our filters and options.
     *
     * @param  boolean $only_count Whether to only return the row count
     * @return string
     */
    public function composeQuery($only_count = false)
    {
        $table  = $this->table;
        
        $countOpen = 0;
        $countClose = 0;
        
        $where  = '';
        $order  = '';
        $limit  = '';
        $skip = '';
        // Search
        if (!empty($this->search_term)) {
            $where .= ' AND (';
            foreach ($this->searchFields as $field) {
                $where .= '`' . $field . '` LIKE "%' . esc_sql($this->search_term) . '%" OR ';
            }
            $where = substr($where, 0, -4) . ')';
        }

        // Where clauses
        $initial = true;
        foreach ($this->bindings['where'] as $wherex) {
            if ($initial) {
                $wherex['append'] = '';
                $initial = false;
            }
			switch ($wherex['type']) {
				case 'where':
                    $where .= ' '.esc_sql(strtoupper($wherex['append'])).' '. $wherex['negation'] .' ';
                    if (count($wherex['queries']) > 1) $where .= '( ';
                    $queryInitial = true;
                    foreach ($wherex['queries'] as $query) {
                        if ($queryInitial) {
                            $query['joint'] = '';
                            $queryInitial = false;
                        }
                        if(!is_array($query['value'])) {
                            $where .= ' '.esc_sql(strtoupper($query['joint'])).' `' . $query['column'] . '` '.esc_sql($query['operator']).' "' . esc_sql($query['value']) . '" ';
                        }
                        else {
                            $where .= ' '.esc_sql( strtoupper($query['joint'])) . ' (';
                            $associative = array_keys((array)$query['value']) !== range(0, count((array)$query['value']) - 1) ? true : false;
                            $count = 0;
                            foreach ($query['value'] as $key => $value) {
                                // If assocaitive, use the arr keys as the columns to check
                                $column = $associative ? $key : $query['column'];
                                if($count) $where .= ' OR ';
                                $where .= ' `' . $column . '` '.esc_sql($query['operator']).' "' . esc_sql($value) . '"';
                                $count++;
                            }
                            $where .= ' )';
                        }
                    }
                    if (count($wherex['queries']) > 1) $where .= ' )';
					break;
				case 'raw':
                    $where .= ' '.esc_sql(strtoupper($wherex['append'])).' '. $wherex['negation'] .' ';
                    if (count($wherex['queries']) > 1) $where .= '( ';
                    $queryInitial = true;
                    foreach ($wherex['queries'] as $query) {
                        if ($queryInitial) {
                            $query['joint'] = '';
                            $queryInitial = false;
                        }
                        $where .= ' '.esc_sql(strtoupper($query['joint'])).' ' . $query['query'] . ' ';
                    }
                    if (count($wherex['queries']) > 1) $where .= ' )';
					break;
				case 'in':
                    $where .= ' '.esc_sql(strtoupper($wherex['append'])).' '. $wherex['negation'] .' ';
                    if (count($wherex['queries']) > 1) $where .= '( ';
                    $queryInitial = true;
                    foreach ($wherex['queries'] as $query) {
                        if ($queryInitial) {
                            $query['joint'] = '';
                            $queryInitial = false;
                        }
                        $where .= ' '.esc_sql( strtoupper($query['joint'])) . ' `' . $query['column'] . '` '.esc_sql($query['operator']).' (';
                        $count = 0;
                        foreach ((array)$query['value'] as $value) {
                            if ($count) $where .= ',';
                            $where .= '"' . esc_sql($value) . '"';
                            $count++;
                        }
                        $where .= ')';
                    }
                    if (count($wherex['queries']) > 1) $where .= ' )';
					break;
				case 'open':
                    $countOpen++;
					$where .= ' '.strtoupper($wherex['append']).' (';
					$initial = true;
                    break;
				case 'close':
                    $countClose++;
					$where .= ')';
                    break;	
				default:
					break;				
			}
        }
        
        if ($countOpen > $countClose) {
            while ($countOpen > $countClose) {
                $where .= ')';
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
 
        $select = "";
        $i = 0;
        if (count($this->columns)) {
            foreach ($this->columns as $key => $column) {
                $select .= $column;
                if (!is_integer($key)) $select .= ' as ' . $key;
                $select .= !$i && count($this->columns) > 1 ? ', ' : ' ';
                $i++;
            }
        } else {
            $select .= ' * ';
        }

        // print_r(apply_filters('wporm_count_query', "SELECT {$select} FROM {$this->from} {$where}{$order}{$limit}{$skip}", $this->table));

        if ($only_count) {
            return apply_filters('wporm_count_query', "SELECT COUNT(*) FROM {$this->from} {$where}", $this->table);
        }

        return apply_filters('wporm_query', "SELECT {$select} FROM {$this->from} {$where}{$order}{$limit}{$skip}", $this->table);
    }
}