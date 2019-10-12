<?php
namespace KNDCC\Wormvc\Manager;

defined('WPINC') OR exit('No direct script access allowed');

use \KNDCC\Wormvc\Manager;
use \KNDCC\Wormvc\Traits\Singleton;

/**
 * Provider Manager
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */
 
class ProviderManager extends Manager
{
    use Singleton;

    /** @var array $providers Stores a list of the registered providers */
    private $providers = array();
	
    /**
     * @param Autoloader $autoloader
     */    
	private function __construct()
    {
        add_action( 'plugins_loaded', array($this, 'runProvidersBoot'), 1 );
    }

    /**
     * Load a provider to the provider manager
     * 
     * @param string $plugin_file The plugin file path
     * @param string|bool $plugin_id The plugin id
     * @return Plugin
     */
    public function add( $provider_classes )
    {
        foreach ( (array)$provider_classes as $provider_class) {
            $provider = $this->wormvc->get($provider_class);
            if ( method_exists ( $provider , 'register' ) ) {
                $provider->register();
            }               
            $this->providers[] = $provider;
        }
    }

    /**
     * Get all providers
     * 
     * @return Plugin
     */
    public function all()
    {
        return $this->providers;
    }
    
    /**
     * Calls the boot method for all the providers
     * 
     * @return Plugin
     */
    public function runProvidersBoot()
    {
        foreach ($this->providers as $provider) {
            if ( method_exists ( $provider , 'boot' ) ) {
                $provider->boot();
            }
        }
        return $this;
    }
}