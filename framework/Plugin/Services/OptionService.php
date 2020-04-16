<?php
namespace Sci\Plugin\Services;

defined('ABSPATH') OR exit('No direct script access allowed');

use Sci\Plugin\Plugin;

/**
 * Options
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class OptionService
{
	/** @var Plugin $plugin The plugin this service belongs to */
	private $plugin;

	/** @var string $key The plugin key */
	private $key;

	/**
	 * Class constructor
	 */
	public function __construct($key, Plugin $plugin)
	{
		$this->key = $key;
		$this->plugin = $plugin;
	}

	/**
	 * Returns an option in the array of options stored as a single WP option
	 *
	 * @param array|string $option
	 * @param boolean $default
	 * @return mixed
	 */
	public function get( $option, $default = false )
	{
		if ( is_array($option) && count($option) > 1 ) {
			$name = $option[0];
			$fieldContent = get_option( $this->key . '_' . $option[1] );
		} else {
			$name = $option;
			$fieldContent = get_option( $this->key );
		}
		if ( false === $fieldContent ) return $default;
		return isset($fieldContent[$name]) ? $fieldContent[$name] : $default;
	}

	/**
	 * Updates an element in the array of options used for this plugin and
	 * stored as a WP unique option The option name is the Plugin ID
	 *
	 * @param array|string $option
	 * @param mixed $value
	 * @return mixed
	 */		
	public function set( $option, $value )
	{
		if ( is_array($option) && count($option) > 1 ) {
			$name = $option[0];
			$option = $this->key . '_' . $option[1];
			
		} else {
			$name = $option;
			$option = $this->key;
		}
		$option_val = get_option( $option );
		$option_val = ( false === $option_val ) ? array() : (array) $option_val;
		$option_val = array_merge( $option_val, array( $name => $value ) );
		return update_option( $option, $option_val );
	}
	
	/**
	 * Deletes an option in the array of options stored as a single WP option
	 * 
	 * @param array $option
	 * @return boolean
	 */
	public function remove($option)
	{
		if ( is_array($option) && count($option) > 1 ) {
			$option = $option[0];
			$fieldName = $this->key . '_' . $option[1];
		} else {
			$fieldName = $this->key;
		}

		$fieldContents = get_option($fieldName);
		if (false === $fieldContents) return false;
		
		if (isset($fieldContents[$option])) {
			unset($fieldContents[$option]);

			print_r($fieldContents);
			update_option( $fieldName, $fieldContents );
			return true;
		}
		return false;
	}

	/**
	 * Returns the array of options stored as a WP option
	 * 
	 * @param string $name
	 * @return array
	 */
	public function getSet( $name = null )
	{
		if ( is_numeric($name) || (is_string($name) && strlen($name)) ) {
			return get_option( $this->key . '_' . $name );
		} else if( $name === null || $name == true ) {
			return get_option( $this->key );
		}
		return false;
	}

	/**
	 * Updates an the array of options stored as a WP option
	 *
	 * @param string $name
	 * @param array $data
	 * @return mixed
	 */		
	public function storeSet( $name = null, $data )
	{
		if ( is_numeric($name) || (is_string($name) && strlen($name)) ) {
			return update_option( $this->key . '_' . $name, $data );
		}
		else if( $name === null || $name == true ) {
			return update_option( $this->key, $data );
		}
		return false;
	}
	
	/**
	 * Deletes an the array of options stored as a WP option
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public function removeSet( $name = null )
	{
		if ( is_numeric($name) || (is_string($name) && strlen($name)) ) {
			return delete_option( $this->key . '_' . $name );
		}
		else if( $name === null || $name == true ) {
			return delete_option( $this->key );
		}
		return false;
	}	
}