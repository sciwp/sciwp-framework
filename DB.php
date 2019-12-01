<?php
namespace Wormvc\Wormvc;

defined('WPINC') OR exit('No direct script access allowed');

use Wormvc\Wormvc\Query;

/**
 * DB Class
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.wormvc.com 
 * @since		Version 1.0.0 
 */
abstract class DB
{    
    /**
     * Returns the table name
     *
     * @param string $table The table name
     * @return \Wormvc\Wormvc\Query
     */
    public static function table($tableName)
    {
        $query = new Query($tableName);
        return $query;
    }

    /**
     * Start a query
     *
     * @return \Wormvc\Wormvc\Query
     */
    public static function query($params)
    {
        return new Query();
    }
}