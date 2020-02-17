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
            add_action( 'init', array($this,'addRewriteRulesAction'), 1);
            add_filter( 'query_vars', function( $query_vars ) {
                $query_vars[] = 'sci';
                return $query_vars;
            }); 
            add_action( 'template_include', array($this,'loadControllerAction'), 10);
            $this->filtersAadded = true;
        }
        //echo($this->cache[$route->regex]."<br/>");
        if (!isset($this->cache[$route->regex]) || $this->cache[$route->regex] !== $name) {

            if (!$this->rewriteRulesFlushed) {
                add_action( 'init', array($this,'flushRewriteRules'), 1);
                $this->rewriteRulesFlushed = true;
            }
            
        }
        return $this;
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
     * Load the action matching the requested route
     * 
     * @param string $template The WordPress template to load
     * @return mixed
     */
    public function loadControllerAction($template)
    {
        $sciVar = get_query_var( 'sci');

        if (!$sciVar) return $template;

        print_r( $this->routes[$sciVar]->getMethods());
        echo("<br/>" .  "<br/>");


        if (isset($this->routes[$sciVar]) && in_array($_SERVER['REQUEST_METHOD'], $this->routes[$sciVar]->getMethods())) {
                
            global $wp;
            $route = $this->routes[$sciVar];
            $action = $route->getAction();

            preg_match_all('/'.$route->getRegex().'/', $wp->request, $matches);

            if (is_array($matches) && isset($matches[1])) {
                $count = 0;
                $paramNamesArr = array_keys($route->getParams());
                echo("<br/>");
                foreach($matches as $key => $match) {
                     if ($key > 0) {
                        $this->params[$paramNamesArr[$count]] = $match[0];
                        $count++;
                    }
                }
            }

            $request_is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
            if ($route->isAjax() !== $request_is_ajax) {
                return $template;
            }

            $includeLayout = $route->getContent() != 'json' && $route->getLayout() && !$request_is_ajax;
            if ($includeLayout) {
                wp_head();
                get_header();
            }
            //&& !strpos($action, '::')
            if (is_string($action) && strpos($action, ".") && file_exists($action)) {
                include ($action);
            } else if (is_callable( $action )){
                $f = new \ReflectionFunction($action);
                $params = array();
                foreach ($f->getParameters() as $param) {
                    if (array_key_exists($param->name, $this->params)) {
                        $params[$param->name] = $this->params[$param->name];
                    }
                }

                return call_user_func_array($action, $params);
            }                      
            else if (!empty($this->params)) {
                $this->sci->get($action, $this->params); 
            } else {
                $this->sci->get($action);
            }
            if ($includeLayout) get_footer(); 

        } else {
             return $template;
        }
    }
}