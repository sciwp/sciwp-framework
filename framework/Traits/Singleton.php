<?php
namespace Sci\Traits;

use Sci\Sci;

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Singleton trait
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
trait Singleton
{
    /** @var array stores all singleton instances */
    private static $_instances = array();

    /**
     * Class constructor
     *
     * @return void
     */
    protected function __construct() {}

    /**
     * Clone
     *
     * Note: PHP 8.0+ disallows `final private` on methods other than the
     * constructor (the access keeps it from being overridden either way).
     *
     * @return void
     */
    private function __clone() {}

    /**
     * Wakeup (PHP 8.0+ requires magic methods to be public)
     *
     * @return void
     */
     public function __wakeup() {}

    /**
     * Create or return an instance
     *
     * When creating and instance, this class is able the read the constructor parameters of the
     * singleton class, read and send the arguments and inject new instances of the dependences
     * if no instances are sent as arguments
     *
     * @return Singleton
     */
    public static function instance ()
    {
        $called_class = get_called_class();
        if ( !isset( self::$_instances[$called_class] ) ) {
            $args = func_get_args();
            $reflector  = new \ReflectionClass($called_class);
            $constructor = $reflector->getConstructor();
            
            $inst_args = [];
            if($constructor && $constructor->getParameters()) {
                // The class constructor has declared arguments
                foreach ($constructor->getParameters() as $key => $parameter) {
                    $paramClass = self::reflectionParamClass($parameter);
                    if ($paramClass) {
                        if (isset($args[$key]) && is_array($args[$key])) {
                            $inst_args[] = Sci::make($paramClass, $args[$key]);
                        } else {
                            $inst_args[] = Sci::make($paramClass);
                        }
                    } else {
                        $inst_args[] = isset($args[$key]) ? $args[$key] : null;
                    }
                }
                self::$_instances[$called_class] = new $called_class ( ...$inst_args );
            }
            else {
                // The class constructor does not have declared arguments
                self::$_instances[$called_class] = new $called_class ( ...$args );
            }

            $instance = self::$_instances[$called_class];
            if (property_exists($instance, 'sci')) {
                $instance->sci = Sci::instance();
            }
        }
        return self::$_instances[$called_class];
    }

    /**
     * PHP 7.4-8.4 compatible replacement for ReflectionParameter::getClass().
     *
     * @param \ReflectionParameter $param
     * @return string|null Fully qualified class name, or null if untyped/builtin
     */
    private static function reflectionParamClass(\ReflectionParameter $param)
    {
        $type = $param->getType();
        if (!$type || !($type instanceof \ReflectionNamedType) || $type->isBuiltin()) {
            return null;
        }
        $name = $type->getName();
        if ($name === 'self' || $name === 'static') {
            $declaring = $param->getDeclaringClass();
            if ($declaring) return $declaring->getName();
        }
        if ($name === 'parent') {
            $declaring = $param->getDeclaringClass();
            if ($declaring && $declaring->getParentClass()) {
                return $declaring->getParentClass()->getName();
            }
        }
        return $name;
    }
}