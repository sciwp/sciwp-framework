<?php
namespace MyPlugin\Sci\Manager;

defined('WPINC') OR exit('No direct script access allowed');

use \MyPlugin\Sci\Manager;
use \MyPlugin\Sci\Route;

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

class RouteManager extends Manager
{
    /** @var array $routes Stores a list of the registered routes */
    private $routes = array();

	/** @var boolean $filtersAadded If the WP filters have been added or not. */
	private $filtersAadded = false;
	
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
    protected function __construct()
    {
        parent::__construct();
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
     * Register a new route into the route manager
     *
     * @param \MyPlugin\Sci\Route $route The route instance
     * @param string $name The route name
     * @return \MyPlugin\Sci\Manager\RouteManager
     */
    public function register($route, $name = false)
    {
        if (!$name) $name = $this->getRoutesNextArrKey();

        $this->routes[$name] = $route;

        if (!$this->filtersAadded) {
            add_action( 'init', array($this,'addRewriteRulesAction'), 1);
            add_filter( 'query_vars', function( $query_vars ) {
                $query_vars[] = 'sci';
                return $query_vars;
            }); 
            add_action( 'template_include', array($this,'loadRouteAction'), 10);
            $this->filtersAadded = true;
        }

        if (!isset($this->cache[$route->regex]) || $this->cache[$route->regex] !== $name) {

            if (!$this->rewriteRulesFlushed) {
                add_action( 'init', array($this,'flushRewriteRules'), 1);
                $this->rewriteRulesFlushed = true;
            }
            
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
     * Flush rewrite rules
     */
    public function flushRewriteRules() {
        $this->cache = [];
        foreach($this->routes as $key => $route) {
            $this->cache[$route->regex] = $key;
        }
        $this->saveCache();
        flush_rewrite_rules(true);
    }

    /**
     * Generate rewrite rules when fushed
     *
     * @return \MyPlugin\Sci\Manager\RouteManager
     */
	public function addRewriteRulesAction() 
	{
        add_filter( 'generate_rewrite_rules', function ( $wp_rewrite ) {
            $routes = array();

            foreach($this->routes as $key => $route) {
                $routes[$route->regex] = 'index.php?sci='.$key;
            }

            $wp_rewrite->rules = array_merge(
                $routes,
                $wp_rewrite->rules
            );
        });
        
        return $this;
    }

    /**
     * Return if the current request is async
     *
     * @return boolean
     */
	public function isAsync() 
	{
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        }
        return false;
	}


    /**
     * Load the action matching the requested route
     * 
     * @param string $template The WordPress template to load
     * @return mixed
     */
    public function loadRouteAction($template)
    {
        $sciVar = get_query_var( 'sci');

        if (!$sciVar) return $template;

        if (!isset($this->routes[$sciVar])) return $template;

        $route = $this->routes[$sciVar];

        if (!in_array($_SERVER['REQUEST_METHOD'], $route->getMethods())) return $template;     

        if ($route->getAsync() !== $this->isAsync())  return $template;

        $route->loadAction();
    }
}