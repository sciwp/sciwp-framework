<?php
namespace Sci\Plugin\Services;

if ( ! defined( 'ABSPATH' ) ) exit;

use Sci\Plugin\Plugin;

/**
 * Deactivation Service
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class DeactivationService
{
	/** @var string $plugin The plugin this service belongs to. */
	private $plugin;

	/** @var string $key The plugin key */
	private $key;

	/** @var string $actions Stores a list of the deactivation actions */	
	private $actions = array();

	/**
	 * Class constructor
	 */
	public function __construct($key, Plugin $plugin)
	{
		$this->key = $key;
		$this->plugin = $plugin;
	}   

	/**
	 * Initializes the class
	 *
     * @param Plugin|string $plugin The plugin/id
	 * @return self
	 */
	public function configure()
    {
		register_deactivation_hook($this->plugin->getFile(), array($this,'run'));
		return $this;
	}

	/**
	 * Ads an action to execute on the plugin deactivation
	 * 
	 * @param string $name The action name
     * @param mixed $name The action function
     * @param array $params The function parameters
	 * @return self
	 */	
	public function addAction($name, $callback, $params = false) 
	{
		$action = array('name' => $name, 'callback' => $callback);

		if ($params) $action['params'] = (array) $params;
		$this->actions[] = $action;

		return $this;
	}

	/**
	 * Plugin deactivation
	 * 
	 * @return void
	 */ 
	public function run()
	{
		if (!current_user_can( 'activate_plugins' )) return;

		foreach ($this->actions as $action) {

			$callback = $action['callback'];

			if (is_string($callback) && strpos($callback, ".") !== false) {
				// File inclusion
				include ($callback);
			} else {
				// Instance
				if (is_array($callback)) {
					if (is_object($callback[0]) && is_string($callback[1])) {
						if ( isset($action['params']) ) {
							// Instance with parameters
							call_user_func_array($callback, $action['params']);
						} else {
							// Instance without parameters
							call_user_func($callback);
						}
					}
					else if (is_string($callback[0]) && class_exists($callback[0]) && is_string($callback[1])) {
						$instance = $this->sci::make($callback[0]);
						if ( isset($check['params']) ) {
							// Instance with parameters
							call_user_func_array([$instance, $callback[1]], $check['params']);
						} else {
							// Instance without parameters
							call_user_func([$instance, $callback[1]]);
						}
					}
					else {
						 trigger_error("Invalid instance or instance function for the activation action" . $action['name']. ".", E_USER_ERROR);
					}
				} else {
					// Functions and static methods
					if ( isset($action['params']) ) {
						// Function with parameters
						$result = call_user_func_array($callback, $action['params']);
					} else {
						// Function without parameters
						$result = call_user_func($callback);
					}
				}
			}
		}
		flush_rewrite_rules();
	}
}