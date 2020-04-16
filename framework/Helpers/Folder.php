<?php
namespace Sci\Helpers;

defined('WPINC') OR exit('No direct script access allowed');

use Sci\Support\Helper;


/**
 * Folder helper
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class Folder extends Helper
{
	/**
	 * Require all files in a folder, including subfolders
	 *
	 * @param $folder An absolute system path
	 */
	public static function requireAll ($folder)
	{
		$scan = preg_grep('/^([^.])/', scandir($folder));
		foreach ($scan as $file) {
			if(is_dir ($folder.'/'.$file)) {
				self::requireAll($folder.'/'.$file);
			}
			else {
				require_once($folder.'/'.$file);
			}
		}
	}
}