<?php
namespace Sci\Support;

use Sci\Sci;
use Sci\Support\Managers\ProviderManager;

defined('ABSPATH') OR exit('No direct script access allowed');

/**
 * Provider
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class Provider
{
	use \Sci\Traits\Sci;

    /** @var array $bindings Class bindings that should be registered. */
    public $bindings;

    /** @var array $singletons Class singletons that should be registered. */
    public $singletons;

	/** @var ProviderManager $providerManager */
	private $providerManager;

	/**
	 * Class constructor
     *
     * @var ProviderManager $providerManager
	 * @return Provider
	 */
	public function __construct(ProviderManager $providerManager)
	{
		$this->providerManager = $providerManager;
	}

	/**
	 * Add a provider
	 *
	 * @return Provider
	 */
    public static function create()
    {
        return Sci::make(self::class); 
	}
	
	/**
	 * Add the provider to the provider manager
	 *
	 * @param string $name The provider id
	 * @return Provider
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
    public function config() {
    }

	/**
	 * Executed when all plugins are loaded
	 */	    
    public function boot() {
    }
}