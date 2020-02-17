<?php
namespace MyPlugin\Sci;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Autoloader Class
 *
 * @author		Eduardo Lazaro Rodriguez <me@edulazaro.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */

class Autoloader
{
	/** @var string $folder Stores de root folder */
	private static $folder;	

	/** @var string $namespace Stores de framework namespace */
	private static $namespace;
	
	/** @var string $plugins Stores the list of plugins using Sci */	
	private static $plugins = array();

	/** @var array $cache Stores de cached classes */
	public static $cache = array();

	/** @var string $file_cache Stores de cache folder path */
	private static $dir_cache;
    
	/** @var string $file_cache Stores de cache file path */
	private static $file_cache;

	/**
	 * Class constructor
	 *
	 * @return	void
	 */
	private function __construct(){}

	/**
	 * Clone
	 *
	 * @return	void
	 */		
	private function __clone() {}

	/**
	 * Wakeup
	 *
	 * @return	void
	 */			
	private function __wakeup() {}	
	
	/**
	 * Initialize the autoloader
	 *
	 * @return	object
	 */
	public static function start()
	{
		self::$folder = substr(plugin_dir_path( __FILE__ ), 0, -1);
		self::$namespace = trim(__NAMESPACE__,'\\');
		spl_autoload_register( array(self::$namespace . '\Autoloader', 'autoload'));
        self::$dir_cache = dirname(self::$folder) . '/cache/';
        self::$file_cache = self::$dir_cache . 'autoload.cache.php';
        self::$cache = self::loadCache();
		return self::class;
	}

	/**
	 * Loads the autoloader cache
	 *
	 * @return	bool
	 */
	public static function loadCache()
	{
		$cache_array = is_file(self::$file_cache) ? (array)include self::$file_cache : [];		
		self::$cache = array_merge(self::$cache , $cache_array);
		return self::$cache;
	}

	/**
	 * Saves the autoloader cache
	 *
	 * @return	bool
	 */
	public static function saveCache()
	{
        if (!file_exists(self::$dir_cache)) mkdir(self::$dir_cache);
		file_put_contents(self::$file_cache, '<?php return ' . var_export(self::$cache, true) . ';')
        or die('Cannot write the file:  '.self::$file_cache);
	}
	
	/**
	 * Checks the autoloader cache
	 *
	 * @return	bool
	 */
	public static function checkCache($class)
	{
        if (isset(self::$cache[$class])) {
            if (self::includeFile(self::$cache[$class])) {
                return true;
            } else {
                unset(self::$cache[$class]);
                self::saveCache();
            }
        }
		return false;
	}

	/**
	 * Seaches for a class file
	 *
     * @param string $folder The folder to search in
	 * @param string $class A class to save in the cache
	 * @return	bool
	 */	
	public static function searchReflexiveClassFile($folder, $class)
	{
		if(file_exists ( $folder.'/'.$class)) {
			return($folder.'/'.$class);
		}
		else {
			$scan = preg_grep('/^([^.])/', scandir($folder));
			$dirs = array_filter(glob($folder.'/'.'*', GLOB_ONLYDIR));
			foreach($dirs as $dir) {
				$result = self::searchReflexiveClassFile($dir, $class);
				if($result) return $result;
			}
			return false;
		}
	}

	/**
	 *  Initialize the autloader
	 *
	 * @param string $id The plugin id
     * @param Array $config The plugin config
	 * @return void
	 */
    public static function addPlugin($id, $config = [])
	{
        self::$plugins[$id] = [
            'namespace' => $config['namespace'],
            'main_namespace' => $config['main_namespace'],
            'dir' => $config['dir'],
            'main_dir' => $config['main_dir'],
            'module_dir' =>  $config['module_dir'],            
            'cache_enabled' => isset($config['cache_enabled']) ? $config['cache_enabled'] : false,
            'reflexive' => isset($config['reflexive']) ? $config['reflexive'] : false,
            'autoload' => isset($config['autoload']) ? $config['autoload'] : [],
		];
	}


	/**
	 * Includes a file
	 *
     * @param string $file The file to include
	 * @param string $class A class to save in the cache
	 * @return	bool
	 */
	public static function includeFile($file, $class = false)
	{
        if (file_exists($file)) {  
            require_once $file;
            if ($class) {
                self::$cache[$class] = $file;
                self::saveCache();						
            }
            return true;
        }
        return false;
	}

