<?php



function roveridx_refresh_js_ver($force_refresh = false) {

	require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

	global			$rover_idx_content;

	rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, ' ROVER_COMPONENT_GET_JS_VERSION');

	$theme_opts		= @get_option(ROVER_OPTIONS_THEMING);
	$current_js_ver	= (isset($theme_opts['js_version']))
							? $theme_opts['js_version']
							: ROVER_JS_VERSION;

	$rover_content	= $rover_idx_content->rover_content(
														'ROVER_COMPONENT_GET_JS_VERSION', 
														array('not-region' => 'Not used', 'not-regions' => 'Not Used')
														);
	$latest_js_ver	= $rover_content['the_html'];

	rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, ' latest_js_ver ['.$latest_js_ver.']');

	if ($force_refresh || (version_compare($latest_js_ver, $current_js_ver) !== 0))
		{
		$theme_opts	= @get_option(ROVER_OPTIONS_THEMING);

		$theme_opts['js_version'] = $latest_js_ver;
		update_option(ROVER_OPTIONS_THEMING, $theme_opts);
		}

	return $latest_js_ver;
	}



?>