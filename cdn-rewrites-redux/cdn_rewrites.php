<?php
/*
Plugin Name: CDN Rewrites Redux
Plugin URI: http://www.amuxbit.com/projects/cdn-rewrites-redux
Description: Redux of the plugin by http://www.phoenixheart.net, I started this fork to continue it's development.  Original desc: This plugin rewrites the host(s) of your static files (JavaScripts, CSS, images etc.) (called Origin) into a CDN (Content Delivery Network) host. REQUIRES PHP >= 5
Version: 1.0.1
Author: Redux by Jason Giedymin for Amuxbit.com, Original by http://www.phoenixheart.net
Author URI: http://jasongiedymin.com
*/

require_once(dirname(__FILE__) . '/config.php');
require_once(dirname(__FILE__) . '/profile.class.php');
require_once(dirname(__FILE__) . '/option.class.php');
require_once(dirname(__FILE__) . '/helper.class.php');
require_once(dirname(__FILE__) . '/cdnr.class.php');

$cdn_rewrites = new CDN_Rewrites();

if (!is_admin()) 
{
    add_action('get_header', array($cdn_rewrites, 'pre_content'), PHP_INT_MAX);
    add_action('wp_footer', array($cdn_rewrites, 'show_powered'));
    add_action('wp_footer', array($cdn_rewrites, 'post_content'), PHP_INT_MAX);
}


add_action('admin_menu', array($cdn_rewrites, 'register_menu'));
add_action('init', array($cdn_rewrites, 'wp_init'));
add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), array($cdn_rewrites, 'add_setting_link'), -10);

register_activation_hook(__FILE__, array($cdn_rewrites, 'activate'));
register_deactivation_hook(__FILE__, array($cdn_rewrites, 'deactivate'));
