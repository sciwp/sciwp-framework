<?php
/*
 * Plugin Name: SCI WP Framework
 * Description: A MVC Framework for WordPress.
 * Version: 1.0.0
 * Author: Eduardo Lazaro
 * License:	GNU Lesser GPL version 2.1
 * Author URI: https://www.edulazaro.com/
 * Support URI: https://www.sciwp.com/
 * Plugin URI: https://www.sciwp.com/
 * License URI:	https://opensource.org/licenses/LGPL-2.1
 * 
 * 
 * https://github.com/sciwp

 * Copyright 2020 Eduardo Lazaro
 *
 * This Plugin is open source and free software:
 * You can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation, either version 2.1 of the License. 
 *    
 * This Plugin is distributed in the hope that
 * it will be useful, but WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE. See the GNU General Public License for more details. 
 *
 * You should have received a copy of the GNU General Public License
 * with this software. 
 */

namespace Sci;
 
defined('WPINC') OR exit('No direct script access allowed');

require_once plugin_dir_path(__FILE__).'framework/Sci.php';
require_once plugin_dir_path(__FILE__).'framework/Autoloader.php';

Autoloader::start('core');
Sci::create(__FILE__);
