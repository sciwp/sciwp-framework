<?php
namespace MyPlugin\Sci;

defined('WPINC') OR exit('No direct script access allowed');

use MyPlugin\Sci\Helpers\Str;
use MyPlugin\Sci\Query;

/**
 * Class Model
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */
abstract class Model
{    
    /** @const string The primary key for the model */
    const PRIMARY_KEY = 'id';

    /** @const bool Indicates if the IDs are auto-incrementing */
    const AUTO_INCREMENT = true;    

    /** @var boolean Enable created, deleted and updated date fields */
    const TIMESTAMPS  = false;

    /** @const string The name of the "created at" column */
    const CREATED_AT = 'created_at';

    /** @const string The name of the "updated at" column */
    const UPDATED_AT = 'updated_at';
    
        /** @const string The name of the "deleted at" column */
    const DELETED_AT = 'deleted_at';

    /** @var boolean The table associated with the model */
    const TABLE_NAME = false;
    
    /** @var boolean The table associated with the model */
    const TABLE_PREFIX = true;

    /** @var list of model attributes */
    protected $attributes = [];

    /**
     * Return configured table prefix
     *
     * @return string
     */
    public static function tablePrefix()
    {
        if (static::TABLE_PREFIX) {
            global $wpdb;
            return $wpdb->prefix;
        } else {
            return '';
        }
    }

    /**
     * Returns the table name
     *
     * @return string
     */
    public static function table()
    {
        if (static::TABLE_NAME) {
            return static::tablePrefix().static::TABLE_NAME;
        } else {
            return static::tablePrefix().Str::toPlural(strtolower(substr(strrchr(get_called_class(), "\\"), 1)));
        }
    }

    /**
     * Get the column used as the primary key, defaults to 'id'
     *
     * @return string
     */
    public static function primaryKey()
    {
        return static::PRIMARY_KEY;
    }

    /**
     * Create a new object from the given data
     *
     * @return self
     */
    public static function create($attributes = [])
    {
        return new static($attributes);
    }

