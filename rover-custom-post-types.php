<?php

//	Custom Post Type names are max 20 chars

define('ROVER_IDX_CUSTOM_POST_DYNAMIC_META',	'rover-idx-dyn-meta');
define('ROVER_IDX_CUSTOM_POST_DYNAMIC_SIDEBAR',	'rover-idx-dyn-side');


class Rover_IDX_Posts
	{
	
	function __construct() {

		require_once ROVER_IDX_PLUGIN_PATH.'rover-custom-post-types-dyn-meta.php';
		require_once ROVER_IDX_PLUGIN_PATH.'rover-custom-post-types-dyn-side.php';

		}

	}

new Rover_IDX_Posts();

?>