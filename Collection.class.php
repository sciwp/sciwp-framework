<?php
namespace Wormvc\Wormvc;

defined('ABSPATH') OR exit('No direct script access allowed');

/**
 * Collection class
 *
 * @author		Eduardo Lazaro Rodriguez <eduzroco@gmail.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */
class Collection implements Interfaces\CollectionInterface
{
    use \Wormvc\Wormvc;

	/** @var array $elements Stores a list of the registered elements */	
	private $elements = array();
	
	/**
	 * Get all the elements
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function all()
	{
		return $this->elements;
	}
	
	/**
	 * Get an element
	 *
	 * @param string $id
	 * @since 1.0.0
	 * @return object|static
	 */
	public function get($element_id)
	{
		return isset($this->elements[$element_id]) ? $this->elements[$element_id] : false;
	}	

	/**
	 * Add an element
	 *
	 * @param string|array $element_id
	 * @param mixed $element
	 * @since 1.0.0
	 * @return $this
	 */		
	public function add($element_id, $element = null)
	{
		if (is_array($element_id)) {
			foreach ($element_id as $key => $elem) {
                $this->elements[$key] = $elem;
			}
		} else {
            $this->elements[$element_id] = $element;
		}
		return $this;
	}

	/**
	 * Remove an element
	 *
	 * @param string|array $element_id
	 * @return $this
	 */
 	public function remove($element_id)
	{
		if (is_array($element_id)) {
			foreach ($element_id as $elem_id) {
                unset($this->elements[$elem_id]);
			}
		} else {
            unset($this->elements[$element_id]);
        }
        return $this;
	}
}