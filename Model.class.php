<?php
namespace KNDCC\Wormvc;

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Class Model
 *
 * @author		Eduardo Lazaro Rodriguez <eduzroco@gmail.com>
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
    protected $table;   

    /**
     * Constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties = array())
    {
        foreach ($properties as $property => $value) {
            $this->{$property} = maybe_unserialize($value);
        }
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
        if (substr($function, 0, 3) == 'get') {
            $model_props = $this->properties();
            $property    = lcfirst(substr($function, 3));
            if (array_key_exists($property, $model_props)) {
                return $this->{$property};
            }
        }

        // Setters following the pattern 'set_{$property}'
        if (substr($function, 0, 3) == 'set') {
            $model_props = $this->properties();
            $property    = lcfirst(substr($function, 3));
            if (array_key_exists($property, $model_props)) {
                $this->{$property} = $arguments[0];
            }
        }
    }

    /**
     * Return configured table prefix
     *
     * @return string
     */
    public function tablePrefix()
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
        return static::tablePrefix().static::TABLE_NAME;
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
     * Return an array with the properties for this model
     *
     * @return array
     */
    public function properties()
    {
        return get_object_vars($this);
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
     * Create a new model from the given data
     *
     * @return self
     */
    public static function create($properties)
    {
        return new static($properties);
    }    

    /**
     * Save the model into the database creating or updating a record
     *
     * @return integer
     */
    public function save()
    {
        global $wpdb;
        // Get the model's properties
        $props = $this->properties();
        // Flatten complex objects
        $props = $this->flattenProps($props);
        // Insert or update?
        if (is_null($props[static::primaryKey()])) {
            $wpdb->insert($this->table(), $props);
            $this->{static::primaryKey()} = $wpdb->insert_id;
        } else {
            $wpdb->update(static::table(), $props, array(static::primaryKey() => $this->{static::primaryKey()}));
        }
        return $this->id;
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
    public static function all()
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