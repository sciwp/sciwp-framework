<?php
namespace Wormvc\Wormvc\Services;

defined('ABSPATH') OR exit('No direct script access allowed');

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
class Activation
{
    /** @var string $plugin_name The plugin name */
    private $plugin_name;    
    
    /** @var string $plugin_file The main plugin file. */
    private $plugin_file;

	/** @var string $checks Stores a list of the activation checks */	
	private $config;

	private $checks = array();
	/** @var string $checks Stores a list of the activation checks */	

	/** @var string $actions Stores a list of the activation actions */	
	private $actions = array();

	/**
	 * Class constructor
	 */
	public function __construct(){}

	/**
	 * Initializes the class
	 *
     * @param string $plugin_id The plugin ID
     * @param string $plugin_file The main plugin file
     * @param array $config The plugin activation config array
	 * @return	object
	 */	
	public function init($plugin_name, $plugin_file, $config_array)
    {
        $this->plugin_name = $plugin_name;
        $this->plugin_file = $plugin_file;
        $this->config = $config_array;
		register_activation_hook( $plugin_file, array($this,'run'));
		return $this;
	}

	/**
	 * Add a check to the plugin activation
	 *
	 * @param string $name The condition name
     * @param string $callback The callback function     
     * @param array $params The function parameters     
	 * @return	object
	 */	
	public function addCheck($name, $callback, $params = false) 
	{
		$check = array('name' => $name, 'callback' => $callback);
		if ($params) $check['params'] = (array) $params;
		$this->checks[] = $check;
		return $this;
	}

	/**
	 * Ads an action to execute on the plugin activation
	 * 
	 * @param string $name The action name
     * @param mixed $name The action function
     * @param array $params The function parameters
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
	 * Plugin activation
	 * 
	 * @since 1.0.0
	 */ 
	public function run()
	{
		global $wp_version;
		$requirements=true;
		$message="";
		if (isset($this->config['check_php'])) {
			if (!is_array($this->config['check_php']) && $this->config['check_php']) {
				if ( version_compare( PHP_VERSION, $this->config['check_php'], '<' ) ) {
					$requirements = false;
					$message .= '<p>'.sprintf('The %1$s plugin requires PHP version %2$s or greater.', '<strong>'. $this->plugin_name.'</strong>', $this->config['check_php']).'</p>';
				}
			}
			else if (isset($this->config['check_php']['enabled']) && $this->config['check_php']['enabled']) {
				if ( version_compare( PHP_VERSION, $this->config['check_php']['version'], '<' ) ) {
					$requirements = false;
					if (isset($this->config['check_php']['error'])) {
						$error = $this->config['check_php']['error'];
					}
					else {
						$error = 'The %1$s plugin requires the plugin %2$s. Please make sure it is installed and enabled and try again.';
					}					
					$message .= '<p>'.sprintf($error, '<strong>'. $this->plugin_name.'</strong>', $this->config['check_php']['version']).'</p>';
				}
			}
		}
		if (isset($this->config['check_wordpress'])) {
			if (!is_array($this->config['check_wordpress']) && $this->config['check_wordpress']) {
				if (version_compare($wp_version, $this->config['check_wordpress'], '<' )) {
					$requirements = false;
					$message .= '<p>'.sprintf('The %1$s plugin requires WordPress version %2$s or greater.', '<strong>'. $this->plugin_name.'</strong>', $this->config['check_wordpress']).'</p>';
				}
			}
			else if (isset($this->config['check_wordpress']['enabled']) && $this->config['check_wordpress']['enabled']) {
				if (version_compare($wp_version, $this->config['check_wordpress']['version'], '<' )) {
					$requirements = false;
					if (isset($this->config['check_wordpress']['error'])) {
						$error = $this->config['check_wordpress']['error'];
					}
					else {
						$error = 'The %1$s plugin requires the plugin %2$s. Please make sure it is installed and enabled and try again.';
					}
					$message .= '<p>'.sprintf($error, '<strong>'. $this->plugin_name.'</strong>', $this->config['check_wordpress']['version']).'</p>';
				}
			}
		}
		if (isset($this->config['check_plugins']) && count($this->config['check_plugins'])) {
			$active_plugins = get_option('active_plugins');
			foreach($active_plugins  as $key => $plugin) {
				$plugin_arr = explode('/', trim($plugin,'/'));
				$active_plugins[$key] = is_array($plugin_arr) ? $plugin_arr[0] : trim($plugin);
			}			
			if (is_array($this->config['check_plugins']) && !isset($this->config['check_plugins']['enabled'])) {
				foreach ( (array) $this->config['check_plugins'] as $key => $name) {
					if (is_numeric($key)) $key = $name;
					if (!in_array($key, $active_plugins)) {
						$requirements = false;
						$name = isset($name) ? $name : $key;
						$message .= '<p>'.sprintf('The %1$s plugin requires the plugin %2$s. Please make sure it is installed and enabled and try again.', '<strong>'. $this->plugin_name.'</strong>', '<strong>'.$name.'</strong>').'</p>';
					}
				}
			}
			else if (isset($this->config['check_plugins']['enabled']) && $this->config['check_plugins']['enabled']) {
				if ( isset($this->config['check_plugins']['plugins']) && count($this->config['check_plugins']['plugins']) ) {
					if (isset($this->config['check_plugins']['error'])) {
						$error = $this->config['check_plugins']['error'];
					}
					else {
						$error = 'The %1$s plugin requires the plugin %2$s. Please make sure it is installed and enabled and try again.';
					}					
					foreach ( (array) $this->config['check_plugins']['plugins'] as $key => $name) {
						if (is_numeric($key)) $key = $name;
						if (!in_array($key, $active_plugins)) {
							$requirements = false;
							$name = isset($name) ? $name : $key;
							$message .= '<p>'.sprintf($error, '<strong>'. $this->plugin_name.'</strong>', '<strong>'.$name.'</strong>').'</p>';
						}
					}
				}
			}
		}
		foreach ($this->checks as $check) {
			$callback = $check['callback'];
			
            // File inclusion
			if (is_string($callback) && strpos($callback, ".") !== false) {
				$result = include ($callback);
			} 
			else {
				// Instance
				if (is_array($callback)) {
					if (is_object($callback[0]) && is_string($callback[1])) {
						// Instance with parameters
						if ( isset($check['params']) ) {
							$result = call_user_func_array($callback, $check['params']);
						}
						// Instance without parameters
						else {
							$result = call_user_func($callback);
						}
					}
					else {
						 trigger_error("Invalid instance or instance function for the activation check" . $check['name']. ".", E_USER_ERROR);
					}
				}
				// Functions and static methods
				else {
					$result = call_user_func($callback);
				}
			}
			if ( $result !== true ) {
				$requirements = false;
				$message .= '<p>'.$result.'</p>';
			}
		}
		if(!$requirements) {
			deactivate_plugins( plugin_basename( __FILE__ ) ) ;
			wp_die($message,'Plugin Activation Error',  array( 'response'=>200, 'back_link'=>TRUE ) );
		}
		foreach ($this->actions as $action) {

			$callback = $action['callback'];
			// File inclusion
			if (is_string($callback) && strpos($callback, ".") !== false) {
				include ($callback);
			} 
			else {
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
					}
					else {
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