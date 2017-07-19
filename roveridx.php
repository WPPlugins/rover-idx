<?php
/*
Plugin Name: Rover IDX
Plugin URI: http://RoverIDX.com/
Description: Real Estate IDX - Listings, Maps, allow customer Saved Searches and Favorites.  Simple shortcodes and easy-to-use Search Panel.  Auto-mailings with your branding right at the top!
Author: Rover IDX, LLC
Author URI: http://RoverIDX.com/
Version: 2.0.0.1314

Author: Rover IDX
Author URI: http://www.RoverIDX.com

Copyright (c) 2008-2016 Rover IDX, LLC  All Rights Reserved.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

 */

class Rover_IDX_Instance {

	private static $_instance;

	public static function get_instance() {

		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self;
			}

		return self::$_instance;

		}

	function __construct() {
		define('ROVER_IDX_PLUGIN_PATH',		plugin_dir_path( __FILE__ ));
		define('ROVER_IDX_PLUGIN_URL', 		plugins_url('rover-idx'));

		require_once (dirname( __FILE__ ) . '/rover-init.php');

		register_activation_hook( __FILE__,		'roveridx_activate' );
		register_deactivation_hook( __FILE__,	'roveridx_deactivate' );
		}
	}


if (rover_is_valid_http_accept())
	$rover_idx_plugin	= Rover_IDX_Instance::get_instance();


function rover_is_valid_http_accept()
	{
	$valid				= true;

	if (defined('DOING_CRON') && DOING_CRON)
		return true;

	if (defined('DOING_AJAX') && DOING_AJAX)
		return true;

	if (isset($_SERVER['HTTP_ACCEPT']))
		{
		$accept_str		= $_SERVER['HTTP_ACCEPT'];
		$accept_parts	= explode(',', $accept_str);

		if (strpos($accept_str, 'image/*') !== false)
			$valid			= false;

		if (count($accept_parts) === 1 && strpos($accept_str, '*/*') !== false)
			{
			if (strpos($_SERVER['HTTP_USER_AGENT'], 'facebook') === false)		//	Let Facebook through
				$valid		= false;
			}
		}

	return $valid;
	}

function roveridx_plugin_rules() {

	rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'add_rewrite_rules ');

	add_rewrite_rule('rover-admin-panel/?([^/]*)', 'rover-dynamic-pages/admin-panel', 'top');
	}

function roveridx_activate() {

	require_once ROVER_IDX_PLUGIN_PATH.'rover-database.php';

	rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'activating ');

    if (!roveridx_tracking_table_exists() || get_site_option('roveridx_db_version') != ROVER_VERSION) 
    	{
		update_rover_tables();
    	}

	//	Setup wp_post and wp_postmeta for Rover dynamic page

	$post_id		= roveridx_setup_rover_page();
	
	roveridx_set_template_meta($post_id, $template = null);

    if (false === get_option(ROVER_OPTIONS_THEMING)) {
		require_once (dirname( __FILE__ ) . '/admin/rover-panel-styling.php');
		update_option(ROVER_OPTIONS_THEMING, rover_idx_theme_defaults($post_id));
		}

	$tmp			= get_option(ROVER_OPTIONS_REGIONS);
    if (!validate_region_slugs($tmp)) {
		require_once (dirname( __FILE__ ) . '/admin/rover-panel-setup.php');
		update_option(ROVER_OPTIONS_REGIONS, rover_idx_setup_defaults());
		}

    if (false === get_option(ROVER_OPTIONS_SEO)) {
		require_once (dirname( __FILE__ ) . '/admin/rover-panel-seo.php');
		update_option(ROVER_OPTIONS_SEO, rover_idx_seo_defaults());
		}

    if (false === get_option(ROVER_OPTIONS_SOCIAL)) {
		require_once (dirname( __FILE__ ) . '/admin/rover-panel-social.php');
		update_option(ROVER_OPTIONS_SOCIAL, rover_idx_social_defaults());
		}

	roveridx_plugin_rules();
	flush_rewrite_rules();
	}

function roveridx_deactivate() {

	rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'deactivating ');

	wp_clear_scheduled_hook( 'roveridx_cron_refresh_sitemap' );
	wp_clear_scheduled_hook( 'roveridx_cron_refresh_social' );
	wp_clear_scheduled_hook( 'roveridx_cron_refresh_js_ver' );

	flush_rewrite_rules();

	}

function validate_region_slugs($tmp)
	{
	if ($tmp === false)
		{
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'option "'.ROVER_OPTIONS_REGIONS.'" does not exist!');
		return false;
		}

	if (is_array($tmp))
		{
		$slug = 'slug';
		foreach ($tmp as $region_key => $region_val)
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Comparing ['.$region_key.'] to ['.$slug.']');

			if (substr($region_key, 0, strlen($slug)) == $slug)
				return true;
			}
		}

	return false;
	}
?>