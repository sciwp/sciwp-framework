<?php
namespace Sci;

defined('WPINC') OR exit('No direct script access allowed');

use Sci\Sci;
use Sci\Traits\Singleton;

/**
 * Manager Base
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
class Manager
{
    use Singleton;

    /** @var $sci The Sci class reference */
    protected $sci;

	/**
	 * Class constructor
	 */
	protected function __construct()
    {
        $this->sci = Sci::class;
    }
}