<?php
namespace MyPlugin\Sci\Manager;

defined('WPINC') OR exit('No direct script access allowed');

use Exception;
use MyPlugin\Sci\Manager;
use MyPlugin\Sci\Assets\Style;

/**
 * StyleManager
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */
class StyleManager extends Manager
{    
    /** @var array $styles Array with styles. */
    private $styles = [];

    /** @var array $zones Array with handles organized by zone. */
    private $zones = ['front' => [], 'admin' => []];

	/** @var boolean $filtersAdded If the WP filters have been added or not. */
    private $filtersAdded = false;

	/**
	 * Class constructor
     *
     * @return \MyPlugin\Sci\Manager\StyleManager
	 */
	protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Register a style into the Style Manager
     * 
     * @param Style $asset The Style instance
     * @param string $handle The Style handle
     * @param string $zone The style zone
     * @return \MyPlugin\Sci\Manager\StyleManager
     */
    function register($asset, $handle, $zone = false)
	{
        if (!($asset instanceof Style)) {
            throw new Exception('Only instances of the Style class can be registered into the Style Manager.');
        }

        $this->styles[$handle] = $asset;

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
	 * Enqueue styles
	 *
     * @param string $zone The style zone
	 * @return \MyPlugin\Sci\Manager\StyleManager
	 */
	public function enqueue($zone)
	{
        if (!isset($this->zones[$zone])) return $this;

        foreach($this->styles as $handle => $style) {
            if (in_array($handle, $this->zones[$zone])) {
                wp_register_style($handle, $style->getSrc(), $style->getDependences(), $style->getVersion(), $style->getMedia());
            }
        }

        foreach($this->zones[$zone] as $handle) {
            wp_enqueue_style($handle);
        }

        return $this;
    }

    /**
     * Add filters to WordPress so the styles are processed
     *
     * @return \MyPlugin\Sci\Manager\StyleManager
     */
	public function addFilters()
	{
        // Enqueue frontend styles
        add_action( 'wp_enqueue_scripts', function() {
            $this->enqueue('front');
        });

        // Enqueue admin panel styles
        add_action( 'admin_enqueue_scripts', function() {
            $this->enqueue('admin');
        });

        // To avoid repeating this action
        $this->filtersAdded = true;
        return $this;
	}
}
