<?php
namespace Sci\Sci\Helpers;

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Folder Helper class
 *
 * @author		Eduardo Lazaro Rodriguez <eduzroco@gmail.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
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