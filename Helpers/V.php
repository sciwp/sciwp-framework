<?php
namespace KNDCC\Wormvc\Helpers;

use KNDCC\Wormvc\Helper;

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Var Helper class
 *
 * @author		Eduardo Lazaro Rodriguez <eduzroco@gmail.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */
class V extends Helper
{
    /**
     * Get the content of a variable only if is set, default or empty if not.
     *
     * @param mixed $var
     * @param bool $default The default fallback value
     * @return mixed
     */	
	public static function get(&$var, $default = false)
	{
		return (isset($var) && ($var!='')) ? $var : $default;
	}

    /**
     * Echo the content of a variable only if is set.
     *
     * @param mixed
     */
	public static function e(&$var)
	{
		if (isset($var)) echo($var);
	}

    /**
     * Escape a variable or array of values.
     *
     * @param mixed $data
     * @return mixed
     */    
    public static function escape( $data ) {
        return esc_sql($data);
    }   

	/**
	 * In a select field, prints the value and selected HTML attributes
	 *
     * @param mixed $var The variable to check
     * @param mixed $value The option value
	 */
	public static function selectVar(&$var, $value = false)
	{
		if (isset($var) && $var == $value) echo('selected ');
		echo('value="'.$value.'"');
	}

 	/**
	 * Escape GET array values
	 */
	public static function escapeGet ()
	{
		foreach ($_GET as $clave => $valor) {
			$_GET[$clave] = esc_sql($_GET[$clave]);
		}
	}

 	/**
	 * Escapes POST array values
	 */
	public static function escapePost ()
	{
		foreach ($_POST as $clave => $valor) {
			$_POST[$clave] = esc_sql($_POST[$clave]);
		}
	}
}