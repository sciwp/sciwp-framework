<?php
namespace MyPlugin\Sci;

defined('WPINC') OR exit('No direct script access allowed');

use MyPlugin\Sci\Query;

/**
 * DB Class
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */
abstract class DB
{    
    /**
     * Returns the table name
     *
     * @param string $table The table name
     * @return \MyPlugin\Sci\Query
     */
    public static function table($tableName)
    {
        $query = new Query($tableName);
        return $query;
    }

    /**
     * Start a query
     *
     * @return \MyPlugin\Sci\Query
     */
    public static function query($params)
    {
        return new Query();
    }
}