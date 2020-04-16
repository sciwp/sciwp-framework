<?php
namespace Sci\Router;

defined('WPINC') OR exit('No direct script access allowed');

use Sci\Sci;

/**
 * Base controller class
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class Request
{
	/** @var array $params The array with route parameters */
	private static $params = [];
	
    /**
	 * Load error page
	 */
    public static function error($errorMessage = '404: Not Found' )
    {
		global $wp_query;

		$wp_query->set_404();
	
		add_action( 'wp_title', function () {
			return '404: Not Found';
		}, 9999 );
	
		status_header( 404 );
		nocache_headers();
	
		require get_404_template();

		exit;
	}

    /**
	 * Set parameters
	 * 
	 * @param array $params Array with parameters
	 * @return Request
	 */
    public static function setParams($params)
	{
		self::$params = $params;
		return self::class;
	}

    /**
	 * Get parameters
	 * 
	 * @return array
	 */
    public static function params()
	{
		return self::$params;
	}

    /**
	 * Get parameter
	 * 
	 * @var string $param The param name
	 * @var mixed $default The default value to return of the param is not found
	 * @return mixed
	 */
    public static function param($param, $default = null)
    {
		if (isset(self::$params[$param])) {
			return $param;
		} else {
			return $default;
		}
	}
}