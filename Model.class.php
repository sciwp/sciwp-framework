<?php
namespace Wormvc\Wormvc;

defined('WPINC') OR exit('No direct script access allowed');

use Wormvc\Wormvc\Helpers\Str;

/**
 * Class Model
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
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
     * @return integer
     */
    public function save()
    {
        global $wpdb;

        $attributes = $this->attributes;

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

        // Insert or update?
        if (!array_key_exists(static::primaryKey(), $attributes)) { 
            if (!static::AUTO_INCREMENT) {
                $attributes[static::primaryKey()] = uniqid();
            }
            $wpdb->insert($this->table(), $attributes);
            $this->{static::primaryKey()} = $wpdb->insert_id;
        } else {
            $wpdb->update(static::table(), $attributes, array(static::primaryKey() => $attributes[static::primaryKey()]));
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
    public static function all($skip = false, $limit = false)
    {
        $query = new Query(get_called_class());
        if ($skip) $query->skip($skip);
        if ($limit) $query->limit($limit);
        return $query->get();        
    }

    /**
     * Find a model by ID or array of IDs
     *
     * @param  integer|array $id
     * @return array|self
     */
    public static function find(...$queries)
    {
        $query = new Query(get_called_class());
        foreach ($queries as $queryArr) {
             $query->orWhere($queryArr);

        }
        return $query->get();  
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
        $results = static::find(...$queries);
        if (!count($results)) return null;
        return ($results[0]);
    }


    /**
     * Find a specific model by a given property value.
     *
     * @param  string $field The table field
     * @param  string $value The field value
     * @return array
     */
    public static function findBy($field, $value)
    {
        global $wpdb;
        
        if (count($values) === 1) {
            // Escape the value
            $value = esc_sql($value);
            // Get the table name
            $table = static::table();
            // Get the item
            $obj = $wpdb->get_row("SELECT * FROM `{$table}` WHERE `".$field."` = ".$value, ARRAY_A);
            // Return null if no item was found, or a new model
            return ($obj ? static::create($obj) : null);
        } else {
        
        }
    }
    
    /**
     * Find a specific model by a given property value.
     *
     * @param  string $field The table field
     * @param  string $value The field value
     * @return self|null
     */
    public static function findOneBy($field, $value)
    {
        $results = static::findBy($field, $value);
        if (!count($results)) return null;
        return ($results[0]);
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
        $wpdb->update(static::table(),[${static::DELETED_AT} => date("Y-m-d h:i:s")], array(static::primaryKey() => $attributes[static::primaryKey()]));
        return $this;
    }





    /**
     * Start a query to find models matching specific criteria.
     *
     * @return Query
     */
    public static function query()
    {
        $query = new Query(get_called_class());
        //$query->setSearchableFields(static::getSearchableFields());
        $query->setPrimaryKey(static::primaryKey());
        return $query;
    }


}