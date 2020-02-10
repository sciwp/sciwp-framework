<?php
namespace Sci\Sci\Helpers;

use Sci\Sci\Helper;
use Sci\Sci\Collection;

defined('WPINC') OR exit('No direct script access allowed');

/**
 * Array Helper class
 *
 * @author		Eduardo Lazaro Rodriguez <eduzroco@gmail.com>
 * @author		Kenodo LTD <info@kenodo.com>
 * @copyright	2018 Kenodo LTD
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @version     1.0.0
 * @link		https://www.Sci.com 
 * @since		Version 1.0.0 
 */
class Arr extends Helper
{
    /**
     * Sorts an array by a property inside its elements, which can be objects or arrays
     * 
     * @param array $arr The array we want to process
     * @param string $index The array index or attribute name
     * @param bool $asc Order ascending if true, descending in false
     * @param bool $preserveKeys Keep array indexes
     * @return array
     */
	public static function sortBySubValue($arr, $index, $asc = true, $preserveKeys = false)
	{
		if (is_object(reset($arr))) {
			$preserveKeys ? uasort($arr, function ($a, $b) use ($index, $asc) {
				return $a->{$index} == $b->{$index} ? 0 : ($a->{$index} - $b->{$index}) * ($asc ? 1 : -1);
			}) : usort($arr, function ($a, $b) use ($index, $asc) {
				return $a->{$index} == $b->{$index} ? 0 : ($a->{$index} - $b->{$index}) * ($asc ? 1 : -1);
			});
		} else {
			$preserveKeys ? uasort($arr, function ($a, $b) use ($index, $asc) {
				return $a[$index] == $b[$index] ? 0 : ($a[$index] - $b[$index]) * ($asc ? 1 : -1);
			}) : usort($arr, function ($a, $b) use ($index, $asc) {
				return $a[$index] == $b[$index] ? 0 : ($a[$index] - $b[$index]) * ($asc ? 1 : -1);
			});
		}
		return $arr;
	}

    /**
     * Merges 2 arrays and sub arrays, overwriting existing values
     * 
     * @param array $arr The array we want to process
     * @param string $index The array index or attribute name
     * @param bool $asc Order ascending if true, descending in false
     * @param bool $preserveKeys Keep array indexes
     * @return array
     */     
	public static function mergeRecursive(array &$array1, array &$array2)
	{
		$merged = $array1;
		foreach ($array2 as $key => &$value) {
			if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
				$merged[$key] = self::arrayMergeRecursiveDistinct($merged[$key], $value);
			}		
			else {
				$merged[$key] = $value;
			}
		}
		return $merged;
	}
    
    /**
     * Determine whether the given value is array accessible.
     *
     * @param  mixed  $value
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Collapse an array of arrays into a single array.
     *
     * @param  array  $array
     * @return array
     */
    public static function collapse($array)
    {
        $results = [];
        foreach ($array as $values) {
            if ($values instanceof Collection) {
                $values = $values->all();
            } elseif (! is_array($values)) {
                continue;
            }
            $results = array_merge($results, $values);
        }
        return $results;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|int  $key
     * @return bool
     */
    public static function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }
        return array_key_exists($key, $array);
    }      

    /**
     * Get all of the given array except for a specified array of keys.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    public static function except($array, $keys)
    {
        static::forget($array, $keys);
        return $array;
    }
    
    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param  array  $array
     * @param  int  $depth
     * @return array
     */
    public static function flatten($array, $depth = INF)
    {
        $result = [];
        foreach ($array as $item) {
            $item = $item instanceof Collection ? $item->all() : $item;
            if (! is_array($item)) {
                $result[] = $item;
            } elseif ($depth === 1) {
                $result = array_merge($result, array_values($item));
            } else {
                $result = array_merge($result, static::flatten($item, $depth - 1));
            }
        }
        return $result;
    }

    /**
     * Push an item onto the beginning of an array.
     *
     * @param  array  $array
     * @param  mixed  $value
     * @param  mixed  $key
     * @return array
     */
    public static function prepend($array, $value, $key = null)
    {
        if (is_null($key)) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }
        return $array;
    }
    /**
     * Get a value from the array, and remove it.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public static function pull(&$array, $key, $default = null)
    {
        $value = static::get($array, $key, $default);
        static::forget($array, $key);
        return $value;
    }
    /**
     * Get one or a specified number of random values from an array.
     *
     * @param  array  $array
     * @param  int|null  $number
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public static function getRandom($array, $number = null)
    {
        $requested = is_null($number) ? 1 : $number;
        $count = count($array);
        if ($requested > $count) {
            throw new InvalidArgumentException(
                "You requested {$requested} items, but there are only {$count} items available."
            );
        }
        if (is_null($number)) {
            return $array[array_rand($array)];
        }
        if ((int) $number === 0) {
            return [];
        }
        $keys = array_rand($array, $number);
        $results = [];
        foreach ((array) $keys as $key) {
            $results[] = $array[$key];
        }
        return $results;
    }

    /**
     * Recursively sort an array by keys and values.
     *
     * @param  array  $array
     * @return array
     */
    public static function sortRecursive($array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = static::sortRecursive($value);
            }
        }
        if (static::isAssoc($array)) {
            ksort($array);
        } else {
            sort($array);
        }
        return $array;
    }

    /**
     * Convert the array into a query string.
     *
     * @param  array  $array
     * @return string
     */
    public static function query($array)
    {
        return http_build_query($array, null, '&', PHP_QUERY_RFC3986);
    }

    /**
     * Filter the array using the given callback.
     *
     * @param  array  $array
     * @param  callable  $callback
     * @return array
     */
    public static function where($array, callable $callback)
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }
}