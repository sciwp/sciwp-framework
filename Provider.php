<?php
namespace MyPlugin\Sci;

use MyPlugin\Sci\Sci;
use MyPlugin\Sci\Manager\ProviderManager;

defined('ABSPATH') OR exit('No direct script access allowed');

/**
 * Provider class
 *
 * @author		Eduardo Lazaro Rodriguez <eduzroco@gmail.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */
class Provider
{
	use \MyPlugin\Sci\Traits\Sci;

    /** @var array $bindings Class bindings that should be registered. */
    public $bindings;

    /** @var array $singletons Class singletons that should be registered. */
    public $singletons;

	/** @var ProviderManager $providerManager */
	private $providerManager;

	/**
	 * Class constructor
     *
     * @var \MyPlugin\Sci\Manager\ProviderManager $providerManager
	 * @return \MyPlugin\Sci\Provider
	 */
	public function __construct(ProviderManager $providerManager)
	{
		$this->providerManager = $providerManager;
	}

	/**
	 * Add a provider
	 *
	 * @return \MyPlugin\Sci\Provider
	 */
    public static function create()
    {
        return Sci::make(self::class); 
	}
	
	/**
	 * Add the provider to the provider manager
	 *
	 * @param string $name The provider id
	 * @return \MyPlugin\Sci\Provider
	 */
	public function register($name = false)
	{
		if ($name) {
			$this->providerManager->register($this, $name);
		} else {
			$this->providerManager->register($this);
		}
        return $this;
    }

	/**
	 * Executed when the provider is registered
	 */
    public function config()
	{
    }

	/**
	 * Executed when all plugins are loaded
	 */	    
    public function boot()
	{
    }
}