<?php

require_once ROVER_IDX_PLUGIN_PATH.'admin/rover-templates.php';

add_action('wp_ajax_rover_idx_save_setup', 			'rover_idx_save_setup_callback');
add_action('wp_ajax_rover_idx_save_scripts',		'rover_idx_save_scripts_callback');
add_action('wp_ajax_rover_idx_quick_start_create',	'rover_idx_quick_start_create_callback');
add_action('wp_ajax_rover_idx_quick_start_info',	'rover_idx_quick_start_info_callback');
add_action('wp_ajax_rover_idx_quick_start_reset',	'rover_idx_quick_start_reset_callback');


add_action('wp_ajax_rover_idx_refresh_js_ver', 		'rover_idx_refresh_js_ver_callback');
add_action('wp_ajax_rover_idx_show_settings', 		'rover_idx_show_settings_callback');


function roveridx_panel_setup_form($atts) {

	?>		
	<div class="wrap <?php echo esc_attr( rover_plugins_identifier() ); ?>" data-page="rover_idx">

		<?php echo roveridx_panel_header('General');	?>

		<div id="rover-setup-panel" class="">

			<ul class="nav nav-tabs" role="tablist">
				<li role="presentation" class="active">
					<a href="#rover-setup-general" aria-controls="rover-setup-general" role="tab" data-toggle="tab">General</a>
				</li>
				<li role="presentation">
					<a href="#rover-setup-office" class="" aria-controls="rover-setup-office" role="tab" data-toggle="tab">Office &amp; Agents</a>
				</li>
				<li role="presentation" class="rover-admin-advanced">
					<a href="#rover-setup-scripts" class="" aria-controls="rover-setup-scripts" role="tab" data-toggle="tab">JS &amp; CSS</a>
				</li>
				<li role="presentation" class="rover-admin-advanced">
					<a href="#rover-setup-adv" class="" aria-controls="rover-setup-adv" role="tab" data-toggle="tab">Advanced</a>
				</li>
			</ul>

			<div class="tab-content" style="padding:30px;">
				<div class="tab-pane active" id="rover-setup-general">
				   <?php roveridx_setup_create_general_panel();	?>
				</div><!-- rover-setup-general -->
	
				<div class="tab-pane" id="rover-setup-office">
				   <?php roveridx_setup_create_office_panel();	?>
				</div><!-- rover-setup-office -->
	
				<div class="tab-pane" id="rover-setup-scripts">
				   <?php roveridx_setup_create_scripts_panel();	?>
				</div><!-- rover-setup-scripts -->
	
				<div class="tab-pane" id="rover-setup-adv">
				   <?php roveridx_setup_create_adv_panel();	?>
				</div><!-- rover-setup-scripts -->
			</div><!-- tab-content -->

		</div><!-- rover-setup-panel -->

	<?php echo roveridx_panel_footer($panel = 'setup');	?>

	<?php

	$force_wp_update	= true;
	global				$current_user;

	?>
		<input type="hidden" id="wp_force_update" name="wp_force_update" value="'<?php echo $force_wp_update; ?>'" />

	<?php
	}

function roveridx_setup_create_general_panel()	{

	require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

	global $rover_idx_content;

	$rover_content	= $rover_idx_content->rover_content(	'ROVER_COMPONENT_SETUP_GENERAL_PANEL', 
														array('not-region' => 'Not used', 'not-regions' => 'Not Used')
														);
	echo $rover_content['the_html'];
	}

function roveridx_setup_create_office_panel()	{

	require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

	global $rover_idx_content;

	$rover_content	= $rover_idx_content->rover_content(	'ROVER_COMPONENT_SETUP_OFFICE_PANEL', 
														array('not-region' => 'Not used', 'not-regions' => 'Not Used')
														);

	echo $rover_content['the_html'];
	}

