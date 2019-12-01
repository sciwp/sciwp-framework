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

	/** @var TemplateManager $template_manager The instance of the template manager. */
	private $template_manager;

    public function __construct(TemplateManager $template_manager)
    {
        $this->template_manager = $template_manager;
    }

    public function template($key, $array_or_path, $name = false, $post_types = false, $theme_path = false)
    {
        $template = new Template($this->plugin, $array_or_path, $name, $post_types, $theme_path);
        $this->template_manager->add($key, $template);
        return $template;
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
        $this->template_manager->templates($plugin_id, (array) $this->plugin->config()['templates']); 
        return $this;
    }
}