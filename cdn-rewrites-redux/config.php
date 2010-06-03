<?php
global $wpdb;
global $cdn_rewrites_config;
$cdn_rewrites_config = array(
	'profiles_table_name' => "{$wpdb->prefix}cdn_rewrites_profiles",
	'plugin_name' => 'cdn-rewrites-redux',
    'plugin_path' => '/' . PLUGINDIR . '/cdn-rewrites-redux/',
	'plugin_version' => '1.0.1',
	'plugin_url' => 'http://www.amuxbit.com/projects/cdn-rewrites-redux',
	'default_options' => array(
		'powered'   => true,
		'debug'     => false,
	),
);
