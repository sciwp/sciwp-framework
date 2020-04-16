<?php
namespace Sci\Support;

defined('ABSPATH') OR exit('No direct script access allowed');

/**
 * Collection
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class Collection implements CollectionInterface
{
    use \Sci\Traits\Sci;

	/** @var array $items Stores a list of the registered items */	
	private $items = [];

    /**
	 * Add a new collection
	 *
     * @param mixed $items
	 * @return Collection
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
	 * @return object|static
	 */
	public function check($key, $value)
	{
		$element = $this->get($key);
		if ($element === $value) return true;
		return false;
	}

	/**
	 * Get the length of an element
	 *
	 * @param string $key
	 * @param integer $length
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
	 * @param string|arra $key
	 * @param mixed $item
	 * @return Collection
	 */
	public function set($key, $item = null)
	{
		if (is_array($key)) {
			foreach($key as $item => $value) {
				$this->items[$item] = $value;
			}
		} else {
            $this->items[$key] = $item;
		}

		return $this;
	}	

	/**
	 * Add an item
	 *
	 * @param string|array $itemId
	 * @param mixed $item
	 * @return Collection
	 */		
	public function add($key, $item = null)
	{
		return $this->set($key, $item);
	}

	/**
	 * Remove an item
	 *
	 * @param string|array $key
	 * @return Collection
	 */
 	public function remove($key)
	{
		if (is_array($key)) {
			foreach ($k as $key) {
                unset($this->items[$k]);
			}
		} else {
            unset($this->items[$key]);
		}

        return $this;
	}
}