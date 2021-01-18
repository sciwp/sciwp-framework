<?php
namespace Sci;

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Component Base
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class Component
{
    /**
	 * Creates an object of the class
     * 
     * @param mixed[] $params The method parameters
	 */
	public static function create(...$params)
    {
        return new static(...$params);
    }
}
