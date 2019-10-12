<?php
include plugin_dir_path( __FILE__ ) . 'Functions/Functions.php';

/**
 * Wormvc run script
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.me>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */

// Load configuration
$config = include plugin_dir_path( dirname(__FILE__) ) . 'config.php';
$config_cache = include plugin_dir_path( dirname(__FILE__) ) . 'cache/config.cache.php';

//Get the base folder name
$base_folder  = strtolower(basename( plugin_dir_path(  dirname( __FILE__ , 1 ) ) ));

// Set the namespace and default to the base folder name
$namespace = isset($config['namespace']) && $config['namespace'] ? $config['namespace']
    : isset($config_cache['namespace']) && $config_cache['namespace'] ? $config_cache['namespace']
    : ucfirst($base_folder);

// Require the Autoloader
require plugin_dir_path( __FILE__ ) . 'Autoloader.php';

// Check if we need to update the namespace in the wormvc files
if (!class_exists( '\\' . $namespace . '\Wormvc\Autoloader') ) {
    $namespace = isset($config['namespace']) && $config['namespace'] ? $config['namespace'] : false;
    if(!$namespace) {
        $file_path = plugin_dir_path( dirname(__FILE__) ) . '/main.php';
        $handle = fopen($file_path, "r");
        if (!$handle) return;
        while (($line = fgets($handle)) !== false) {
            if (strpos($line, 'namespace') === 0) {
                $parts = preg_split('/\s+/', $line);
                $namespace = rtrim(trim($parts[1]), ';');
                break;
            }
        }
        fclose($handle);
        if (!$namespace) $namespace = ucfirst($base_folder);
    }

    // Dynamic name function to prevent overlapping with other plugins embedding Wormvc
    ${$base_folder.'ReplaceStringFunction'} = function($replaceStringFunction, $folder, $old_namespace, $new_namespace)
    {
        foreach (glob($folder."/*.php") as $filename) {
            $file_content = file_get_contents($filename);
            file_put_contents($filename, strtr($file_content, [$old_namespace => $new_namespace]));
        }
        $dirs = array_filter(glob($folder.'/'.'*', GLOB_ONLYDIR));
        foreach($dirs as $dir) {
            $replaceStringFunction($replaceStringFunction, $dir, $old_namespace, $new_namespace);
        }
    };

    // Dynamic name function to prevent overlapping with other plugins using embedding Wormvc
    ${$base_folder.'ReplaceCoreNamespaceFunction'} = function($replaceStringFunction , $base_folder, $new_namespace) {
        $new_core_namespace = $new_namespace . '\Wormvc';
        $old_core_namespace = NULL;
        $file_path = plugin_dir_path( __FILE__ ) . 'Autoloader.php';
        $handle = fopen($file_path, "r");
        if (!$handle) return;
        while (($line = fgets($handle)) !== false) {
            if (strpos($line, 'namespace') === 0) {
                $parts = preg_split('/\s+/', $line);
                $old_core_namespace = rtrim(trim($parts[1]), ';');
                break;
            }
        }
        fclose($handle);
        $replaceStringFunction($replaceStringFunction, dirname(__FILE__), $old_core_namespace, $new_core_namespace);
    };

    ${$base_folder.'ReplaceCoreNamespaceFunction'}(${$base_folder.'ReplaceStringFunction'}, $base_folder, $namespace);
    $config_cache['namespace'] = $namespace;
    file_put_contents ( plugin_dir_path( dirname(__FILE__) ) . '/cache/config.cache.php', "<?php if ( ! defined( 'ABSPATH' ) ) exit; \n\n".'return ' . var_export( $config_cache , true) . ';');
    // Require the Autoloader
    require plugin_dir_path( __FILE__ ) . 'Autoloader.php';
}

// Start the autoloader
$autoloader_class = '\\' . $namespace . '\Wormvc\Autoloader';
$autoloader_class::start();

// Initialize Wormvc
$wormvc_class = '\\'.$namespace.'\Wormvc\Wormvc';
$wormvc_class::instance()->init();