<?php
namespace Sci\Router\Managers;

defined('WPINC') OR exit('No direct script access allowed');

use Sci\Manager;
use Sci\Router\Rest;

/**
 * Rest Manager
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class RestManager extends Manager
{
    /** @var array $routes Stores a list of the registered routes */
    private $routes = array();

	/** @var boolean $isActionInit If the WP actions have been added or not. */
	private $isActionInit = false;

	/**
	 * Class constructor
     *
     * @return RestManager
	 */
	protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Register a new route into the route manager
     *
     * @param Rest $route The route instance
     * @param string $name The route name
     * @return RouteManager
     */
    public function register($route, $name = false)
    {
        if (!$name) $name = $this->getRoutesNextArrKey();

        $this->routes[$name] = $route;

        if (!$this->isActionInit) {
            add_action( 'rest_api_init', [$this,'addRestRoutes'], 1);
            $this->isActionInit = true;
        }

        return $this;
    }

    /**
     * Get next array numeric key
     *
     * @return integer
     */
    public function getRoutesNextArrKey()
    {
        if (count($this->routes)) {
            $numericKeys = array_filter(array_keys($this->routes), 'is_int');
            if (count($numericKeys)) {
                return max($numericKeys) + 1;
            }
        }
        return 1;
    }

    /**
     * Add routes to WordPress
     *
     * @return RestManager
     */
	public function addRestRoutes()
	{
        foreach($this->routes as $key => $route) {
            register_rest_route($route->getNamespace(), $route->regex, array(
                'methods' => $route->getMethods(),
                'callback' => [$route, 'loadAction'],
                'args' => $route->getArgs(),
            ));
        }        

        return $this;
	}
}