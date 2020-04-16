<?php
namespace Sci\Plugin;

use Sci\Sci;
use Sci\Support\Collection;
use Sci\Plugin\Managers\PluginManager;


defined('ABSPATH') OR exit('No direct script access allowed');

/**
 * Plugin
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class Plugin
{
    /** @var $name The plugin name */
    private $name;

    /** @var string $file The main Plugin file */
    private $file;
    
    /** @var $dir The Plugin base full path dir */
    private $dir;

	/** @var string $mainDir Stores de plugin main full path folder */	
	private $mainDir;
	
	/** @var string $modulesDir Stores de plugin modules full path folder */	
	private $modulesDir;
    
    /** @var string $namespace The Plugin base namespace */
    private $namespace;

    /** @var string $url The Plugin url */
    private $url;
    
    /** @var Array $name  The Plugin config cache array */
    private $configCache;
    
    /** @var string $textDomain The Plugin text domain  */
    private $textDomain;
    
    /** @var string $domainPath The Plugin text domain dir path  */
    private $domainPath;

    /** @var Collection $services Collection to store the services */
    private $services;

    /** @var PluginManager $pluginManager The plugin manager */
    protected $pluginManager;
   
    /** @var Array $autoloaderCache The Plugin text domain dir path  */
    private $autoloaderCache;

    /** @var Collection $config Collection to store config data */
    public $config;

    /** @var Sci $sci Sci instance */
     public $sci;
    
    public function __construct($pluginFile, PluginManager $pluginManager, Collection $services, Collection $config)
    {
        $this->sci = Sci::instance();

        // Injected instances
        $this->pluginManager = $pluginManager;
        $this->services = $services;
        $this->config = $config;
        
        $this->file = $pluginFile;
        $this->dir = rtrim( dirname( $this->file ), '/' );
        $this->url = plugin_dir_url( dirname( $this->file ) );

        $configData = file_exists(  $this->dir . '/config.php' ) ? include  $this->dir . '/config.php' : [];
        $this->config->add($configData);
        $dirName = strtolower( basename(  $this->dir ) );

        if ($this->config->check('rebuild', true) || !file_exists(  $this->dir . '/cache/config.cache.php' )) {
            $this->configCache = [];
            //These values are secondary values, used in case they are not provided in the config file
            $name = $this->getMetaField('Plugin Name' );
            $textDomain = $this->getMetaField( 'Text Domain' );
            $domainPath = $this->getMetaField( 'Domain Path' );
            $this->configCache['name'] = $name ? $name :  $dirName;
            $this->configCache['text_domain'] = $textDomain  ? $textDomain :  $dirName;
            $this->configCache['domain_path'] = $domainPath  ? $domainPath : 'languages';
            
            // Update the config cache file
            file_put_contents ( $this->dir . '/cache/config.cache.php', "<?php if ( ! defined( 'ABSPATH' ) ) exit; \n\n".'return ' . var_export( $this->configCache , true) . ';');
        };

        $this->configCache = include $this->dir . '/cache/config.cache.php';

        // Set the plugin name
        if (isset( $this->configCache['name'] ) && strlen( $this->configCache['name'] )) {
            $this->name = $this->configCache['name'];
        } else {
            $this->name = $dirName;
        }

        if (!$this->config->length('text_domain')) {
            $this->textDomain = isset($this->configCache['text_domain']) && strlen($this->configCache['text_domain']) ? $this->configCache['text_domain'] : $dirName;
        }

        $this->configureDomainPath();

        $this->namespace = $this->getNamespace();

        $this->configureMainDir();

        $this->configureModulesDirectory();

        $this->autoloaderCache  = $this->config->check('autoloader/cache', true);
    }

	/**
	 * Add a new plugin
	 *
     * @param string $pluginFile The plugin file path
     * @return Plugin
	 */
    public static function create ($pluginFile)
    {
        $plugin = Sci::make(Plugin::class, [$pluginFile]);
        return $plugin;
    }

    /**
     * Configure the text domain path
     * 
     * @return void
     */
    private function configureDomainPath()
    {
        if ($this->config->length('domain_path')) {
            $this->domainPath = trim($this->config->get('domain_path'), '/');
        } else if (isset($this->configCache['domain_path']) && strlen($this->configCache['domain_path'])) {
           $this->domainPath = trim($this->configCache['domain_path'], '/');
        } else {
           $this->domainPath = trim('languages');          
        }
    }

    /**
     * Configure then main directory
     * 
     * @return void
     */
    private function configureMainDir()
    {
        $configMainDir = $this->config->get('dir/main');

		if ($configMainDir) {
            $this->mainDir = trim($configMainDir, '/');
            $this->mainDir = $configMainDir === '' ? $this->dir : $this->dir . '/' . $configMainDir;
            return;
        }

        $this->mainDir = file_exists($this->dir . '/app') ? $this->dir . '/app' : $this->dir;
    }


    /**
     * Configure the modules directory
     * 
     * @return void
     */
    private function configureModulesDirectory()
    {
        $configModulesDir = $this->config->get('dir/modules');

        if ($configModulesDir) {
            $this->modulesDir = trim($configModulesDir, '/');
            if ($configModulesDir == '') {
                $this->modulesDir = file_exists($this->dir . '/modules') ? $this->dir . '/modules' : false ;
            } else {
                $this->modulesDir = $this->dir .'/'. $configModulesDir;
            }
            return;
        }
        
        $this->modulesDir = file_exists($this->dir . '/modules') ? $this->dir . '/modules' : false;
    }

	/**
	 * Add the plugin to the plugin manager
	 *
	 * @return Plugin
	 */
    public function register ($pluginId = false, $addon = false) {
        $this->pluginManager->register($this, $pluginId, $addon);
        return $this;
    }

    /**
     * Get all the services
     * 
     * @return array Array with services
     */
    public function services ()
    {
        return $this->services;
    }

    /**
     * Get a single service
     * 
     * @return mixed The requested service
     */
    public function service ($serviceId)
    {
        return $this->services->get($serviceId);
    }    

    public function getName ()
    {
        if ($this)
        return $this->config['name'];
    }

    /**
     * Get the plugin configuration
     * 
     * @param string $setting The setting to get
     * @return mixed The setting
     */
    public function config ($setting = null)
    {
        if ($setting) return $this->config->get($setting);
        return $this->config;
    }

    /**
     * Get the namespace form the main plugin file
     * 
     * @return string The plugin root namespace
     */
    public function getNamespace ()
    {
        if ($this->namespace) return $this->namespace;

        if ($this->config->length('namespace')) return $this->config->get('namespace');

        if (isset( $this->configCache['namespace'] ) && strlen( $this->configCache['namespace'] )) {
            return $this->configCache['namespace'];
        } 

        $file_content = file_get_contents($this->file);
        if (preg_match('#^\s*namespace\s+(.+?);$#sm',  $file_content, $m)) $namespace = $m[1];
        else $namespace = strtolower( basename(  $this->dir ) );

        $this->configCache['namespace'] = $namespace;
        $fileContents = "<?php if ( ! defined( 'ABSPATH' ) ) exit; \n\n".'return ' . var_export( $this->configCache, true) . ';';
        file_put_contents ( $this->dir . '/cache/config.cache.php', $fileContents );

        return $namespace;
    }

    /**
     * Get the namespace form the main plugin file
     * 
     * @return string The plugin main namespace
     */
    public function getMainNamespace ()
    {
        return $this->mainNamespace;
    }

    /**
     * Get a WordPress header field form the main plugin file
     * 
     * @param string $field The header field
     * @return string The header field value
     */
    public function getMetaField($field)
    {
        $to_return = "";
        $handle = fopen($this->file, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (preg_match( '/^[\* ]*?'.$field.'.*?:(.*?)$/', trim($line), $matches ) && count($matches) > 1) {
                    $to_return = $matches[1];
                }
            }
            fclose($handle);
        }
        return trim($to_return);
    }

    /**
     * Get the Plugin file
     * 
     * @return string The Plugin main file
     */   
	public function getFile()
	{
		return $this->file;
	}    
        
    /**
     * Get the Plugin dir
     * 
     * @return string The Plugin base dir
     */   
	public function getDir()
	{
		return $this->dir;
	}    
    
    /**
     * Get the Moduledir
     * 
     * @return string The Plugin modules folder
     */   
	public function getModulesDir()
	{
		return $this->modulesDir;
	}

    /**
     * Get the Maindir
     * 
     * @return string The Plugin main folder
     */   
	public function getMainDir()
	{
		return $this->mainDir;
	}

    /**
     * Get the Maindir
     * 
     * @return string The Plugin main folder
     */   
	public function getAutoloaderCacheEnabled()
	{
		return $this->autoloaderCache;
	}
}