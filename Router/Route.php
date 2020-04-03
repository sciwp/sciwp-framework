<?php
namespace Sci\Router;

defined('WPINC') OR exit('No direct script access allowed');

use Sci\Sci;
use Sci\Router\Route;
use Sci\Router\Managers\RouteManager;

/**
 * Route
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class Route
{
    use \Sci\Traits\Sci;
    
    /** @var string $route */
	private $route;
    
	/** @var Mixed $action */
	private $action;
    
    /** @var string $regex */
	public $regex;

	/** @var boolean $rest */
	private $rest = false;
    
	/** @var array $params */
	private $params = array();

	/** @var array $methods Request methods */
	private $methods;
    
    /** @var boolean $async Run only of it's an ajax request */
	private $async = false;

	/** @var string $layout Add wordpress layout */
	private $layout = true;

	/** @var RouteManager $routeManager */
	private $routeManager;

	/**
	 * Class constructor
     *
	 * @var string $route
     * @var mixed $action
	 * @return Route
	 */
	public function __construct($methods, $route, $action, RouteManager $routeManager)
	{
		$this->routeManager = $routeManager;

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
                $this->params[$match] = isset($matches[4][$key]) && $matches[4][$key] ? '(?P<'.$match.'>'.$matches[4][$key].')' : '(?P<'.$match.'>[A-Za-z0-9\-\_]+)';
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
	 * Add a route
	 *
	 * @param string $method The route request method
	 * @param string $route The route expression
	 * @param mixed $action The action, file or function
	 * @return Route
	 */
    public static function create($method, $route, $action)
    {
        return Sci::make(self::class, [$method, $route, $action]);
    }

	/**
	 * Create and register a route
	 *
	 * @param string $method The route request method
	 * @param string $route The route expression
	 * @param mixed $action The action, file or function
	 * @return Route
	 */
    public static function commit($method, $route, $action)
    {
		$route = self::create($method, $route, $action);
		$route->register();
        return $route;
    }

	/**
	 * Add a new route answering to the get method
	 *
	 * @param string $route The route expression
	 * @param mixed $action The action, file or function
	 * @return Route
	 */
    public static function get($route, $action)
    {
		$route = self::create('get', $route, $action);
        return $route;
    }
    
	/**
	 * Add a new route answering to the post method
	 *
	 * @param string $route The route expression
	 * @param mixed $action The action, file or function
	 * @return Route
	 */
    public static function post($route, $action)
    {
		$route = self::create('post', $route, $action);
        return $route;
    }

	/**
	 * Add a new route answering to the put method
	 *
	 * @param string $route The route expression
	 * @param mixed $action The action, file or function
	 * @return Route
	 */
    public static function put($route, $action)
    {
		$route = self::create('put', $route, $action);
		return $route;
    }

	/**
	 * Add a new route answering to the patch method
	 *
	 * @param string $route The route expression
	 * @param mixed $action The action, file or function
	 * @return Route
	 */
    public static function patch($route, $action)
    {
		$route = self::create('patch', $route, $action);
		return $route;
    }

	/**
	 * Add a new route answering to the delete method
	 *
	 * @param string $route The route expression
	 * @param mixed $action The action, file or function
	 * @return Route
	 */
    public static function delete($route, $action)
    {
		$route = self::create('delete', $route, $action);
		return $route;
    }

	/**
	 * Add a new route answering to the options method
	 *
	 * @param string $route The route expression
	 * @param mixed $action The action, file or function
	 * @return Route
	 */
    public static function options($route, $action)
    {
		$route = self::create('options', $route, $action);
		return $route;
    }

	/**
	 * Add a new route answering all methods
	 *
	 * @param string $route The route expression
	 * @param mixed $action The action, file or function
	 * @return Route
	 */
    public static function any($route, $action)
    {
		$route = self::create(['get', 'post', 'put', 'patch', 'delete', 'options'], $route, $action);
		return $route;
    }

	/**
	 * Add the route to the route manager
	 *
	 * @param string $name
	 * @return Route
	 */
	public function register($name = false)
	{
		if ($name) $this->routeManager->register($this, $name);
		else $this->routeManager->register($this);
        return $this;
    }

	/**
	 * Add parameter restrictions
	 *
	 * @param string|array $args[0] Parameter name or array
	 * @param string $args[1] Regex restriction
	 * @return Route
	 */		
    public function where(...$args)
    {
		if (!is_array($args[0])) {
			$args = array($args[0] => $args[1]);
		} else {
			$args = $args[0];
		}
		foreach ($args as $key => $arg) {
            $optionalCharacter = false;
            if (substr($this->params[$key], -1) == '?') {
				$this->params[$key] = '?(?P<'.$key.'>' . $arg . ')?'; 
            } else {
                $this->params[$key] = '(?P<'.$key.'>' . $arg . ')';
            }                
		}
        $this->generateRegex();
		return $this;
	}

	/**
	 * Set the request to accept async calls
	 *
	 * @return Route
	 */		
    public function async($value = true)
    {
        $this->async = $value;
		return $this;
    }

    /**
	 * Sets if the response will contain WordPress layout
	 *
	 * @return Route
	 */		
    public function layout($value = false)
    {
        $this->layout = $value;
		return $this;
	}

	/**
	 * Generate regular expression
	 *
	 * @return Route
	 */		
	public function generateRegex()
	{
        $this->regex = str_replace('/', '\/', $this->route) . '\/?$';
        //$this->regex = '/^' .$this->route. '$/'
        foreach ($this->params as $key => $regex) {
            $this->regex = preg_replace("/(\{".$key."\})/", $regex, $this->regex);
		}
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
	 * Load the route action
	 *
	 * @return void
	 */		
    public function loadAction()
    {
        global $wp;
		$requestParams = [];

        preg_match_all('/'.$this->regex.'/', $wp->request, $matches);

        if (is_array($matches) && isset($matches[1])) {
            $count = 0;
			$paramNamesArr = array_keys($this->params);

            foreach($matches as $key => $match) {
                if ($key > 0 && $match[0]) {
                    $requestParams[$paramNamesArr[$count]] = $match[0];
                    $count++;
                }
            }
		}
		
        if ($this->layout && !$this->async) {
            wp_head();
            get_header();
        }
		
		if (is_string($this->action)) {

			if (strpos($this->action, ".") && file_exists($this->action)) {
				include ($this->action);
			} else if (!empty($requestParams)) {
				Sci::make($this->action, $requestParams); 
			} else {
				Sci::make($this->action);
			}

        } else if (is_callable( $this->action )){

			$f = new \ReflectionFunction($this->action);
			$callParams = array();

            foreach ($f->getParameters() as $key => $param) {
				if ($param->getClass()) {
					if (isset($requestParams[$param->getName()]) && is_array($requestParams[$param->getName()])) {
				
						$callParams[] = Sci::make($param->getClass()->name, $requestParams[$param->getName()]);
					} else {

						// it's a funcion or a static method
						$callParams[] = Sci::make($param->getClass()->name);
					}
				} else {
					// If it is a simple parameter
					if (isset($requestParams[$param->getName()])) {
						$callParams[] =  $requestParams[$param->getName()];
					} else if ($param->isDefaultValueAvailable()) {
						$callParams[] = $param->getDefaultValue();
					}
				}
			}
			
			return call_user_func_array($this->action, $callParams);

        } else {
            Sci::make($this->action);
		}

		if ($this->layout && !$this->async) {
			get_footer();
		}
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
	 * @return boolean
	 */		
	public function getAsync()
	{
		return $this->async;
	}


	/**
	 * Get if the response has WordPress layout
	 *
	 * @return boolean
	 */		
	public function getLayout()
	{
		return $this->layout;
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
	 * @return array
	 */		
	public function getParams()
	{
		return $this->params;
	}
}