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
    use Singleton;

    /** @var Sci  $_instance The class instance. */  
    private static $_instance;

    /** @var Container $container Stores bindings and creation actions */
    protected $container;    

    /** @var PluginManager $pluginManager Stores a reference to the plugin manager. */
    private $pluginManager;

    /** @var ProviderManager $providerManager Stores a reference to the provider manager. */
    private $providerManager;
    
    /** @var TemplateManager $templateManager Stores a reference to the template manager. */
    private $templateManager;
    
    /** @var RouteManager $routeManager Stores a reference to the route manager. */
    private $routeManager;   

    /**
     * @param PluginManager $pluginManager
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
            self::$_instance->container = Container::instance();
        }
        return self::$_instance;
    }

	/**
	 * Initialize the main components
	 */    
    public function init ()
    {
        $this->pluginManager = self::get(PluginManager::class);
        $this->templateManager = self::get(TemplateManager::class);
        $this->providerManager = self::get(ProviderManager::class);
        $this->routeManager = self::get(RouteManager::class);
        $this->restManager = self::get(RestManager::class);
        return $this;
    }

    /**
     * Get the container
     *
     * @return Container
     */
    public function container()
    {
        return $this->container;
    }

    /**
     * Get a plugin
     * @param string $plugin The plugin id
     * @return Plugin
     */
    public function plugin($plugin_id)
    {
        return $this->pluginManager->get($plugin_id);
    }
    
    /**
     * Get the plugin manager
     *
     * @return PluginManager
     */
    public function plugins()
    {
        return $this->pluginManager;
    }

    /**
     * Get the plugin manager
     *
     * @return PluginManager
     */
    public function pluginManager()
    {
        return $this->pluginManager;
    }

    /**
     * Get the provider manager
     *
     * @return ProviderManager
     */       
    public function providers()
    {
        return $this->providerManager;
    }

    /**
     * Get the template manager
     *
     * @return \MyPlugin\Sci\Manager\TemplateManager
     */    
    public function templateManager()
    {
        return $this->templateManager;
    }

    /**
     * Get the route manager
     *
     * @return RouteManager
     */
    public function router()
    {
        return $this->routeManager;
    }

    /**
     * Get the route manager
     *
     * @return RouteManager
     */
    public function routeManager()
    {
        return $this->routeManager;
    }

    /**
     * Get the rest manager
     *
     * @return RestManager
     */
    public function restManager()
    {
        return $this->restManager;
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
        if ($name === 'make') return self::instance()->container->make(...$arguments);
        if ($name === 'bind') return self::instance()->container->bind(...$arguments);
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
        if ($name === 'make') return self::instance()->container->make(...$arguments);
        if ($name === 'bind') return self::instance()->container->bind(...$arguments);
    }
}