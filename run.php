<?php
namespace MyPlugin;

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
$pluginFolder = basename(plugin_dir_path(dirname( __FILE__ , 1 )));
$parsedPluginFolder = preg_replace("/[^a-z0-9]/", '', strtolower($pluginFolder));

$configFile = plugin_dir_path(dirname(__FILE__)) . 'config.php';

$cacheDir = plugin_dir_path( dirname(__FILE__) ) . 'cache/';
$configCacheFile = $cacheDir . 'config.cache.php';

/**
 * {Dynamic}ReplacePatternFunction
 * 
 * Replace a string in all files of a folder, dynamic function name to avoid collisions and improve
 * compatibility with bundled Sci plugins
 *
 * @param $replacePatternFunction The function to process the string
 * @param $folder The folder to search for files
 * @param $oldString The string to be replaced
 * @param $newString The replacement string
 */
${$parsedPluginFolder.'ReplacePatternFunction'} = function($replacePatternFunction, $folder, $oldString, $newString)
{
    foreach (glob($folder."/*.php") as $filename) {
        $fileContent = file_get_contents($filename);
        
        $fileContent = preg_replace(
            "/(namespace +\\\?)(" . $oldString . ")((?:\\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+)*[ \n]*;)/",
            "$1{$newString}$3",
            $fileContent,
            1
        );

        $fileContent = preg_replace(
            "/".$oldString."(\\\[a-zA-Z0-9_\x7f-\xff]+)/",
            "{$newString}$1",
            $fileContent
        );

        file_put_contents($filename, $fileContent);
    }
    $dirs = array_filter(glob($folder.'/'.'*', GLOB_ONLYDIR));
    foreach($dirs as $dir) {
        $replacePatternFunction($replacePatternFunction, $dir, $oldString, $newString);
    }
};

// Load config file
if (!file_exists($configFile)) die('Cannot open the config file:  ' . $configFile);

$config = file_exists($configFile) ? include $configFile : [];

$configCache = file_exists($configCacheFile) ? include $configCacheFile : ['namespace' => null, 'autoloader' => ['cache' => null]];

$rebuildNamespace = !isset($configCache['parsed_plugin_folder'])
            || (isset($config['rebuild']) && $config['rebuild'] === true)
            || (isset($config['rebuild_code']) && $config['rebuild_code'] === true)
            || (isset($config['namespace']) && $config['namespace'] !== __NAMESPACE__)
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

    if ($namespace !== __NAMESPACE__) {
    
        // Update just SCIWP or both code and SCIWP Framework namespaces
        $baseDir = !isset($config['rebuild_code']) || $config['rebuild_code'] !== true ? dirname(__FILE__) : dirname(__DIR__, 1);

        ${$parsedPluginFolder . 'ReplacePatternFunction'}(
            ${$parsedPluginFolder . 'ReplacePatternFunction'},
            $baseDir,
            __NAMESPACE__,
             $namespace
        );

        $configCache['plugin_folder'] = $pluginFolder;
        $configCache['parsed_plugin_folder'] = $parsedPluginFolder;

        if (!file_exists($cacheDir)) mkdir($cacheDir);
        file_put_contents ($configCacheFile, "<?php if ( ! defined( 'ABSPATH' ) ) exit; \n\n".'return ' . var_export( $configCache, true) . ';')
        or die('Cannot write the file:  '.$configCacheFile);

        header("Refresh:0");
        wp_die('Please wait, gnome engineers are updating plugin namespace...', 'SCIWP Framework');
    }
}


if (isset($config['autoloader']['cache']) /*&& $config['autoloader']['cache'] !== $configCache['autoloader']['cache']*/) {

    $configCache['autoloader']['cache'] = $config['autoloader']['cache'];
    
    if (!file_exists($cacheDir)) mkdir($cacheDir);
    file_put_contents ($configCacheFile, "<?php if ( ! defined( 'ABSPATH' ) ) exit; \n\n".'return ' . var_export( $configCache, true) . ';')
    or die('Cannot write the file:  '.$configCacheFile);

    $autoloadCacheFile = $cacheDir . 'autoload.cache.php';
    file_put_contents ($autoloadCacheFile, "<?php if ( ! defined( 'ABSPATH' ) ) exit; \n\n".'return array ();')
    or die('Cannot write the file:  '.$autoloadCacheFile);
}

// Start the autoloader and Sci
require plugin_dir_path( __FILE__ ) . 'Autoloader.php';

if(class_exists('\\' . __NAMESPACE__ . '\Sci\Autoloader')) {
    $autoloaderClass = '\\' . __NAMESPACE__ . '\Sci\Autoloader';
    $autoloaderClass::start();
    $sciClass = '\\'. __NAMESPACE__ .'\Sci\Sci';

    return $sciClass::instance()->init();
}

die('Please rebuild SCIWP.');
