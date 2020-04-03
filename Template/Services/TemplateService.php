<?php
namespace Sci\Template\Services;

defined('WPINC') OR exit('No direct script access allowed');

use Exception;
use Sci\Plugin\Plugin;
use Sci\Template\Template;
use Sci\Template\Managers\TemplateManager;

/**
 * Template Service
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class TemplateService
{
	/** @var string $plugin The plugin this servuice belongs to. */
	private $plugin;

	/** @var TemplateManager $templateManager The instance of the template manager. */
	private $templateManager;

    /**
     * Constructor
     */
    public function __construct(Plugin $plugin, TemplateManager $templateManager)
    {
        $this->plugin = $plugin;
        $this->templateManager = $templateManager;
    }

    /**
     * Allows to add a template using a relative route
     * 
     * @param string $template The plugin relative route to the template file
     * @param string $name The name to display in WordPress for the template
     * @param string|array $postTypes The post type or post types to add to the template
     * @param string $themePath The path relative to the theme where the plugin should also look for
     * @return void
     */
    public function template($template, $name = false, $postTypes = false, $themePath = false)
    {
        if (is_array($template)) {
            $template['path'] = $this->plugin->getDir() . '/' . $template['path'];
        } else {
            $template = $this->plugin->getDir() . '/' . $template;
        }
        
        return Template::create($template, $name, $postTypes, $themePath);
    }
    
    /**
     * Allows to add a set of templates in array format
     * 
     * @param array $templatesDataArr Set of arrays with template data
     * @param boolean $register If the templates should be registered
     * @return void
     */
    public function templates($templatesDataArr, $register = false)
    {
        foreach ($templatesDataArr as $key => $templateData) {
            $template = $this->template($templateData);
            if($register) {
                $template->register($key);
            }
        }
        return $this;
    }

	/**
	 * Initializes the class
	 *
	 * @return self
	 */
	public function configure()
	{
        $templates = $this->plugin->config()->get('templates');
        
        if (!$templates) return;

        foreach ( (array) $templates as $key => $template) {
            
            if (is_array($template)) {
                $template['path'] = $this->plugin->getDir() . '/' . $template['path'];
            } else {
                $template = [
                    'path' => $this->plugin->getDir() . '/' . $template,
                    'name' => $key,
                    'post_types' => ['post']
                ];
            }

            Template::create($template)->register($key);
        }

        return $this;
    }
}