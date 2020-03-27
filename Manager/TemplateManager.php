<?php
namespace MyPlugin\Sci\Manager;

defined('WPINC') OR exit('No direct script access allowed');

use \MyPlugin\Sci\Manager;
use \MyPlugin\Sci\Template;
use \Exception;

/**
 * TemplateManager
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */

class TemplateManager extends Manager
{
	/** @var array $templates The array of templates that the plugins include. */
	private $templates = array();

	/** @var boolean $filtersAdded If the WP filters have been added or not. */
    private $filtersAdded = false;
    
    /** @var string[] $postTypesWithTemplates The post types with templates. */
	private $postTypesWithTemplates = [];

	/**
	 * Class constructor
     *
     * @return \MyPlugin\Sci\Manager\TemplateManager
	 */
	protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Add a new template to the template manager
     *
     * @param \MyPlugin\Sci\Template $template The template object
     * @param string|bool $key The template identification key
     * @return \MyPlugin\Sci\Manager\TemplateManager
     */
	public function register($template, $key = false)
	{
        if (!is_object($template) || !($template instanceof \MyPlugin\Sci\Template)) {
            throw new Exception('Only instances of the Template class can be registered.');
        }

        if (!$key) $key = $this->getTemplatesNextArrKey();

        $this->templates[$key] = $template;

        foreach ($template->getPostTypes() as $postType) {
            if (!in_array($postType, $this->postTypesWithTemplates)) {
                $this->postTypesWithTemplates[] = $postType;
            }
        }

        if (!$this->filtersAdded) $this->addFilters();

        return $this;
    }

    /**
     * Get next array numeric key
     *
     * @return integer
     */
    public function getTemplatesNextArrKey()
    {
        if (count($this->templates)) {
            $numericKeys = array_filter(array_keys($this->templates), 'is_int');
            if (count($numericKeys)) {
                return max($numericKeys) + 1;
            }
        }
        return 1;
    }

    /**
     * Add filters to WordPress so the templates are processed
     *
     * @return \MyPlugin\Sci\Manager\TemplateManager
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

        // Page attributes support for post types with templates
        add_action( 'admin_init', [$this, 'addPageAttributesSupport'] );

        // Add Attributes meta box to custom post types with page-attributes option enabled
        add_action( 'add_meta_boxes', [$this, 'addPostTypeTemplateDropdown'] );
        add_action( 'save_post', [$this, 'addSaveTemplateAction'] );

        // To avoid repeating this action
        $this->filtersAdded = true;
        return $this;
	}

    /**
     * Add support for page-attributes
     * 
     * @param string $postType The name of the the post type
     * 
     * @return void
     */
    function addPageAttributesSupport()
    {
        foreach ($this->postTypesWithTemplates as $postType) {
            if (!post_type_supports($postType, 'page-attributes') ) {
                add_post_type_support( $postType, 'page-attributes' );
            }
        }
    }

    /**
     * Add the attributes meta box to posts with page-attributes enabled
     * 
     * @return void
     */
    function addPostTypeTemplateDropdown()
    {
        global $post;
        if ( 'page' != $post->post_type && post_type_supports($post->post_type, 'page-attributes') ) {
            add_meta_box( 'custompageparentdiv', __('Template'), [$this, 'addPostTypeAttributesMetaBox'], NULL, 'side', 'core');
        }
    }

    /**
     * Add the attributes meta box with the template selector
     * 
     * @param \WP_POST $post The post object
     * @return void
     */
    function addPostTypeAttributesMetaBox($post)
    {
        $template = get_post_meta( $post->ID, '_wp_page_template', 1 );
        ?>
        <select name="page_template" id="page_template">
            <?php $default_title = apply_filters( 'default_page_template_title',  __( 'Default Template' ), 'meta-box' ); ?>
            <option value="default"><?php echo esc_html( $default_title ); ?></option>
            <?php page_template_dropdown($template); ?>
        </select>
        <?php
    }
    
    /**
     * Save the selected template
     * 
     * @param int $postId The id of the post which will be saved
     * @return void
     */
    function addSaveTemplateAction( $postId )
    {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) return;
        if ( ! current_user_can( 'edit_post', $postId ) ) return;
        if ( ! empty( $_POST['page_template'] ) && get_post_type( $postId ) != 'page' ) {
            update_post_meta( $postId, '_wp_page_template', $_POST['page_template'] );
        }
    }

	/**
	 * Adds our template to the page dropdown for v4.7+
     *
     * @param array $postTemplates The current post templates
	 * @return array
	 */
	public static function addTemplatesToDropdown($postTemplates)
    {
        foreach ($this->templates as $key => $template) {
			if (in_array(get_post_type(), $template->getPostTypes())) {
				$postTemplates[$key] = $template->getName();
			}
        }

        return $postTemplates;
	}

	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
     * 
     * @param mixed $atts
     * @return mixed
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

		// Add the modified cache to allow WordPress to pick it up for listing available templates
		wp_cache_add($cache_key, $templates, 'themes', 1800);

		return $atts;
	}

	/**
	 * Checks if the template is assigned to the page
     *
     * @param string $template Template id or template path
	 * @return array
	 */
	public function viewTemplate( $templatePath )
    {
		global $wp_version;

		if (is_search()) return $templatePath;
		
		global $post;
        if (!$post) return $templatePath;

		$selectedTemplate = get_post_meta($post->ID, '_wp_page_template', true);

		$templates = array();
        foreach ($this->templates as $key => $tpl) {
            if (in_array(get_post_type(), $tpl->getPostTypes())) {
                $templates[$key] = $tpl;
			}
        }

		if (!isset( $templates[$selectedTemplate])) return $templatePath;

		if (file_exists($templates[$selectedTemplate]->getThemePath())) return $templates[$selectedTemplate]->getThemePath();
        
        if (file_exists( $templates[$selectedTemplate]->getPath() )) return $templates[$selectedTemplate]->getPath();

		return $templatePath;
	}
}
