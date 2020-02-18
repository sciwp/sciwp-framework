<?php
namespace MyPlugin\Sci;

use \MyPlugin\Sci\Manager\PluginManager;
use \MyPlugin\Sci\Manager\TemplateManager;
use \MyPlugin\Sci\Manager\ProviderManager;
use \MyPlugin\Sci\Manager\RouteManager;
use \MyPlugin\Sci\Manager\RestManager;
use \MyPlugin\Sci\Traits\Singleton;

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Main Sci class
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */

class Sci
{
    use \MyPlugin\Sci\Traits\Singleton;

    /** @var Sci  $_instance The class instance. */  
    private static $_instance;

    /** @var PluginManager $plugin_manager Stores a reference to the plugin manager. */
    private $plugin_manager;

    /** @var ProviderManager $provider_manager Stores a reference to the provider manager. */
    private $provider_manager;
    
    /** @var TemplateManager $template_manager Stores a reference to the template manager. */
    private $template_manager;
    
    /** @var RouteManager $route_manager Stores a reference to the route manager. */
    private $route_manager;    
    
    /** @var array $created Class instantiation functions. */
    protected $created = [];     

    /** @var array $bindings Bindings. */
    protected $bindings = [];    

    /** @var array $alias Class alias. */
    protected $aliases = [];       

    /**
     * @param PluginManager $plugin_manager
     */
    private function __construct (){}

	/**
	 *  Returns a unique instance or creates a new one
	 *
	 * @return	bool
	 */
    public static function instance ()
    {
        if (!isset( self::$_instance)) {
            self::$_instance = new Sci;
        }
        return self::$_instance;
    }

	/**
	 * Initialize the main components
	 */    
    public function init ()
    {
        $this->plugin_manager = self::get(PluginManager::class);
        $this->template_manager = self::get(TemplateManager::class);
        $this->provider_manager = self::get(ProviderManager::class);
        $this->route_manager = self::get(RouteManager::class);
        $this->rest_manager = self::get(RestManager::class);
        return $this;
    }   

    
    /**
     * Get a plugin
     * @param string $plugin The plugin id
     * @return Plugin
     */
    public function plugin($plugin_id)
    {
        return $this->plugin_manager->get($plugin_id);
    }
    
    /**
     * Get the plugin manager
     *
     * @return PluginManager
     */
    public function plugins()
    {
        return $this->plugin_manager;
    }

    /**
     * Get the plugin manager
     *
     * @return PluginManager
     */
    public function pluginManager()
    {
        return $this->plugin_manager;
    }

    /**
     * Get the provider manager
     *
     * @return PRoviderManager
     */       
    public function providers()
    {
        return $this->provider_manager;
    }

    /**
     * Get the template manager
     *
     * @return \MyPlugin\Sci\Manager\TemplateManager
     */    
    public function templateManager()
    {
        return $this->template_manager;
    }

    /**
     * Get the route manager
     *
     * @return RouteManager
     */
    public function router()
    {
        return $this->route_manager;
    }

    /**
     * Get the route manager
     *
     * @return RouteManager
     */
    public function routeManager()
    {
        return $this->route_manager;
    }

    /**
     * Get the rest manager
     *
     * @return RestManager
     */
    public function restManager()
    {
        return $this->rest_manager;
    }
    /**
     * Bind a class name or alias to a class or instance
     *
     * @param string $bind The class name or alias
     * @param string $to The class name or instance
     */    
	public function bind($bind, $to)
	{
        if (is_null($bind)) return;
        unset($this->aliases[$bind], $this->bindings[$bind]);
        
        if (is_callable($to) ) {
            $closure = \Closure::fromCallable($to);
            $reflection = new \ReflectionFunction($to);
            if (class_exists($bind)) {
                $this->bindings[$bind] = call_user_func_array($closure, $reflection->getParameters());               
            } else {
                $this->aliases[$bind] = call_user_func_array($closure, $reflection->getParameters()); 
            }
        } else if (is_object($to) || class_exists($to)) {
            if (class_exists($bind)) {
                $this->bindings[$bind] =  $to;
            } else {
                $this->aliases[$bind] =  $to;
            }
        }
	}

    /**
     * Executes a function when an instance is created
     *
     * @param string $class The class name
     * @param string $callback The function to execute
     */    
	public function created($class, $callback)
	{
        if (!isset($this->created[$class])) {
            $this->created[$class] = array();
        }
        $this->created[$class][] = $callback;
	}

    /**
     * This magic method allows to use the get method both statically and within an instance
     * 
     * @param string $name The function name
     * @param array $arguments The function a arguments
     */
    public function __call($name, $arguments)
    {
        if ($name === 'get') return self::make(...$arguments);
    }

    /**
     * This magic method allows to use the get method both statically and within an instance
     * 
     * @param string $name The function name
     * @param array $arguments The function a arguments
     */
    public static function __callStatic($name, $arguments)
    {
        if ($name === 'get') return self::make(...$arguments);
    }

