<?php
namespace Wormvc\Wormvc\Traits;

use \Wormvc\Wormvc\Wormvc as WormvcMain;

defined('WPINC') OR exit('No direct script access allowed'); 

/**
 * Trait Get
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.me>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */
trait Wormvc
{
    /** @var WormvcMain $wormvc Stores the Wormvc reference */
    public $wormvc;

    /**
     * Get the main Wormvc class instance
     */
    public function wormvc()
    {
        if ($this->wormvc === null) {
            $this->wormvc = WormvcMain::instance();
        }
        return $this->wormvc;
    }
}