<?php
namespace Wormvc\Wormvc\Services;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Autoloader Class
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */
class Deactivation
{
	use \Wormvc\Wormvc;

	/** @var string $actions Stores a list of the deactivation actions */	
	private $actions = array();

	/**
	 * Class constructor
	 */
	public function __construct(){}    

	/**
	 * Initializes the class
	 *
     * @param string $plugin_file The main plugin file
	 * @return	object
	 */
	public function init($plugin_file)
    {
		register_deactivation_hook( $plugin_file, array($this,'run'));
		return $this;
	}

	/**
	 * Ads a condition for the plugin activation
	 *
	 * @return	object
	 */	
	public function action($name, $callback, $params = false) 
	{
		$action = array('name' => $name, 'callback' => $callback);
		if ($params) $action['params'] = (array) $params;
		$this->actions[] = $action;
		return $this;
	}

	/**
	 * Plugin deactivation
	 */ 
	public function run()
	{
		if (!current_user_can( 'activate_plugins' )) return;
		foreach ($this->actions as $action) {

			$callback = $action['callback'];
			// File inclusion
			if (is_string($callback) && strpos($callback, ".") !== false) {
				include ($callback);
			} else {
				// Instance
				if (is_array($callback)) {
					if (is_object($callback[0]) && is_string($callback[1])) {
						// Instance with parameters
						if ( isset($action['params']) ) {
							call_user_func_array($callback, $action['params']);
						}
						// Instance without parameters
						else {
							call_user_func($callback);
						}
					} else {
						 trigger_error("Invalid instance or instance function for the activation action" . $action['name']. ".", E_USER_ERROR);
					}
				}
				// Functions and static methods
				else {
					call_user_func($callback);
				}
			}
		}
		flush_rewrite_rules();
	}
}