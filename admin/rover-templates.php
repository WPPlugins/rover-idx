<?php
add_action('wp_ajax_rover_idx_toggle_advanced',							'rover_idx_toggle_advanced_callback');
add_action('wp_ajax_rover_idx_assemble_agent_data',						'rover_idx_assemble_agent_data_callback');
add_action('wp_ajax_rover_idx_create_agent_cpt',						'rover_idx_create_agent_cpt_callback');




function roveridx_panel_header($title)	{

	$theming_opts	=		get_option(ROVER_OPTIONS_THEMING);
	$advanced		=		@$theming_opts['ui_advanced'];
	$advanced		=		($advanced == 1)
									? true
									: false;
	$full_ver		=		explode(".", ROVER_VERSION_FULL);
	$minor_ver		=		(count($full_ver) == 4)
									? '  build '.$full_ver[3]
									: '';

	$theHTML		=		'<div id="header" class="wrap rover_wp_admin_header">';

	$theHTML		.=		    '<h2>';
	$theHTML		.=		 		esc_html( $title );

	$theHTML		.=				'<a id="logo" href="https://roveridx.com" title="IDX Plugin | WordPress IDX"  style="float:right;text-align:center;width:15%;">
										<img src="'.ROVER_IDX_PLUGIN_URL.'/images/roverLogo160.png" style="width:160px;" title="Rover IDX">
										<div class="caption help-block" style="border-top: 1px solid #DDD;font-size:14px;"><center>Version '.roveridx_get_version().' <span style="color:#666;font-size:10px;">'.$minor_ver.'</span></center></div>
									</a>';

	$theHTML		.=				'<div class="btn-group rover-advanced-toggle pull-right" style="margin-right:30px;" data-toggle="buttons">
										<label class="btn btn-primary btn-xs '.(($advanced === false) ? 'active' : '').'">
											<input type="radio" name="options" id="rover-advanced-off" autocomplete="off" '.(($advanced === false) ? 'checked' : '').'> Standard
										</label>
										<label class="btn btn-primary btn-xs '.(($advanced === true) ? 'active' : '').'">
											<input type="radio" name="options" id="rover-advanced-on" autocomplete="off" '.(($advanced === true) ? 'checked' : '').'> Advanced
										</label>
									</div>';

	$theHTML		.=		    '</h2>';
	$theHTML		.=		'</div>';

	return $theHTML;
	}

function roveridx_panel_js($panel)	{

	require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

	global $rover_idx_content;

	$rover_content	=		$rover_idx_content->rover_content(	'ROVER_COMPONENT_SETTINGS_PANEL_JS', 
															array(
																'panel'				=> $panel
																)
															);
	return $rover_content['the_html'];
	}

function roveridx_panel_footer($panel)	{

	$current_user	=		wp_get_current_user();
	$upload_base	=		wp_upload_dir();
	$label_id		=		rand(1,9999);

	$theHTML		=		'<div style="float:right;">';
	$theHTML		.=			'<a href="http://www.facebook.com/RoverIDX" title="RoverIDX Facebook page" target="_blank">';
	$theHTML		.=				'<img style="border:none;margin-left:10px;" src="'.ROVER_IDX_PLUGIN_URL.'/images/facebook-icon.png" />';
	$theHTML		.=			'</a>';
	$theHTML		.=		'</div>';

	$theHTML		.=		'<input type="hidden" id="rover_idx" name="rover_idx" value="1" />';

	$theHTML		.=		'<input type="hidden" id="wp_security" name="security" value="'.wp_create_nonce(ROVERIDX_NONCE).'" />';

	$theHTML 		.=		'<input type="hidden" name="wp_name" value="'.sanitize_text_field( $current_user->display_name ).'" />';
	$theHTML 		.=		'<input type="hidden" name="wp_email" value="'.sanitize_email( $current_user->user_email ).'" />';

//	$theHTML 		.=		'<input type="hidden" name="wp_plugin_url" class="no-serial" value="'.plugin_dir_url('roveridx.php').'" />';
//
//	$theHTML 		.=		'<input type="hidden" name="wp_upload_dir" class="no-serial" class="" value="'.$upload_base['basedir'].'" />';
//	$theHTML 		.=		'<input type="hidden" name="wp_upload_url" class="no-serial" class="" value="'.$upload_base['baseurl'].'" />';
//
	$theHTML 		.=		'<input type="hidden" name="wp_site_url" class="no-serial" value="'.get_site_url().'" />';
//	$theHTML 		.=		'<input type="hidden" name="wp_admin_url" class="no-serial" value="'.admin_url().'" />';

	$theHTML		.=		'<div class="rover-confirm modal fade" role="dialog" aria-labelledby="#'.$label_id.'" aria-hidden="true" style="display:none;position:fixed;top:50%;left:25%;width:50%;z-index:1051;">
								<div class="modal-dialog" style="max-width:100%;">
									<div class="modal-content">
										<div class="modal-header">
											<h4 class="modal-title" style="float:left;margin:0;" id="'.$label_id.'">Your question goes here</h4>
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
											<div style="clear:both;"></div>
										</div>
										<div class="modal-body">
											<i class="fa fa-cog fa-spin fa-2x fa-fw" style="margin:30px auto;padding:0;border:0;text-align:center;"></i>
										</div>
										<div class="modal-footer">
											<button type="button" class="yes btn btn-primary pull-right" style="margin:0 5px;">Yes</button>
											<button type="button" class="no btn btn-primary pull-right"  style="margin:0 5px;">No</button>
										</div>
									</div><!-- /.modal-content -->
								</div><!-- /.modal-dialog -->
							</div><!-- /#edit_client -->';

	$theHTML		.=		roveridx_panel_js($panel);

	return $theHTML;
	}

function rover_idx_toggle_advanced_callback() {

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	$theming_opts					= get_option(ROVER_OPTIONS_THEMING);
	$theming_opts['ui_advanced']	= rover_idx_validate_post_bool( 'ui_advanced' );

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'ui_advanced ['.(($theming_opts['ui_advanced'] == 1) ? 'true' : 'false').']');

	$r								= update_option(ROVER_OPTIONS_THEMING, $theming_opts);

	$responseVar = array(
	                    'html'		=> $theming_opts['ui_advanced'],
	                    'success'	=> $r
	                    );

    echo json_encode($responseVar);
	
	die();
	}

?>