<?php
namespace Sci\Traits;

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Static class trait
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
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