    /**
     * Constructor
     *
     * @param array $properties
     */
    public function __construct(array $attributes = array())
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = maybe_unserialize($value);
        }
    }
 
    /**
     * Get a property via the mafic get method
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->attributes[$key])) return $this->attributes[$key];
        else return null;
    }

    /**
     * Set a property via the magic get method
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Magically handle getters and setters for the class properties
     *
     * @param  string $function
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($function, $arguments)
    {
        // Getters following the pattern 'get_{$property}'
        if (substr($function, 0, 3) == 'get') {
            $model_props = get_object_vars($this);
            $property    = lcfirst(substr($function, 3));

            if (array_key_exists($property, $model_props)) {
                return $this->{$property};
            }
        }

        // Setters following the pattern 'set_{$property}'
        if (substr($function, 0, 3) == 'set') {
            $model_props = get_object_vars($this);
            $property    = lcfirst(substr($function, 3));

            if (array_key_exists($property, $model_props)) { 
                $this->{$property} = $arguments[0];
            }
        }
    }

    /**
     * flattenProps
     *
     * @param  array $props
     * @return array
     */
    public function flattenProps($props)
    {
        foreach ($props as $property => $value) {
            if (is_object($value) && get_class($value) == 'DateTime') {
                $props[$property] = $value->format('Y-m-d H:i:s');
            } else if (is_array($value)) {
                $props[$property] = serialize($value);
            } else if ($value instanceof AbstractClass) {
                $props[$property] = $value->{$value->primaryKey()}[0];
            }
        }
        return $props;
    }

    /**
     * Save the model into the database creating or updating a record
     *
     * @return self
     */
    public function save()
    {
        global $wpdb;

        $attributes = $this->attributes;
        $isNewRecord = !array_key_exists(static::primaryKey(), $attributes);

        // Convert complex objects to strings to insert into the database
        foreach ($attributes as $key => $value) {
            if (is_object($value) && get_class($value) == 'DateTime') {
                $attributes[$key] = $value->format('Y-m-d H:i:s');
            } else if (is_array($value)) {
                $attributes[$key] = serialize($value);
            } else if ($value instanceof AbstractClass) {
                $attributes[$key] = $value->{$value->primaryKey()}[0];
            }
        }

        if (static::TIMESTAMPS) {
            $attributes[static::UPDATED_AT] = date("Y-m-d h:i:s");
            if ($isNewRecord) $attributes[static::CREATED_AT] = $attributes[static::UPDATED_AT];
        }

        if ($isNewRecord) {
            if (!static::AUTO_INCREMENT) {
                $attributes[static::primaryKey()] = uniqid();
            }
            $wpdb->insert($this->table(), $attributes);
            $this->{static::primaryKey()} = $wpdb->insert_id;
        } else {
            $wpdb->update(static::table(), $attributes, [static::primaryKey() => $attributes[static::primaryKey()]]);
        }
        
        if (static::TIMESTAMPS) {
            $this->attributes[static::UPDATED_AT] =  $attributes[static::UPDATED_AT];
            if ($isNewRecord) $this->attributes[static::CREATED_AT] = $attributes[static::CREATED_AT];
        }

        return $this;
    }

    /**
     * Return all database records for this model
     *
     * @param int skip
     * @param int limit
     * @return array
     */
    public static function findAll($skip = false, $limit = false)
    {
        $query = new Query();
        $query->setModel(get_called_class());
        if ($skip) $query->skip($skip);
        if ($limit) $query->limit($limit);
        
        $models = [];
        $results = $query->get();
        if ($results) {
            foreach ($results as $index => $result) {
                $models[$index] = static::create((array) $result);
            }
        }
        return $models;
    }

    /**
     * Find an array if models by ID or array of IDs
     *
     * @param  integer|array $id
     * @return array
     */
    public static function find(...$queries)
    {
        $queries = func_get_args();
        $hasArray = false;
        $skip = false;
        $limit = false;

        foreach($queries as $query) {
           if (is_array($query)) $hasArray = true;
        }

        if ($hasArray && is_int($queries[count($queries) - 1])) {
            $skip = array_pop($queries);
            if (is_int($queries[count($queries) - 1])) {
                $limit = $skip;
                $skip = array_pop($queries);
            }
        }

        $query = new Query();
        $query->setModel(get_called_class());

        call_user_func_array([$query, 'where'], $queries);

        if ($skip) $query->skip($skip);
        if ($limit) $query->limit($limit);

        $models = [];   
        $results = $query->get();
        if ($results) {
            foreach ($results as $index => $result) {
                $models[$index] = static::create((array) $result);
            }
        }
        return $models;  
    }

    /**
     * Find a specific model by a given property value.
     *
     * @param  string $property
     * @param  string $value
     * @return false|self
     */
    public static function findOne(...$queries)
    {
        $queries = func_get_args();
        $query = new Query();
        $query->setModel(get_called_class());

        call_user_func_array([$query, 'where'], $queries);

        $query->skip(0)->limit(1);

        $results = $query->get();
        if (count($results)) {
            return static::create((array) $results[0]);
        }
        return false;
    }

    /**
     * Delete the model from the database
     *
     * @return boolean
     */
    public function delete()
    {
        global $wpdb;
        return $wpdb->delete(static::table(), array(static::primaryKey() => $this->{static::primaryKey()}));
    }

    /**
     * Soft delete the model from the database
     *
     * @return self
     */
    public function remove()
    {
        global $wpdb;
        if (static::TIMESTAMPS) {
            $wpdb->update(static::table(),[static::DELETED_AT => date("Y-m-d h:i:s")], array(static::primaryKey() => $this->attributes[static::primaryKey()]));
        }
        return $this;
    }

    /**
     * Start a query to find models matching specific criteria.
     *
     * @return Query
     */
    public static function query()
    {
        $query = new Query();
        $query->setModel(get_called_class());
        return $query;
    }
}