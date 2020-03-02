<?php
namespace MyPlugin\Sci;

use \MyPlugin\Sci\Sci;
use \MyPlugin\Sci\Traits\Singleton;

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Container class
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */

class Container
{
    use Singleton;

    /** @var array $created Class instantiation functions. */
    protected $actions = [];

    /** @var array $bindings Bindings. */
    protected $bindings = [];

    /** @var array $alias Class alias. */
    protected $aliases = [];


	public function bindings($binding)
	{
        if (isset($this->bindings[$binding])) {
            return $this->bindings[$binding];
        }
        return false;
    }   

	public function actions($class)
	{
        if (isset($this->actions[$class])) {
            return $this->actions[$class];
        }
        return false;
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
     * @param string $action The function to execute
     */    
	public function created($class, $action)
	{
        if (!isset($this->actions['created'])) {
            $this->created['created'] = array();
        }

        if (!isset($this->created['created'][$class])) {
            $this->created['created'][$class] = array();
        }

        $this->created['created'][$class][] = $action;
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
                    $callParams[] = $this->make($param->getClass()->name, $args[$param->getName()]);
                    unset($args[$param->getName()]);
                 } else if (isset($args[$key]) && is_array($args[$key])) {
                    $callParams[] = $this->make($param->getClass()->name, $args[$key]);
                } else {
                    $callParams[] = $this->make($param->getClass()->name);
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
    public function make($class_name, $params = array())
    {        
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
                $instance = $this->make($class_name);
                $callParams = $this->getMethodParams($class_name, $classMethodName, $params);
                return call_user_func_array(array($instance, $classMethodName), $callParams);
                //return self::make([$instance, $classMethodName], $params);
            } else if (strpos($class_name, '::') !== false) {
                $arr = explode('::',$class_name);
                $class_name   = $arr[0];
                $classMethodName = $arr[1];
                return call_user_func_array(array($class_name, $classMethodName), $params); 
            }
        }

        if (is_string($class_name) && isset($this->aliases[$class_name])) {
            $class_name = $this->aliases[$class_name];
        }
        else if (is_string($class_name) && $this->bindings($class_name)) {
            $class_name = $this->bindings($class_name);
        }
        
        if(is_object($class_name)) {
            $class_name->sci = Sci::instance();
            return $class_name;
        }

		$reflector = new \ReflectionClass($class_name);
		$constructor = $reflector->getConstructor();

        if (self::classUsesTrait($class_name, Singleton::class) && !$classMethodName) $classMethodName = 'instance';
        if ($class_name == Sci::class) $classMethodName = 'instance';

		if ( ($constructor && !$constructor->isPublic()) || $classMethodName) {
            // Singleton or static class
			if ($classMethodName) {
                $class_method = $reflector->getMethod ($classMethodName);
				if(count($params)) {
                    $callParams = $this->getMethodParams($class_name, $classMethodName, $params);
					return call_user_func_array(array($reflector->getName(), $classMethodName), $class_method);
				} else {
                    return call_user_func(array($reflector->getName(), $classMethodName));
                }
			}
			else {
				return $reflector->getName();
			}
		} else {
            // New object instance
            if ($constructor) {
                $callParams = $this->getMethodParams($class_name, '__construct', $params);
                $instance = $reflector->newInstanceArgs($callParams);
            } else {
                $instance = $reflector->newInstance();
            }
            $instance->sci = Sci::instance();
            
            // Check creation functions
            $class_name_index = ltrim($class_name, "\\");

            if ($this->actions($class_name_index)) {
                foreach ($this->actions($class_name_index) as $function) {
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