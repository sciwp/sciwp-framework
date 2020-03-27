<?php
namespace MyPlugin\Sci\Services;

defined('WPINC') OR exit('No direct script access allowed');

use MyPlugin\Sci\Plugin;

/**
 * Class AssetService
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */
class AssetService
{	
	/** @var array $assets Stores the asset files */
	public static $assets = array();
	
	/** @var array $assets Stores the group files */
	public static $groups = array();
	
	/** @var string $folder Stores de plugin base folder */	
	private static $folder;

	/** @var string $uri Stores de plugin base uri */	
	private static $url;	
	

	/**
	 * Initializes the class
	 *
     * @param \MyPlugin\Sci\Plugin|string $plugin The plugin/id
	 * @return \MyPlugin\Sci\Services\AssetService
	 */

	/**
	 * Initialize the Asset loader
	 *
	 * @return AssetService
	 */
	public function init($plugin)
	{
		$this->plugin = $plugin instanceof \MyPlugin\Sci\Plugin ? $plugin : $this->Sci->plugin($plugin);

		add_action( 'wp_enqueue_scripts', [$this, 'enqueueFrontAssets'] );
		add_action( 'admin_enqueue_scripts', [$this, 'enqueueAdminAssets'] );
		
	}


	


	/**---------------------------------------------------------------
	 * Enqueue a registered asset (manual)
	 * ---------------------------------------------------------------
	 * @static
	 */
	public static function enqueue()
	{
		$asset_ids = func_get_args ();
		foreach($asset_ids as $asset_id) {
			if (!isset(self::$assets[$asset_id]) || !isset(self::$assets[$asset_id]['src'])) break;
			$asset = self::$assets[$asset_id];
			$dependences = isset($asset['deps']) ? (array) $asset['deps'] : array();
			$version = isset($asset['ver']) ? $asset['ver'] : false;
			if ((substr( $asset['src'], 0, 7 ) !== "http://") && (substr( $asset['src'], 0, 8 ) !== "https://")) $asset['src'] = Plugin::url($asset['src']);
			if (isset($asset['type']) && ($asset['type'] == 'css')) {
				$media = isset($asset['media']) ? $asset['media'] : 'all';
				wp_enqueue_style($asset_id, $asset['src'], $dependences, $version, $media);				
			}
			else {
				$in_footer = isset($asset['footer']) && $asset['footer']  ? true : false;
				wp_enqueue_script($asset_id, $asset['src'], $dependences, $version, $in_footer);					
			}
		}
	}
	
	
	
	/**---------------------------------------------------------------
	 * Add an asset to the Asset loader
	 * ---------------------------------------------------------------
	 * @static
	 */	 
	/*
	public static function addAsset($name, $values = array())
	{
		self::$assets = array_merge(self::$assets, array($name => $values));
		return __class__;
	}
	*/
	
	/**---------------------------------------------------------------
	 * Add an array of assets to the Asset loader
	 * ---------------------------------------------------------------
	 * @static
	 */	 
	/*
	public static function addAssets($assets = array())
	{
		self::$assets = array_merge(self::$assets, (array) $assets);
	}
	*/
		
	
	/**---------------------------------------------------------------
	 * Add a group to the Asset loader
	 * ---------------------------------------------------------------
	 * @static
	 */
	/*
	public static function addGroup($name, $assets = array())
	{
		self::$groups = array_merge(self::$groups, (array) $assets);
	}
	*/
	
	/**---------------------------------------------------------------
	 * Add an array of assets to the Asset loader
	 * ---------------------------------------------------------------
	 * @static
	 */
	/*
	public static function addGroups($groups = array())
	{
		self::$groups = array_merge(self::$groups, (array) $groups);
	}	
	*/
}