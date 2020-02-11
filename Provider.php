<?php
namespace MyPlugin\Sci;

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
    /** @var array $bindings Class bindings that should be registered. */
    public $bindings;

    /** @var array $singletons Class singletons that should be registered. */
    public $singletons;

	/**
	 * Register elements in the Sci container
	 */			
	public function register()
	{
	}

	/**
	 * Executed when all plugins are loaded
	 */	    
    public function boot()
	{ 
    }   
}