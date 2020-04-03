<?php
namespace Sci\Traits;

use Sci\Sci as Core;

defined('WPINC') OR exit('No direct script access allowed'); 

/**
 * Sci trait
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
trait Sci
{
    /** @var Sci $Sci Stores the Sci reference */
    public $sci;

    /**
     * Get the main Sci class instance
     */
    public function Sci()
    {
        if ($this->sci === null) {
            $this->sci = Core::instance();
        }
        return $this->sci;
    }
}