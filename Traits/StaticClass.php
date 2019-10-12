<?php
namespace KNDCC\Wormvc\Traits;

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Trait StaticClass
 *
 * @author		Eduardo Lazaro Rodriguez <eduzroco@gmail.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */
trait StaticClass
{
	/**
	 *  Class constructor
	 */
    final protected function __construct(){}

	/**
	 * Clone
	 */	
    final private function __clone(){}

	/**
	 * Wakeup
	 */			
	final protected function __wakeup(){}
}