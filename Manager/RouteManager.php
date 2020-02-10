<?php
namespace Sci\Sci\Manager;

defined('WPINC') OR exit('No direct script access allowed');

use \Sci\Sci\Manager;
use \Sci\Sci\Route;
use \Sci\Sci\Helpers\Url;

/**
 * Route Manager
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */

class RouteManager extends Manager
{
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
    
    /** @var string $dir_cache Stores de cache file fir path */
	private $dir_cache;
    
    /** @var string $file_cache Stores de cache file path */
	private $file_cache;
    
    /** @var boolean $rewrite_rules_flushed Stores if the rules have been flushed */
	private $rewrite_rules_flushed = false;

	/**
	 * Class constructor
	 */
	protected function __construct(){
        $this->dir_cache = dirname(dirname(substr(plugin_dir_path( __FILE__ ), 0, -1))) . '/cache/';
        $this->file_cache = $this->dir_cache . 'route.cache.php';
        $this->cache = is_file($this->file_cache) ? (array)include $this->file_cache : [];
    }
    
	/**
	 * Saves the route cache
	 *
	 * @return	bool
	 */
	public function saveCache()
	{
        if (!file_exists($this->dir_cache)) mkdir($this->dir_cache);
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
        $this->register($route);
		return $route;
	}

    /**
     * Register a new route into the route manager
     *
     * @param \Sci\Sci\Route $route The route instance
     * @return \Sci\Sci\Manager\RouteManager
     */
    public function register($route)
    {
        if (count($this->routes)) $this->routes[] = $route;
        else $this->routes[1] = $route;

        if (!$this->filters_added) {
            add_action( 'init', array($this,'addRewriteRulesAction'), 1);
            add_filter( 'query_vars', function( $query_vars ) {
                $query_vars[] = 'Sci';
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
        flush_rewrite_rules(true);
       
    }

    /**
     * Generate rewrite rules when fushed
     *
     * @return \Sci\Sci\Manager\RouteManager
     */
	public function addRewriteRulesAction() 
	{
        add_filter( 'generate_rewrite_rules', function ( $wp_rewrite ) {
            $routes = array();
            foreach($this->routes as $key => $route) {
                $routes[$route->regex] = 'index.php?Sci='.$key;
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
        $Sci_var = get_query_var( 'Sci');
        if ( $Sci_var ) {

            if (isset($this->routes[$Sci_var]) && in_array($_SERVER['REQUEST_METHOD'], $this->routes[$Sci_var]->getMethods())) {
                global $wp;
                $route = $this->routes[$Sci_var];
                $action = $route->getAction();

                preg_match_all('/'.$route->getRegex().'/', $wp->request, $matches);

                if (is_array($matches) && isset($matches[1])) {
                    $count = 0;
                    $paramNamesArr = array_keys($route->getParams());
                    echo("<br/>");
                    print_R($matches);
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
                
                if (is_string($action) && strpos($action, ".") && file_exists($action)) {
                    include ($action);
                } else if (is_callable( $action ) && !strpos($action, '::')){
                        
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
                   $this->Sci->get($action, $this->params); 
                } else {
                    $this->Sci->get($action);
                }
                if ($includeLayout) get_footer(); 

            } else {
                return $template;
            }
        } else {
            return $template;
        }
    }
}