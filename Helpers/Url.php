<?php
namespace Wormvc\Wormvc\Helpers;

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Str Helper class
 *
 * @author		Eduardo Lazaro Rodriguez <eduzroco@gmail.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */
class Url extends Helper
{
	/**
	 * Get an array with the URL segments
	 * @string $url Any URL
	 * @return array
	 */	
	private static function segments ($url = false)
    {
        $segments = array();
        if (!$url) $url = substr(preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']), 1);
		foreach (explode('/', $url) as $key => $param) {
			$segments[$key] =  str_replace('/', '', $param);
		}
        return $segments;
	}
}