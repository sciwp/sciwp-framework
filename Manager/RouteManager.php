<?php
namespace Wormvc\Wormvc\Manager;

defined('WPINC') OR exit('No direct script access allowed');

use \Wormvc\Wormvc\Traits\Singleton;
use \Wormvc\Wormvc\Manager;
use \Wormvc\Wormvc\Route;
use \Wormvc\Wormvc\Helpers\Url;

/**
 * Route Manager
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */

class RouteManager extends Manager
{
    use Singleton;

    /** @var array $routes Stores a list of the registered routes */
    private $routes = array();

	/** @var boolean $filters_added If the WP filters have been added or not. */
	private $filters_added = false;

	/** @var array $segments Segments in the url */
	private $segments = array();    
	
	/** @var array $params Parameters to pass to the action function or method */
	private $params = array();
    
    /** @var array $cache Stores de cached rewrite rules */
	public $cache = array();
    
    /** @var string $file_cache Stores de cache file path */
	private $file_cache;
    
    /** @var boolean $rewrite_rules_flushed Stores if the rules have been flushed */
	private $rewrite_rules_flushed = false;

	/**
	 * Class constructor
	 */
	private function __construct(){
        $this->dir_cache = dirname(substr(plugin_dir_path( __FILE__ ), 0, -1)) . '/cache/';
        $this->file_cache = $this->dir_cache . 'route.cache.php';
        $this->cache = is_file($this->file_cache) ? (array)include $this->file_cache : [];
        echo($this->file_cache);
    }
    
	/**
	 * Saves the route cache
	 *
	 * @return	bool
	 */
	public function saveCache()
	{
        if (!file_exists($this->file_cache)) mkdir($this->dir_cache);
		file_put_contents($this->file_cache, '<?php return ' . var_export($this->cache, true) . ';')
        or die('Cannot write the file:  '.$this->file_cache);
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
        $this->registerRoute($route);
		return $route;
	}

    /**
     * Register a new route into the route manager
     *
     * @param \Wormvc\Wormvc\Route $route The route instance
     * @return \Wormvc\Wormvc\Manager\RouteManager
     */
    public function registerRoute($route)
    {
        if (count($this->routes)) $this->routes[] = $route;
        else $this->routes[1] = $route;

        if (!$this->filters_added) {
            add_action( 'init', array($this,'addRewriteRulesAction'), 1);
            add_filter( 'query_vars', function( $query_vars ) {
                $query_vars[] = 'wormvc';
                return $query_vars;
            }); 
            add_action( 'template_include', array($this,'loadControllerAction'), 10);
            $this->filters_added = true;
        }

        if (!isset($this->cache[$route->regex])) {

            if (!$this->rewrite_rules_flushed) {
                add_action( 'init', array($this,'flushRewriteRules'), 1);
                $this->rewrite_rules_flushed = true;
            }
            $this->cache[$route->regex] = key($this->routes);
            $this->saveCache();
        }

        return $this;
    }

    /**
     * Flush rewrite rules
     */
    public function flushRewriteRules() {
        flush_rewrite_rules(  true );
       
    }

    /**
     * Generate rewrite rules when fushed
     *
     * @return \Wormvc\Wormvc\Manager\RouteManager
     */
	public function addRewriteRulesAction() 
	{
        add_filter( 'generate_rewrite_rules', function ( $wp_rewrite ) {
            $routes = array();
            foreach($this->routes as $key => $route) {
                $routes[$route->regex] = 'index.php?wormvc='.$key;
            }
            $wp_rewrite->rules = array_merge(
                $routes,
                $wp_rewrite->rules
            );
        });
        
        return $this;
	}

    /**
     * Load the action matching a route
     */
    public function loadControllerAction($template)
    {
        $wormvc_var = get_query_var( 'wormvc');
        if ( $wormvc_var ) {

            if (isset($this->routes[$wormvc_var]) && in_array($_SERVER['REQUEST_METHOD'], $this->routes[$wormvc_var]->getMethods())) {
                
                $route = $this->routes[$wormvc_var];
                $action = $route->getAction();
                
                $request_is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
                if ($route->isAjax() !== $request_is_ajax) return $template;


                   ob_start();

                if ($route->getContent() == 'json') {
                    if (is_string($action) && strpos($action, ".") && file_exists($action)) $result = include ($action);
                    else $result = $this->wormvc->get($action);
                } else {
                    if ($route->getLayout() && !$request_is_ajax) {
                        wp_head();
                        get_header();
                    }
                    if (is_string($action) && strpos($action, ".") && file_exists($action)) include ($action);
                    else $this->wormvc->get($action);
                    if ($route->getLayout() && !$request_is_ajax) get_footer();  
                }
            } else {
                return $template;
            }
        } else {
            return $template;
        }
    }
}