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

    /** @const string The name of the "created at" column */
    const CREATED_AT = 'created_at';

    /** @const string The name of the "updated at" column */
    const UPDATED_AT = 'updated_at';

    /** @var string The table associated with the model */
    const TABLE_NAME = false;

    /** @var list of model attributes */
    protected $attributes = [];

    /**
     * Return configured table prefix
     *
     * @return string
     */
    public static function tablePrefix()
    {
        global $wpdb;
        return $wpdb->prefix;
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
            return static::tablePrefix().Str::toPlural(strtolower(substr(strrchr(get_class($this), "\\"), 1)));
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
    public static function create($attributes)
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
     * Set a property via the mafic get method
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
     * Magically handle getters and setters.
     *
     * @param  string $function
     * @param  array  $arguments
     * @return mixed
     */
    public function __call($function, $arguments)
    {
        // Getters following the pattern 'get_{$property}'
        if (substr($function, 0, 4) == 'get_') {
            $model_props = get_object_vars($this);
            $property    = lcfirst(substr($function, 4));
            if (array_key_exists($property, $model_props)) {
                return $this->{$property};
            }
        }

        // Setters following the pattern 'set_{$property}'
        if (substr($function, 0, 4) == 'set_') {
            $model_props = get_object_vars($this);
            $property    = lcfirst(substr($function, 4));

            if (array_key_exists($property, $model_props)) { 
                $this->{$property} = $arguments[0];
            }
        }
    }

    /**
     * Convert complex objects to strings to insert into the database
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
                $props[$property] = $value->primaryKey();
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

        // Flatten complex objects
        $attributes = $this->flattenProps($this->attributes);

        // Insert or update?
        if (!array_key_exists(static::primaryKey(), $attributes)) {            
            $wpdb->insert($this->table(), $attributes);
            $this->{static::primaryKey()} = $wpdb->insert_id;
        } else {
            $wpdb->update(static::table(), $attributes, array(static::primaryKey() => $attributes[static::primaryKey()]));
        }
        return $this;
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
     * Find a specific model by a given property value.
     *
     * @param  string $property
     * @param  string $value
     * @return false|self
     */
    public static function findOneBy($property, $value)
    {
        global $wpdb;
        // Escape the value
        $value = esc_sql($value);
        // Get the table name
        $table = static::table();
        // Get the item
        $obj = $wpdb->get_row("SELECT * FROM `{$table}` WHERE `{$property}` = '{$value}'", ARRAY_A);
        // Return false if no item was found, or a new model
        return ($obj ? static::create($obj) : false);
    }

    /**
     * Find a model by ID
     *
     * @param  integer $id
     * @return false|self
     */
    public static function findOne($id)
    {
        return static::findOneBy(static::primaryKey(), (int) $id);
    }

    /**
     * Start a query to find models matching specific criteria.
     *
     * @return Query
     */
    public static function query()
    {
        $query = new Query(get_called_class());
        $query->setSearchableFields(static::getSearchableFields());
        $query->setPrimaryKey(static::primaryKey());
        return $query;
    }

    /**
     * Return all database records for this model
     *
     * @return array
     */
    public static function findAll()
    {
        global $wpdb;
        // Get the table name
        $table = static::table();
        // Get the items
        $results = $wpdb->get_results("SELECT * FROM `{$table}`");
        foreach ($results as $index => $result) {
            $results[$index] = static::create((array) $result);
        }
        return $results;
    } 
}