function roveridx_setup_create_scripts_panel()	{

	$theme_options	= get_option(ROVER_OPTIONS_THEMING);

	$css_framework	= (isset($theme_options['css_framework']) && !empty($theme_options['css_framework'])) 
							? $theme_options['css_framework'] 
							: 'rover'; 

	$load_bs		= (isset($theme_options['load_admin_bootstrap']) && $theme_options['load_admin_bootstrap'] === 'No') 
							? false 
							: true; 

	$load_fd		= (isset($theme_options['load_foundation']) && $theme_options['load_foundation'] === 'No') 
							? false 
							: true;

	$load_ga		= (isset($theme_options['load_google_api']) && $theme_options['load_google_api'] === 'No') 
							? false 
							: true;

	$load_fa		= (isset($theme_options['load_fontawesome']) && $theme_options['load_fontawesome'] === 'No') 
							? false 
							: true;

	$load_em		= (isset($theme_options['load_emojis']) && $theme_options['load_emojis'] === 'No') 
							? false 
							: true;

	$custom_js		= (isset($theme_options['custom_js']) && !empty($theme_options['custom_js']))
							? $theme_options['custom_js'] 
							: null; 

	$in_header		= (isset($theme_options['custom_js_in_header']) && !empty($theme_options['custom_js_in_header']))
							? $theme_options['custom_js_in_header'] 
							: 'No'; 

	$js_ver			= (isset($theme_options['js_version']) && !empty($theme_options['js_version']))
							? $theme_options['js_version'] 
							: 'Not set'; 
?>

	<div class="form-group">
		<div class="row">
			<div class="help-block"><a href="https://developers.google.com/+/web/api/javascript" target="_blank">Google API</a> is loaded from a Rover server by default.  If your theme or plugin already loads the Google API, you can tell Rover to not load it.</div>
			<div class="col-sm-offset-2 col-sm-10">
				<div class="radio">
					<label>
						<input type="radio" class="load_google_api" name="roveridx_theming[load_google_api]" value="Yes" <?php echo ($load_ga === true) ? 'checked' : ''; ?> />
						Allow Rover to load Google API
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" class="load_google_api" name="roveridx_theming[load_google_api]" value="No" <?php echo ($load_ga === false) ? 'checked' : ''; ?> />
						Use theme / plugin Google API
					</label>
				</div>
			</div>
		</div>
	</div>

	<div class="form-group">
		<div class="row">
			<div class="help-block"><a href="http://fortawesome.github.io/Font-Awesome/cheatsheet/" target="_blank">FontAwesome</a> is loaded from a Rover server by default.  If your theme already loads FontAwesome, you can tell Rover to not load it.</div>
			<div class="col-sm-offset-2 col-sm-10">
				<div class="radio">
					<label>
						<input type="radio" class="load_fontawesome" name="roveridx_theming[load_fontawesome]" value="Yes" <?php echo ($load_fa === true) ? 'checked' : ''; ?> />
						Allow Rover to load FontAwesome
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" class="load_fontawesome" name="roveridx_theming[load_fontawesome]" value="No" <?php echo ($load_fa === false) ? 'checked' : ''; ?> />
						Use theme's FontAwesome
					</label>
				</div>
			</div>
		</div>
	</div>

	<div class="form-group">
		<div class="row">
			<div class="help-block">Rover IDX uses <a href="http://getbootstrap.com/" target="_blank">Bootstrap 3</a> as the CSS framework for the plugin admin pages.  If your theme already loads Bootstrap, you can tell Rover to not load it.</div>
			<div class="col-sm-offset-2 col-sm-10">
				<div class="radio">
					<label>
						<input type="radio" class="load_admin_bootstrap" name="roveridx_theming[load_admin_bootstrap]" value="Yes" <?php echo ($load_bs === true) ? 'checked' : ''; ?> />
						Allow Rover to load Bootstrap
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" class="load_admin_bootstrap" name="roveridx_theming[load_admin_bootstrap]" value="No" <?php echo ($load_bs === false) ? 'checked' : ''; ?> />
						Use theme's Bootstrap
					</label>
				</div>
			</div>
		</div>
	</div>

	<div class="form-group">
		<div class="row">
			<div class="help-block"><a href="https://make.wordpress.org/core/tag/emoji/">Wordpress 4.2</a> supports emojis, and automatically loads extra JS and CSS to every page.  If you do not use emojis, or if your goal is the fastest page load times, you can disable them.</div>
			<div class="col-sm-offset-2 col-sm-10">
				<div class="radio">
					<label>
						<input type="radio" class="load_emojis" name="roveridx_theming[load_emojis]" value="Yes" <?php echo ($load_em === true) ? 'checked' : ''; ?> />
						Allow Wordpress to load Emoji icons
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" class="load_emojis" name="roveridx_theming[load_emojis]" value="No" <?php echo ($load_em === false) ? 'checked' : ''; ?> />
						Remove Emoji icons
					</label>
				</div>
			</div>
		</div>
	</div>

	<div class="form-group">
		<div class="row">
			<div class="help-block"><b>Custom JS</b> - Load any JS, for instance Google Analytics tracking code.</div>
			<div class="col-sm-2">
				<div class="radio">
					<label>
						<input type="radio" class="custom_js_in_header" name="roveridx_theming[custom_js_in_header]" value="Yes" <?php echo ($in_header === 'Yes') ? 'checked' : ''; ?> />
						Load in header
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" class="custom_js_in_header" name="roveridx_theming[custom_js_in_header]" value="No" <?php echo ($in_header !== 'Yes') ? 'checked' : ''; ?> />
						Load in footer
					</label>
				</div>
			</div>
			<div class="col-sm-10">
				<textarea class="form-control customjs" name="roveridx_theming[custom_js]" style="box-sizing:border-box;max-height: 400px;min-height: 200px;overflow: auto;resize:vertical;"><?php echo stripslashes( $custom_js ); ?></textarea>
			</div>
		</div>
	</div>

	<div class="form-group clear">
		<div class="row">
			<div class="col-md-4 col-sm-12 well well-sm pull-right">
				<div class="help-block">Remote JS ver: <span class="rover-js-ver bold"><?php echo $js_ver; ?></span>&nbsp;&nbsp;<i class="fa fa-refresh refresh_js_ver" style="cursor: pointer;" aria-hidden="true"></i></div>
			</div>
		</div>
	</div>

	<p style="clear:both;">&nbsp;</p>

	<div class="form-group">
		<button type="button" class="btn btn-primary btn-save-general-scripts">Save</button>
		<span class="rover-msg-icon" style="display:none;"><i class="fa fa-refresh fa-spin"></i></span><span class="rover-msg-text"></span>
	</div>

<?php
	}

