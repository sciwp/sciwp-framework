<?php
namespace Wormvc\Wormvc\Manager;

defined('WPINC') OR exit('No direct script access allowed');

use \Wormvc\Wormvc\Manager;
use \Wormvc\Wormvc\Template;
use \Exception;

/**
 * TemplateManager
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */

class TemplateManager extends Manager
{
	/** @var array $templates The array of templates that the plugins include. */
	private $templates = array();

	/** @var boolean $filters_added If the WP filters have been added or not. */
	private $filters_added = false;

    /**
     * Create a new template and add it to the template manager
     *
     * @param string $key The template key
     * @param string|array $array_or_path Template path or array with the template data
     * @param string $name The template name
     * @param array $postTypes Array with the post types of the template
     * @param string $themePath The plugin template path
     * @return \Wormvc\Wormvc\Manager\TemplateManager
     */
    public function template($key, $template, $name = false, $postTypes = false, $themePath = false)
	{
        $template = new Template($key, $template, $name, $postTypes, $themePath);
        $this->register($key, $template);
        return $template;
    }

    /**
     * Create a new template and add it to the template manager
     *
     * @param array $templatesArray An array of template definitions
     * @return \Wormvc\Wormvc\Manager\TemplateManager
     */
    public function templates($templatesArray)
	{
        foreach($templatesArray as $key => $templateData) {
            $this->template($key, $templateData);
        }
        return $this;
    }

    /**
     * Add a new template to the template manager
     *
     * @param string|arrat $key_or_array Array of tempaltes or template key
     * @param \Wormvc\Wormvc\Template $template The template identification name
     * @return \Wormvc\Wormvc\Manager\TemplateManager
     */
	public function register($template)
	{
        if (is_array($template)) {
            foreach($template as $key => $templateVal) {
                $this->register($key, $templateVal);
            }
            return $this;
        }

        if (!is_object($template) || !($template instanceof \Wormvc\Wormvc\Template)) {
            throw new Exception('Only instances of the Template class are accepted.');
        }

        $this->templates[$template->getKey()] = $template;

        if (!$this->filters_added) {
            $this->addFilters();
            $this->filters_added = true;
        }

        return $this;
    }

    /**
     * Add filters to WordPress so the templates are processed
     *
     * @return \Wormvc\Wormvc\Manager\TemplateManager
     */
	public function addFilters()
	{
		// Add a filter to the attributes metabox to inject template into the cache.
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {
			add_filter('page_attributes_dropdown_pages_args', [$this, 'registerTemplates']);
		}
		else {
			add_filter('theme_page_templates', [$this, 'addTemplatesToDropdown']);
		}

		// Add a filter to the save post to inject out template into the page cache
		add_filter('wp_insert_post_data', [$this, 'registerTemplates']);

		// Add a filter to the template include to determine if the page has our template assigned and return it's path
		add_filter('template_include', [$this, 'viewTemplate']);
        
        // To avoid repeating this action
        $this->filters_added = true;
        return $this;
	}

	/**
	 * Adds our template to the page dropdown for v4.7+
     *
     * @param array $post_templates The current post templates
	 * @return array
	 */
	public static function addTemplatesToDropdown($post_templates)
    {
        foreach ($this->templates as $key => $template) {
			if (in_array(get_post_type(), $template->getPostTypes())) {
				$post_templates[$key] = $template->getName();
			}
        }

        return $post_templates;
	}

	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 */
	public function registerTemplates( $atts )
    {
		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list. If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();
		if (empty($templates)) $templates = array();

		// New cache, therefore remove the old one
		wp_cache_delete($cache_key , 'themes');

		// Add our templates to the list of templates by merging them with the existing templates array.
        foreach ($this->templates as $key => $template) {
           $templates[$key] = $template->getName();
        }
        
        //$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add($cache_key, $templates, 'themes', 1800);

		return $atts;
	}

	/**
	 * Checks if the template is assigned to the page
     *
     * @param string $template Template id or template path
	 * @return array
	 */
	public function viewTemplate( $template_path )
    {
		global $wp_version;

		if (is_search()) return $template_path;
		
		global $post;
        if (!$post) return $template_path;

		$selected_template = get_post_meta($post->ID, '_wp_page_template', true);

		$templates = array();
        foreach ($this->templates as $key => $tpl) {
            if (in_array(get_post_type(), $tpl->getPostTypes())) {
                $templates[$key] = $tpl;
			}
        }

		if (!isset( $templates[$selected_template])) return $template_path;

		if (file_exists($templates[$selected_template]->getThemePath())) return $templates[$selected_template]->getThemePath();
        
        if (file_exists( $templates[$selected_template]->getPath() )) return $templates[$selected_template]->getPath();

		return $template_path;
	}
}
