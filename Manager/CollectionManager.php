<?php
namespace KNDCC\Wormvc\Manager;

use \KNDCC\Wormvc\Collection;
use \KNDCC\Wormvc\Manager;

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Collection Manager
 *
 * @author		Eduardo Lazaro Rodriguez <me@mcme.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */
 
class CollectionManager extends Manager
{	
    /** @var array $collections Stores a list of the registered collections */
    private $collections = array();

    /**
     * Constructor
     */	
	public function __construct(){}

    /**
     * Add a collection
     * 
     * @param string $collection_id The collection id
     * @return Collection
     */    
    public function add($collection_id)
    {
        if (!isset($this->collections[$collection_id])) {
            $this->collections[$collection_id] = $this->wormvc->get(Collection::class);
        }
        return $this->collections[$collection_id];
    }

    public function all()
    {
        return $this->collections;
    }
	
	public function get($collection_id)
	{
		return isset($this->collections[$collection_id]) ? $this->collections[$collection_id] : false;
	}

 	public function remove($collection_id)
	{
        if (isset($this->collections[$collection_id])) {
            unset($this->collections[$collection_id]);
            return true;
        }
        return false;
	}   
}