<?php
namespace Sci\Database;

defined('WPINC') OR exit('No direct script access allowed');

/**
 * DB
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.sciwp.com
 * @since		Version 1.0.0 
 */
abstract class DB
{
    /**
     * Returns the table name
     *
     * @param string $table The table name
     * @return Query
     */
    public static function table($tableName)
    {
        $query = new Query($tableName);
        return $query;
    }

    /**
     * Start a query
     *
     * @return Query
     */
    public static function query()
    {
        return new Query();
    }
}