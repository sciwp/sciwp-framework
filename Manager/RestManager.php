<?php
namespace MyPlugin\Sci\Manager;

defined('WPINC') OR exit('No direct script access allowed');

use \MyPlugin\Sci\Manager;
use \MyPlugin\Sci\Route;
use \MyPlugin\Sci\Helpers\Url;

/**
 * Route Manager
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.sciwp.com 
 * @since		Version 1.0.0 
 */

class RestManager extends Manager
{
    /** @var array $routes Stores a list of the registered routes */
    private $routes = array();

	/** @var boolean $filtersAadded If the WP filters have been added or not. */
	private $filtersAadded = false;

	/** @var array $segments Segments in the url */
	private $segments = array();    
	
	/** @var array $params Parameters to pass to the action function or method */
	private $params = array();
    
    /** @var array $cache Stores de cached rewrite rules */
	public $cache = array();
    
    /** @var string $dirCache Stores de cache file fir path */
	private $dirCache;
    
    /** @var string $fileCache Stores de cache file path */
	private $fileCache;
    
    /** @var boolean $rewriteRulesFlushed Stores if the rules have been flushed */
	private $rewriteRulesFlushed = false;

	/**
	 * Class constructor
	 */
	protected function __construct(){
        $this->dirCache = dirname(dirname(substr(plugin_dir_path( __FILE__ ), 0, -1))) . '/cache/';
        $this->fileCache = $this->dirCache . 'route.cache.php';
        $this->cache = is_file($this->fileCache) ? (array) include $this->fileCache : [];
    }
    
	/**
	 * Saves the route cache
	 *
	 * @return	bool
	 */
	public function saveCache()
	{
        if (!file_exists($this->dirCache)) mkdir($this->dirCache);
		file_put_contents($this->fileCache, '<?php return ' . var_export($this->cache, true) . ';')
        or die('Cannot write the file:  '.$this->fileCache);
	}

	/**
	 * Add a new route
     *
	 * @var $route
     * @var $action
	 * @return string
	 */	
	public function route($methods, $route, $action) 
	{
		$route = new Route($methods, $route, $action);
        $this->register($route);
		return $route;
	}

    /**
     * Register a new route into the route manager
     *
     * @param \MyPlugin\Sci\Route $route The route instance
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

        if (!$this->filtersAadded) {
            add_action( 'rest_api_init', [$this,'addRestRoutes'], 1);
            $this->filtersAadded = true;
        }


        return $this;
    }

    /**
     * Generate rewrite rules when fushed
     *
     * @return \MyPlugin\Sci\Manager\RouteManager
     */
	public function addRestRoutes() 
	{
        foreach($this->routes as $key => $route) {
            register_rest_route('myplugin/v2', $route->regex, array(
                'methods' => $route->getMethods(),
                'callback' => [$this, 'loadAction'],
            ));
        }        

        return $this;
	}

    /**
     * Load the action matching the requested route
     * 
     * @param string $template The WordPress template to load
     * @return mixed
     */
    public function loadAction() {

        //echo("------------------------------------------");
    }

}