<?php
namespace KNDCC\Wormvc;

use \KNDCC\Wormvc\Wormvc;

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Route
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */
class Route
{
	/** @var string $regex */
	public $regex;
    
	/** @var array $params */
	private $params = array();
    
    
	/** @var array $params */
	private $route = array();        

	/** @var string $name */
	private $name;

	/** @var Mixed $callback */
	private $callback;

	/**
	 * Route request string (get/post)
	 * @var	boolean
	 */
	private $is_post = 0;		

    
	/**
	 * Layout
	 * @var	string
	 */
	private $layout = true;
	
	/**
	 * Request html/file/json)
	 * @var	string
	 */
	private $response = 'html';	
	
	/**
	 * Run only of it´s an ajax request
	 * @var	boolean
	 */
	private $is_ajax = false;
	
	/**
	 * Class constructor
     *
	 * @var string $pattern
     * @var mixed $callback
	 * @return \KNDCC\Wormvc\Route
	 */
	public function __construct($pattern, $callback, $name = false)
	{
        if($name) $this->name = $name;
        // Remove trailing slashes
        $pattern = trim($pattern, '/'); 

        /*
		$pattern = preg_replace('/\<\:(.*?)\|(.*?)\>/', '(?P<\1>\2)', $pattern); // Custom capture, format: <:var_name|regex>
		$pattern = preg_replace('/\<\:(.*?)\>/', '(?P<\1>[A-Za-z0-9\-\_]+)', $pattern); // Alphanumeric capture (0-9A-Za-z-_), format: <:var_name>
		$pattern = preg_replace('/\<\#(.*?)\>/', '(?P<\1>[0-9]+)', $pattern); // Numeric capture (0-9), format: <#var_name>
		$pattern = preg_replace('/\<\!(.*?)\>/', '(?P<\1>[^\/]+)', $pattern); // Wildcard capture (Anything EXCLUDING directory separators), format: <!var_name>	
		$pattern = preg_replace('/\<\*(.*?)\>/', '(?P<\1>.+)', $pattern); // Wildcard capture (Anything INCLUDING directory separators), format: <*var_name>
		$pattern = '/^' . str_replace('/', '\/', $pattern) . '$/'; // Build regular expression syntax	
        */

        // Get parameters
        preg_match_all('/\{(.*?)(\|((.*?)({.*})?)?)?\}/', rtrim($pattern, '/'), $matches);

        if (is_array($matches) && isset($matches[1])) {
            foreach ((array) $matches[1] as $key => $match) {
                $this->params[$match] = isset($matches[3][$key]) && $matches[3][$key] ? $matches[3][$key] : "[A-Za-z0-9\-\_]+";
                
                /**
                NEW PARAM: add order, regex and name. Then, add to a request object, whcih will be linked to the current route
                
                Also change Wormvc trait route to kndcc\wormvc\traits\wormvc
                */
            }           
        }

        $pattern = preg_replace('/\{(.*?)(\|((.*?)({.*})?)?)?\}/', '{$1}', $pattern); // Custom capture, format: <:var_name|regex>
        $this->route = $pattern;   
        $this->generateRegex();

		$this->callback = $callback;
        return $this;
	}

    public function where(...$args)
    {
		if (!is_array($args[0])) $args = array($args[0] => $args[1]);	
        else $args = $args[0];
		foreach ($args as $key => $arg) {
            $this->params[$key] = $arg;                          
		}
        $this->generateRegex();
		return $this;
    }

	/**---------------------------------------------------------------
	 * Get the route name
	 * ---------------------------------------------------------------
	 * @return	string
	 */		
	public function generateRegex()
	{
        $this->regex = str_replace('/', '\/', $this->route) . '\/?$';
        //$this->regex = '/^' .$this->route. '$/'
        foreach ($this->params as $key => $regex) {
            $this->regex = preg_replace("/(\{".$key."\})/", '('.$regex.')', $this->regex);
        }        
        return $this->regex;
	}

	/**---------------------------------------------------------------
	 * Get the route callback
	 * ---------------------------------------------------------------
	 * @return	Mixed
	 */		
	public function getCallback()
	{
		return $this->callback;
	}

	/**---------------------------------------------------------------
	 * Get if it´s an async route
	 * ---------------------------------------------------------------
	 * @return	Boolean
	 */		
	public function getAjax()
	{
		return $this->is_ajax;
	}
	
	/**---------------------------------------------------------------
	 * Get if there should be post vars
	 * ---------------------------------------------------------------
	 * @return	Boolean
	 */		
	public function getPost()
	{
		return $this->is_post;
	}	
	
	/**---------------------------------------------------------------
	 * Get the response type
	 * ---------------------------------------------------------------
	 * @return	string
	 */		
	public function getResponse()
	{
		return $this->response;
	}
	
	/**---------------------------------------------------------------
	 * Get the layout
	 * ---------------------------------------------------------------
	 * @return	string
	 */		
	public function getLayout()
	{
		return $this->layout;
	}

	/**---------------------------------------------------------------
	 * Sets/Gets the route name
	 * ---------------------------------------------------------------
	 * @return	Route
	 */		
	public function name($name = null)
	{
		if ($name === null) return $this->name ? $this->name : '';
		else $this->name = $name;
		return $this;
	}
	/**---------------------------------------------------------------
	 * Sets if it´s an async request
	 * ---------------------------------------------------------------
	 * @return	Route
	 */

	public function ajax($value)
	{
		if($value) $this->is_ajax= true;
		return $this;
	}

	/**---------------------------------------------------------------
	 * Sets if it´s an async request
	 * ---------------------------------------------------------------
	 * @return	Route
	 */
	 
	public function post($value)
	{
		if($value) $this->is_post= 1;
		else $this->is_post= 2;
		return $this;
	}
	
	/**---------------------------------------------------------------
	 * Sets the layout
	 * ---------------------------------------------------------------
	 * @return	Route
	 */
	 
	public function layout($value)
	{
		$this->layout = $value;
		return $this;
	}		
	
	/**---------------------------------------------------------------
	 * Sets the response type paramenter
	 * ---------------------------------------------------------------
	 * @return	Route
	 */
	 
	public function response($value)
	{
		$this->response = $value;
		return $this;
	}	
}