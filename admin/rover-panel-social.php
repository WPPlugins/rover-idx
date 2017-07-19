<?php

require_once ROVER_IDX_PLUGIN_PATH.'admin/rover-templates.php';



// Render the Plugin options form
function roveridx_social_panel_form($atts) {
	$settings				=	get_option(ROVER_OPTIONS_SOCIAL);

	$post_to_wp_comments	=	(isset($settings['post_to_wp_comments']))
								? $settings['post_to_wp_comments']
								: false;

	$email_on_error			=	(isset($settings['email_on_error']))
								? $settings['email_on_error']
								: false;

	$email_on_post			=	(isset($settings['email_on_post']))
								? $settings['email_on_post']
								: false;

	$rover_idx_cat			= 'Rover IDX Property';
	?>
	
	<div id="wp_defaults" class="wrap <?php echo esc_attr( rover_plugins_identifier() ); ?>" data-page="rover-panel-social">

	<?php echo roveridx_panel_header('Social'); ?>

		<div id="rover-social-panel">

			<?php 
			$regionsArray = rover_get_selected_regions();

			require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

			global $rover_idx_content, $wpdb;

			$qry							=	"SELECT * FROM $wpdb->posts AS p".	
												" LEFT JOIN $wpdb->term_relationships as r ON p.ID = r.object_ID".
												" LEFT JOIN $wpdb->term_taxonomy as tax ON r.term_taxonomy_id = tax.term_taxonomy_id".
												" LEFT JOIN $wpdb->terms as terms ON tax.term_id = terms.term_id".
	
												" WHERE	p.post_type = 'post'".
												" AND	p.post_status = 'publish'".
												" AND	p.ID = r.object_id".
												" AND 	terms.name = '".$rover_idx_cat."'";
	
												//	echo $qry.'<br />';
	
			$posts							= $wpdb->get_results( $qry );

			// Get the ID of a given category
			$category_id					= get_cat_ID( $rover_idx_cat );

    		// Get the URL of this category
		    $category_link					= get_category_link( $category_id );

			$settings['control_post_to_wp_as_user']	= rover_wp_user_select($settings, 'post_to_wp_as_user');
			$settings['wp_post_count']		= count($posts);
			$settings['wp_posts_url']		= $category_link;

			$the_content = $rover_idx_content->rover_content(	'ROVER_COMPONENT_WP_SOCIAL_PANEL', 
													array_merge(
																array('region'	=> $regionsArray[0], 'regions'	=> implode(',',$regionsArray)), 
																$settings
																) 
													);
			echo $the_content['the_html'];
			?>

			<p class="submit">
				<span id="jq_msg"></span>
			</p>

		</div>

	<?php echo roveridx_panel_footer($panel = 'social');	?>
	
	</div>

<?php

	}

function rover_idx_social_defaults() {
	return array(
	                    'post_new'					=> false,
	                    'post_price_change'			=> false,
	                    'post_sold'					=> false,
	                    'post_open_houses'			=> false,
	                    'post_monthly_data'			=> false,

	                    'post_to_wp'				=> false,
	                    'post_to_wp_as_user'		=> 1,
						'post_to_wp_comments'		=> false,
	                    'post_to_fb'				=> false,
	                    'post_to_gp'				=> false,
	                    'post_to_tw'				=> false,

	                    'email_on_error'			=> false,
	                    'email_on_error_to_user'	=> '',
	                    'email_on_post'				=> false,
	                    'email_on_post_to_user'		=> '',

						'publish_office_listings'	=> false,

	                    'fb_access_token'			=> false,
	                    'facebook_app'				=> 'disabled'
						);
	}


function rover_idx_social_callback() {

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	$social_array									= array();

	$social_array['post_new']						= ($_POST['post_new'] == 0) ? false : true;
	$social_array['post_price_change']				= ($_POST['post_price_change'] == 0) ? false : true;
	$social_array['post_sold']						= ($_POST['post_sold'] == 0) ? false : true;
	$social_array['post_open_houses']				= ($_POST['post_open_houses'] == 0) ? false : true;
	$social_array['post_monthly_data']				= ($_POST['post_monthly_data'] == 0) ? false : true;

	$social_array['post_to_wp']						= rover_idx_validate_post_bool('post_to_wp');
	$social_array['post_to_wp_as_user']				= intval($_POST['post_to_wp_as_user']);
	$social_array['post_to_wp_comments']			= sanitize_text_field( $_POST['post_to_wp_comments'] );

	$social_array['post_to_fb']						= rover_idx_validate_post_bool('post_to_fb');
	$social_array['post_to_gp']						= rover_idx_validate_post_bool('post_to_gp');
	$social_array['post_to_tw']						= rover_idx_validate_post_bool('post_to_tw');

	$social_array['email_on_error']					= rover_idx_validate_post_bool('email_on_error');
	$social_array['email_on_error_to_user']			= intval($_POST['email_on_error_to_user']);
	$social_array['email_on_post']					= rover_idx_validate_post_bool('email_on_post');
	$social_array['email_on_post_to_user']			= intval($_POST['email_on_post_to_user']);

	$social_array['publish_office_listings']		= rover_idx_validate_post_bool('publish_office_listings');

	
	$social_array['facebook_app']					= sanitize_text_field( $_POST['facebook_app'] );
	$social_array['fb_app_id']						= sanitize_text_field( $_POST['fb_app_id'] );
	$social_array['fb_access_token']				= sanitize_text_field( $_POST['fb_access_token'] );
	$social_array['fb_name']						= sanitize_text_field( $_POST['fb_name'] );
	
	$social_array['rand']							= rand(0,1500);

	$r												= update_option(ROVER_OPTIONS_SOCIAL, $social_array);

	$responseVar = array(
	                    'success'					=> $r,
	                    'post_to_wp'				=> rover_idx_validate_post_bool('post_to_wp'),
	                    'post_to_fb'				=> rover_idx_validate_post_bool('post_to_fb'),
	                    'post_to_gp'				=> rover_idx_validate_post_bool('post_to_gp'),
	                    'post_to_tw'				=> rover_idx_validate_post_bool('post_to_tw'),
	                    'facebook_app'				=> rover_idx_validate_post_bool('facebook_app')
	                    );

    echo json_encode($responseVar);
	
	die();
	}


add_action('wp_ajax_rover_idx_social', 'rover_idx_social_callback');


function rover_idx_run_social_cron_callback() {

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	require_once ROVER_IDX_PLUGIN_PATH.'rover-social.php';

	roveridx_refresh_social();

	$responseVar = array(
	                    'success'				=> true
	                    );

    echo json_encode($responseVar);
	
	die();
	}

add_action('wp_ajax_rover_idx_run_social_cron', 'rover_idx_run_social_cron_callback');

	

function rover_wp_user_select($settings, $key)
	{
	global $wpdb;
	
	$authors	= $wpdb->get_results("SELECT ID, user_nicename from $wpdb->users ORDER BY display_name");

	foreach($authors as $author) {
		$selected	= (count($authors) == 1)
							? 'selected=selected'
							: roveridx_val_is_selected($settings, $key, $author->ID);

		$theHTML	.=	"<option value='".$author->ID."' ".$selected."> ".$author->user_nicename."</option>";		
		}

	return $theHTML;
	}

?>