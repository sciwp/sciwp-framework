<?php

namespace MyPlugin\Sci;

defined('WPINC') OR exit('No direct script access allowed');

use Exception;
use MyPlugin\Sci\Plugin;
use MyPlugin\Sci\Manager\PluginManager;

/**
 * Class View
 */
class View
{
    /** @var array $params Parameter array */
    protected $params = array();

    /** @var string $file */
	protected $file;

	/** @var string $plugin */
	protected $plugin;

	/** @var string $module */
	protected $module;

    /**
     * Constructor
     *
     * @param string $file $The view file without extensiona nd relative to the views folder
     * @param string|Plugin $plugin The plugin instance or plugin id
     * @param string $module The module name
     * @param PluginManager $pluginManager Plugin manager instance
     */
    public function __construct($file, $plugin = null, $module = null, PluginManager $pluginManager)
    {
		$this->pluginManager = $pluginManager;
		$this->file = $file;

		if ($plugin instanceof Plugin) $this->plugin = $plugin;
		else if ($plugin) $this->plugin = $this->pluginManager->get($plugin);
		else $this->plugin = $this->pluginManager->getMain();

		if ($module) $this->module = $module;
	}

    /**
     * Create a new template
     *
     * @param string $file The view file without extensiona nd relative to the views folder
     * @param string|Plugin $plugin The plugin instance or plugin id
     * @param string $module The module name
	 * @return View
     */
    public static function create($file, $plugin = null, $module = null)
    {
        return Sci::make(self::class, [$file, $plugin, $module]);
    }

    /**
     * Gets the full view path
     *
	 * @return string
     */
    private function getViewPath()
    {
		$viewsFolder = $this->plugin->config('views/dir');
		if (!$viewsFolder) $viewsFolder = 'views';

		$path = $this->file;

		if ($this->module !== null) {
			$path = $this->plugin->getModulesDir() . '/' . $this->module . '/' . $viewsFolder . '/' . $path;
		} else {
			$path = $this->plugin->getMainDir() . '/' . $viewsFolder. '/' . $path;
		}


		if (file_exists($path. '.view.php')) return $path. '.view.php';
		else if (file_exists($path. '.php')) return $path. '.php';

		throw new Exception('It was not possible to find the template ' . $path . '(.view).php');
	}

	/**
	 * Renders the view
	 *
	 * @param string $content Some content
	 * @return void
	 */
	public function render($content = '')
	{
		$file = $this->getViewPath();

		foreach($this->params as $key => $value) {
			${$key} = $value;
		}

		return include ($file);
	}

	/**
	 * Gets the result of rendering the view
	 * 
	 * @return String
	 */
	public function process()
	{
		$file = $this->getViewPath();

		ob_start();
		$this->render($file);
		$render = ob_get_contents();
		ob_end_clean();
		ob_end_flush();
		return ($render);
	}

	/**
	 * Set the view params
	 * 
	 * @return View
	 */
	public function params($params = array(), $override = false)
	{
		if ($override) $this->params = $params;
		else $this->params = array_merge($this->params, $params);
		return $this;
	}

	/**
	 * Set a view param
	 * 
	 * @return View
	 */
	public function param($key, $value)
	{
		$this->params[$key] = $value;
		return $this;
	}	
}