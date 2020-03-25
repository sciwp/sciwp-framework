<?php
namespace MyPlugin\Sci;

defined('WPINC') OR exit('No direct script access allowed');

use Exception;
use \MyPlugin\Sci\Plugin;
use \MyPlugin\Sci\Manager\TemplateManager;

/**
 * Template
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */
 
class Template
{
    /** @var \MyPlugin\Sci\Manager\TemplateManager $templateManager The Sci template manager */
    protected $templateManager;

	/** @var string $path The template path relative to the plugin base folder */
	protected $path;

	/** @var string $themePath The path relative to the theme where the plugin should also look for */
	protected $themePath;

	/** @var string $name The name to display in WordPress for the template */
	protected $name;

	/** @var string $postTypes The post type to add the template to */
	protected $postTypes;

    /**
     * Create a new template
     *
     * @param string $template|array The template file or array with the other fields
     * @param string $name The name to display in WordPress for the template
     * @param string|array $postTypes The post type or post types to add to the template
     * @param string $themePath The path relative to the theme where the plugin should also look for
     */
    public function __construct($template, $name = false, $postTypes = false, $themePath = false)
    {
        $this->templateManager = \MyPlugin\Sci\Sci::instance()->templateManager();

        if (is_array($template)) {

            if (!isset($template['path']) || !$template['path']) {
                throw new Exception('A template path is required.');
            }

            $path = $template['path'];

            if (!$name && isset($template['name'])) {
                $name = $template['name'];
            }

            if (!$postTypes && isset($template['post_types'])) {
                $postTypes = $template['post_types'];
            }

            if (!$themePath && isset($template['theme_path'])) {
                $themePath = $template['theme_path'];
            }

        } else {
            $path = $template;
        }

        if (!$name) throw new Exception('A template name is required.');

        $this->path = $path;
        $this->name = $name;
        $this->postTypes = $postTypes ? (array) $postTypes : [];
        if ($themePath) $this->themePath = $themePath;
    }

	/**
	 * Add a new template
	 *
     * @param string $template The template file or array with the other fields
     * @param string $name The name to display in WordPress for the template
     * @param string|array $postTypes The post type or post types to add to the template
     * @param string $themePath The path relative to the theme where the plugin should also look for
	 * @return \MyPlugin\Sci\Template
	 */
    public static function create($template, $name = false, $postTypes = false, $themePath = false)
    {
        $template = new self($template, $name, $postTypes, $themePath);
        return  $template;
    }

    /**
     * Add the template to the template manager
     * @param string $key The template key
     * @return \MyPlugin\Sci\Template
     */
    public function register($key = false)
    {
        if (!$key) $key = str_replace(' ', '-', strtolower($this->name));
        $this->templateManager->register($this, $key);
        return $this;
    }

    /**
     * Returns the template plugin path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns the template theme path
     *
     * @return string
     */
    public function getThemePath()
    {
        return get_theme_root() . '/'. get_stylesheet() . '/' . ltrim($this->themePath, '/');
    }

    /**
     * Returns the post name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;  
    }

    /**
     * Returns the post types
     *
     * @return array
     */
    public function getPostTypes()
    {
        return $this->postTypes;
    }

    /**
     * Set the plugin
     *
     * @param string|\MyPlugin\Sci\Plugin $plugin
     * @return \MyPlugin\Sci\Template
     */
    public function setPlugin($plugin_id)
    {
        $this->plugin = $plugin instanceof \MyPlugin\Sci\Plugin ? $plugin : $this->Sci->plugin($plugin_id);
        return $this;
    }

    /**
     * Set the template path in the plugin
     *
     * @param string $path The path of the template
     * @return \MyPlugin\Sci\Template
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Set the template path in the theme
     *
     * @param string $path The path of the template file in the theme
     * @return \MyPlugin\Sci\Template
     */
    public function setThemePath($themePath)
    {     
        $this->themePath = $themePath;
        
        return $this;
    }

    /**
     * Se the template name
     *
     * @param array|string $postTypes The name to display in WordPress
     * @return \MyPlugin\Sci\Template
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set the post types array
     *
     * @param array|string $postTypes A post type or an array of post types
     * @return \MyPlugin\Sci\Template
     */
    public function setPostTypes($postTypes)
    {
        $this->postTypes = (array) $postTypes;
        return $this;
    }
}