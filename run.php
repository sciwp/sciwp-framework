<?php

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Sci run script
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	Eduardo Lázaro Rodríguez
 * @license		http://opensource.org/licenses/MIT MIT License
 * @version     1.0.0
 * @link		https://www.sciwp.com 
 * @since		Version 1.0.0 
 */

// Files and vars used in this script
$configFile = plugin_dir_path(dirname(__FILE__)) . 'config.php';

$cacheDir = plugin_dir_path( dirname(__FILE__) ) . 'cache/';
$configCacheFile = $cacheDir . 'config.cache.php';

$pluginFolder = basename(plugin_dir_path(dirname( __FILE__ , 1 )));
$parsedPluginFolder = preg_replace("/[^a-z0-9]/", '', strtolower($pluginFolder));

/**
 * {Dynamic}ReplaceStringFunction
 * 
 * Replace a string in all files of a folder, dynamic function name to avoid collisions and improve
 * compatibility with bundled Sci plugins
 *
 * @param $replaceStringFunction The function to process the string
 * @param $folder The folder to search for files
 * @param $old_string The string to be replaced
 * @param $new_string The replacement string
 */
${$parsedPluginFolder.'ReplaceStringFunction'} = function($replaceStringFunction, $folder, $old_string, $new_string)
{
    foreach (glob($folder."/*.php") as $filename) {
        $file_content = file_get_contents($filename);
        file_put_contents($filename, strtr($file_content, [$old_string => $new_string]));
    }
    $dirs = array_filter(glob($folder.'/'.'*', GLOB_ONLYDIR));
    foreach($dirs as $dir) {
        echo($dir . " ". $old_string . " - ". $new_string);
        $replaceStringFunction($replaceStringFunction, $dir, $old_string, $new_string);
    }
};

/**
 * {Dynamic}ReplaceCoreNamespaceFunction
 * 
 * Replaces the old namespace with the new one inside all the Sci files, dynamic function name to avoid
 * collisions and improve compatibility with bundled Sci plugins
 *
 * @param $replaceStringFunction The function to process the string
 * @param $new_namespace The replacement namespace
 */
${$parsedPluginFolder.'ReplaceCoreNamespaceFunction'} = function($replaceStringFunction, $new_namespace) {
    $new_core_namespace = $new_namespace . '\Sci';
    $old_core_namespace = NULL;
    $old_namespace = NULL;
    $file_path = plugin_dir_path( __FILE__ ) . 'Sci.php';
    $handle = fopen($file_path, "r") or die('The old namespace in the Sci Trait was not found');

    if (!$handle) return;

    while (($line = fgets($handle)) !== false) {
        if (strpos($line, 'namespace') === 0) {
            $parts = preg_split('/\s+/', $line);
            $old_core_namespace = rtrim(trim($parts[1]), ';');
            $old_namespace = preg_split('/\\\+/', $old_core_namespace)[0];
            break;
        }
    }

    fclose($handle);

    if ($old_core_namespace !== $new_core_namespace) {
        // Update SCIWP Framework namespaces
        $replaceStringFunction($replaceStringFunction, dirname(__DIR__, 1), $old_core_namespace, $new_core_namespace);

        // Update caller file namespaces (usually main.php)
        $backtrace =  debug_backtrace();
        $mainFilePath = $backtrace[1]['file']; echo( $mainFilePath);
        $fileContent = file_get_contents($mainFilePath);
        file_put_contents($mainFilePath, strtr($fileContent, [$old_core_namespace => $new_core_namespace]));

        // Update config file namespace
        $fileContent = file_get_contents($configFile);
        file_put_contents($configFile, strtr($fileContent, [$old_core_namespace => $new_core_namespace]));
    }
};

// Load config file
if (!file_exists($configFile)) die('Cannot open the config file:  ' . $configFile);

$config = file_exists($configFile) ? include $configFile : [];

$configCache = file_exists($configCacheFile) ? include $configCacheFile : ['namespace' => null, 'autoloader' => ['cache' => null]];

$rebuildNamespace =  !isset($configCache['namespace'])
            || !isset($configCache['parsed_plugin_folder'])
            || (isset($config['rebuild']) && $config['rebuild'] === true)
            || (isset($config['namespace']) && $config['namespace'] !== $configCache['namespace'])
            || ucfirst($configCache['parsed_plugin_folder']) !== ucfirst($parsedPluginFolder);

if ($rebuildNamespace) {
    if (isset($config['namespace']) && $config['namespace']) {
        $namespace = $config['namespace'];
    } else {
        $namespace = call_user_func( function() use ($parsedPluginFolder) {

            // Try to get the namespace from the main.php file
            $handle = fopen(plugin_dir_path( dirname(__FILE__) ) . '/main.php', "r");
        
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (strpos($line, 'namespace') === 0) {
                        $parts = preg_split('/\s+/', $line);
                        if (isset($parts[1])) {
                            return rtrim(trim($parts[1]), ';');
                        }                        
                    }
                }
                fclose($handle);
            }

            // Fallback to the plugin folder name
            return ucfirst($parsedPluginFolder);
        });
    }

    if (!isset($configCache['namespace']) || $namespace !== $configCache['namespace']) {

        // Rebuild SCIWP files
        ${ $parsedPluginFolder . 'ReplaceCoreNamespaceFunction' }(${$parsedPluginFolder.'ReplaceStringFunction'}, $namespace);

        $configCache['namespace'] = $namespace;
        $configCache['plugin_folder'] = $pluginFolder;
        $configCache['parsed_plugin_folder'] = $parsedPluginFolder;

        if (!file_exists($cacheDir)) mkdir($cacheDir);
        file_put_contents ($configCacheFile, "<?php if ( ! defined( 'ABSPATH' ) ) exit; \n\n".'return ' . var_export( $configCache, true) . ';')
        or die('Cannot write the file:  '.$configCacheFile);
    }
}

if (isset($config['autoloader']['cache']) && $config['autoloader']['cache'] !== $configCache['autoloader']['cache']) {

    $configCache['autoloader']['cache'] = $config['autoloader']['cache'];
    
    if (!file_exists($cacheDir)) mkdir($cacheDir);
    file_put_contents ($configCacheFile, "<?php if ( ! defined( 'ABSPATH' ) ) exit; \n\n".'return ' . var_export( $configCache, true) . ';')
    or die('Cannot write the file:  '.$configCacheFile);

    $autoloadCacheFile = $cacheDir . 'autoload.cache.php';
    file_put_contents ($autoloadCacheFile, "<?php if ( ! defined( 'ABSPATH' ) ) exit; \n\n".'return array ();')
    or die('Cannot write the file:  '.$autoloadCacheFile);
}

// Require the Autoloader
$namespace = isset($configCache['namespace']) && $configCache['namespace'] ? $configCache['namespace'] :
             (isset($config['namespace']) && $config['namespace'] ? $config['namespace'] : ucfirst($parsedPluginFolder));

// Start the autoloader and Sci
require plugin_dir_path( __FILE__ ) . 'Autoloader.php';

if(class_exists('\\' . $namespace . '\Sci\Autoloader')) {
    $autoloaderClass = '\\' . $namespace . '\Sci\Autoloader';
    $autoloaderClass::start();
    $sciClass = '\\'.$namespace.'\Sci\Sci';

    return $sciClass::instance()->init();
}

die('Please rebuild SCIWP.');
