<?php
namespace Sci\Asset\Services;

defined('WPINC') OR exit('No direct script access allowed');

use Exception;
use Sci\Sci;
use Sci\Plugin\Plugin;
use Sci\Asset\Script;
use Sci\Asset\Managers\ScriptManager;

/**
 * Script Service
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class ScriptService
{
	/** @var string $plugin The plugin this service belongs to. */
	private $plugin;

	/** @var ScriptManager $scriptManager The instance of the script manager. */
	private $scriptManager;

    /**
     * Constructor
     */
    public function __construct(Plugin $plugin, ScriptManager $scriptManager)
    {
        $this->plugin = $plugin;
        $this->scriptManager = $scriptManager;
    }

    /**
	 * Read the plugin configuration
	 *
	 * @return ScriptService
	 */
	public function configure()
	{
        $scripts = $this->plugin->config()->get('scripts');
        
        if (!$scripts) return;

        foreach ( (array) $scripts as $handle => $script) {
            
            if (is_array($script)) {

                $src = plugin_dir_url($this->plugin->getDir()) . '/' . $script['src'];
                $version = $script['version'] ?? $version;
                $dependencies = isset($script['dependencies']) ?? $dependencies;
                $footer = $script['footer'] ?? $footer;
                $zone = $script['zone'] ?? false;
                $script = Sci::make(Script::class, [$src, $version, $dependencies, $footer]);
                $this->scriptManager->register($script, $handle, $zone);

            } else {
                $src = plugin_dir_url($this->plugin->getDir()) . '/' . $script;
                $script = Sci::make(Script::class, [$src]);
                $this->scriptManager->register($script, $handle);
            }
        }

        return $this;
    }
}