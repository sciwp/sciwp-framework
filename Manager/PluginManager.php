<?php
namespace MyPlugin\Sci\Manager;

defined('WPINC') OR exit('No direct script access allowed');

use \MyPlugin\Sci\Sci;
use \MyPlugin\Sci\Plugin;
use \MyPlugin\Sci\Manager;
use \MyPlugin\Sci\Autoloader;
use \MyPlugin\Sci\Services\Activation as ActivationService;

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
     * @param string|bool $pluginId The plugin id
     * @return Plugin
     */
    public function register($plugin, $pluginId = false)
    {

        if (!$pluginId) $pluginId = str_replace( ' ', '-', strtolower(basename($plugin->getDir())));

        if (isset($this->plugins[$pluginId])) {
            throw new Exception('The plugin with id ' . $pluginId . ' is already registered.');
        }
        
        $this->plugins[$pluginId] = $plugin;

        // Add the plugin to the Autoloader
        $autoload = $plugin->config->get('autoloader/autoload');
        $autoloadData = [
            'namespace' => $plugin->getNamespace(),
            'main_namespace' =>  $plugin->getMainNamespace(),
            'dir' => $plugin->getDir(),
            'main_dir' =>  $plugin->getMainDir(),
            'module_dir' =>  $plugin->getModulesDir(),            
            'cache_enabled' => $plugin->getAutoloaderCacheEnabled(),
            'reflexive' =>  $plugin->config->get('autoloader/reflexive'),
            'autoload' =>  $autoload ? $autoload : [],
        ];

        $this->autoloader::addPlugin($pluginId, $autoloadData);

        if ($providers = $plugin->config->get('providers')) {
            $this->Sci->providerManager()->register((Array) $providers);
        }
        
        return $this->plugins[$pluginId];
    }

    /**
     * Get all plugins
     * 
     * @return Plugin[]
     */
    public function all()
    {
        return $this->plugins;
    }

    /**
     * Get all loaded plugins
     * @param string $id The plugin id
     * @return Plugin
     */	
	public function get($pluginId = false)
	{
        if (!$pluginId) return $this->all();
		return isset($this->plugins[$pluginId]) ? $this->plugins[$pluginId] : false;
	}
}