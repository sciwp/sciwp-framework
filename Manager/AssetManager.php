<?php
namespace MyPlugin\Sci\Manager;

defined('WPINC') OR exit('No direct script access allowed');

use MyPlugin\Sci\Manager;
use MyPlugin\Sci\Manager\StyleManager;
use MyPlugin\Sci\Manager\ScriptManager;

/**
 * AssetManager
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */

class AssetManager extends Manager
{
    /** @var StyleManager $styleManager The Style Manager instance */
    private $styleManager;

    /** @var ScriptManager $scriptManager The Script Manager instance */
    private $scriptManager;

	/**
	 * Class constructor
	 */
	protected function __construct()
    {
        parent::__construct();
        $this->styleManager = $this->sci::make(StyleManager::class);;
        $this->scriptManager = $this->sci::make(ScriptManager::class);;
    }

    /**
     * Register a style into the Style Manager
     * 
     * @param Style $asset The Style instance
     * @param string $handle The Style handle
     * @param string $zone The style zone
     * @return \MyPlugin\Sci\Manager\AssetManager
     */
    function style($asset, $handle, $zone = false)
	{
        $this->styleManager->register($asset, $handle, $zone);
        return $this;
    }
    /**
     * Register a script into the Script Manager
     * 
     * @param Script $asset The script instance
     * @param string $handle The script handle
     * @param string $zone The script zone
     * @return \MyPlugin\Sci\Manager\AssetManager
     */
    function script($asset, $handle, $zone = false)
	{
        $this->scriptManager->register($asset, $handle, $zone);
        return $this;
    }
}
