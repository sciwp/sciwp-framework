<?php
namespace Wormvc\Wormvc;

use Wormvc\Wormvc\Services\TemplateService;
use Wormvc\Wormvc\Services\Activation as ActivationService;
use Wormvc\Wormvc\Services\Deactivation as DeactivationService;
use Wormvc\Wormvc\Template as Template;
use Wormvc\Wormvc\Collection;
use Wormvc\Wormvc\Wormvc;

defined('ABSPATH') OR exit('No direct script access allowed');

/**
 * Plugin class
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */
class Plugin
{
    /** @var $id The plugin id */
    private $id;
    
    /** @var $name The plugin name */
    private $name;
    
    /** @var $dir The Plugin base full path dir */
    private $dir;

	/** @var string $main_folder Stores de plugin main full path folder */	
	private $main_dir;
	
	/** @var string $module_folder Stores de plugin component full path folder */	
	private $module_dir;    
    
    /** @var string $file The main Plugin file */
    private $file;
    
    /** @var string $namespace The Plugin base namespace */
    private $namespace;

	/** @var string $main_namespace The namespace of the Plugin main folder */
	private $main_namespace;

    /** @var string $url The Plugin url */
    private $url;
    
    /** @var Array $config The Plugin config array */
    private $config;
    
    /** @var Array $name  The Plugin config cache array */
    private $config_cache ;
    
    /** @var string $text_domain The Plugin text domain  */
    private $text_domain;
    
    /** @var string $domain_path The Plugin text domain dir path  */
    private $domain_path;

    /** @var Array $autoloader_cache The Plugin text domain dir path  */
    private $autoloader_cache;       

    /** @var Collection $services Collection to store the services */
    private $extensions;
    
    public function __construct($plugin_file, $plugin_id, Collection $extensions)
    {
        $this->file = $plugin_file;
        $this->dir = rtrim( dirname( $this->file ), '/' );
        $this->url = plugin_dir_url( dirname( $this->file ) );
        $this->config = file_exists(  $this->dir . '/config.php' ) ? include  $this->dir . '/config.php' : array();
        $this->config_cache = file_exists(  $this->dir . '/cache/config.cache.php' ) ? include   $this->dir . '/cache/config.cache.php' : array();

        $this->config['dir_name'] = strtolower( basename(  $this->dir ) );

        if (
            (isset($config['on_init']['check_meta']) && $config['on_init']['check_meta'])
            || (isset($config_cache['on_init']['check_meta']) && $config_cache['on_init']['check_meta'])
        ) {
            //These values are secondary values, used in case they are not provided in the config file
            $name = $this->getPluginFileMeta( $this->file, 'Plugin Name' );
            $text_domain = $this->getPluginFileMeta( 'Text Domain' );
            $domain_path = $this->getPluginFileMeta( 'Domain Path' );
            $this->config_cache['name'] = $name ? $name : ucfirst( $this->config['dir_name'] );
            $this->config_cache['text_domain'] = $text_domain  ? $text_domain : $this->config['dir_name'];
            $this->config_cache['domain_path'] = $domain_path  ? $domain_path : 'languages';
            $this->config_cache['on_init']['check_meta'] = false;
            // Update the config cache file
            file_put_contents ( $this->dir . '/cache/config.cache.php', "<?php if ( ! defined( 'ABSPATH' ) ) exit; \n\n".'return ' . var_export( $this->config_cache , true) . ';');
        }

        if (!isset( $this->config['id'] ) || !strlen( $this->config['id'] )) $this->config['id'] = $this->config['dir_name'];

        if (!isset( $this->config['name'] ) || !strlen( $this->config['name'] )) {
            $this->config['name'] = isset( $this->config_cache['name'] ) && strlen( $this->config_cache['name'] ) ? $this->config_cache['name'] : $this->config['dir_name'];
        }

        if (!isset( $this->config['text_domain'] ) || !strlen( $this->config['text_domain'])) {
            $this->text_domain = isset($this->config_cache['text_domain']) && strlen($this->config_cache['text_domain']) ? $this->config_cache['text_domainl'] : $this->config['dir_name'];
        }        

        if (isset( $this->config['domain_path'] ) && strlen( $this->config['domain_path'])) {
             $this->domain_path = trim($this->config['domain_path'], '/');
        } else if (isset($this->config_cache['domain_path']) && strlen($this->config_cache['domain_path'])) {
            $this->domain_path = trim($this->config_cache['domain_path'], '/');
        } else {
            $this->domain_path = trim('languages');          
        }

        // Base namespace
        if (isset( $this->config['namespace'] ) && strlen( $this->config['namespace'] )) {
            $this->namespace = $this->config['namespace'];
        } else if (isset( $this->config_cache['namespace'] ) && strlen( $this->config_cache['namespace'] )) {
            $this->namespace = $this->config_cache['namespace'];
        } else {
            $this->namespace = $this->getNamespace();
            if (!$this->namespace) $this->namespace = ucfirst(strtolower($this->config['dir_name']));
            $this->config_cache['namespace'] = $this->namespace;
            $file_contents = "<?php if ( ! defined( 'ABSPATH' ) ) exit; \n\n".'return ' . var_export( $this->config_cache, true) . ';';
            file_put_contents ( plugin_path( dirname(__FILE__) ) . '/cache/config.cache.php', $file_contents );
        }
        
        // Main namespace
        $this->main_namespace = isset($this->config['main_namespace']) && strlen($this->config['main_namespace']) ? $this->config['main_namespace'] : 'App';

        // Set main folder
		if (isset($this->config['folders']['main'])) {
			$this->config['folders']['main'] = trim($this->config['folders']['main'], '/');
			$this->main_dir = $this->config['folders']['main'] == '' ? $this->dir : $this->dir . '/' . $this->config['folders']['main'];
		}
		else $this->main_dir = file_exists($this->dir . '/app') ? $this->dir . '/app' : $this->dir;
		
        // Set modules folder
		if (isset($this->config['folders']['modules'])) {
			$this->config['folders']['modules'] = trim($this->config['folders']['modules'], '/');
			$this->module_dir = $this->config['folders']['modules'] == '' ? file_exists($this->dir . '/modules') ? $this->dir . '/modules' : false : $this->dir .'/'. $this->config['folders']['modules'];
		}
		else $this->config['module_folder'] = file_exists($this->config['base_folder'] . '/modules') ? $this->dir . '/modules' : false;        

        // Autoloader Cache
        $this->autoloader_cache  = isset($this->config['autoloader']['cache']) && $this->config['autoloader']['cache'] ? true : false;

        // Extensions
        $this->extensions = $extensions;
        if (isset($this->config()['extensions'])) {
            foreach ($this->config()['extensions'] as $key => $extension) {
                $instance = Wormvc::make($extension);
                $instance->init($this);
                $this->extensions->add($key, $instance);
            }
        }
    }

