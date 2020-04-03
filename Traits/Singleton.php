<?php
namespace Sci\Traits;

use Sci\Sci;

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Singleton trait
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
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
     * @return void
     */ 
    final private function __clone() {}

    /**
     * Wakeup
     *
     * @return void
     */         
     protected function __wakeup() {}

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
            
            if($constructor->getParameters()) {
                // The class constructor has declared arguments
                foreach ($constructor->getParameters() as $key => $parameter) {
                    if ($parameter->getClass()) {
                        if (isset($args[$key]) && is_array($args[$key])) {
                            $inst_args[] = Sci::make($parameter->getClass()->name, $args[$key]);
                        } else {
                            $inst_args[] = Sci::make($parameter->getClass()->name);
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
        }
        self::$_instances[$called_class]->Sci = Sci::instance();
        return self::$_instances[$called_class];
    }
}