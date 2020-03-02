<?php
namespace MyPlugin\Sci;

use \MyPlugin\Sci\Manager\RestManager;
use \MyPlugin\Sci\Sci;

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
class Rest
{
    use \MyPlugin\Sci\Traits\Sci;
	
	/** @var \WP_REST_Request $request WordPress request */
	private static $request;

    /** @var string $namespace */
	public $namespace;

    /** @var string $route */
	private $route;
    
	/** @var Mixed $action */
	private $action;
    
    /** @var string $regex */
	public $regex;
    
	/** @var array $params */
    private $params = array();
    
    /** @var array $validators */
	private $validators = array();

	/** @var array $methods Request methods */
	private $methods;

	/** @var array $args Route args */
	private $args = [];

	/**
	 * Class constructor
     *
     * @var string $namespace The Api namespace
     * @var string|array[string]  $methods The request method
	 * @param string $route The rest route expression
	 * @param mixed  $action The rest action, file or function
     * @var \MyPlugin\Sci\Manager\RestManager $restManager Rest manager instance
	 * @return \MyPlugin\Sci\Rest
	 */
	public function __construct($namespace, $methods, $route, $action, RestManager $restManager)
	{
        $this->restManager = $restManager;

        $this->namespace = $namespace;

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
            }           
        }

        $this->route = preg_replace('/\{(.*?)(\?)?(\|((.*?)({.*})?)?)?\}/', '{$1}', $route); 
        $this->generateRegex();

		$this->action = $action;
        return $this;
	}

	/**
	 * Return the request object
	 *
	 * @return \WP_REST_Request
	 */
	public static function request() {
		return self::$request;
	}

	/**
	 * Add a route
	 *
     * @param string $namespace The Api namespace
	 * @param string $method The route request method
	 * @param string $route The rest route expression
	 * @param mixed  $action The rest action, file or function
	 * @return \MyPlugin\Sci\Rest
	 */
    public static function create($namespace, $method, $route, $action)
    {
        $route = new self($namespace, $method, $route, $action, RestManager::instance());
        return $route;
    }

	/**
	 * Create and register a route
	 *
     * @param string $namespace The Api namespace
	 * @param string $method The route request method
	 * @param string $route The rest route expression
	 * @param mixed  $action The rest action, file or function
	 * @return \MyPlugin\Sci\Rest
	 */
    public static function commit($namespace, $method, $route, $action)
    {
		$route = self::create($namespace, $method, $route, $action);
		$route->register();
        return $route;
    }

	/**
	 * Add a new route answering to the get method
	 *
     * @param string $namespace The Api namespace
	 * @param string $route The rest route expression
	 * @param mixed  $action The rest action, file or function
	 * @return \MyPlugin\Sci\Rest
	 */
    public static function get($namespace, $route, $action)
    {
        $route = self::create($namespace, 'get', $route, $action);
        return $route;
    }
    
	/**
	 * Add a new route answering to the post method
	 *
     * @param string $namespace The Api namespace
	 * @param string $route The rest route expression
	 * @param mixed  $action The rest action, file or function
	 * @return \MyPlugin\Sci\Rest
	 */
    public static function post($namespace, $route, $action)
    {
        $route = self::create($namespace, 'post', $route, $action);
        return $route;
    }

	/**
	 * Add a new route answering to the put method
	 *
     * @param string $namespace The Api namespace
	 * @param string $route The rest route expression
	 * @param mixed  $action The rest action, file or function
	 * @return \MyPlugin\Sci\Rest
	 */
    public static function put($route, $action)
    {
       $route = self::create($namespace, 'put', $route, $action);
       return $route;
    }

	/**
	 * Add a new route answering to the patch method
	 *
     * @param string $namespace The Api namespace
	 * @param string $route The rest route expression
	 * @param mixed  $action The rest action, file or function
	 * @return \MyPlugin\Sci\Rest
	 */
    public static function patch($namespace, $route, $action)
    {
        $route = self::create($namespace, 'patch', $route, $action);
        return $route;
    }

	/**
	 * Add a new route answering to the delete method
	 *
     * @param string $namespace The Api namespace
	 * @param string $route The rest route expression
	 * @param mixed  $action The rest action, file or function
	 * @return \MyPlugin\Sci\Rest
	 */
    public static function delete($namespace, $route, $action)
    {
        $route = self::create($namespace, 'delete', $route, $action);
        return $route;
    }

	/**
	 * Add a new route answering to the options method
	 *
     * @param string $namespace The Api namespace
	 * @param string $route The rest route expression
	 * @param mixed  $action The rest action, file or function
	 * @return \MyPlugin\Sci\Rest
	 */
    public static function options($namespace, $route, $action)
    {
        $route = self::create($namespace, 'options', $route, $action);
        return $route;
    }

	/**
	 * Add a new route answering all methods
	 *
     * @param string $namespace The Api namespace
	 * @param string $route The rest route expression
	 * @param mixed  $action The rest action, file or function
	 * @return \MyPlugin\Sci\Rest
	 */
    public static function any($namespace, $route, $action)
    {
        $route = self::create($namespace, ['get', 'post', 'put', 'patch', 'delete', 'options'], $route, $action);
        return $route;
    }

	/**
	 * Add a new route answering the selected methods
	 *
     * @param string $namespace The Api namespace
	 * @param string $route The rest route expression
	 * @param mixed  $action The rest action, file or function
	 * @return \MyPlugin\Sci\Rest
	 */
    public static function match($namespace, $methods, $route, $action)
    {
        $route = self::create($namespace, $methods, $route, $action);
        return $route;
    }    

	/**
	 * Add the route to the route manager
	 *
     * @param string $name The rest route name
	 * @return \MyPlugin\Sci\Rest
	 */
    public function register($name = false) {
		if ($name) $this->restManager->register($this, $name);
		else $this->restManager->register($this);
        return $this;
    }

	/**
	 * Add parameter restrictions
	 *
	 * @param string|array $args[0] Parameter name or array
	 * @param string $args[1] Regex restriction
	 * @return \MyPlugin\Sci\Rest
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
	 * Add WP args
	 *
	 * @param string|array $args array of args
	 * @return \MyPlugin\Sci\Rest
	 */		
    public function args($args)
    {
		$this->args = (array) $args;
		return $this;
	}

	/**
	 * Generate regular expression
	 *
	 * @return \MyPlugin\Sci\Rest
	 */		
	public function generateRegex()
	{
        $this->regex = str_replace('/', '\/', $this->route) . '\/?$';
        foreach ($this->params as $key => $regex) {
            $this->regex = preg_replace("/(\{".$key."\})/", $regex, $this->regex);
		}
        return $this;
	}

	/**
	 * Get the route namespace
	 *
	 * @return string
	 */		
	public function getNamespace()
	{
		return $this->namespace;
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
     * Load the action matching the requested route
     * 
     * @param \WP_REST_Request $request The WordPress request object
     * @return mixed
     */
	public function loadAction(\WP_REST_Request $request)
	{
		global $wp;
		
		self::$request =  $request;

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

		if (is_string($this->action)) {

			if (strpos($this->action, ".") && file_exists($this->action)) {
				return include ($this->action);
			} else if (!empty($requestParams)) {
				return Sci::make($this->action, $requestParams); 
			} else {
				return Sci::make($this->action);
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
            return Sci::make($this->action);
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
	 * Get the regular expression
	 *
	 * @return string
	 */		
	public function getRegex()
	{
		return $this->regex;
	}

	/**
	 * Get the route args
	 *
	 * @return array
	 */		
	public function getArgs()
	{
		return $this->args;
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