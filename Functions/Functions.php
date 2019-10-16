<?php
namespace Wormvc;

/**
 * Returns the Wormvc instance
 * 
 * @return Wormvc
 */
function wormvc ()
{
    return \Wormvc\Wormvc\Wormvc::instance ();
}

/**
 * Returns an array with all the plugins
 * 
 * @return \Wormvc\Wormvc\Manager\PluginManager
 */
function plugins()
{
	return wormvc()->plugins();
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