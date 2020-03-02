<?php
namespace MyPlugin\Sci\Manager;

defined('WPINC') OR exit('No direct script access allowed');

use \MyPlugin\Sci\Manager;
use \MyPlugin\Sci\Provider;

/**
 * Provider Manager
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */
 
class ProviderManager extends Manager
{
    /** @var array $providers Stores a list of the registered providers */
    private $providers = array();
	
	/**
	 * Class constructor
     *
     * @return \MyPlugin\Sci\Manager\ProviderManager
	 */
	protected function __construct()
    {
        add_action( 'plugins_loaded', array($this, 'boot'), 1 );
    }

    /**
     * Register Provider into the Provider Manager
     * 
     * @param object|array $providers The plugin file path
     * @return \MyPlugin\Sci\Manager\ProviderManager
     */
    public function register($providers)
    {
        foreach ((array)$providers as $provider) {
            
            if(!is_object($provider)) {
                $provider = $this->Sci::make($provider);
            }

            if (!is_subclass_of($provider, Provider::class)) {
                throw new Exception('Only child classes or instances of the Provider class are accepted.');
            }

            $this->providers[] = $provider;
            $provider->start();
        }
    }

    /**
     * Calls the boot method for all the providers
     * 
     * @return \MyPlugin\Sci\Manager\ProviderManager;
     */
    public function boot()
    {
        foreach ($this->providers as $provider) {
            if ( method_exists ( $provider , 'boot' ) ) {
                $provider->boot();
            }
        }
        return $this;
    }
}