    /**
     * Get parameters from a method and match them to the current parameters
     * 
     * Works with both numeric and string keys, used with routes
     * 
     * @param string $className The class name
     * @param string $className The method name
     * @param array $params The requested parameters
     * @return array
     */
    public function getMethodParams($className, $methodName, $args)
    {
        if ($methodName == '__construct') {
            $reflectionClass= new \ReflectionClass($className);
            $reflectionMethod = $reflectionClass->getConstructor();
        } else {
            $reflectionMethod = new \ReflectionMethod($className, $methodName);
        }

        if (!$reflectionMethod->getParameters()) return $args;

        $callParams = array();
        foreach ($reflectionMethod->getParameters() as $key => $param) {

            $isObject = false;
            if (isset($args[$param->getName()])) $isObject = isset($args[$param->getName()]);
            else if (isset($args[$key])) $isObject = isset($args[$key]);

            if ($param->getClass() && !$isObject) {
                 if (isset($args[$param->getName()]) && is_array($args[$param->getName()])) {
                    $callParams[] = self::make($param->getClass()->name, $args[$param->getName()]);
                    unset($args[$param->getName()]);
                 } else if (isset($args[$key]) && is_array($args[$key])) {
                    $callParams[] = self::make($param->getClass()->name, $args[$key]);
                } else {
                    $callParams[] = self::make($param->getClass()->name);
                }
             } else {
				if (isset($args[$param->getName()])  ) {
                    $callParams[] = $args[$param->getName()];
                    unset($args[$param->getName()]);
                } else if (isset($args[$key])  ) {
					$callParams[] = $args[$key];
                }
                else if ($param->isDefaultValueAvailable()) {
					$callParams[] = $param->getDefaultValue();
				}
			}
        }

        return $callParams;
    }

    /**
     * Allows to get an instace of any class, injecting the dependences when possible
     * 
     * @param \Object $class_name The classto instantiate
     * @param array $params The array with the arguments
     */    
    public static function make($class_name, $params = array())
    {
        $sci = self::instance();
        
		$classMethodName = false;
		$class_method = false;

		if (is_array($class_name) && count($class_name) == 2) {
			$classMethodName = $class_name[1];
			$class_name   = $class_name[0];
		} 

        if (is_string($class_name)) {
            if (strpos($class_name, '@') !== false) {
                $arr = explode('@',$class_name);
                $class_name   = $arr[0];
                $classMethodName = $arr[1];
                $instance = $sci->get($class_name);
                $callParams = $sci->getMethodParams($class_name, $classMethodName, $params);
                return call_user_func_array(array($instance, $classMethodName), $callParams);
                //return self::make([$instance, $classMethodName], $params);
            } else if (strpos($class_name, '::') !== false) {
                $arr = explode('::',$class_name);
                $class_name   = $arr[0];
                $classMethodName = $arr[1];
                return call_user_func_array(array($class_name, $classMethodName), $params); 
            }
        }

        if (is_string($class_name) && isset($sci->aliases[$class_name])) {
            $class_name = $sci->aliases[$class_name];
        }
        else if (is_string($class_name) && isset($sci->bindings[$class_name])) {
            $class_name = $sci->bindings[$class_name];
        }
        
        if(is_object($class_name)) {
            $class_name->sci = self::instance();
            return $class_name;
        }

		$reflector = new \ReflectionClass($class_name);
		$constructor = $reflector->getConstructor();

        if (self::classUsesTrait($class_name, Singleton::class) && !$classMethodName) $classMethodName = 'instance';
        if ($class_name == self::class) $classMethodName = 'instance';
        
		// Singleton or static class
		if ( ($constructor && !$constructor->isPublic()) || $classMethodName) {
			if ($classMethodName) {
                $class_method = $reflector->getMethod ($classMethodName);
				if(count($params)) {
                    $callParams = $sci->getMethodParams($class_name, $classMethodName, $params);
					return call_user_func_array(array($reflector->getName(), $classMethodName), $class_method);
				} else {
                    return call_user_func(array($reflector->getName(), $classMethodName));
                }
			}
			else {
				return $reflector->getName();
			}
		}
		// New object instance
		else {
            if ($constructor) {
                $callParams = $sci->getMethodParams($class_name, '__construct', $params);
                $instance = $reflector->newInstanceArgs($callParams);
            } else {
                $instance = $reflector->newInstance();
            }
            $instance->sci = self::instance();
            
            // Check creation functions
            $class_name_index = ltrim($class_name, "\\");
            if (isset($sci->created[$class_name_index])) {
                foreach ($sci->created[$class_name_index] as $function) {
                    if (is_callable($function) ) {
                        
                        if (is_array($function)) {
                            $param_arr = array(
                                'instance' => $instance,
                            );
                            call_user_func_array($function, $param_arr);
                        } else {
                            $closure = \Closure::fromCallable($function);
                            $reflection = new \ReflectionFunction($function);
                            $param_arr = array(
                                'instance' => $instance,
                            );
                            call_user_func_array($closure, $param_arr);
                           
                        }
             
                    }
                }
            }
            
            return $instance;
		}
	}

    /**
     * Checks if a class uses a Trait
     * 
     * @param string $class_name The class name
     * @param string $trait The trait name
     * @return bool
     */
    public static function classUsesTrait($class_name, $trait)
    {
        return in_array($trait, self::getClassTraits($class_name));   
    }

    /**
     * Returns a the list of traits a class uses
     * 
     * @param string $class The class name
     * @param bool $autoload If the function will be able to use the autoloader
     * @return array
     */    
    public static function getClassTraits($class, $autoload = true)
    {
        $traits = [];

        // Get traits of all parent classes
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traitsToSearch = $traits;
        while (!empty($traitsToSearch)) {
            $newTraits = class_uses(array_pop($traitsToSearch), $autoload);
            $traits = array_merge($newTraits, $traits);
            $traitsToSearch = array_merge($newTraits, $traitsToSearch);
        };

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }

        return array_unique($traits);
    }
}