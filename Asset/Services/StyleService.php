<?php
namespace Sci\Asset\Services;

defined('WPINC') OR exit('No direct script access allowed');

use Exception;
use Sci\Sci;
use Sci\Plugin\Plugin;
use Sci\Asset\Style;
use Sci\Asset\Managers\StyleManager;

/**
 * Style Service
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
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
     */
    public function __construct(Plugin $plugin, StyleManager $styleManager)
    {
        $this->plugin = $plugin;
        $this->styleManager = $styleManager;
    }

    /**
	 * Read the plugin configuration
	 *
     * @param Plugin|string $plugin The plugin/id
	 * @return StyleService
	 */
	public function configure()
	{
        $styles = $this->plugin->config()->get('styles');
        
        if (!$styles) return;

        foreach ( (array) $styles as $handle => $style) {
            
            if (is_array($style)) {

                $src = plugin_dir_url($this->plugin->getDir()) . '/' . $style['src'];
                $version = $style['version'] ?? $version;
                $dependencies = $style['dependencies'] ?? $dependencies;
                $media = $style['media'] ?? $media;
                $zone = $style['zone'] ?? false;
                $style = Sci::make(Style::class, [$src, $version, $dependencies, $media]);
                $this->styleManager->register($style, $handle, $zone);
            } else {
                $src = plugin_dir_url($this->plugin->getDir()) . '/' . $style;
                $style = Sci::make(Style::class, [$src]);
                $this->styleManager->register($style, $handle);
            }
        }

        return $this;
    }
}