<?php
namespace Sci\Plugin\Services;

defined('ABSPATH') OR exit('No direct script access allowed');

use Sci\Plugin\Plugin;

/**
 * Activation Service
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class ActivationService
{
	/** @var string $plugin The plugin this service belongs to. */
	private $plugin;

    /** @var string $checks Stores a list of the activation checks */
	public $checks = array();

	/** @var string $actions Stores a list of the activation actions */	
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
     * @param Plugin|string $plugin The plugin/id
	 * @return self
	 */	
	public function configure()
    {        
		register_activation_hook($this->plugin->getFile(), array($this,'run'));
		return $this;
	}

	/**
	 * Add a check to the plugin activation
	 *
	 * @param string $name The condition name
     * @param string $callback The callback function     
     * @param array $params The function parameters     
	 * @return self
	 */	
	public function addCheck($name, $callback, $params = false) 
	{
		$check = array('name' => $name, 'callback' => $callback);
		if ($params) {
            $check['params'] = (array) $params;
        }
		$this->checks[] = $check; 
		return $this;
	}

	/**
	 * Ads an action to execute on the plugin activation
	 * 
	 * @param string $name The action name
     * @param mixed $name The action function
     * @param array $params The function parameters
	 * @return self
	 */	
	public function addAction($name, $callback, $params = false) 
	{
		$action = array('name' => $name, 'callback' => $callback);
		if ($params) {
            $action['params'] = (array) $params;
		}
        $this->actions[] = $action;
		return $this;
	}

	/**
	 * Plugin activation
	 * 
	 * @return void
	 */ 
	public function run()
	{
		global $wp_version;

		$config = $this->plugin->config()->get('activation');
		$requirements = true;
		$message = "";

		if (isset($config['php'])) {
			if (!is_array($config['php']) && $config['php']) {
				if ( version_compare( PHP_VERSION, $config['php'], '<' ) ) {
					$requirements = false;
					$message .= '<p>'.sprintf('The %1$s plugin requires PHP version %2$s or greater.', '<strong>' . $this->plugin->getName() . '</strong>', $config['php']).'</p>';
				}
			} else if (isset($config['php']['enabled']) && $config['php']['enabled']) {
				if ( version_compare( PHP_VERSION, $config['php']['version'], '<' ) ) {
					$requirements = false;
					if (isset($config['php']['message'])) {
						$error = $config['php']['message'];
					} else {
						$error = 'The %1$s plugin requires the PHP version %2$s or greater. Please make sure it is installed and try again.';
					}					
					$message .= '<p>'.sprintf($error, '<strong>' . $this->plugin->getName() . '</strong>', $config['php']['version']).'</p>';
				}
			}
		}

		if (isset($config['wordpress'])) {
			if (!is_array($config['wordpress']) && $config['wordpress']) {
				if (version_compare($wp_version, $config['wordpress'], '<' )) {
					$requirements = false;
					$message .= '<p>'.sprintf('The %1$s plugin requires WordPress version %2$s or greater.', '<strong>' . $this->plugin->getName() . '</strong>', $config['wordpress']).'</p>';
				}
			} else if (isset($config['wordpress']['enabled']) && $config['wordpress']['enabled']) {
				if (version_compare($wp_version, $config['wordpress']['version'], '<' )) {
					$requirements = false;
					if (isset($config['wordpress']['message'])) {
						$error = $config['wordpress']['message'];
					} else {
						$error = 'The %1$s plugin requires the plugin %2$s. Please make sure it is installed and try again.';
					}
					$message .= '<p>'.sprintf($error, '<strong>' . $this->plugin->getName() .'</strong>', $config['wordpress']['version']).'</p>';
				}
			}
		}

		if (isset($config['plugins'])) {
            
            if (!is_array($config['plugins'])) {
                $config['plugins'] = [$config['plugins']];
            }
            
            if (is_array($config['plugins']) && count($config['plugins'])) {
            
                $active_plugins = get_option('active_plugins');
                foreach($active_plugins  as $key => $plugin) {
                    $plugin_arr = explode('/', trim($plugin,'/'));
                    $active_plugins[$key] = is_array($plugin_arr) ? $plugin_arr[0] : trim($plugin);
                }

                foreach ((array) $config['plugins'] as $key => $requiredPlugin) {

                    if (!is_array($requiredPlugin)) {
                        if (!in_array($requiredPlugin, $active_plugins)) {
                            $requirements = false;
                            $error = 'The %1$s plugin requires the plugin %2$s. Please make sure it is installed and enabled and try again.';
                            $message .= '<p>'.sprintf($error, '<strong>'. $this->plugin->getName() .'</strong>', '<strong>'.$requiredPlugin.'</strong>').'</p>';
                        }
                    } else {
                        if (!in_array($key, $active_plugins)) {
                            $requirements = false;
                            if (isset($requiredPlugin['message'])) {
                                $error = $requiredPlugin['message'];
                            } else {
                                $error = 'The %1$s plugin requires the plugin %2$s. Please make sure it is installed and enabled and try again.';
                            }					
                            $name = isset($requiredPlugin['name']) ? $requiredPlugin['name'] : $key;
                            $message .= '<p>'.sprintf($error, '<strong>' . $this->plugin->getName() . '</strong>', '<strong>'.$name.'</strong>').'</p>';
                        }
                    }
                }
            }
		}

		foreach ($this->checks as $check) {
			$callback = $check['callback'];
			if (is_string($callback) && strpos($callback, ".") !== false) {
				// File inclusion
				$result = include ($callback);
			} else {
				if (is_array($callback)) {
					// Instance
					if (is_object($callback[0]) && is_string($callback[1])) {
						if ( isset($check['params']) ) {
							// Instance with parameters
							$result = call_user_func_array($callback, $check['params']);
						} else {
							// Instance without parameters
							$result = call_user_func($callback);
						}
					}
					else {
						trigger_error("Invalid instance or instance function for the activation check" . $check['name']. ".", E_USER_ERROR);
					}
				} else {
					// Functions and static methods
					if ( isset($check['params']) ) {
						// Instance with parameters
						$result = call_user_func_array($callback, $check['params']);
					} else {
						// Instance without parameters
						$result = call_user_func($callback);
					}
				}
			}
			if ( $result !== true ) { 
				$requirements = false;
				$message .= '<p>'.$result.'</p>';
			}
		}

		if (!$requirements) {
			deactivate_plugins( plugin_basename( __FILE__ ) ) ;
			wp_die($message,'Plugin Activation Error',  array( 'response'=>200, 'back_link'=>TRUE ) );
		}

		foreach ($this->actions as $action) {
			$callback = $action['callback'];
			// File inclusion
			if (is_string($callback) && strpos($callback, ".") !== false) {
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