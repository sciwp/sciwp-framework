<?php

namespace Sci;

use Exception;
use Sci\Plugins\Plugin;
use Sci\Plugin\Managers\PluginManager;

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Core Sci class
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class Sci
{   
    /** @var Sci $_instance The class instance. */  
    protected static $_instance;
    
    /** @var string $file The main plugin file */
    protected static $file;
    
    /** @var string[] $requirements */
    protected static $requirements = [
        'PHP' => '7.2',
        'WP' => '5.0',
    ];
    
    /** @var PluginManager[] $plugiManager Stores references to PluginManager. */
    private $pluginManager;

    /** @var Container $container Stores bindings and creation actions */
    protected $container;

	/**
	 * Returns a unique instance or creates a new one
	 *
	 * @return	Sci
	 */
    public static function instance ()
    {
        if (!isset( self::$_instance)) {
            self::$_instance = new Sci;
            self::$_instance->container = Container::instance();
        }
        return self::$_instance;
    }

	/**
	 * Create a new Sci instance
	 *
     * @param string $file
     * @param string $name
	 * @return Sci
	 */
    public static function create($file, $name = null)
    {
        self::$file = $file;
        register_activation_hook( self::$file, [self::class, 'activation']);

        try {
            $sci = self::instance();
            $sci->pluginManager = self::make(PluginManager::class);
            add_action( 'activated_plugin', [self::class, 'loadFirst']);

            // Register the current plugin so it uses the framework
            if ($name !== null ) Plugin::create($file)->register($name);

        } catch (Exception $e) {
            wp_die($e->getMessage(),'Plugin Activation Error',  array( 'response'=>200, 'back_link'=>TRUE ) );
        }

        return $sci;
    }
    
	/**
	 * Check PHP and WP core requirements
	 *
	 * @return void
	 */
    public static function activation()
    {
        global $wp_version;
  
        $flags = [];
        $errorText = '';

        if ( version_compare( PHP_VERSION, self::$requirements['PHP'], '<' ) ) {
            $flags[] = 'PHP';
            $errorText .= '<p><strong>SCIWP Framework</strong> plugin requires PHP version '.self::$requirements['PHP'].' or greater.</p>';
        }

        if (version_compare($wp_version,  self::$requirements['WP'], '<' )) { 
            $flags[] = 'WordPress';
            $errorText .= '<p><strong>SCIWP Framework</strong> plugin requires WordPress version '.self::$requirements['WP'].' or greater.</p>';
        }

        if (!count($flags)) return;
        
        deactivate_plugins(self::$file);
        wp_die($errorText,'Plugin Activation Error',  array( 'response'=>200, 'back_link'=>TRUE ) );
    }

	/**
	 * This plugin should have priority loading
	 *
	 * @return void
	 */
    public static function loadFirst ()
    {
        $file = basename(dirname(dirname(__FILE__) )) . '/main.php';
        $plugins = get_option( 'active_plugins' );
        if (!count($plugins)) return;

        if ( $key = array_search( $file, $plugins ) ) {
            array_splice( $plugins, $key, 1 );
            array_unshift( $plugins, $file );
            update_option( 'active_plugins', $plugins );
        } 
    }

    /**
     * Get the container
     *
     * @return Container
     */
    public function container()
    {
        return $this->container;
    }

    /**
     * Get a plugin
     * @param string $plugin The plugin id
     * @return Plugin
     */
    public function plugin($pluginId)
    {
        return $this->pluginManager->get($pluginId);
    }
    
    /**
     * Get all plugin
     *
     * @return Plugin
     */

    public function plugins()
    {
        return $this->pluginManager->all();
    }

    /**
     * This magic method allows to use the get method both statically and within an instance
     * 
     * @param string $name The function name
     * @param array $arguments The function a arguments
     */
    public function __call($name, $arguments)
    {
        if ($name === 'make') return self::instance()->container->make(...$arguments);
        if ($name === 'bind') return self::instance()->container->bind(...$arguments);
        if ($name === 'alias') return self::instance()->container->alias(...$arguments);
        if ($name === 'created') return self::instance()->container->created(...$arguments);
    }

    /**
     * This magic method allows to use the get method both statically and within an instance
     * 
     * @param string $name The function name
     * @param array $arguments The function a arguments
     */
    public static function __callStatic($name, $arguments)
    {
        if ($name === 'make') return self::instance()->container->make(...$arguments);
        if ($name === 'bind') return self::instance()->container->bind(...$arguments);
        if ($name === 'alias') return self::instance()->container->alias(...$arguments);
        if ($name === 'created') return self::instance()->container->created(...$arguments);
    }
}