    /**
     * Get the extensions collection
     * 
     * @return Collection The extensions collection
     */
    public function extensions ()
    {
        return $this->extensions;
    }

    /**
     * Get a single extension
     * 
     * @return mixed The requested extension
     */
    public function extension($extensionId)
    {
        return $this->extensions->get($extensionId);
    }    

    /**
     * Get the template manager
     * 
     * @return mixed The requested service
     */
    public function templateManager()
    {
        if (!$this->template_manager) $this->template_manager = $this->wormvc->get(TemplateManager::class, [$this->dir]);
        return $this->template_manager;
    }   


    public function getId ()
    {
        return $this->id;
    }

    public function getName ()
    {
        return $this->config['name'];
    }

    public function getConfig ()
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
        } else return false;
    }

    /**
     * Get the namespace form the main plugin file
     * 
     * @return string The plugin root namespace
     */
    public function getMainNamespace ()
    {
        if (isset($this->main_namespace) && $this->main_namespace) return $this->main_namespace;
    }    

    /**
     * Get a WordPress header field form the main plugin file
     * 
     * @param string $field The header field
     * @return string The header field value
     */
    public function getPluginFileMeta($field)
    {
        $to_return = "";
        $handle = fopen($this->file, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (preg_match( '/^[\* ]*?'.$field.'.*?:(.*?)$/', trim($line), $matches ) && count($matches) > 1) $to_return = $matches[1];
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
	public function getModuleDir()
	{
		return $this->module_dir;
	}
    
    /**
     * Get the Maindir
     * 
     * @return string The Plugin main folder
     */   
	public function getMainDir()
	{
		return $this->main_dir;
	}
    
     /**
     * Get the Maindir
     * 
     * @return string The Plugin main folder
     */   
	public function getAutoloaderCacheEnabled()
	{
		return $this->autoloader_cache;
	}   

    /**
     * Get the Plugin configuration
     * 
     * @param id $id The plugin id
     * @param id $file_path The plugin main file path
     * @return void
     */   
	public function config($path = null)
	{
		if ($this->config == null) $this->config = include $this->dir . '/config.php';
		if ($path === null || $path == '') return $this->config;
		$path_arr = explode("/",trim($path, '/'));
		$processed = self::$config;
		foreach ($path_arr as $element) {
			if (!isset($processed[$element])) return;
			else $processed = $processed[$element];
		}
		return $processed;
	}
    
	

		// Load text domain
		//$languages_path = this->dir . $this->domain_path;
		//load_plugin_textdomain( $this->text_domain, false, $this->domain_path ); 

		   /*
		// Register activation and deactivation hooks

		
		$collectionManager = self::get('\Wormvc\Wormvc\Manager\CollectionManager');
		
		
		if ( isset($this->plugin::config['collections']) && is_array($this->plugin::config['collections']) ) {
			foreach ($this->plugin::config['collections'] as $key => $collection)
				if (is_array($collection)) {
					
					
					
					if (isset($collection['container'])) $container = $collection['container'];
					else $container = '\Wormvc\Wormvc\Collection';
					if (isset($collection['shortcut'])) $shortcut = $collection['shortcut'];
					else $shortcut = $key;
					$this->collections[$shortcut] = $this->wormvc::get($container);
					
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

        
		
		
		$collectionManager->boot();
		if(isset($this->wormvc::config['collections']) ){
			$collectionManager->init($this->wormvc::config['collections']);
		}
		
		
	
		self::$collections['managers'] = array();
		self::$collections['managers']['collection'] = $collectionManager;

       		
		
		self::addStaticMethod('collections', function () {
			return self::$collections;
		});		

		
		

		self::$collections = new \Wormvc\Wormvc\Collection();
		self::addStaticMethod('collections', function () {
			return self::$collections;
		});
		

		self::$services =  new \Wormvc\Wormvc\Collection();
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


/*
// Init the Plugin
return call_user_func('\\'.$namespace.'\Wormvc\Plugin::init', $config);
*/

}