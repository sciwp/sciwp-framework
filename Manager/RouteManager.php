<?php
namespace Wormvc\Wormvc\Manager;

defined('WPINC') OR exit('No direct script access allowed');

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
    /** @var array $routes Stores a list of the registered routes */
    private $routes = array();
	
	/** @var array $segments Segments in the url */
	private $segments = array();    

    private $index = 1;    
	
	/**
	 * Parameters to pass to the callback function or method
	 * @var array
	 */
	private $params = array();    
    
    /**
     * @param Autoloader $autoloader
     */    
	public function __construct()
    {
        
        
        add_action( 'init', array($this,'addRewriteRulesAction'), 1);
        add_filter( 'query_vars', function( $query_vars ) {
            $query_vars[] = 'wormvc';
            return $query_vars;
        } );        
        
        add_action( 'template_redirect', array($this,'loadControllerAction'), 1);
          
        
       
        
    }


        public function loadControllerAction() {
            $wormvc_var =  get_query_var( 'wormvc');
            echo($wormvc_var."-----------------------------------------------------------------------<br>");
            if ( $wormvc_var ) {
                if(isset($this->routes[$wormvc_var])) {
                    echo("----------------------------------XXXXXXXXXXXXX");
                    $route = $this->routes[$wormvc_var];
                    $callback = $route->getCallback();

                    $this->wormvc->get($callback);					

                }
                else if($wormvc_var == 'x') {
                    echo("----------------------------------OKOKOKOKOKOK");

                    
                    
                    
                }                
                /*
                wp_head();
                get_header();
                echo("--------------");
                get_footer();
                die;
                */
            }
        }         

    
    
    
    
    
    
    
    
    
    
    /**
     * Generate rewrite rules when fushed
     */
	public function addRewriteRulesAction() 
	{
        add_filter( 'generate_rewrite_rules', function ( $wp_rewrite ) {
            
            $routes = array();
            foreach($this->routes as $key => $route) {
                $routes[$route->regex]  = 'index.php?wormvc='.$key;
            }
            $cosa = 'my-custom-([0-9]{2})\/s\/?$';
            $routes[$cosa] = 'index.php?wormvc=x';

            $wp_rewrite->rules = array_merge(
                $routes,
                $wp_rewrite->rules
            );

            echo("<pre>"); print_r($wp_rewrite->rules); echo("</pre>");
            //['my-custom-[0-9]{2}/s/?$' => 'index.php?wormvc=1'],
        } );        



	}

	/**
	 * Sets a route
     *
	 * @var $route
     * @var $callback
     * @var $priority
	 * @return string
	 */	
	public function route($route, $callback, $name = false) 
	{
		$route = new Route($route, $callback, $name);
        
        if($route->name()) {
            $this->routes[$route->name()] = $route;
        } else {
            $this->routes[$this->index] = $route;
            $this->index++;
        }
       
		return $route;
	}

    
	/**---------------------------------------------------------------
	 * Cleans a url
	 * ---------------------------------------------------------------
	 * @static
	 * @return	string
	 */
	protected function getCleanUrl($url)
	{
		// Remove the script name from the file
		$url = str_replace(dirname($_SERVER['SCRIPT_NAME']), '', $url);
		
		// Remove the query string
		$query_string = strpos($url, '?');
		if ($query_string !== false) $url = substr($url, 0, $query_string);

		// If the URL looks like http://localhost/index.php/path/to/folder remove /index.php
		if (substr($url, 1, strlen(basename($_SERVER['SCRIPT_NAME']))) == basename($_SERVER['SCRIPT_NAME'])) {
		  $url = substr($url, strlen(basename($_SERVER['SCRIPT_NAME'])) + 1);
		}
		// Make sure the URI ends in a /

		if (substr($url, -1) == '/') $url = substr($url, 0, -1);

		// Replace multiple slashes in a url, such as /my//dir/url
		$url = preg_replace('/\/+/', '/', $url);
		return $url;
	}    
    
    
    
	/**
	 * Runs the router
	 */		
	public function run()
	{
		ksort($this->routes);
		$this->segments = Url::segments();
		$url = $this->getCleanUrl($_SERVER['REQUEST_URI']);
		echo("<pre>"); print_r($this->routes);echo("</pre>"); 
		foreach ($this->routes as $route) {
            
            
			if (!preg_match($route->getPattern(), $url, $matches)) {

                define('IS_AJAX', ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'));
                        
                //Ajax check
                if(IS_AJAX && !$route->getAjax()) continue;
                if (!IS_AJAX && $route->getAjax()) continue;

                //Post check
                if (empty($_POST) &&  $route->getPost() == 1) continue;
                if (!empty($_POST) &&  $route->getPost() == 2) continue;							

                        
                $response = $route->getResponse();
                        
                if ($response == 'json' || IS_AJAX) { //Response::layout(false);
                }else {} //Response::layout($route->getLayout());
                        
                $callback = $route->getCallback();

                foreach ($matches as $key => $match) {
                    if (is_string($key)) $this->params[] = $match;
                }
                    
                ob_start();

                if (is_string($callback)) {
                            if (strpos($callback, ".")) {
                                include ($callback); // Include a single file
                            }
                            else if ((substr($callback, 0, -1) == '/') && is_dir($callback)) {
                                //Return a file using a recursive folder
                                $file = fileByUrlSegments($callback);
                                if ($file) include ($file);
                            }
                }
                else {
                    if (is_array($callback)) {
                        if (class_exists($callback[0])) $callback[0] = $this->wormvc->get($callback[0]);
                    }
                    call_user_func_array($callback, array_values($this->params));						
                }
                
                $result = ob_get_contents();
                        
                ob_end_clean();
                        
                //Response::setContent($result)->render();
                    
                ob_end_flush();
                        
                return $result;
			}
		}
		return false;
	}
}