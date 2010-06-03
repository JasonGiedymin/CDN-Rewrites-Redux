<?php
if(!defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN'))
    exit();

require_once(dirname(__FILE__) . '/config.php');

delete_option($cdn_rewrites_config['plugin_name']);

global $wpdb;
$wpdb->query("DROP TABLE `$cdn_rewrites_config[profiles_table_name]`");
