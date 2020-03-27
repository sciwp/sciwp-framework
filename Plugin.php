<?php
namespace MyPlugin\Sci;

use MyPlugin\Sci\Manager\PluginManager;
use MyPlugin\Sci\Template as Template;
use MyPlugin\Sci\Collection;
use MyPlugin\Sci\Sci;

defined('ABSPATH') OR exit('No direct script access allowed');

/**
 * Plugin class
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
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

	/** @var string $mainNamespace The namespace of the Plugin main folder */
    private $mainNamespace;

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
    
    public function __construct($plugin_file, PluginManager $pluginManager, Collection $services, Collection $config)
    {
        // Injected instances
        $this->pluginManager = $pluginManager;
        $this->services = $services;
        $this->config = $config;
        
        $this->file = $plugin_file;
        $this->dir = rtrim( dirname( $this->file ), '/' );
        $this->url = plugin_dir_url( dirname( $this->file ) );

        $configData = file_exists(  $this->dir . '/config.php' ) ? include  $this->dir . '/config.php' : [];
        $this->config->add($configData);

        $this->configCache = file_exists(  $this->dir . '/cache/config.cache.php' ) ? include   $this->dir . '/cache/config.cache.php' : [];

        $dirName = strtolower( basename(  $this->dir ) );

        if ($this->config->check('rebuild', true)) {

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

        // Set the plugin name
        if (isset( $this->configCache['name'] ) && strlen( $this->configCache['name'] )) {
            $this->name = $this->configCache['name'];
        } else {
            $this->name = $dirName;
        }

        if (!$this->config->length('text_domain')) {
            $this->textDomain = isset($this->configCache['text_domain']) && strlen($this->configCache['text_domain']) ? $this->configCache['text_domain'] : $dirName;
        }

        if ($this->config->length('domain_path')) {
             $this->domainPath = trim($this->config->get('domain_path'), '/');
        } else if (isset($this->configCache['domain_path']) && strlen($this->configCache['domain_path'])) {
            $this->domainPath = trim($this->configCache['domain_path'], '/');
        } else {
            $this->domainPath = trim('languages');          
        }

        // Base namespace
        if ($this->config->length('namespace')) {
            $this->namespace = $this->config->get('namespace');
        } else if (isset( $this->configCache['namespace'] ) && strlen( $this->configCache['namespace'] )) {
            $this->namespace = $this->configCache['namespace'];
        } else {
            $this->namespace = $this->getNamespace();
            $this->configCache['namespace'] = $this->namespace;
            $file_contents = "<?php if ( ! defined( 'ABSPATH' ) ) exit; \n\n".'return ' . var_export( $this->configCache, true) . ';';
            file_put_contents ( plugin_dir_path( dirname(__FILE__) ) . 'cache/config.cache.php', $file_contents );
        }

        // Set main folder
		if ($configMainDir = $this->config->get('dir/main')) {
            $this->mainDir = trim($configMainDir, '/');
			$this->mainDir = $configMainDir === '' ? $this->dir : $this->dir . '/' . $configMainDir;
		}
		else {
            $this->mainDir = file_exists($this->dir . '/app') ? $this->dir . '/app' : $this->dir;
        }

        // Main namespace
        if ($this->config->length('main_namespace')) {
            $this->mainNamespace =  $this->config->get('main_namespace');
        } else {
            $this->mainNamespace =  preg_replace("/[^A-Za-z0-9]/", '', basename($this->mainDir));
        }

        // Set modules folder
		if ($config_modules_dir = $this->config->get('dir/modules')) {
            $this->modulesDir = trim($config_modules_dir, '/');
            if ($config_modules_dir == '') {
                $this->modulesDir = file_exists($this->dir . '/modules') ? $this->dir . '/modules' : false ;
            } else {
                $this->modulesDir = $this->dir .'/'. $config_modules_dir;
            }
		}
		else {
            $this->modulesDir = file_exists($this->dir . '/modules') ? $this->dir . '/modules' : false;
        }

        // Autoloader Cache
        $this->autoloaderCache  = $this->config->check('autoloader/cache', true);

        // services
        if ($services = $this->config->get('services')) {
            foreach ($services as $key => $service) {
                $instance = Sci::make($service);
                $instance->init($this);
                $this->services->add($key, $instance);
            }
        }
    }

	/**
	 * Add a new plugin
	 *
     * @param string $pluginFile The plugin file path
     * @return Plugin
	 * @return \MyPlugin\Sci\Plugin
	 */
    public static function create ($pluginFile)
    {
        $plugin = Sci::make(\MyPlugin\Sci\Plugin::class, [$pluginFile]);
        return $plugin;
    }

	/**
	 * Add the plugin to the plugin manager
	 *
	 * @return \MyPlugin\Sci\Plugin
	 */
    public function register () {
        $this->pluginManager->register($this);
        return $this;
    }

    /**
     * Get the services collection
     * 
     * @return Collection The services collection
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

    /**
     * Get the template manager
     * 
     * @return mixed The requested service
     */
    /*
    public function templateManager ()
    {
        if (!$this->template_manager) $this->template_manager = Sci::make(TemplateManager::class, [$this->dir]);
        return $this->template_manager;
    }
    */

    public function getName ()
    {
        if ($this)
        return $this->config['name'];
    }

    public function config ()
    {
        return $this->config;
    }
    
    /**
     * Get the namespace form the main plugin file
     * 
     * @return string The plugin root namespace
     */
    public function getNamespace ()
    {
        if (isset($this->namespace) && $this->namespace) return $this->namespace;
        $file_content = file_get_contents($this->file);
        if (preg_match('#^\s*namespace\s+(.+?);$#sm',  $file_content, $m)) {
            return $m[1];
        }
        return strtolower( basename(  $this->dir ) );
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

    
		// Load text domain
		//$languages_path = this->dir . $this->domainPath;
		//load_plugin_textdomain( $this->textDomain, false, $this->domainPath ); 
		 /*

		if ( isset($this->plugin::config['collections']) && is_array($this->plugin::config['collections']) ) {
			foreach ($this->plugin::config['collections'] as $key => $collection)
				if (is_array($collection)) {
					
					
					
					if (isset($collection['container'])) $container = $collection['container'];
					else $container = '\MyPlugin\Sci\Collection';
					if (isset($collection['shortcut'])) $shortcut = $collection['shortcut'];
					else $shortcut = $key;
					$this->collections[$shortcut] = $this->Sci::get($container);
					
					$collectionManager->add($key, $collection_class);
					
					
					if (isset($collection['register']) && is_array($collection['register'])) {
						$this->collections[$shortcut]->register($collection['register']);
					}
				}
				else {
					$collectionManager->add($id, $this::get($collection));
				}
			}
		}
        
        */
     
		/*
		$this->plugin::addStaticMethod('collections', function ($collection_id = null) {
			if (isset($collection_id) && $collection_id) {
				return $this->get($collection_id);
			}
			return $this;
		});

		
		self::addStaticMethod('collections', function () {
			return self::$collections;
		});		

		
	
		self::$collections = new \MyPlugin\Sci\Collection();
		self::addStaticMethod('collections', function () {
			return self::$collections;
		});
		

		self::$services =  new \MyPlugin\Sci\Collection();
		self::addStaticMethod('services', function () {
			return self::$collections['services'];
		});		
		foreach ($config['collections']['services']['regsiter'] as $element) {
			self::services()->add();
		}
		
		// Include helpers
		//Helper::requireFilesOnce(plugin_dir_path( __FILE__ ) . 'functions');
		
		$scan = preg_grep('/^([^.])/', scandir(self::dir() . 'methods'));
		foreach($scan as $file) {
			require_once(self::dir() . 'methods/'.$file);	
		}
		//add_filter( 'get_post_metadata' , array(self::SELF, 'fallback_get_post_metadata'), 10 , 4 );		
		
		//Los archivos de configuracion se debn juntar antes de este punto, en caso de haber varios
		Service\Assets::init();
		Service\Assets::addAssets(self::$config['assets']['assets']);
		Service\Assets::addGroups(self::$config['assets']['groups']);

		//Service\Asset::add('media', 'image_uploader');
		//TaxonomyController::init();
        return __class__;
       
	}		
	*/
}