<?php
namespace Sci\Sci;

defined('ABSPATH') OR exit('No direct script access allowed');

/**
 * Collection class
 *
 * @author		Eduardo Lazaro Rodriguez <eduzroco@gmail.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */
class Collection implements Interfaces\CollectionInterface
{
    use \Sci\Sci\Traits\Sci;

	/** @var array $items Stores a list of the registered items */	
	private $items = [];

    /**
	 * Add a new collection
	 *
     * @param mixed $items
	 * @return \Sci\Sci\Collection
	 */
    public static function create($items = [])
    {
        return new self((array) $items);
    }

    /**
     * Create a new collection
     *
     * @param mixed $items
     * @return void
     */
    public function __construct($items = [])
    {
        $this->items = (array) $items;
    }

    /**
     * Determine if an item exists in the collection
     *
     * @param  mixed  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return bool
     */
    public function contains($value, $operator = null, $comparedValue = null)
    {
        if (func_num_args() === 1) return in_array($value, $this->items);

        $key = array_search($value, $this->items);
        if ($key === false) return false;


        $this->contains($this->operatorForWhere(...func_get_args()));
    }


	/**
	 * Get all the items
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function all()
	{
		return $this->items;
	}
	
	/**
	 * Get an element
	 *
	 * @param string $id
	 * @since 1.0.0
	 * @return object|static
	 */
	public function get($key)
	{
		return isset($this->items[$key]) ? $this->items[$key] : false;
	}	

	/**
	 * Add an item
	 *
	 * @param string|array $itemId
	 * @param mixed $item
	 * @since 1.0.0
	 * @return $this
	 */		
	public function add($key, $item = null)
	{
		if (is_array($key)) {
			foreach ($key as $key => $item) {
                $this->item[$key] = $item;
			}
		} else {
            $this->item[$key] = $item;
		}
		return $this;
	}

	/**
	 * Remove an item
	 *
	 * @param string|array $element_id
	 * @return $this
	 */
 	public function remove($key)
	{
		if (is_array($key)) {
			foreach ($k as $key) {
                unset($this->item[$k]);
			}
		} else {
            unset($this->item[$key]);
        }
        return $this;
	}
}