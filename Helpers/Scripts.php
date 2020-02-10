<?php
namespace Sci\Sci\Helpers;

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Scripts Helper class
 *
 * @author		Eduardo Lazaro Rodriguez <eduzroco@gmail.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */
class Scripts extends Helper
{
	/**
	 * Include Sci admin scripts
     */
	public static function includeAdmin()
	{
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_media();
		wp_enqueue_script('Sci_scripts', plugins_url('Sci/Resources/js/admin_scripts.js', __FILE__ ), false, '0.1', true);
	}
}