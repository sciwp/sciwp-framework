<?php
namespace MyPlugin\Sci\Traits;

use \MyPlugin\Sci\Sci as SciMain;

defined('WPINC') OR exit('No direct script access allowed'); 

/**
 * Trait Get
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.me>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */
trait Sci
{
    /** @var SciMain $Sci Stores the Sci reference */
    public $Sci;

    /**
     * Get the main Sci class instance
     */
    public function Sci()
    {
        if ($this->Sci === null) {
            $this->Sci = SciMain::instance();
        }
        return $this->Sci;
    }
}