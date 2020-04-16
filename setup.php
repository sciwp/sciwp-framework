<?php
namespace Sci;

defined('WPINC') OR exit('No direct script access allowed');

require_once plugin_dir_path(__FILE__).'updater.php';

/**
 * PluginSetup
 *
 * @author		Eduardo Lazaro Rodriguez <edu@edulazaro.com>
 * @copyright	2020 Kenodo LTD
 * @license		https://opensource.org/licenses/LGPL-2.1  GNU Lesser GPL version 2.1
 * @version     1.0.0
 * @link		https://www.sciwp.com 
 * @since		Version 1.0.0 
 */
class Setup
{
    /**
     * Renames the plugin base folder
     *
     * @return void
     */
    public static function checkPluginFolder($folder)
    {
        if (basename(__DIR__) == $folder) return;

        $currentFolder = trim(trim(plugin_dir_path(__FILE__),"/"),"\\");
        $newFolder = trim(trim(dirname(plugin_dir_path(__FILE__)),"/"),"\\") . DIRECTORY_SEPARATOR  . $folder;

        rename($currentFolder, $newFolder) && wp_die("Ups, seems you forgot to rename the plugin folder to \"".$folder."\". Don't worry, the mad sciencists have done it for you. Click back and try activating the plugin again.", 'SCIWP Framework', array( 'response'=>200, 'back_link'=>TRUE ) );
    
        wp_die("Ups, there was an issue renaming your plugin folder name, please rename it manually to \"".$folder."\" and try again.", 'SCIWP Framework', array( 'response'=>200, 'back_link'=>TRUE ) );
    }
    
    /**
     * Renames the plugin base folder
     *
     * @return void
     */
    public static function checkUpdates($config)
    {
        if (is_admin()) {
            new \WP_GitHub_Updater($config);
        }
    }  
}