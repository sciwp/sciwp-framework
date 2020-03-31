<?php
namespace MyPlugin\Sci;

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
    use \MyPlugin\Sci\Traits\Sci;

	/** @var array $items Stores a list of the registered items */	
	private $items = [];

    /**
	 * Add a new collection
	 *
     * @param mixed $items
	 * @return \MyPlugin\Sci\Collection
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
     * @param  mixed  $value
     * @return bool
     */
    public function contains($value)
    {
        if (func_num_args() === 1) return in_array($value, $this->items);

        $key = array_search($value, $this->items);
        if ($key === false) return false;
        return true;
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
	 * @param string|number|null $key
	 * @since 1.0.0
	 * @return mixed
	 */
	public function get($key =  null)
	{
		if ($key == null) return $this->all();

		if (isset($this->items[$key])) return $this->items[$key];

		if (strpos($key, '/') === false) return false;

		$itemsArr = $this->items;
		$pathArr = explode("/",trim($key, '/'));

		foreach ($pathArr as $subKey) {
			if (!isset($itemsArr[$subKey])) {
				return false;
			} else {
				$itemsArr = $itemsArr[$subKey];
			}
		}

		return $itemsArr;
	}

	/**
	 * Check an element
	 *
	 * @param string $key
	 * @param string $value
	 * @since 1.0.0
	 * @return object|static
	 */
	public function check($key, $value)
	{
		if (isset($this->items[$key]) && $this->items[$key] === $value) return true;
		return false;
	}

	/**
	 * Get the length of an element
	 *
	 * @param string $key
	 * @param integer $length
	 * @since 1.0.0
	 * @return integer|boolean
	 */
	public function length($key, $length = null)
	{
		if (!isset($this->items[$key])) return false;

		if (is_string($this->items[$key])) {
			if($length === null) return strlen($this->items[$key]);
			else return strlen($this->items[$key]) === $length;
		}

		if (is_array($this->items[$key])) {
			if($length === null)  return count($this->items[$key]);
			else return count($this->items[$key]) === $length;
		}

		return false;
	}

	/**
	 * Set a value for an element
	 *
	 * @param string $key
	 * @param string $value
	 * @since 1.0.0
	 * @return $this
	 */
	public function set($key, $value)
	{
		$this->items[$key]  = $value;
		return $this;
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
			$this->items = $key + $this->items;
		} else {
            $this->items[$key] = $item;
		}

		return $this;
	}

	/**
	 * Remove an item
	 *
	 * @param string|array $key
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