function roveridx_setup_create_adv_panel()	{

	require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

	global $rover_idx_content;

	$rover_content	=		$rover_idx_content->rover_content(	'ROVER_COMPONENT_SETUP_ADV_PANEL', 
														array('not-region' => 'Not used', 'not-regions' => 'Not Used')
														);
	echo $rover_content['the_html'];
	}

function rover_idx_setup_defaults() {
	$perm										= get_option('permalink_structure');
	$url_ends_with_slash						= true;
	if ($perm && substr($perm, -1) != '/')
		$url_ends_with_slash					= false;

	return array(       'CCML'					=> 'region',
	                    'slugCCML'				=> 'ma',
	                    'url_ends_with_slash'	=> $url_ends_with_slash);
	}



/*************************************************/
//	Callbacks
/*************************************************/

function rover_idx_save_setup_callback() {		//	Used by both IDX & CRM

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	$rr											= wp_parse_args($_POST['region_data']);

	if (array_key_exists('roveridx_regions', $rr))
		$rr										= $rr['roveridx_regions'];

	$rr['domain_id']							= intval($_POST['did']);
	if (!empty($_POST['salt']))
		$rr['api_key']							= sanitize_text_field( $_POST['salt'] );

	$r											= update_option(ROVER_OPTIONS_REGIONS, $rr);

	$responseVar								= array(
	            								       'region_data'	=> $_POST['region_data'],
	            								       'success'		=> true		//	Folks are getting confused when we say 'Settings were not updated'
	            								       );

    echo json_encode($responseVar);

	die();
	}

