<?php
namespace Wormvc\Wormvc;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use \Wormvc\Wormvc\Plugin;
use \Wormvc\Wormvc\Manager\PluginManager;

/**
 * Autoloader Class
 *
 * @author		Eduardo Lazaro Rodriguez <eduzroco@gmail.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */

class Autoloader
{
	/** @var string $folder Stores de root folder */
	private static $folder;	

	/** @var string $namespace Stores de framework namespace */
	private static $namespace;
	
	/** @var string $plugins Stores the list of plugins using Wormvc */	
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
	 * @static
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
	 * @static
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
	 * @static
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
	 * @static
	 * @return	bool
	 */
	public static function checkCacheClass($class)
	{
        if (isset(self::$cache[$class])) {
            if (file_exists (self::$cache[$class])) {
                return self::$cache[$class];
            }
            else unset(self::$cache[$class]);
		}
		return false;
	}    
    
	/**
	 * Seaches for a class file
	 *
	 * @static
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
    public static function addPlugin($id, $config = [], $autoload = [] )
	{
        self::$plugins[$id] = [
            'namespace' => $config['namespace'],
            'main_namespace' => $config['main_namespace'],
            'dir' => $config['dir'],
            'main_dir' => $config['main_dir'],
            'module_dir' =>  $config['module_dir'],            
            'cache_enabled' => isset($config['cache_enabled']) ? $config['cache_enabled'] : false,
            'reflexive' => isset($config['reflexive']) ? $config['reflexive'] : false,
            'autoload' => $autoload
        ];
	}

	/**
	 *  Main autoload function
	 *
	 * @static
	 * @param string $class The class name
	 * @return	bool
	 */
	public static  function autoload( $class )
	{
		$class_arr = explode('\\', trim($class,'\\'));

		if (count ($class_arr) < 2) return false; // Not a valid Wormvc namespace, as it should contain the base namespace and the class

		// Wormvc files
        if ( $class_arr[0] . '\\' . $class_arr[1] == self::$namespace ) {

            // Autoload Get trait
            if (!isset($class_arr[2])) {
                require_once self::$folder . '/Traits/Wormvc.php';
                return true;
            }
            // Autoload regular Wormvc files
            else {
                $relative_class = trim(substr($class, strlen($class_arr[0])), '\\'); // Remove base namespace from the class name
                array_shift ($class_arr); // Remove base namespace from the array
                array_shift ($class_arr); // Remove Wormvc namespace from the array
       
                $class_file = self::$folder;
                $count_class_arr = count($class_arr);

                foreach ($class_arr as $key => $element) {
                    $class_file .= '/' . $element;
                }
            
                // OPTION 1: Namespace structure is a folder route
                if (file_exists($class_file . '.php')) {
                    require_once $class_file . '.php';
                    return true;
                }
                // OPTION 2: Namespace structure is a folder route, and the class has the class suffix
                else if (file_exists($class_file . '.class.php')) {
                    require_once $class_file . '.class.php';
                    return true;			
                }
                return false;
            }        
        } else {
			$plugin = false;
			foreach(self::$plugins as $key => &$p) {

                // REV
                if (isset($p['autoload'][trim($class,'\\')])) {
                    if (file_exists ($p['main_dir']. '/'. $p['autoload'][trim($class,'\\')])) {
                        include $p['main_dir']. '/'. $p['autoload'][trim($class,'\\')];
                    }
                }

				if ($p['namespace'] == $class_arr[0]) {
					$plugin = $p;
				}
			}

			if(!$plugin) return false;

            // Check the cache array
            if ($plugin['cache_enabled']) {
                $file = self::checkCacheClass($class);
                if ($file) {
                    require_once $file;
					return true;	
                }
            }

            // Remove the base plugin namespace
            $relative_class = trim(substr(trim($class,'\\'), strlen($class_arr[0])), '\\'); // Remove base namespace from the class name
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

			if (file_exists($class_file . '.php')) {
                // OPTION 1: Namespace structure is a folder route
                if ($plugin['cache_enabled']) {
                    self::$cache[$class] = $class_file . '.php';
					self::saveCache();						
				}                        
				require_once $class_file . '.php';
				return true;
			}
            else if (file_exists($class_file . '.class.php')) {
                // OPTION 2: Namespace structure is a folder route, and the class has the class suffix
                if ($plugin['cache_enabled']) {
                    self::$cache[$class] = $class_file . '.class.php';
					self::saveCache();						
				}                
				require_once $class_file . '.class.php';
				return true;			
			}
            else if ($plugin['reflexive']) {
				// OPTION 3: Namespace structure is a file name with the class suffix
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
				if($file) {
					if ($plugin['cache_enabled']) {
						self::$cache[$class] = $file;
						self::saveCache();						
					}
					require_once $file;
					return true;				
				}
            }
            return false;
		}
	}
}