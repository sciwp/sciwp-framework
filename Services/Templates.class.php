<?php
/**
 * TemplateManager
 */

namespace KNDCC\Wormvc\Service;

use \KNDCC\Wormvc\Plugin;
 
class Templates {

	use \KNDCC\Wormvc\Traits\StaticClass;

	const SELF = __class__;

	/**
	 * The array of templates that this plugin tracks.
	 */
	public static $templates = array();


	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	public static function init() {

		self::$templates = array();


		// Add a filter to the attributes metabox to inject template into the cache.
		if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ) {
			add_filter('page_attributes_dropdown_pages_args',array( self::SELF, 'registerTemplates' ));
		}
		else {
			add_filter('theme_page_templates', array( self::SELF, 'addTemplatesToDropdown' ));
		}

		// Add a filter to the save post to inject out template into the page cache
		add_filter('wp_insert_post_data', array( self::SELF, 'registerTemplates' ));

		// Add a filter to the template include to determine if the page has our template assigned and return it's path
		add_filter('template_include', array( self::SELF, 'viewTemplate'));
	}

	public static function addTemplates	($templates)
	{
		foreach ($templates as $key => $template) {
			if ( !is_array($templates[$key]) ) $templates[$key] = array('name' => $template);
			if (!isset($templates[$key]['name'])) $templates[$key]['name'] = $key;
			if (!isset($templates[$key]['post_type'])) $templates[$key]['post_type'] = array();
			if (!isset($templates[$key]['file_theme'])) $templates[$key]['file_theme'] = $key;
			if (!is_array($templates[$key]['post_type'])) $templates[$key]['post_type'] = (array) $templates[$key]['post_type'];
		}
		self::$templates = array_merge_recursive(self::$templates, $templates);
	}
	
	
	/**
	 * Adds our template to the page dropdown for v4.7+
	 *
	 */
	public static function addTemplatesToDropdown( $posts_templates ) {
		
        // get new templates per post type
        $templates = array();
		
        foreach (self::$templates as $key => $template) {
			if (empty($template['post_type'])) $template['post_type'][] = 'page';
			if (in_array(get_post_type(), $template['post_type'])) {
				$templates[$key] = $template['name'];
			}
        }
        $posts_templates = array_merge($posts_templates, $templates);
        return $posts_templates;
	}

	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 */
	public static function registerTemplates( $atts ) {

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list.
		// If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}

		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key , 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
	

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;

	}

	/**
	 * Checks if the template is assigned to the page
	 */
	public static function viewTemplate( $template ) {
		global $wp_version;

		if ( is_search() ) return $template;
		
		global $post; if ( ! $post ) return $template;

		$selected_template = get_post_meta($post->ID, '_wp_page_template', true);

        if (version_compare($wp_version, '4.7', '<')) {
			$templates = self::$templates;
        }
		else {
			$templates = array();
            foreach (self::$templates as $key => $tpl) {
                if (in_array(get_post_type(), $tpl['post_type'])) {
					$templates[$key] = $tpl;
			    }
            }
        }

		//If the template is not defined by this plugin, continue
		if ( ! isset( $templates[$selected_template] ) ) return $template;

		//Priorizar tema
		$template_theme_file =  get_theme_root() . '/'. get_stylesheet().'/'.ltrim($templates[$selected_template]['file_theme'], '/');
		if (file_exists($template_theme_file)) return $template_theme_file;

		$template_plugin_file =  Plugin::file() . $selected_template;
		if ( file_exists( $template_plugin_file ) ) return $template_plugin_file;
		return $template;

	}

}
