<?php
namespace Sci\Asset;

defined('WPINC') OR exit('No direct script access allowed');

use Exception;
use Sci\Sci;
use Sci\Asset\Asset;
use Sci\Asset\Managers\ScriptManager;

/**
 * Script
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class Script extends Asset
{
    /** @var boolean $footer If the script should be added in footer/header  */
    protected $footer;
    
    /** @var ScriptManager $scriptManager The Script Manager */
	protected $scriptManager;

    /**
     * Create a new Script
     * 
     * @param string $handle The script handle
     * @param string $src The script location
     * @param string $version The script asset version
     * @param string[]  $dependencies The registered script dependencies
     * @param string $footer Script location
     */
    public function __construct($src,  $version = false, $dependencies = [], $footer = true, ScriptManager $scriptManager)
    {
        parent::__construct($src, $version, $dependencies);
        $this->scriptManager = $scriptManager;
        $this->footer = $footer;
    }

	/**
	 * Add a new script
	 *
     * @param string $src The script location
     * @param string $version The script version
     * @param string[]  $dependencies The registered script dependencies
     * @param string $zone The script zone
     * @param string $footer Script location
	 * @return Script
	 */
    public static function create($src,  $version = false, $dependencies = [],  $footer = true)
    {
        $script = Sci::make(self::class, [$src, $version, $dependencies, $footer]);
        return  $script;
    }

    /**
     * Add the script to the Script Manager
     * 
     * @param string $handle The script handle
     * @param string $zone Frontend or admin panel
     * @return Script
     */
    public function register($handle, $zone = false)
    {
        $this->scriptManager->register($this, $handle, $zone);
        return $this;
    }

    /**
     * Returns if the asset should be placed in footer or in header
     *
     * @return boolean
     */
    public function getFooter()
    {
        return $this->footer;
    }

    /**
     * Sets if the asset should be placed in footer (true) or in header (false)
     * 
     * @param boolean $footer The boolen value
     * @return Script
     */
    public function setFooter($footer)
    {
        $this->footer = $footer;
        return $this;
    }
}