<?php
namespace Sci\Traits; 

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Method Binding trait
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
trait MethodBinding
{
	/** @var \ArrayObject Contains non static methods */
	private $_methods = array();
	
	/** @var \ArrayObject Contains static methods */
	public static $_static_methods = array();
	
	/**
	 * Adds a regular method to an object
	 *
     * @param string $name The method name
     * @param callable $callable The function to add
	 */
	public function addMethod($name, $callable)
	{
		if (!is_callable($callable)) {
			throw new InvalidArgumentException('Second param must be callable');
		}
		$this->_methods[$name] = Closure::bind($callable, $this, get_class());
	}
	
	/**
	 * Adds a static method to a class
	 *
     * @param string $name The method name
     * @param callable $callable The static function to add
	 */
	public static function addStaticMethod($name, $callable)
	{
		if (!is_callable($callable)) {
			throw new InvalidArgumentException('Second param must be callable');
		}
		self::$_static_methods[$name] = \Closure::bind($callable, NULL, __CLASS__);				
	}	

	/**
	 * Magic method used to call regular methods.
	 * 
     * @param string $name The method name
     * @param array $args The method args
	 */
	final public function __call($methodName, array $args)
	{
		if ( isset($this->methods[$methodName]) ) {
			return call_user_func_array($this->methods[$methodName], $args);
		}
		throw new \RunTimeException('Call to undefined instance method: '.$methodName.' for the class '.get_class($this));
	}

	/**
	 * Magic method used to call static methods.
	 *
     * @param string $name The method name
     * @param array $args The method args     
	 */
   final public static function __callStatic($name, array $args)
   {
		if (isset(self::$_static_methods[$name])) {
			return call_user_func(self::$_static_methods[$name], $args);
		}
		throw new \RunTimeException('Call to undefined static method: '.$name.' for the class'.static::__CLASS__ );
   }
}