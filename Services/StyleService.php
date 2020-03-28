<?php
namespace MyPlugin\Sci\Services;

defined('WPINC') OR exit('No direct script access allowed');

use MyPlugin\Sci\Manager\StyleManager;
use MyPlugin\Sci\Assets\Style;
use MyPlugin\Sci\Plugin;
use Exception;

/**
 * StyleService
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */

class StyleService
{
	/** @var string $plugin The plugin this service belongs to. */
	private $plugin;

	/** @var StyleManager $styleManager The instance of the style manager. */
	private $styleManager;

    /**
     * Constructor
     * 
     */
    public function __construct(Plugin $plugin, StyleManager $styleManager)
    {
        $this->plugin = $plugin;
        $this->styleManager = $styleManager;
    }

    /**
	 * Read the plugin configuration
	 *
     * @param \MyPlugin\Sci\Plugin|string $plugin The plugin/id
	 * @return $this
	 */
	public function configure()
	{
        $styles = $this->plugin->config()->get('styles');
        
        if (!$styles) return;

        foreach ( (array) $styles as $handle => $style) {
            
            if (is_array($style)) {

                $src = plugin_dir_url($this->plugin->getDir()) . '/' . $style['src'];
                $version = $style['version'] ?? $version;
                $dependences = $style['dependences'] ?? $dependences;
                $media = $style['media'] ?? $media;
                $zone = $style['zone'] ?? false;
                Style::create($src, $version, $dependences, $media)->register($handle, $zone);

            } else {
                $src = plugin_dir_url($this->plugin->getDir()) . '/' . $style;
                Style::create($src)->register($handle);
            }
        }

        return $this;
    }
}