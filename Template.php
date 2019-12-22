<?php
namespace Wormvc\Wormvc;

defined('WPINC') OR exit('No direct script access allowed');

use \Wormvc\Wormvc\Plugin;
use \Wormvc\Wormvc\Manager\TemplateManager;

/**
 * Template
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */
 
class Template
{
    /** @var \Wormvc\Wormvc\Manager\TemplateManager $template_manager The wormvc template manager */
     protected $template_manager;
    
	/** @var string $plugin The plugin where the template is located */
	protected $plugin;

	/** @var string $path The template path relative to the plugin base folder */
	protected $path;

	/** @var string $theme_path The path relative to the theme where the plugin should also look for */
	protected $theme_path;

	/** @var string $name The name to display in WordPress for the template */
	protected $name;

	/** @var string $post_types The post type to add the template to */
	protected $post_types;

    /**
     * Create a new template
     *
     * @param string|\Wormvc\Wormvc\Plugin $plugin_id The plugin id
     * @param string|array $template Plugin array data or the template path relative to the plugin base folder
     * @param string $name The name to display in WordPress for the template
     * @param string|array $post_types The post type or post types to add to the template
     * @param string $theme_path The path relative to the theme where the plugin should also look for
     */
    public function __construct($plugin_id, $template, $name = false, $post_types = false, $theme_path = false)
    {
        $this->template_manager = TemplateManager::instance();
        $this->plugin = $plugin_id instanceof Plugin ? $plugin_id : $this->wormvc->plugin($plugin_id);
        
        if (is_array($template)) {
            if (!isset($template['path']) || !$template['path']) throw new Exception('A template path is required.');
            $path = $template['path'];
            if (!$name && isset($template['name'])) $name = $template['name'];
            if (!$post_types && isset($template['post_types'])) $post_types = $template['post_types'];
            if (!$theme_path && isset($template['theme_path'])) $theme_path = $template['theme_path'];
        } else {
            $path = $template;
        }

        $this->path = $path;
        $this->name = $name;
        $this->post_types = $post_types ? (array) $post_types : [];
        if ($theme_path) $this->theme_path = $theme_path;
    }

    /**
     * Add the template to the template manager
     *
     * @return \Wormvc\Wormvc\Template
     */
    public function register($key) {
        if (!$this->name) $this->name = $key;
        $this->template_manager->add($key, $this);     
        return $this;
    }

    /**
     * Returns the plugin
     *
     * @return string
     */
    public function getPlugin() {
        return $this->plugin;
    }

    /**
     * Returns the template plugin path
     *
     * @return string
     */
    public function getPath() {
        return $this->plugin->getDir() . '/' . $this->path;
    }

    /**
     * Returns the template theme path
     *
     * @return string
     */
    public function getThemePath() {
        return get_theme_root() . '/'. get_stylesheet() . '/' . ltrim($this->theme_path, '/');
    }

    /**
     * Returns the post name
     *
     * @return string
     */
    public function getName() {
        return $this->name;  
    }

    /**
     * Returns the post types
     *
     * @return array
     */
    public function getPostTypes() {
        return $this->post_types;
    }

    /**
     * Set the plugin
     *
     * @param string|\Wormvc\Wormvc\Plugin $plugin
     * @return \Wormvc\Wormvc\Template
     */
    public function setPlugin($plugin_id)
    {
        $this->plugin = $plugin instanceof \Wormvc\Wormvc\Plugin ? $plugin : $this->wormvc->plugin($plugin_id);
        return $this;
    }

    /**
     * Set the template path in the plugin
     *
     * @param string $path The path of the template
     * @return \Wormvc\Wormvc\Template
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
     * @return \Wormvc\Wormvc\Template
     */
    public function setThemePath($theme_path)
    {     
        $this->theme_path = $theme_path;
        
        return $this;
    }

    /**
     * Se the template name
     *
     * @param array|string $post_types The name to display in WordPress
     * @return \Wormvc\Wormvc\Template
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set the post types array
     *
     * @param array|string $post_types A post type or an array of post types
     * @return \Wormvc\Wormvc\Template
     */
    public function setPostTypes($post_types)
    {
        $this->post_types = (array) $post_types;
        return $this;
    }
}