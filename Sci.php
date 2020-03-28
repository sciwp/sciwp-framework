<?php

namespace MyPlugin\Sci;

use \MyPlugin\Sci\Manager;
use \MyPlugin\Sci\Manager\PluginManager;
use \MyPlugin\Sci\Manager\TemplateManager;
use \MyPlugin\Sci\Manager\ProviderManager;
use \MyPlugin\Sci\Manager\RouteManager;
use \MyPlugin\Sci\Manager\RestManager;
use \MyPlugin\Sci\Manager\ScriptManager;
use \MyPlugin\Sci\Manager\StyleManager;
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
    protected static $_instance;

    /** @var Manager[] $managers Stores references to managers. */
    private $managers = [];

    /** @var Container $container Stores bindings and creation actions */
    protected $container; 

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
        $this->pluginManager = self::make(PluginManager::class);

        $this->managers['plugin']   =   self::make(TemplateManager::class);
        $this->managers['template'] =   self::make(TemplateManager::class);
        $this->managers['provider'] =   self::make(ProviderManager::class);
        $this->managers['route']    =   self::make(RouteManager::class);
        $this->managers['rest']     =   self::make(RestManager::class);
        $this->managers['script']   =   self::make(ScriptManager::class);
        $this->managers['style']    =   self::make(StyleManager::class);
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
     * Get the requested manager
     *
     * @param string $name The manager name
     * @return Manager
     */
    public function manager($name)
    {
        if (!isset($this->managers[$name])) {
            throw new Exception('The manager ' . $name . ' is not configured.');
        }
        return $this->managers[$name];
    }

    /**
     * Get all the managers
     *
     * @return Manager[]
     */
    public function managers()
    {
        return $this->managers;
    }

    /**
     * Get a plugin
     * @param string $plugin The plugin id
     * @return Plugin
     */
    public function plugin($pluginId)
    {
        return $this->managers['plugin']->get($pluginId);
    }
    
    /**
     * Get the plugin manager
     *
     * @return PluginManager
     */
    public function plugins()
    {
        return $this->managers['plugin'];
    }

    /**
     * Get the plugin manager
     *
     * @return PluginManager
     */
    public function pluginManager()
    {
        return $this->managers['plugin'];
    }

    /**
     * Get the provider manager
     *
     * @return ProviderManager
     */       
    public function providerManager()
    {
        return $this->managers['provider'];
    }

    /**
     * Get the provider manager
     *
     * @return ProviderManager
     */       
    public function providers()
    {
        return $this->managers['provider'];
    }

    /**
     * Get the template manager
     *
     * @return \MyPlugin\Sci\Manager\TemplateManager
     */    
    public function templateManager()
    {
        return $this->managers['template'];
    }

    /**
     * Get the route manager
     *
     * @return RouteManager
     */
    public function routeManager()
    {
        return $this->managers['route'];
    }

    /**
     * Get the rest manager
     *
     * @return RestManager
     */
    public function restManager()
    {
        return $this->managers['rest'];
    }

    /**
     * Get the style manager
     *
     * @return StyleManager
     */
    public function styleManager()
    {
        return $this->managers['style'];
    }

    /**
     * Get the script manager
     *
     * @return ScriptManager
     */
    public function scriptManager()
    {
        $this->managers['script'];
    }

    /**
     * This magic method allows to use the get method both statically and within an instance
     * 
     * @param string $name The function name
     * @param array $arguments The function a arguments
     */
    public function __call($name, $arguments)
    {
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
        if ($name === 'make') return self::instance()->container->make(...$arguments);
        if ($name === 'bind') return self::instance()->container->bind(...$arguments);
    }
}