<?php
namespace MyPlugin\Sci\Services;

if ( ! defined( 'ABSPATH' ) ) exit;

use MyPlugin\Sci\Plugin;

/**
 * DeactivationService Class
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */
class DeactivationService
{
	/** @var string $plugin The plugin this service belongs to. */
	private $plugin;

	/** @var string $actions Stores a list of the deactivation actions */	
	private $actions = array();

	/**
	 * Class constructor
	 */
	public function __construct(Plugin $plugin)
	{
		$this->plugin = $plugin;
	}   

	/**
	 * Initializes the class
	 *
     * @param \MyPlugin\Sci\Plugin|string $plugin The plugin/id
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
					} else {
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