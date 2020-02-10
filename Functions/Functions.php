<?php
namespace Sci;

/**
 * Returns the Sci instance
 * 
 * @return Sci
 */
function Sci ()
{
    return \Sci\Sci\Sci::instance ();
}

/**
 * Returns an array with all the plugins
 * 
 * @return \Sci\Sci\Manager\PluginManager
 */
function plugins()
{
	return Sci()->plugins();
}

/**
 * Returns a plugin the plugin id
 * 
 * @param string $plugin_id The plugin id
 * @return Plugin|Plugin[]
 */
function plugin($plugin_id = false)
{
    if ($plugin_id) {
	    return plugins()->get($plugin_id);
    }
	else {
        return plugins()->get();
	}
}