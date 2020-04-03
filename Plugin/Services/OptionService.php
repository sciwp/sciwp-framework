<?php
namespace Sci\Plugin\Services;

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
if ( ! defined( 'ABSPATH' ) ) exit;

class Options
{
	/** @var string $plugin The plugin this service belongs to*/
	private $plugin;

	/** @var string $key Options key */
	private $key;

	/**
	 * Class constructor
	 */
	public function __construct(Plugin $plugin)
	{
		$this->plugin = $plugin;
		$this->key = 	basename($plugin->getDir());
	}

	/**
	 * Returns an option in the array of options stored as a single WP option
	 *
	 * @param array $option
	 * @param boolean $default
	 * @return mixed
	 */
	public function get( $option, $default = false )
	{
		if ( is_array($option) && count($option) > 1 ) {
			$name = $option[0];
			$option_val = get_option( $this->key . '_' . $option[1] );
		} else {
			$name = $option;
			$option_val = get_option( $this->key );
		}
		if ( false === $option_val ) return $default;
		return isset($option_val[$name]) ? $option_val[$name] : $default;
	}

	/**
	 * Updates an element in the array of options used for this plugin and
	 * stored as a WP unique option The option name is the Plugin ID
	 *
	 * @param array $option
	 * @param mixed $value
	 * @return mixed
	 */		
	public function update( $option, $value )
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
	public function remove( $option )
	{
		if ( is_array($option) && count($option) > 1 ) {
			$name = $option[0];
			$option_val = get_option( $this->key . '_' . $option[1] );
		} else {
			$name = $option;
			$option_val = get_option( $this->key );
		}

		if ( false === $option_val ) return false;
		
		if (isset($option_val[$name])) {
			unset($option_val[$name]);
			update_option( $option, $option_val );
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
	 * @return mixed
	 */		
	public function updateSet( $name = null, $value )
	{
		if ( is_numeric($name) || (is_string($name) && strlen($name)) ) {
			return update_option( $this->key . '_' . $name, $value );
		}
		else if( $name === null || $name == true ) {
			return update_option( $this->key, $value );
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