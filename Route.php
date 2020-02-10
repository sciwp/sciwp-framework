<?php
namespace Sci\Sci;

use \Sci\Sci\Sci;

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Route
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */
class Route
{
    use \Sci\Sci\Traits\Sci;
    
    /** @var string $route */
	private $route;
    
	/** @var Mixed $action */
	private $action;
    
    /** @var string $regex */
	public $regex;
    
	/** @var array $params */
	private $params = array();

	/** @var array $methods Request methods */
	private $methods;
    
    /** @var boolean $is_ajax Run only of it´s an ajax request */
	private $is_ajax = false;

	/** @var	string $layout Add wordpress layout */
	private $add_layout = true;
	
	/** @var string $content If it´s html/file/json */
	private $content = 'html';	

	/**
	 * Class constructor
     *
	 * @var string $route
     * @var mixed $action
	 * @return \Sci\Sci\Route
	 */
	public function __construct($methods, $route, $action)
	{
        $this->route_manager = \Sci\Sci\Sci::instance()->routeManager();

        $this->methods = (array) $methods;
        foreach($this->methods as $key => $value) {
            $this->methods[$key] = strtoupper($value);
        }
      
        // Remove trailing slashes
        $route = trim($route, '/');

        // Get parameters
        preg_match_all('/\{(.*?)(\?)?(\|((.*?)({.*})?)?)?\}/', rtrim($route, '/'), $matches);

        if (is_array($matches) && isset($matches[1])) {
            foreach ((array) $matches[1] as $key => $match) {
                $this->params[$match] = isset($matches[4][$key]) && $matches[4][$key] ? '('.$matches[4][$key].')' : "([A-Za-z0-9\-\_]+)";
                if($matches[2][$key] == '?') {
                    $this->params[$match] = '?' . $this->params[$match] . '?';
                }
                /** NEW PARAM: add order, regex and name. Then, add to a request object, which will be linked to the current route */
            }           
        }

        $this->route = preg_replace('/\{(.*?)(\?)?(\|((.*?)({.*})?)?)?\}/', '{$1}', $route); 
        
        $this->generateRegex();

		$this->action = $action;
        return $this;
	}

	/**
	 * Add a new route answering to the get method
	 *
	 * @param string $route
	 * @param mixed $action
	 * @return \Sci\Sci\Route
	 */
    public static function get($route, $action)
    {
        $route = new self('get', $route, $action);
        $route->register();
        return $route;
    }
    
	/**
	 * Add a new route answering to the post method
	 *
	 * @param string $route
	 * @param mixed $action
	 * @return \Sci\Sci\Route
	 */
    public static function post($route, $action)
    {
        $route = new self('post', $route, $action);
        $route->register();
        return $route;
    }

	/**
	 * Add a new route answering to the put method
	 *
	 * @param string $route
	 * @param mixed $action
	 * @return \Sci\Sci\Route
	 */
    public static function put($route, $action)
    {
       $route = new self('put', $route, $action);
       $route->register();
       return $route;
    }

	/**
	 * Add a new route answering to the patch method
	 *
	 * @param string $route
	 * @param mixed $action
	 * @return \Sci\Sci\Route
	 */
    public static function patch($route, $action)
    {
        $route = new self('patch', $route, $action);
        $route->register();
        return $route;
    }

	/**
	 * Add a new route answering to the delete method
	 *
	 * @param string $route
	 * @param mixed $action
	 * @return \Sci\Sci\Route
	 */
    public static function delete($route, $action)
    {
        $route = new self('delete', $route, $action);
        $route->register();
        return $route;
    }

	/**
	 * Add a new route answering all methods
	 *
	 * @param string $route
	 * @param mixed $action
	 * @return \Sci\Sci\Route
	 */
    public static function any($route, $action)
    {
        $route = new self(['get', 'post', 'put', 'patch', 'delete'], $route, $action);
        $route->register();
        return $route;
    }

	/**
	 * Add a new route answering the selected methods
	 *
	 * @param string $route
	 * @param mixed $action
	 * @return \Sci\Sci\Route
	 */
    public static function match($methods, $route, $action)
    {
        $route = new self($methods, $route, $action);
        $route->register();
        return $route;
    }    


	/**
	 * Add the route to the route manager
	 *
	 * @return \Sci\Sci\Route
	 */
    public function register() {
        $this->route_manager->register($this);
        return $this;
    }

	/**
	 * Add parameter description
	 *
	 * @return \Sci\Sci\Route
	 */		
    public function where(...$args)
    {
		if (!is_array($args[0])) $args = array($args[0] => $args[1]);	
        else $args = $args[0];
		foreach ($args as $key => $arg) {
            $optionalCharacter = false;
            if (substr($this->params[$key], -1) == '?') {
                $this->params[$key] = '?(' . $arg . ')?'; 
            } else {
                $this->params[$key] = '?(' . $arg . ')';
            }                
		}
        $this->generateRegex();
		return $this;
    }

	/**
	 * Set the request to accept async calls
	 *
	 * @return \Sci\Sci\Route
	 */		
    public function ajax($value = true)
    {
        $this->is_ajax = $value;
		return $this;
    }

    /**
	 * Set the response type
	 *
	 * @return \Sci\Sci\Route
	 */		
    public function content($value = 'html')
    {
        $this->content = $value;
		return $this;
    }

    /**
	 * Sets if the response will contain WordPress layout
	 *
	 * @return \Sci\Sci\Route
	 */		
    public function layout($value = false)
    {
        $this->add_layout = $value;
		return $this;
    }

	/**
	 * Generate regular expression
	 *
	 * @return \Sci\Sci\Route
	 */		
	public function generateRegex()
	{
        $this->regex = str_replace('/', '\/', $this->route) . '\/?$';
        //$this->regex = '/^' .$this->route. '$/'
        foreach ($this->params as $key => $regex) {
            $this->regex = preg_replace("/(\{".$key."\})/", $regex, $this->regex);
        }
        echo($this->regex);echo("<br/>");
        return $this;
	}

	/**
	 * Get the route action
	 *
	 * @return string|array
	 */		
	public function getAction()
	{
		return $this->action;
	}
    
    /**
	 * Get the request methods
	 *
	 * @return array
	 */		
	public function getMethods()
	{
		return $this->methods;
	}

	/**
	 * Get if it´s an async route
	 *
	 * @return	Boolean
	 */		
	public function isAjax()
	{
		return $this->is_ajax;
	}
	
	/**
	 * Get the content type of the response
	 *
	 * @return string
	 */		
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Get if the response has WordPress layout
	 *
	 * @return boolean
	 */		
	public function getLayout()
	{
		return $this->add_layout;
	}
    
	/**
	 * Get the regular expression
	 *
	 * @return string
	 */		
	public function getRegex()
	{
		return $this->regex;
	}

    /**
	 * Get the parameters
	 *
	 * @return arrays
	 */		
	public function getParams()
	{
		return $this->params;
	}
}