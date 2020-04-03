<?php
namespace Sci\Manager;

defined('WPINC') OR exit('No direct script access allowed');

use Exception;
use Sci\View;
use Sci\Manager;
use Sci\Plugin\Plugin;

/**
 * View Manager
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class ViewManager extends Manager
{    
    /** @var array $directory View folder */
    private $directory = [];

	/**
	 * Class constructor
	 */
	protected function __construct()
    {
        parent::__construct();
    }

	/**
	 * Return a view
	 *
     * @param string $views The view relative route
     * @param string $module The plugin module
	 * @return ViewManager
	 */
	public function view($view, $module = false)
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
     * @return ViewManager
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
