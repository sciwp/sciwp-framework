<?php
/**
 * Class View
 */

namespace MyPlugin\Sci;

defined('WPINC') OR exit('No direct script access allowed');

use Plugin;
use Helper;

class View {

	use Traits\Singleton;

    /** @var Array $params Parameter array */
    protected $params = array();

    /** @var String $template */
    protected $template;

	/** @var \Documentor\Core\Plugin $plugin Plugin reference*/
	protected $plugin = Plugin::class;
	
	/**
	 * Set the view params
	 * 
	 * @return	Page
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
	 * @return	Page
	 */
	public function param($key, $value)
	{
		$this->params[$key] = $value;
		return $this;
	}

	/**
	 * Renders the current page
	 *
	 * @return	Void
	 */
	public function render($content = false)
	{
		foreach($this->params as $key => $value) ${$key} = $value;
		if ($this->template) return include ($this->template);
		else if ($content) echo($content);
		else if (count($this->params)) {
			echo("<pre>");
			print_r($this->params);
			echo("<pre>");
		}
	}
	/**
	 * Gets the result of rendering the view, not rendering it
	 * 
	 * @return	String
	 */
	public function process($path_to_file = false)
	{
		ob_start();
		$this->render($path_to_file);
		$render = ob_get_contents();
		ob_end_clean();
		ob_end_flush();
		return ($render);
	}		
}