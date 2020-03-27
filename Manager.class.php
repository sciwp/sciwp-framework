<?php
namespace MyPlugin\Sci;

defined('WPINC') OR exit('No direct script access allowed');

use \MyPlugin\Sci\Sci;
use \MyPlugin\Sci\Traits\Singleton;

/**
 * Manager Base class
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
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