	/**
	 * File name has extension
	 *
	 * @param string $string The file name
     * @param string $extension The file extension
	 * @return	bool
	 */
    public static function hasExtension($file, $extension)
    {
        $fileLenght  = strlen($file);
        $extensionLength = strlen($extension);

        if ($extensionLength > $fileLenght) return false;
        return substr_compare($file, $extension, $fileLenght - $extensionLength, $extensionLength) === 0;
    }

	/**
	 *  Main autoload function
	 *
	 * @param string $class The class name
	 * @return	bool
	 */
	public static  function autoload($class)
	{
        $class = trim($class);
		$class_arr = explode('\\', $class);

		// Not a valid Sci namespace, as it should contain at least the base namespace and the class
		if (count ($class_arr) < 2) return false;

		// Sci files
        if ( $class_arr[0] . '\\' . $class_arr[1] == self::$namespace ) {

            // Autoload Get trait
            if (!isset($class_arr[2])) {
                require_once self::$folder . '/Traits/Sci.php';
                return true;
            }
            // Autoload regular Sci files
            else {
                $relative_class = trim(substr($class, strlen($class_arr[0])), '\\'); // Remove base namespace from the class name
                array_shift ($class_arr); // Remove base namespace from the array
                array_shift ($class_arr); // Remove Sci namespace from the array
       
                $class_file = self::$folder;
                $count_class_arr = count($class_arr);

                foreach ($class_arr as $key => $element) {
                    $class_file .= '/' . $element;
                }
                
                if (self::includeFile($class_file . '.php')) return true;
                else if (self::includeFile($class_file . '.class.php')) return true;
                return false;
            }        
        } else {
            // Check the cache array
            if (self::checkCache($class)) return true;

			$plugin = false;

			foreach(self::$plugins as $key => &$p) {

				if ($p['namespace'] == $class_arr[0]) {
					$plugin = $p;
				}

                $classToCache = $p['cache_enabled'] ? $class : false;

                // Configured custom namespaces
                foreach ($p['autoload'] as $keyAutoload => $folder) {

					$base = trim($keyAutoload,'\\');

                    if (substr($class, 0, strlen($base)) === $base ) {

                        $class_file = $p['dir']. '/'. trim($folder,'\/');
                        $class_arr_r = explode('\\', substr($class, strlen($base), strlen($class)));

                        foreach ($class_arr_r as $key => $element) {
                            if ($element) $class_file .= '/' . $element;
                        }

                        if (self::includeFile($class_file . '.php',  $classToCache)) return true;
                        if (self::includeFile($class_file . '.class.php',  $classToCache)) return true;
                    }
                }
			}

			if (!$plugin) return false;
            $classToCache = $plugin['cache_enabled'] ? $class : false;

            // Remove the base plugin namespace
            $relative_class = trim(substr($class, strlen($class_arr[0])), '\\'); // Remove base namespace from the class name
			array_shift ($class_arr); // Remove base namespace from the array
            
			$class_file = $plugin['dir'];

			foreach ($class_arr as $key => $element) {
				if ($key == 0) {
					if (count($class_arr) > 1) {
						switch ($element) {
							case $plugin['main_namespace']:
                                $class_file = $plugin['main_dir'];
                                break;
							default: $class_file = $plugin['module_dir'] .'/'. $element;
						}
					}
					else {
						$class_file .= '/' . $element;
					}
				}
				else {
					$class_file .= '/' . $element;
				}
			}

            if (self::includeFile($class_file . '.php',  $classToCache)) return true;
            if (self::includeFile($class_file . '.class.php',  $classToCache)) return true;

            if ($plugin['reflexive']) {
				// Namespace structure is a file name with the class suffix
                $found = false;				
				$class_name = '';
				foreach ( array_reverse($class_arr) as $key => $namespace) {
					if ($key == count($class_arr) - 1) {
						$class_name .= $namespace.'.';
					}
					else {
						$class_name .= $namespace . '.';
					}
				}
				$file = self::searchReflexiveClassFile($plugin['dir'], $class_name.'class.php');
				if ($file && self::includeFile($file,  $classToCache)) return true;
            }
            return false;
		}
	}
}