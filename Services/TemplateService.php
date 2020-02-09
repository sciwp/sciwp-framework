<?php
namespace Wormvc\Wormvc\Services;

defined('WPINC') OR exit('No direct script access allowed');

use \Wormvc\Wormvc\Template;
use \Wormvc\Wormvc\Plugin;
use \Wormvc\Wormvc\Manager\TemplateManager;
use \Exception;


/**
 * TemplateService
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */

class TemplateService
{
	/** @var string $plugin The plugin this servuice belongs to. */
	private $plugin;

	/** @var TemplateManager $templateManager The instance of the template manager. */
	private $templateManager;

    public function __construct(TemplateManager $templateManager)
    {
        $this->templateManager = $templateManager;
    }

    public function template($key, $template, $name = false, $post_types = false, $theme_path = false)
    {
        $templateInstance = Template::create($key, $template, $name, $post_types, $theme_path);
        $this->templateManager->register($templateInstance);
        return $templateInstance;
    }
    
    public function templates($tempate_data_arr)
    {
        foreach ($tempate_data_arr as $key => $tempate_data){
            $this->template($key, $tempate_data);
        }
        return $this;
    }
    
	public function init($plugin_id)
	{
        $this->plugin = $plugin_id instanceof \Wormvc\Wormvc\Plugin ? $plugin_id : $this->wormvc->plugin($plugin_id);

        if (!isset($this->plugin->config()['templates'])) return;

        foreach ((array)$this->plugin->config()['templates'] as $key => $template) {
            
            if (is_array($template)) {
                $template['path'] = $this->plugin->getDir() . '/' . $template['path'];
            } else {
                $template = $this->plugin->getDir() . '/' . $template;
            }
            $this->template($key, $template); 
        }

        return $this;
    }
}