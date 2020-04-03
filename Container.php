<?php
namespace Sci;

use Exception;
use Sci\Sci;
use Sci\Traits\Singleton;

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

    /** @var array $actions Class actions */
    protected $actions = [];

    /** @var array $bindings Bindings */
    protected $bindings = [];

    /** @var array $singletons The object a singleton should return */
    protected $singletons = [];

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


    /**
     * Bind an alias to a class
     *
     * @param string $alias The class alias
     * @param string $to The class name, method or instance
     * @return Container
     */    
	public function alias($alias, $to = null)
	{
        $alias = ltrim($alias, '\\');

        if (is_null($alias)) {
            throw new Exception('Cannot use a null alias.');
        }

        if (is_callable($to) || is_object($to) || class_exists($to)) {
            $this->bindings[$alias] = $to;
            return $this;
        }
        throw new Exception('Invalid binding for ' . $alias .'.');
    }

    /**
     * Bind a class name or alias to a class or instance
     *
     * @param string $bind The class name or alias
     * @param string $to The class name, method or instance
     * @return Container
     */    
	public function bind($bind, $to = null)
	{
        $bind = ltrim($bind, '\\');

        if (is_null($bind)) {
            throw new Exception('Cannot use a null binding.');
        } else if (!class_exists($bind)) {
            throw new Exception('The binded class does not exist.');
        }

        if (is_null($to)) {
            // Useful for singletons
            $this->bindings[$bind] = $bind;
            return $this;
        } else if (is_callable($to) || is_object($to) || class_exists($to)) {
            $this->bindings[$bind] = $to;
            return $this;
        }
        throw new Exception('Invalid binding for ' . $bind .'.');
    }
 
    /**
     * Resolve always to the same instance
     *
     * @param string $bind The class name or alias
     * @param string $to The class name, method or instance
     * @return Container
     */    
	public function singleton($bind, $to = null)
	{
        $bind = ltrim($bind, '\\');
        
        if (!isset($this->bindings[$bind])) {
            $this->bind($bind, $to);
        }
        $this->singletons[$bind] = false;
        return $this;
	}

    /**
     * Resolve method
     *
     * @param string $bind The class name to bind
     * @param string $to The class name, method or instance to resolve
     * @param boolean $single If the singletons should be checked
     * @return mixed 
     */ 
	public function resolve($bind, $to = null, $single = true)
	{
        if (isset($this->singletons[$bind]) && $single) {
            if ($this->singletons[$bind]) {
                return $this->singletons[$bind]; 
            }

            $this->singletons[$bind] = $this->resolve($bind, $to, false);
            return $this->singletons[$bind];
        }

        if ($to == null) {
            if (!isset($this->bindings[$bind])) {
                throw new Exception('The binding does not exist.');
            }
            return $this->resolve($bind, $this->bindings[$bind]);
        }

        if (is_callable($to)) {
            $closure = \Closure::fromCallable($to);
            $reflection = new \ReflectionFunction($to);
            $result = call_user_func_array($closure, $reflection->getParameters());
            if (is_object($result)) {
                $className = get_class($result);
                $this->runInstanceActions($className, $result);
            }
            return $result;
        } else if (is_object($to) || class_exists($to)) {
            return $to;
        }
        throw new Exception('It was not possible to resolve the binding.');
    }

    /**
     * Executes a function when an instance is created
     *
     * @param string $class The class name
     * @param string $action The function to execute
     */    
	public function created($class, $action)
	{
        $class = ltrim($class, '\\');

        if (!isset($this->actions[$class])) {
            $this->actions[$class] = array();
        }

        $this->actions[$class][] = $action;
    }

    /**
     * Execute actions
     *
     * @param string $class The class to send to the action
     * @param object $instance The created instance
     */ 
	public function runInstanceActions($class, $instance)
	{
        if (!isset($this->actions[$class])) return;
        if (!count($this->actions[$class])) return;
  
        if (isset($this->actions[$class])) {

            foreach ($this->actions[$class] as $function) {

                if (is_callable($function) ) { 
                    if (is_array($function)) {
                        $paramArr = [
                            'instance' => $instance,
                            'sci' => Sci::instance()
                        ];
                        call_user_func_array($function, $paramArr);
                    } else {
                        $closure = \Closure::fromCallable($function);
                        $reflection = new \ReflectionFunction($function);
                        $paramArr = [
                            'instance' => $instance,
                            'sci' => Sci::instance()
                        ];
                        call_user_func_array($closure, $paramArr);
                       
                    }
         
                }
            }
        }
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
    public function make($class_name, $params = [])
    {
        if (!is_array($params)) $params = [$params];

		$classMethodName = false;
		$class_method = false;

        if (is_string($class_name)) {
            $class_name = ltrim($class_name, '\\');
        } else if (is_array($class_name) && count($class_name) == 2) {
			$classMethodName = $class_name[1];
			$class_name   = $class_name[0];
		} 

        # TODO: This became hard to read
        # keep just in the make method requried code for instantiation
        # Separate method stuff in a 'compose' method
        # Future: contextual binding
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

        if (is_string($class_name) && isset($this->bindings[$class_name])) {
            $class_name = $this->resolve($class_name);
        }

        if (is_object($class_name)) {
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
					return call_user_func_array([$reflector->getName(), $classMethodName], $class_method);
				} else {
                    return call_user_func([$reflector->getName(), $classMethodName]);
                }
			} else {
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

            $this->runInstanceActions($class_name_index, $instance);

            return $instance;
		}
	}
}