function rover_idx_save_scripts_callback()	{

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	$theme_options								= get_option(ROVER_OPTIONS_THEMING);

	$theme_options['load_admin_bootstrap']		= rover_idx_validate_post_yes_no('load_admin_bootstrap');
	$theme_options['load_fontawesome']			= rover_idx_validate_post_yes_no('load_fontawesome');
	$theme_options['load_google_api']			= rover_idx_validate_post_yes_no('load_google_api');
	$theme_options['load_emojis']				= rover_idx_validate_post_yes_no('load_emojis');
	$theme_options['custom_js']					= $_POST['custom_js'];
	$theme_options['custom_js_in_header']		= rover_idx_validate_post_yes_no('in_header');

	$r											= update_option(ROVER_OPTIONS_THEMING, $theme_options);

	$responseVar								= array(
														'success'		=> true		//	Folks are getting confused when we say 'Settings were not updated'
														);

    echo json_encode($responseVar);

	die();
	}

function rover_idx_quick_start_create_callback()		{

	require_once ROVER_IDX_PLUGIN_PATH.'rover-database.php';

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	$responseVar	= array(
							'html'				=> roveridx_create_quick_setup_pages($_POST)
							);

    echo json_encode($responseVar);

	die();	
	}

function rover_idx_quick_start_info_callback()	{

	require_once ROVER_IDX_PLUGIN_PATH.'rover-database.php';

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	$responseVar	= array(
							'html'				=> roveridx_fetch_quick_setup_pages()
							);

    echo json_encode($responseVar);

	die();		
	}

function rover_idx_quick_start_reset_callback()	{

	require_once ROVER_IDX_PLUGIN_PATH.'rover-database.php';

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	$responseVar	= array(
							'html'				=> roveridx_reset_quick_setup_pages()
							);

    echo json_encode($responseVar);

	die();			
	}

function rover_idx_refresh_js_ver_callback()	{

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	require_once ROVER_IDX_PLUGIN_PATH.'rover-scheduled.php';

	$responseVar	= array(
							'ver'				=> roveridx_refresh_js_ver($force_refresh = true)
							);

    echo json_encode($responseVar);

	die();
	}

function rover_idx_show_settings_callback() {

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	$td_style		=	'style="padding:0;margin:0"';

	$the_html[]		=	'<table>';

	$the_html[]		=		'<tr><td '.$td_style.' colspan="2"><b>Region</b></td></tr>';
	foreach (get_option(ROVER_OPTIONS_REGIONS) as $key => $val)
	$the_html[]		=		'<tr><td '.$td_style.'>'.$key.'</td><td '.$td_style.'>'.$val.'</td></tr>';

	$the_html[]		=		'<tr><td '.$td_style.' colspan="2"><b>Theme</b></td></tr>';
	foreach (get_option(ROVER_OPTIONS_THEMING) as $key => $val)
	$the_html[]		=		'<tr><td '.$td_style.'>'.$key.'</td><td '.$td_style.'>'.$val.'</td></tr>';

	$the_html[]		=		'<tr><td '.$td_style.' colspan="2"><b>SEO</b></td></tr>';
	foreach (get_option(ROVER_OPTIONS_SEO) as $key => $val)
	$the_html[]		=		'<tr><td '.$td_style.'>'.$key.'</td><td>'.$val.'</td '.$td_style.'></tr>';

	$the_html[]		=		'<tr><td '.$td_style.' colspan="2"><b>Social</b></td></tr>';
	foreach (get_option(ROVER_OPTIONS_SOCIAL) as $key => $val)
	$the_html[]		=		'<tr><td '.$td_style.'>'.$key.'</td><td>'.$val.'</td '.$td_style.'></tr>';

	$the_html[]		=	'</table>';

	$responseVar = array(
	                    'html'		=> implode('', $the_html)
	                    );

    echo json_encode($responseVar);
	
	die();
	}

?>