<?php
namespace KNDCC\Wormvc\Manager;

defined('WPINC') OR exit('No direct script access allowed');

use \KNDCC\Wormvc\Plugin;
use \KNDCC\Wormvc\Manager;
use \KNDCC\Wormvc\Autoloader;
use KNDCC\Wormvc\Services\Activation as ActivationService;
use \KNDCC\Wormvc\Traits\Singleton;

/**
 * Plugin Manager
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */
 
class PluginManager extends Manager
{
    use Singleton;

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
    public function add($plugin_file, $plugin_id)
    {
        if (!$plugin_id) $plugin_id = strtolower(basename(plugin_dir_path($plugin_file)));
        $this->plugins[$plugin_id] = $this->wormvc->get('\KNDCC\Wormvc\Plugin', [$plugin_file, $plugin_id]);        
       
        // Add the plugin to the Autoloader
        $this->autoloader::addPlugin(
            $plugin_id,
            array(
                'namespace' => $this->plugins[$plugin_id]->getNamespace(),
                'main_namespace' =>  $this->plugins[$plugin_id]->getMainNamespace(),
                'dir' => $this->plugins[$plugin_id]->getDir(),
                'main_dir' =>  $this->plugins[$plugin_id]->getMainDir(),
                'module_dir' =>  $this->plugins[$plugin_id]->getModuleDir(),                
                'autoloader_cache_enabled' => $this->plugins[$plugin_id]->getAutoloaderCacheEnabled(),
                'config_autoloader_reflexive' =>  $this->plugins[$plugin_id]->config()['autoloader']['reflexive']
            )
        );

 
        $activation_service = $this->wormvc->get(ActivationService::class);
        $this->plugins[$plugin_id]->services()->add('activation', $activation_service);
        
        // Add the providers to the provider manager
        $plugin_config = $this->plugins[$plugin_id]->config();
        if (isset($plugin_config['providers'])) {
            $this->wormvc->providerManager()->add((Array) $plugin_config['providers']);
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