<?php
namespace Sci\Support;

/**
 * Collection Interface
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
defined('ABSPATH') OR exit('No direct script access allowed');

interface CollectionInterface
{	
	/**
	 * Get all the elements
	 *
	 * @return array
	 */	
	public function all();

	/**
	 * Get an element
	 *
	 * @param string $id
	 * @return mixed
	 */	
	public function get($id);
	
	/**
	 * Add an element
	 *
	 * @param string|array $element_id
	 * @param mixed $element
	 * @return CollectionInterface
	 */
	public function add($element_id, $element);
    
	/**
	 * Remove an element
	 *
	 * @param string|array $element_id
	 * @return CollectionInterface
	 */
    public function remove($element_id);  
}