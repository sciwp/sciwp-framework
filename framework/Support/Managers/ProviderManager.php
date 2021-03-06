<?php
namespace Sci\Support\Managers;

defined('WPINC') OR exit('No direct script access allowed');

use Exception;
use Sci\Manager;
use Sci\Support\Provider;

/**
 * Provider Manager
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class ProviderManager extends Manager
{
    /** @var array $providers Stores a list of the registered providers */
    private $providers = array();
	
	/**
	 * Class constructor
	 */
	protected function __construct()
    {
        parent::__construct();
        add_action( 'plugins_loaded', array($this, 'boot'), 1 );
    }

    /**
     * Register Provider into the Provider Manager
     * 
     * @param object|array $providers The plugin file path
     * @return ProviderManager
     */
    public function register($providers)
    {
        foreach ((array)$providers as $provider) {
            
            if (!is_object($provider)) {
                $provider = $this->sci::make($provider);
            }

            if (!is_subclass_of($provider, Provider::class)) {
                throw new Exception('Only child classes or instances of the Provider class are accepted as providers.');
            }

            $this->providers[] = $provider;

            if ( method_exists ( $provider , 'registered' ) ) {
                $provider->registered();
            }
        }

        return $this;
    }

    /**
     * Calls the boot method for all the providers
     * 
     * @return ProviderManager
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