<?php
namespace KNDCC\Wormvc;

/**
 * Autoloader Class
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Options
{
	use Wormvc;

	/**
	 * Returns an option in the array of options stored as a single WP option
	 *
	 * @since 1.2.0
	 */
	public static function get( $option, $default = false )
	{
		if ( is_array($option) && count($option) > 1 ) {
			$name = $option[0];
			$option_val = get_option( self::wormvc()::$config['id'] . '_' . $option[1] );
		}
		else {
			$name = $option;
			$option_val = get_option( self::wormvc()::$config['id'] );
		}
		if ( false === $option_val ) return $default;
		return isset($option_val[$name]) ? $option_val[$name] : $default;
	}

	/**----------------------------------------------------------------------------------
	 * Updates an element in the array of options used for this plugin and
	 * stored as a WP unique option The option name is the Plugin ID
	 * ----------------------------------------------------------------------------------
	 * @since 1.2.0
	 */		
	public static function update( $option, $value )
	{
		if ( is_array($option) && count($option) > 1 ) {
			$name = $option[0];
			$option = self::wormvc()::$config['id'] . '_' . $option[1];
			
		}
		else {
			$name = $option;
			$option = self::wormvc()::$config['id'];
		}
		$option_val = get_option( $option );
		$option_val = ( false === $option_val ) ? array() : (array) $option_val;
		$option_val = array_merge( $option_val, array( $name => $value ) );
		return update_option( $option, $option_val );
	}
	
	/**------------------------------------------------------------------------------------- 
	 * Deletes an option in the array of options stored as a single WP option
	 * -------------------------------------------------------------------------------------
	 * @since 1.2.0
	 */
	public static function remove( $option )
	{
		if ( is_array($option) && count($option) > 1 ) {
			$name = $option[0];
			$option_val = get_option( self::wormvc()::$config['id'] . '_' . $option[1] );
		}
		else {
			$name = $option;
			$option_val = get_option( self::wormvc()::$config['id'] );
		}
		if ( false === $option_val ) return false;
		
		if (isset($option_val[$name])) {
			unset($option_val[$name]);
			update_option( $option, $option_val );
			return true;
		}
		return false;
	}

	/**------------------------------------------------------------------------
	 * Returns the array of options stored as a WP option
	 * ------------------------------------------------------------------------
	 * @since 1.2.0
	 */
	public static function getSet( $name = null )
	{
		if ( is_numeric($name) || (is_string($name) && strlen($name)) ) {
			return get_option( self::wormvc()::$config['id'] . '_' . $name );
		}
		else if( $name === null || $name == true ) {
			return get_option( self::wormvc()::$config['id'] );
		}
		return false;
	}

	/**----------------------------------------------------------------------------------
	 * Updates an the array of options stored as a WP option
	 * ----------------------------------------------------------------------------------
	 * @since 1.2.0
	 */		
	public static function updateSet( $name = null, $value )
	{
		if ( is_numeric($name) || (is_string($name) && strlen($name)) ) {
			return update_option( self::wormvc()::$config['id'] . '_' . $name, $value );
		}
		else if( $name === null || $name == true ) {
			return update_option( self::wormvc()::$config['id'], $value );
		}
		return false;
	}
	
	/**------------------------------------------------------------------------------------- 
	 * Deletes an the array of options stored as a WP option
	 * -------------------------------------------------------------------------------------
	 * @since 1.2.0
	 */
	public static function removeSet( $name = null )
	{
		if ( is_numeric($name) || (is_string($name) && strlen($name)) ) {
			return delete_option( self::wormvc()::$config['id'] . '_' . $name );
		}
		else if( $name === null || $name == true ) {
			return delete_option( self::wormvc()::$config['id'] );
		}
		return false;
	}	
}