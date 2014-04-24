<?php
/*
Plugin Name: Canvas Extension - Papple Menu
Plugin URI: http://pootlepress.com/
Description: An extension for WooThemes Canvas that contains a menu design inspired by apple.com. This helps you customise the look and feel of your navigation menu in the Canvas theme by WooThemes.
Version: 1.0.3
Author: PootlePress
Author URI: http://pootlepress.com/
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

	
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	require_once( 'pootlepress-apple-menu-functions.php' );
	require_once( 'classes/class-pootlepress-apple-menu.php' );

    $GLOBALS['pootlepress_apple_menu'] = new Pootlepress_Apple_Menu( __FILE__ );
    $GLOBALS['pootlepress_apple_menu']->version = '1.0.3';
	
?>
