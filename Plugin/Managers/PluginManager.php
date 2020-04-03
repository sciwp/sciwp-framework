<?php
namespace Sci\Plugin\Managers;

defined('WPINC') OR exit('No direct script access allowed');

use Exception;
use Sci\Sci;
use Sci\Manager;
use Sci\Autoloader;
use Sci\Plugin\Plugin;
use Sci\Support\Managers\ProviderManager;

/**
 * Plugin Manager
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class PluginManager extends Manager
{
    /** @var array $plugins Stores a list of the registered plugins */
    private $plugins = array();

     /** @var $autoloader Reference to the Autoloader class */
	private $autoloader;
    
    /** @var string $mainPluginKey Stores the main plugin key */
    private $mainPluginKey = false;

	public function __construct(ProviderManager $providerManager)
	{
        parent::__construct();
        $this->providerManager = $providerManager;
        $this->autoloader = Autoloader::class;
	}

    /**
     * Load a plugin into the plugin manager
     * 
     * @param string $plugin_file The plugin file path
     * @param string|bool $pluginId The plugin id
     * @return Plugin
     */
    public function register($plugin, $pluginId = false, $addon = false)
    {

        if (!$pluginId) $pluginId = str_replace( ' ', '-', strtolower(basename($plugin->getDir())));

        if (isset($this->plugins[$pluginId])) {
            throw new Exception('The plugin with id ' . $pluginId . ' is already registered.');
        }
        
        $this->plugins[$pluginId] = $plugin;

        if (!$addon) $this->mainPluginKey = $pluginId;

        // Add the plugin to the Autoloader
        $autoload = $plugin->config->get('autoloader/autoload');
        $autoloadData = [
            'namespace' => $plugin->getNamespace(),
            'dir' => $plugin->getDir(),
            'main_dir' =>  $plugin->getMainDir(),
            'module_dir' =>  $plugin->getModulesDir(),            
            'cache_enabled' => $plugin->getAutoloaderCacheEnabled(),
            'reflexive' =>  $plugin->config->get('autoloader/reflexive'),
            'autoload' =>  $autoload ? $autoload : [],
        ];

        $this->autoloader::addPlugin($pluginId, $autoloadData);

        if ($providers = $plugin->config->get('providers')) {
            $this->providerManager->register((Array) $providers);
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
	public function get($pluginId)
	{
        if (!isset($this->plugins[$pluginId])) {
            throw new Exception('The plugin ' . $pluginId . ' is not registered.');
        }
		return $this->plugins[$pluginId];
    }
    
    /**
     * Get the main plugin
     * 
     * @return Plugin
     */
    public function getMain()
    {
        return $this->plugins[$this->mainPluginKey];
    }
}