<?php
namespace Sci\Asset\Managers;

defined('WPINC') OR exit('No direct script access allowed');

use Exception;
use Sci\Manager;
use Sci\Asset\Script;
use Sci\Plugin\Plugin;

/**
 * Script Manager
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class ScriptManager extends Manager
{    
    /** @var array $scripts Array with scripts. */
    private $scripts = [];

    /** @var array $zones Array with handles organized by zone. */
    private $zones = ['front' => [], 'admin' => []];

	/** @var boolean $filtersAdded If the WP filters have been added or not. */
    private $filtersAdded = false;

	/**
	 * Class constructor
	 */
	protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Register a script into the Style Manager
     * 
     * @param Script $asset The script instance
     * @param string $handle The script handle
     * @param string $zone The script zone
     * @return ScriptManager
     */
    public function register($asset, $handle, $zone = false)
    {
        if (!($asset instanceof Script)) {
            throw new Exception('Only instances of the Script class can be registered into the Script Manager.');
        }
        
        $this->scripts[$handle] = $asset;

        if ((!$zone || $zone == 'front') && !in_array($handle, $this->zones['front'] )) {
            $this->zones['front'][] = $handle;
        }

        if ((!$zone || $zone == 'admin') && !in_array($handle, $this->zones['admin'] )) {
            $this->zones['admin'][] = $handle;
        }

        if (!$this->filtersAdded) $this->addFilters();

        return $this;
    }

	/**
	 * Enqueue scripts
	 *
     * @param string $zone The script zone
	 * @return ScriptManager
	 */
	public function enqueue($zone)
	{
        if (!isset($this->zones[$zone])) return $this;

        foreach($this->scripts as $handle => $script) {
            if (in_array($handle, $this->zones[$zone])) {
                wp_register_script($handle, $script->getSrc(), $script->getDependences(), $script->getVersion(), $script->getFooter());
            }
        }

        foreach($this->zones[$zone] as $handle) {
            wp_enqueue_script($handle);
        }

        return $this;
    }

    /**
     * Add filters to WordPress so the scripts are processed
     *
     * @return ScriptManager
     */
	public function addFilters()
	{
        // Enqueue frontend scripts
        add_action( 'wp_enqueue_scripts', function() {
            $this->enqueue('front');
        });

        // Enqueue admin panel scripts
        add_action( 'admin_enqueue_scripts', function() {
            $this->enqueue('admin');
        });

        // To avoid repeating this action
        $this->filtersAdded = true;
        return $this;
    }
}
