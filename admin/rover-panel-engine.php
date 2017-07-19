<?php

require_once ROVER_IDX_PLUGIN_PATH.'admin/rover-templates.php';
//require_once plugin_dir_path( __FILE__ ).'rover-common.php';


// Render the Plugin options form
function roveridx_panel_engine_form($atts) {

	?>
	<div id="wp_defaults" class="wrap <?php echo esc_attr( rover_plugins_identifier() ); ?>">

		<?php 
		global			$rover_idx;
	
		echo roveridx_panel_header('Engine'); 	
	
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Starting');
	
		if (count($rover_idx->all_selected_regions) === 0)
			{
			echo		'<div>Please select one or more Regions from the main RoverIDX settings panel.</div>';
			}
		else
			{
			global $rover_idx_content;
			$rover_content	=	$rover_idx_content->rover_content(	ROVER_COMPONENT_WP_ENGINE_PANEL, 
																	array(	'region' => $rover_idx->all_selected_regions[0], 
																			'regions' => implode(',',$rover_idx->all_selected_regions))
																	);
			echo	$rover_content['the_html'];
			}

		echo roveridx_panel_footer();
		?>

	</div>


	<input type="hidden" id="wp_security" name="security" value="<?php echo wp_create_nonce(ROVERIDX_NONCE); ?>" />
	<input type="hidden" id="wp_post_id" name="wp_post_id" value="<?php echo roveridx_get_rover_page_id(); ?>" />

	<?php	global		$current_user;	?>

	<input type="hidden" id="wp_name" name="wp_name" value="<?php echo $current_user->display_name; ?>" />
	<input type="hidden" id="wp_email" name="wp_email" value="<?php echo $current_user->user_email; ?>" />

<script type="text/javascript" >
//<![CDATA[
	jQuery(document).ready(function($) {

		jQuery('#rover_post_id').val(jQuery('#wp_post_id').val());	//	Easier then having to pass post_id through rover-cross-domain-component

	});
//]]>
</script>

<?php
	}

function rover_force_negative($num) {
	return (-1 * abs($num));
	}
function roveridx_get_rover_page_id()	{		//	The slow way - use only from Admin pages

	global 		$wpdb;
	$post_id 	= $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s", ROVERIDX_META_PAGE_ID ) );

	return $post_id;
	}
function rover_idx_engine_callback() {

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	$engine_array						= array();
	$engine_array['load_jq']			= $_POST['load_jq'];
	$engine_array['load_jq_ui']			= $_POST['load_jq_ui'];
	$current_theme_options				= get_option(ROVER_OPTIONS_THEMING);

	$new_options_array					= (is_array($current_theme_options))
													? wp_parse_args($engine_array, $current_theme_options)
													: $engine_array;

	$r = update_option(ROVER_OPTIONS_THEMING, $new_options_array);

	roveridx_set_template_meta($_POST['rover_post_id'], $template = null);

	$responseVar = array(
	                    'rover_post_id'	=> $engine_array['rover_post_id'],
	                    'success'		=> $r
	                    );

    echo json_encode($responseVar);
	
	die();
	}

add_action('wp_ajax_rover_idx_engine', 'rover_idx_engine_callback');
?>