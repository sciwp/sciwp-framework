<?php
namespace Sci\Sci\Interfaces;

/**
 * Collection interface
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */

defined('ABSPATH') OR exit('No direct script access allowed');

interface CollectionInterface
{	
	/**
	 * Get all the elements
	 *
	 * @since 1.0.0
	 * @return array
	 */	
	public function all();

	/**
	 * Get an element
	 *
	 * @param string $id
	 * @since 1.0.0
	 * @return object|static
	 */	
	public function get($id);
	
	/**
	 * Add an element
	 *
	 * @param string|array $element_id
	 * @param mixed $element
	 * @since 1.0.0
	 * @return $this
	 */
	public function add($element_id, $element);
    
	/**
	 * Remove an element
	 *
	 * @param string|array $element_id
	 * @return $this
	 */
    public function remove($element_id);  
}