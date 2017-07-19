<?php

require_once ROVER_IDX_PLUGIN_PATH.'admin/rover-templates.php';

// Render the Plugin options form
function roveridx_panel_styling_form($atts) {

	global			$rover_idx;

	$options 		= get_option(ROVER_OPTIONS_THEMING); 

	?>
	<div id="wp_defaults" class="wrap <?php echo esc_attr( rover_plugins_identifier() ); ?>" data-page="rover-panel-styling">

	<?php 
	echo roveridx_panel_header('Styling'); 	
	rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Starting');
	?>

	<div id="rover-styling-panel" class="">

		<style type="text/css">
			.rover-tabs-vert {border-bottom: none;float: left;height: auto;list-style:none!important;padding: 0;vertical-align: top;width: 100%;}
			.rover-tabs-vert > li {margin: 0;width: 100%;}
			.rover-tab-container {background:#fff;padding:10px;}
			.rover-tab-nav :after {clear: both;}
			.rover-tab-container .rover-tab-nav > li {float: left;list-style: none;margin: 0 4px 1px 0!important;padding: 2px;}
			.rover-tab-nav > li > a {border: 1px solid #DDD;border-radius: 4px 4px 0 0;border-bottom-color: rgba(0, 0, 0, 0);cursor: pointer;display: block;margin-right: 2px;line-height: 1.42857143;position: relative;padding: 10px 15px;text-decoration: none;}
			.rover-tab-nav > li > a.active,.rover-tab-nav > li > a.active, .rover-tab-nav > li > a.active:hover, .rover-tab-nav > li > a.active:focus {color: #555;cursor: default;background-color: #FFF;border-bottom: 2px solid #fff;}
			.rover-tab-nav > li > a:hover {background-color: #CCC;}
			.rover-htab-contents {max-width: 100%;padding: 10px 0;vertical-align: top;}
			.rover-htab-contents.active {display: inline-block;}
			.rover-htab-contents-vertical {float: left;padding: 0px 0;}
		</style>

		<div class="col-md-2">
		<ul id="rover-styling-tabs" class="nav-tab-wrapper rover-tab-nav rover-tabs-vert">
			<li>
				<a href="#style-general" class="rover-htab active rover-nowrap" id="style-general-tab">Quick Start</a>
			</li>
			<li>
				<a href="#style-search" class="rover-htab rover-nowrap" id="style-search-tab">Search Panel</a>
			</li>
			<li>
				<a href="#style-full" class="rover-htab rover-nowrap" id="style-full-tab">Full Page</a>
			</li>
			<li>
				<a href="#style-listings" class="rover-htab rover-nowrap" id="style-listings-tab">Listings Page</a>
			</li>
			<li>
				<a href="#style-map" class="rover-htab rover-nowrap" id="style-map-tab">Map Page</a>
			</li>
			<li>
				<a href="#style-property" class="rover-htab rover-nowrap" id="style-property-tab">Property Page</a>
			</li>
			<li>
				<a href="#style-templates" class="rover-htab rover-nowrap rover-admin-advanced" id="style-templates-tab">Templates</a>
			</li>
		</ul>
		</div>
	
		<div class="col-md-10">
		<div id="style-general" class="rover-htab-contents rover-htab-contents-vertical active">
			<?php roveridx_styling_quick_panel();	?>
		</div>
	
		<div id="style-search" class="rover-htab-contents rover-htab-contents-vertical">
			<?php roveridx_styling_search_panel();	?>
		</div>
	
		<div id="style-full" class="rover-htab-contents rover-htab-contents-vertical">
			<?php roveridx_styling_full_panel();	?>
		</div>
	
		<div id="style-listings" class="rover-htab-contents rover-htab-contents-vertical">
			<?php roveridx_styling_listings_panel();	?>
		</div>

		<div id="style-map" class="rover-htab-contents rover-htab-contents-vertical <?php echo roveridx_load_google($options); ?>">
			<?php roveridx_styling_map_panel();	?>
		</div>
	
		<div id="style-property" class="rover-htab-contents rover-htab-contents-vertical">
			<?php roveridx_styling_property_panel();	?>
		</div>
	
		<div id="style-templates" class="rover-htab-contents rover-htab-contents-vertical">
			<?php roveridx_styling_mail_template_panel();	?>
		</div>

		<div style="clear:both;">

			<script type="text/javascript">/*<![CDATA[*//*---->*/
				(function( $ ){

						$("#rover-styling-panel a.rover-htab").on("click", function(){ 

							var $t = $(this);
							if ($t.prop("disabled") !== true && !$t.hasClass("active"))
								{
								$t.prop("disabled", true);

								var $p = $("#rover-styling-tabs");
								var id = $(this).attr("href");

								$p.find(".rover-tab-nav:first .rover-htab").removeClass("active");
								$t.addClass("active");

								$("#rover-styling-panel .rover-htab-contents").hide();
								$(id).show();

								$t.prop("disabled", false);
								}
							});

					})( jQuery );
			/*--*//*]]>*/</script>
		</div>

	</div>


	<p class="submit">
		<span id="jq_msg"></span>
	</p>

	<?php echo roveridx_panel_footer($panel = 'styling');	?>
	
	</div>


<?php
	}

function roveridx_get_menus($theme_options)	{

	$menu_names				= array();
	foreach ( get_registered_nav_menus() as $location => $description ) {
		$menu_names[]		= $location;
		}

	return implode(',', $menu_names);
	}

function roveridx_load_google($theme_options)	{

	return (isset($theme_options['load_google_api']) && $theme_options['load_google_api'] === 'No') 
							? 'rover-no-google-api' 
							: 'rover-load-google-api';
	}

function roveridx_styling_quick_panel()	{

	require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

	global $rover_idx_content;

	$theme_options	=		get_option(ROVER_OPTIONS_THEMING);
	$theme_options	=		(is_array($theme_options))
									? $theme_options
									: array();
	$upload_dir		=		wp_upload_dir(); 
	$all_templates	=		roveridx_get_all_templates();

	$rover_content	=		$rover_idx_content->rover_content(	'ROVER_COMPONENT_WP_STYLE_QUICK_PANEL', 
														array_merge(
															$theme_options,
															array(
																'not-region'		=> 'Not used', 
																'not-regions'		=> 'Not Used',
//																'settings'			=> $theme_options,
																'rover_css'			=> roveridx_get_css($theme_options),
																'wp_menus'			=> roveridx_get_menus($theme_options),
																'plugin_url'		=> ROVER_IDX_PLUGIN_URL,
																'upload_url'		=> $upload_dir['baseurl'],
																'property_template'	=> roveridx_get_templates($all_templates, $theme_options, 'property_template'),
																'mc_template'		=> roveridx_get_templates($all_templates, $theme_options, 'mc_template'),
																'rep_template'		=> roveridx_get_templates($all_templates, $theme_options, 'rep_template'),
																'template'			=> roveridx_get_templates($all_templates, $theme_options, 'template')
																)
															)
														);
	echo $rover_content['the_html'];
	}

function roveridx_styling_search_panel()	{

	require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

	global $rover_idx_content;

	$theme_options	=		get_option(ROVER_OPTIONS_THEMING);

	$rover_content	=		$rover_idx_content->rover_content(	'ROVER_COMPONENT_WP_STYLE_SEARCH_PANEL', 
														array(
															'not-region'		=> 'Not used', 
															'not-regions'		=> 'Not Used',
															'settings'			=> $theme_options
															)
														);
	echo $rover_content['the_html'];
	}

function roveridx_styling_full_panel()		{

	require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

	global $rover_idx_content;

	$rover_content	=		$rover_idx_content->rover_content(	'ROVER_COMPONENT_WP_STYLE_FULL_PANEL', 
														array(
															'not-region'		=> 'Not used', 
															'not-regions'		=> 'Not Used'
															)
														);
	echo $rover_content['the_html'];
	}

function roveridx_styling_listings_panel()	{

	require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

	global $rover_idx_content;

	$regions_array	= rover_get_selected_regions();

	$rover_content	= $rover_idx_content->rover_content(
														'ROVER_COMPONENT_WP_STYLE_LISTINGS_SETTINGS_PANEL', 
														array(
															'region' => $regions_array[0], 
															'regions' => implode(',',$regions_array)
															)
														);
	echo $rover_content['the_html'];
	}

function roveridx_styling_map_panel()	{

	require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

	global $rover_idx_content;

	$rover_content	=		$rover_idx_content->rover_content(	'ROVER_COMPONENT_WP_STYLE_MAP_PANEL', 
														array(
															'not-region'		=> 'Not used', 
															'not-regions'		=> 'Not Used'
															)
														);
	echo $rover_content['the_html'];


	global		$rover_idx_admin;
	}

function roveridx_styling_property_panel()	{

	require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

	global $rover_idx_content;

	$rover_content	=		$rover_idx_content->rover_content(	'ROVER_COMPONENT_WP_STYLE_PROPERTY_PANEL', 
																array(
																	'not-region'		=> 'Not used', 
																	'not-regions'		=> 'Not Used'
																	)
																);
	echo $rover_content['the_html'];
	}

function roveridx_styling_mail_template_panel()	{

	require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

	global $rover_idx_content;

	?>
	<div class="container-fluid">
		<div class="row spacing">
	<?php
		$rover_content	=		$rover_idx_content->rover_content(	'ROVER_COMPONENT_WP_STYLE_MAIL_TEMPLATE_PANEL', 
															array(
																'not-region'		=> 'Not used', 
																'not-regions'		=> 'Not Used'
																)
															);
		echo $rover_content['the_html'];
	
		?>
		</div>
	</div>

	<?php

	}

function rover_idx_theme_defaults($post_id = null) {

	require_once ROVER_IDX_PLUGIN_PATH.'rover-scheduled.php';

	$theme_defaults	= array(
							'theme'					=> 'cupertino',
							'css_framework'			=> ROVER_DEFAULT_CSS_FRAMEWORK,
							'css'					=> 'ltgray_flat.css',
							'login_button'			=> 'menu-primary',
							'load_admin_bootstrap'	=> 'Yes',
							'load_fontawesome'		=> 'Yes',
							'load_emojis'			=> 'No',
							'ui_advanced'			=> false,
							'load_google_libraries'	=> null,
							'google_map_key'		=> null,
							'site_version'			=> ROVER_VERSION_FULL,
							'js_version'			=> roveridx_refresh_js_ver($force_refresh = false)
							);

	if (!is_null($post_id))
		$theme_defaults['rover_post_id']	= $post_id;

	return $theme_defaults;
	}


function rover_idx_theme_callback() {

	rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Starting');

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	$theme_options										= get_option(ROVER_OPTIONS_THEMING);

	$theme_options['theme']								= sanitize_text_field( $_POST['theme'] );
	$theme_options['css_framework']						= sanitize_text_field( $_POST['css_framework'] );
	$theme_options['css']								= sanitize_text_field( $_POST['css'] );
	$theme_options['template']							= sanitize_text_field( $_POST['template'] );
	$theme_options['property_template']					= sanitize_text_field( $_POST['property_template'] );
	$theme_options['cp_template']						= sanitize_text_field( $_POST['cp_template'] );
	$theme_options['mc_template']						= sanitize_text_field( $_POST['mc_template'] );
	$theme_options['rep_template']						= sanitize_text_field( $_POST['rep_template'] );
	$theme_options['rental_template']					= sanitize_text_field( $_POST['rental_template'] );
	$theme_options['login_button']						= sanitize_text_field( $_POST['login_button'] );
	$theme_options['load_google_libraries']				= sanitize_text_field( $_POST['load_google_libraries'] );
	$theme_options['google_map_key']					= sanitize_text_field( $_POST['google_map_key'] );
	$theme_options['rover_highlight_text']				= sanitize_text_field( $_POST['rover_highlight_text'] );

	$r 													= update_option(ROVER_OPTIONS_THEMING, $theme_options);
	if ($r === true)
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Theme options were changed');
	else
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Theme options were not changed');


	roveridx_set_template_meta($new_theme_options['rover_post_id'], sanitize_text_field( $_POST['template'] ) );

	$responseVar = array(
	                    'theme'							=> $theming_array['theme'],
	                    'css_framework'					=> $theming_array['css_framework'],
	                    'css'							=> $theming_array['css'],
	                    'success'						=> $r
	                    );

    echo json_encode($responseVar);

	die();
	}


add_action('wp_ajax_rover_idx_theme', 'rover_idx_theme_callback');


function rover_idx_fetch_theme_settings_callback() {

	rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Starting');

	check_ajax_referer(ROVERIDX_NONCE, 'security');

    echo json_encode(array(
	                    'success'						=> true,
	                    'settings'						=> get_option(ROVER_OPTIONS_THEMING)
	                    ));

	die();
	}


add_action('wp_ajax_rover_idx_fetch_theme_settings', 'rover_idx_fetch_theme_settings_callback');



function rover_idx_overwrite_theme_settings_callback() {

	rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Starting');

	check_ajax_referer(ROVERIDX_NONCE, 'security');

//	$source_wp_theme_options							= stripslashes($_POST['source_wp_theme_options']);
	$source_wp_theme_options							= $_POST['source_wp_theme_options'];

	rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'source_wp_theme_options ['.$source_wp_theme_options.']');

//	$unsanitized_object									= json_decode($source_wp_theme_options, true);
	$unsanitized_object									= $source_wp_theme_options;

	if (is_array($unsanitized_object))
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'unsanitized_object is an array of ['.count($unsanitized_object).'] items');
	else
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'unsanitized_object is NOT an array ');

	$new_theme_options									= array();
	foreach($unsanitized_object as $option_key => $option_val)
		$new_theme_options[$option_key]					= sanitize_text_field( $unsanitized_object[$option_key] );

	$r 													= update_option(ROVER_OPTIONS_THEMING, $new_theme_options);

    echo json_encode(array(
	                    'success'						=> true,
						'msg'							=> 'Wordpress settings have been updated'
	                    ));

	die();
	}


add_action('wp_ajax_rover_idx_overwrite_theme_settings', 'rover_idx_overwrite_theme_settings_callback');


function roveridx_get_jqui_themes($options)	{

	$ui_jquery_themes = array('black-tie', 'blitzer', 'cupertino', 'dot-luv', 'excite-bike', 'flick', 'overcast', 'hot-sneaks', 'humanity',
							'pepper-grinder', 'redmond', 'smoothness', 'start', 'sunny', 
							'ui-darkness', 'ui-lightness', 'vader');
	
	$theHTML = null;
	foreach ($ui_jquery_themes as $theme)	{
		$theHTML	.=		"<option value='".$theme."' ".roveridx_theme_is_selected($options, $theme)."> ".$theme."</option>";		
		}
	
	//	Add Rover-custom themes
	
	$rover_themes	=	plugin_dir_url('roveridx.php').'rover-idx/css/themes/';
	
	$rover_js_theme		= $rover_themes."Aristo/Aristo.css";
	$theHTML	.=		"<option value='".$rover_js_theme."' ".roveridx_theme_is_selected($options, $rover_js_theme)."> Aristo</option>";		
	$rover_js_theme		= $rover_themes."Absolution/absolution.blue.css";
	$theHTML	.=		"<option value='".$rover_js_theme."' ".roveridx_theme_is_selected($options, $rover_js_theme)."> Absolution</option>";		
	$rover_js_theme		= $rover_themes."Bootstrap/demo.css";
	$theHTML	.=		"<option value='".$rover_js_theme."' ".roveridx_theme_is_selected($options, $rover_js_theme)."> Bootstrap</option>";		
	
	return $theHTML;
	}
function roveridx_get_css($options)
	{
	$css_files				= array();
	$all_css				= array();

	if ($handle = opendir(ROVER_IDX_PLUGIN_PATH.'/css')) {
	
	    /* This is the correct way to loop over the directory. */
	    while (false !== ($file = readdir($handle))) {
		    if (strpos($file, '.css', 1))
		    	{
			    $css_files[]= $file;
	        	}
	        }

	    closedir($handle);
	    }

	sort($css_files);

	foreach($css_files as $one_css_file)
		{
	    $the_css			= substr($one_css_file, 0, (strlen($one_css_file) - 4));
	    $sel				= ($options['css'] == $one_css_file)	? 'selected=selected' : '';
	    $all_css[]			= '<option value="'.$one_css_file.'" '.$sel.'>'.$the_css.'</option>';
		}

	$upload_dir = wp_upload_dir();
	if (file_exists($upload_dir['basedir'].'/rover-custom.css'))
		{
		$sel				= ($options['css'] == 'rover-custom.css')	? 'selected=selected' : '';
		$all_css[]			= '<option value="rover-custom.css" '.$sel.'>rover-custom.css</option>';
		}

	return implode('', $all_css);
	}
function roveridx_theme_is_selected($options, $theme)	{
	return ($options['theme'] == $theme)
						?	'selected=selected'	
						:	'';
	}
function roveridx_use_themes_fullpage_mechanism()		{
	if (function_exists('genesis_unregister_layout') || 
		function_exists('woo_post_meta'))
		{
		return true;
		}
	
	return false;
	}

function roveridx_get_all_templates()	{

	global $wpdb;

	$all_templates			= array();
//	$rows = $wpdb->get_results("SELECT DISTINCT meta_value FROM ".$wpdb->postmeta." WHERE meta_key = '".WP_TEMPLATE_KEY."' ORDER BY meta_key", ARRAY_A);
//	foreach ($rows as $row)
//		{
//		$all_templates[]	= $row['meta_value'];
//		}
//
//	$path_to_default		= get_page_template();
//	if (file_exists($path_to_default))
//		$all_templates[]	= basename($path_to_default);
//
//	$all_templates			= array_unique($all_templates);

	$templates				= get_page_templates();
	foreach ( $templates as $template_name => $template_filename ) {
		$all_templates[]	= $template_filename;
		}

	return $all_templates;
	}

function roveridx_get_templates($all_templates, $options, $key)	{

	$the_html				= array();
	$val					= ($options && isset($options[$key]))
									? $options[$key]
									: null;
	foreach ($all_templates as $one_template)
		{
		$selected 			= ($one_template == $val)	
									?	'selected=selected'	
									:	'';
		$the_html[]			= "<option value='".$one_template."' ".$selected."> ".$one_template."</option>";
		}
	
	return implode("", $the_html);
	}


?>