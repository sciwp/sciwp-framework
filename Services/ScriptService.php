<?php
namespace MyPlugin\Sci\Services;

defined('WPINC') OR exit('No direct script access allowed');

use MyPlugin\Sci\Manager\ScriptManager;
use MyPlugin\Sci\Assets\Script;
use MyPlugin\Sci\Plugin;
use Exception;

/**
 * ScriptService
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */

class ScriptService
{
	/** @var string $plugin The plugin this service belongs to. */
	private $plugin;

	/** @var ScriptManager $scriptManager The instance of the script manager. */
	private $scriptManager;

    public function __construct(Plugin $plugin, ScriptManager $scriptManager)
    {
        $this->plugin = $plugin;
        $this->scriptManager = $scriptManager;
    }

    /**
	 * Read the plugin configuration
	 *
     * @param \MyPlugin\Sci\Plugin|string $plugin The plugin/id
	 * @return $this
	 */
	public function configure()
	{
        $scripts = $this->plugin->config()->get('scripts');
        
        if (!$scripts) return;

        foreach ( (array) $scripts as $handle => $script) {
            
            if (is_array($script)) {

                $src = plugin_dir_url($this->plugin->getDir()) . '/' . $script['src'];
                $version = $script['version'] ?? $version;
                $dependences = $script['dependences'] ?? $dependences;
                $footer = $script['footer'] ?? $footer;
                $zone = $script['zone'] ?? false;
                Script::create($src, $version, $dependences, $footer)->register($handle, $zone);

            } else {
                $src = plugin_dir_url($this->plugin->getDir()) . '/' . $script;
                Script::create($src)->register($handle);
            }
        }

        return $this;
    }
}