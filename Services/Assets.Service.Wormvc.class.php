<?php
/**
 * Class Page
 */

namespace  MyPlugin\Sci\Service;

use \MyPlugin\Sci\Plugin;
use \MyPlugin\Sci\Helper as Helper;

defined('WPINC') OR exit('No direct script access allowed');

class Assets {

	use \MyPlugin\Sci\Traits\StaticClass;

	const SELF = __class__;
	
	/** @var array $assets Stores de asset files */
	public static $assets = array();
	
	/** @var array $assets Stores de group files */
	public static $groups = array();
	
	/** @var string $folder Stores de plugin base folder */	
	private static $folder;

	/** @var string $uri Stores de plugin base uri */	
	private static $url;	
	
	/**---------------------------------------------------------------
	 * Initialize the Asset loader
	 * ---------------------------------------------------------------
	 * @static
	 */
	public static function init()
	{
		add_action( 'wp_enqueue_scripts', array(self::SELF, 'enqueueFrontAssets') );
		add_action( 'admin_enqueue_scripts', array(self::SELF, 'enqueueAdminAssets') );
		
	}

	/**---------------------------------------------------------------
	 * Enqueue admin scripts and styles (auto = true)
	 * ---------------------------------------------------------------
	 * @static
	 */
	public static function enqueueAdminAssets()
	{
		foreach(self::$assets as $key => $asset) {
			if (isset($asset['auto']) && $asset['auto'] && (!isset($asset['zone']) || $asset['zone'] == 'admin')) self::enqueue($key);
		}
	}
	
	/**---------------------------------------------------------------
	 * Enqueue front scripts and styles (auto = true)
	 * ---------------------------------------------------------------
	 * @static
	 */
	public static function enqueueFrontAssets()
	{
		foreach(self::$assets as $key => $asset) {
			if (isset($asset['auto']) && $asset['auto'] && (!isset($asset['zone']) || $asset['zone'] == 'front')) self::enqueue($key);
		}		

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
	 * Enqueue group
	 * ---------------------------------------------------------------
	 * @static
	 */
	public static function enqueueGroup($group_id)
	{
		if (!isset(self::$groups[$group_id])) return;
		call_user_func_array(self::enqueue(), (array) $groups[$group_id]);
	}	

	/**---------------------------------------------------------------
	 * Enqueue groups
	 * ---------------------------------------------------------------
	 * @static
	 */
	public static function enqueueGroups()
	{
		$group_ids = func_get_args ();
		foreach($group_ids as $group_id) self::enqueueGroup($group_id);
	}		
	
	/**---------------------------------------------------------------
	 * Add an asset to the Asset loader
	 * ---------------------------------------------------------------
	 * @static
	 */	 
	public static function addAsset($name, $values = array())
	{
		self::$assets = array_merge(self::$assets, array($name => $values));
		return __class__;
	}
	
	/**---------------------------------------------------------------
	 * Add an array of assets to the Asset loader
	 * ---------------------------------------------------------------
	 * @static
	 */	 
	public static function addAssets($assets = array())
	{
		self::$assets = array_merge(self::$assets, (array) $assets);
	}
		
	
	/**---------------------------------------------------------------
	 * Add a group to the Asset loader
	 * ---------------------------------------------------------------
	 * @static
	 */
	public static function addGroup($name, $assets = array())
	{
		self::$groups = array_merge(self::$groups, (array) $assets);
	}
	
	/**---------------------------------------------------------------
	 * Add an array of assets to the Asset loader
	 * ---------------------------------------------------------------
	 * @static
	 */	 
	public static function addGroups($groups = array())
	{
		self::$groups = array_merge(self::$groups, (array) $groups);
	}	
}