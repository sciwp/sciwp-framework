<?php
namespace MyPlugin\Sci\Services;

defined('WPINC') OR exit('No direct script access allowed');

use MyPlugin\Sci\Template;
use MyPlugin\Sci\Plugin;
use MyPlugin\Sci\Manager\TemplateManager;
use Exception;


/**
 * TemplateService
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */

class TemplateService
{
	/** @var string $plugin The plugin this servuice belongs to. */
	private $plugin;

	/** @var TemplateManager $templateManager The instance of the template manager. */
	private $templateManager;

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
     * @return void
     */
    public function templates($templatesDataArr)
    {
        foreach ($templatesDataArr as $key => $templateData) {
            $this->template($templateData)->register($key);
        }
        return $this;
    }

	/**
	 * Initializes the class
	 *
     * @param \MyPlugin\Sci\Plugin|string $plugin The plugin/id
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