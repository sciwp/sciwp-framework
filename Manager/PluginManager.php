<?php
namespace Sci\Sci\Manager;

defined('WPINC') OR exit('No direct script access allowed');

use \Sci\Sci\Sci;
use \Sci\Sci\Plugin;
use \Sci\Sci\Manager;
use \Sci\Sci\Autoloader;
use \Sci\Sci\Services\Activation as ActivationService;

/**
 * Plugin Manager
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */
 
class PluginManager extends Manager
{
    /** @var array $plugins Stores a list of the registered plugins */
    private $plugins = array();

     /** @var Autoloader $autoloader Reference to the Autoloader class */
	private $autoloader;
	
	public function __construct()
	{
        $this->autoloader = Autoloader::class;
	}

    /**
     * Load a plugin into the plugin manager
     * 
     * @param string $plugin_file The plugin file path
     * @param string|bool $plugin_id The plugin id
     * @return Plugin
     */
    public function register($plugin)
    {
        $plugin_id = $plugin->getId();

        if (isset($this->plugins[$plugin_id])) {
            throw new Exception('The plugin with id ' . $plugin_id . ' is already registered.');
        }
        
        $this->plugins[$plugin_id] = $plugin;

        $autoload = isset($this->plugins[$plugin_id]->config()['autoload']) ? $this->plugins[$plugin_id]->config()['autoload'] : [];
        // Add the plugin to the Autoloader
        $this->autoloader::addPlugin(
            $plugin_id,
            [
                'namespace' => $plugin->getNamespace(),
                'main_namespace' =>  $plugin->getMainNamespace(),
                'dir' => $plugin->getDir(),
                'main_dir' =>  $plugin->getMainDir(),
                'module_dir' =>  $plugin->getModuleDir(),                
                'cache_enabled' => $plugin->getAutoloaderCacheEnabled(),
                'reflexive' =>  $plugin->config()['autoloader']['reflexive'],
                'autoload' =>  $autoload,
            ]
        );

        $config = $plugin->config();

        // Add the providers to the provider manager
        if (isset($config['providers'])) {
            $this->Sci->providers()->register((Array) $config['providers']);
        }
        
        return $this->plugins[$plugin_id];
    }

    /**
     * Get all plugins
     * 
     * @return Plugin
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * Get all loaded plugins
     * @param string $plugin_id The plugin id
     * @return Plugin
     */	
	public function get($id)
	{
		return isset($this->plugins[$id]) ? $this->plugins[$id] : false;
	}
}