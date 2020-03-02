<?php
namespace MyPlugin\Sci;

defined('WPINC') OR exit('No direct script access allowed');

use \WP_Error;
use \MyPlugin\Sci\Traits\Singleton;

/**
 * Response
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.sciwp.com 
 * @since		Version 1.0.0 
 */

class Response
{
    use Singleton;

    /** @var integer $status The response status code */
    private $status = 200;

	/** @var string $format The response format */
	private $format = 'json';

	/** @var string $content The response content */
	private $content = '';

    /**
     * Register a new route into the route manager
     *
     * @param \MyPlugin\Sci\Route $route The route instance
     * @param string $name The route name
     * @return \MyPlugin\Sci\Manager\RouteManager
     */
    public function register($route, $name = false)
    {
        if (!$name) {
            if (count($this->routes)) {
                $name = max(array_filter(array_keys($this->routes), 'is_int')) + 1;
            } else {
                $name = 1;
            }
        }

        $this->routes[$name] = $route;

        if (!$this->isActionInit) {
            add_action( 'rest_api_init', [$this,'addRestRoutes'], 1);
            $this->isActionInit = true;
        }

        return $this;
    }

    /**
     * Add routes to WordPress
     *
     * @return \MyPlugin\Sci\Manager\RestManager
     */
	public function addRestRoutes()
	{
        foreach($this->routes as $key => $route) {
            register_rest_route($route->getNamespace(), $route->regex, array(
                'methods' => $route->getMethods(),
                'callback' => [$route, 'loadAction'],
            ));
        }        

        return $this;
	}
}