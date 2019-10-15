<?php

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Wormvc run script
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */

include plugin_dir_path( __FILE__ ) . 'Functions/Functions.php';

// Files and vars used in this script
$config_file = plugin_dir_path(dirname(__FILE__)) . 'config.php';
$config_cache_file = plugin_dir_path( dirname(__FILE__) ) . 'cache/config.cache.php';
$plugin_base_folder  = strtolower(basename(plugin_dir_path(dirname( __FILE__ , 1 ))));

/**
 * {Dynamic}ReplaceStringFunction
 * 
 * Replace a string in all files of a folder, dynamic function name to avoid collisions and improve
 * compatibility with bundled wormvc plugins
 *
 * @param $replaceStringFunction The function to process the string
 * @param $folder The folder to search for files
 * @param $old_string The string to be replaced
 * @param $new_string The replacement string
 */
${$plugin_base_folder.'ReplaceStringFunction'} = function($replaceStringFunction, $folder, $old_string, $new_string)
{
    foreach (glob($folder."/*.php") as $filename) {
        $file_content = file_get_contents($filename);
        file_put_contents($filename, strtr($file_content, [$old_string => $new_string]));
    }
    $dirs = array_filter(glob($folder.'/'.'*', GLOB_ONLYDIR));
    foreach($dirs as $dir) {
        $replaceStringFunction($replaceStringFunction, $dir, $old_string, $new_string);
    }
};

/**
 * {Dynamic}ReplaceCoreNamespaceFunction
 * 
 * Replaces the old namespace with the new one inside all the Wormvc files, dynamic function name to avoid
 * collisions and improve compatibility with bundled wormvc plugins
 *
 * @param $replaceStringFunction The function to process the string
 * @param $new_namespace The replacement namespace
 */
${$plugin_base_folder.'ReplaceCoreNamespaceFunction'} = function($replaceStringFunction, $new_namespace) {
    $new_core_namespace = $new_namespace . '\Wormvc';
    $old_core_namespace = NULL;
    $old_namespace = NULL;
    $file_path = plugin_dir_path( __FILE__ ) . 'Traits/Wormvc.php';
    $handle = fopen($file_path, "r") or die('The old namespace in the Wormvc Trait was not found');
    if (!$handle) return;
    while (($line = fgets($handle)) !== false) {
        if (strpos($line, 'namespace') === 0) {
            $parts = preg_split('/\s+/', $line);
            $old_namespace = rtrim(trim($parts[1]), ';');
            $old_core_namespace = $old_namespace . '\Wormvc';
            break;
        }
    }
    fclose($handle);
    $replaceStringFunction($replaceStringFunction, dirname(__FILE__), $old_core_namespace, $new_core_namespace);
    $file_content = file_get_contents(dirname(__FILE__).'/Traits/Wormvc.php');   
    file_put_contents($file_path, strtr($file_content, ['namespace '.$old_namespace.';' => 'namespace '.$new_namespace.';']));
};

// Load config file
if (!file_exists($config_file)) die('Cannot open the config file:  ' . $config_file);
$config = include $config_file;

// Include config cache file and create it if it does not exists
if (!file_exists($config_cache_file)) {
    file_put_contents($config_cache_file, "<?php if ( ! defined( 'ABSPATH' ) ) exit; \n\n".'return array();')
        or die('Cannot create the file:  '.$config_cache_file);
    fclose($handle);
}
$config_cache = include $config_cache_file;

$rebuild = !isset($config['namespace']) || (isset($config['rebuild']) && $config['rebuild'] === true) || $config['namespace'] !== $config_cache['namespace'] ? true : false;

if ($rebuild) {
    $namespace = isset($config['namespace']) && $config['namespace'] ? $config['namespace'] : call_user_func(function() use($plugin_base_folder) {
        // Try to get the namespace from the main.php file
        $handle = fopen(plugin_dir_path( dirname(__FILE__) ) . '/main.php', "r")
                  or die('Cannot open the wormvc plugin main.php file');
        while (($line = fgets($handle)) !== false) {
            if (strpos($line, 'namespace') === 0) {
                $parts = preg_split('/\s+/', $line);
                $namespace = rtrim(trim($parts[1]), ';');
                break;
            }
        }
        fclose($handle);
        // Fallback to the plugin folder name
        return isset($namespace) ? $namespace : ucfirst($plugin_base_folder);
    });

    ${$plugin_base_folder.'ReplaceCoreNamespaceFunction'}(${$plugin_base_folder.'ReplaceStringFunction'}, $namespace);
    $config_cache['namespace'] = $namespace;
    file_put_contents ( plugin_dir_path( dirname(__FILE__) ) . '/cache/config.cache.php', "<?php if ( ! defined( 'ABSPATH' ) ) exit; \n\n".'return ' . var_export( $config_cache , true) . ';');
}

// Require the Autoloader
$namespace = isset($config['namespace']) && $config['namespace'] ? $config['namespace']
             : isset($config_cache['namespace']) && $config_cache['namespace'] ? $config_cache['namespace']
             : ucfirst($plugin_base_folder);

// Start the autoloader and Wormvc
require plugin_dir_path( __FILE__ ) . 'Autoloader.php';

if(class_exists('\\' . $namespace . '\Wormvc\Autoloader')) {

    $autoloader_class = '\\' . $namespace . '\Wormvc\Autoloader';
    $autoloader_class::start();

    $wormvc_class = '\\'.$namespace.'\Wormvc\Wormvc';
    return $wormvc_class::instance()->init(); 

} else throw new Exception('Please rebuild Wormvc.');
