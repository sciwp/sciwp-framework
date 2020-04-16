<?php
namespace Sci\Helpers;

defined('WPINC') OR exit('No direct script access allowed');

use Sci\Support\Helper;

/**
 * Scripts helper
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
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