<?php

require_once ROVER_IDX_PLUGIN_PATH.'admin/rover-templates.php';
require_once ROVER_IDX_PLUGIN_PATH.'admin/rover-panel-lists.php';


add_action('wp_ajax_rover_idx_seo',										'rover_idx_seo_callback');
add_action('wp_ajax_rover_idx_do_sitemap',								'rover_idx_do_sitemap_callback');
add_action('wp_ajax_rover_idx_sitemap_history',							'rover_idx_sitemap_history_callback');
add_action('wp_ajax_rover_idx_create_city_dynamic_definitions',			'rover_idx_create_city_dynamic_definitions_callback');
add_action('wp_ajax_rover_idx_create_subdivision_dynamic_definitions',	'rover_idx_create_subdivision_dynamic_definitions_callback');


// Render the Plugin options form
function roveridx_panel_seo_form($atts) {
	
	?>		
	<div class="wrap <?php echo esc_attr( rover_plugins_identifier() ); ?>" data-page="rover-panel-seo">

		<?php echo roveridx_panel_header('SEO');	?>

		<?php 
		$regions_array	= rover_get_selected_regions();
		if (count($regions_array) === 0)
			{
			?>
			<div>Please select one or more Regions from the main RoverIDX settings panel.</div>
			<?php
			}
		else
			{
			?>
			<div id="rover-seo-panel" class="rover-tabs">

				<ul class="nav nav-tabs" role="tablist">
					<li role="presentation" class="active">
						<a href="#rover-seo-general" aria-controls="rover-seo-general" role="tab" data-toggle="tab">General</a>
					</li>
					<li role="presentation">
						<a href="#rover-seo-dynamic" aria-controls="rover-seo-dynamic" role="tab" data-toggle="tab">Dynamic Page Definitions</a>
					</li>
					<li role="presentation">
						<a href="#rover-seo-sidebar" aria-controls="rover-seo-sidebar" role="tab" data-toggle="tab">Dynamic Page Sidebars</a>
					</li>
				</ul>

				<div class="tab-content" style="padding:30px;">
					<div class="tab-pane active" id="rover-seo-general">
						<?php roveridx_create_seo_general_panel($regions_array);	?>
					</div>

					<div class="tab-pane" id="rover-seo-dynamic">
					   <?php roveridx_create_seo_meta_panel($regions_array);	?>
					</div>

					<div class="tab-pane" id="rover-seo-sidebar">
					   <?php roveridx_create_seo_sidebar_panel();	?>
					</div>
				</div>

			</div>
			<?php	
			}

		?>
		<?php echo roveridx_panel_footer($panel = 'seo');	?>

	</div><!-- wrap	-->

	<?php

	}

function roveridx_create_seo_general_panel($regions_array)	{

	require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

	global					$rover_idx_content;

	$rover_content	=		$rover_idx_content->rover_content(	'ROVER_COMPONENT_WP_SEO_PANEL', 
															array(
																'region'	=> $regions_array[0], 
																'regions'	=> implode(',',$regions_array), 
																'settings'	=> get_option(ROVER_OPTIONS_SEO)
																)
															);
	$theHTML		=		$rover_content['the_html'];

	$seo_array		=		get_option(ROVER_OPTIONS_SEO);
	$enabled		=		(isset($seo_array['sitemap_enabled']) && ($seo_array['sitemap_enabled'] == 'disabled'))
									? 'disabled'
									: 'enabled'; 
	$theHTML		.=		'<input type="hidden" id="sitemap_enabled_in_options" value="'.$enabled.'" />';
	$theHTML		.=		'<input type="hidden" id="sitemap_updated" value="'.$seo_array['updated'].'" />';

	$theHTML		.=		'<div class="rover-site-pages" style="display:none;">';
	foreach ( get_pages(array('post_status' => 'publish')) as $page )
		{
		$theHTML	.=			'<option value="' . get_page_link( $page->ID ) . '">'.$page->post_title.'</option>';
		}
	$theHTML		.=		'</div>';

	echo $theHTML;
	}
function roveridx_create_seo_meta_panel($regions_array)	{

	global			$wpdb;

	global					$rover_idx_content;

	$rover_content	=		$rover_idx_content->rover_content(	'ROVER_COMPONENT_WP_SEO_DYNAMIC_PAGE_DEF_PANEL', 
															array(
																'region'	=> $regions_array[0], 
																'regions'	=> implode(',',$regions_array), 
																'settings'	=> get_option(ROVER_OPTIONS_SEO)
																)
															);
	$the_help		=		$rover_content['the_html'];

	$table_opts		= array(
							'cpt'	=> ROVER_IDX_CUSTOM_POST_DYNAMIC_META,
							'top'	=> '<div class="container-fluid">
											<div class="row rover-layout-help">'.$the_help.'</div><!-- row -->
											<div class="row btn-toolbar">
												<a href="'.admin_url('post-new.php?post_type='.ROVER_IDX_CUSTOM_POST_DYNAMIC_META).'">
													<button type="button" class="add_new_meta btn btn-sm btn-primary" aria-hidden="true"><span class="fa fa-magic"></span>&nbsp;Add Dynamic Page Definition</button>
												</a>
												<button type="button" class="add_new_meta_subdivisions btn btn-sm btn-primary pull-right" aria-hidden="true"><span class="fa fa-plus"></span>&nbsp;Subdivisions</button>
												<button type="button" class="add_new_meta_cities btn btn-sm btn-primary pull-right" aria-hidden="true"><span class="fa fa-plus"></span>&nbsp;Cities</button>
												<div class="clearfix"></div>
											</div><!-- row -->
										</div><!-- container-fluid -->'
							);

	$wp_list_table	= new Rover_List_Table($table_opts);
	$wp_list_table->prepare_items("SELECT * FROM $wpdb->posts WHERE post_type = '".ROVER_IDX_CUSTOM_POST_DYNAMIC_META."' AND post_status = 'publish'");
	
	$wp_list_table->display();
	
	roveridx_seo_dynamic_city_dialog($regions_array);
	roveridx_seo_dynamic_subdivision_dialog($regions_array);
	}

function roveridx_create_seo_sidebar_panel()	{

	global			$wpdb;

	$table_opts		= array(
							'cpt'	=> ROVER_IDX_CUSTOM_POST_DYNAMIC_SIDEBAR,
							'top'	=> '<div class="container-fluid">
											<div class="row rover-layout-help">
												<b>Dynamic Page Sidebars</b> are designed to be used with <b>Dynamic Page Definitions</b>.  If your property detail page is configured to use a template that displays a sidebar, you can configure what is displayed within that sidebar here.  This has great lead generation tool potential - for instance, adding a call-to-action in the sidebar with specific property references.
											</div>
											<div class="row btn-toolbar">
												<a href="'.admin_url('post-new.php?post_type='.ROVER_IDX_CUSTOM_POST_DYNAMIC_SIDEBAR).'">
													<button type="button" class="add_new_meta btn btn-sm btn-primary" aria-hidden="true"><span class="fa fa-magic"></span>&nbsp;Add Dynamic Page Sidebar</button>
												</a>
											</div>
										</div>'
							);

	$wp_list_table	= new Rover_List_Table($table_opts);
	$wp_list_table->prepare_items("SELECT * FROM $wpdb->posts WHERE post_type = '".ROVER_IDX_CUSTOM_POST_DYNAMIC_SIDEBAR."' AND post_status = 'publish'");
	
	$wp_list_table->display();
	}
	
function rover_idx_seo_defaults() {

	return array(
				'enabled'			=> 1,
				'crawler_redirect'	=> '404'
				);

	}

function roveridx_build_sitemap_history($all_regions, $atts)
	{
	$theHTML									= '<div class="container-fluid">';
	$include_region_label						= (count($all_regions) > 1) ? true : false;
	$never										= true;

	foreach ($all_regions as $one_region)
		{
		if (array_key_exists($one_region, $atts))
			{
			$never								= false;

			$theHTML .=							'<div class="row">';

			if ($include_region_label)
				$theHTML .=							'<div class="col-md-12">['.$one_region.']</div>';

			$theHTML .=			   					'<div class="col-md-12"><span style="color:green;">'.esc_html( $atts[$one_region]['timestamp'] ).'</span><span> / '.esc_html($atts[$one_region]['desc']).'</span></div>';
			$theHTML .=			   					'<div class="col-md-12"><a href="'.esc_url($atts[$one_region]['url']).'" target="_blank">'.esc_url($atts[$one_region]['url']).'</a></div>';

			$theHTML .=			   				'</div><!-- row -->';
			}
		}

	if (empty($theHTML))
		$theHTML	= '<div style="font-weight:bold;">Never</div>';

	$theHTML .=									'</div><!-- container -->';

	return array(
				'html'	=> $theHTML, 
				'never'	=> $never);
	}

function rover_idx_refresh_dynamic_definitions()
	{
	global						$wpdb;

	$wp_list_table				= new Rover_List_Table();
	$wp_list_table->prepare_items("SELECT * FROM $wpdb->posts WHERE post_type = '".ROVER_IDX_CUSTOM_POST_DYNAMIC_META."' AND post_status = 'publish'");

	ob_start();
	$wp_list_table->display_rows_or_placeholder();

	return ob_get_clean();
	}

function roveridx_seo_dynamic_city_dialog($regions_array)
	{

	require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

	global						$wpdb, $rover_idx_content;

	$rover_content				= $rover_idx_content->rover_content(	'ROVER_COMPONENT_GET_STATES_AND_CITIES', 
																	array(
																			'region'		=> $regions_array[0], 
																			'regions'		=> implode(',',$regions_array)
																		)
																	);

	$checkbox_html				= array();

	$all_cities					= json_decode($rover_content['the_html'], true);

	foreach ($all_cities as $one_state => $cities)
		{
		$checkbox_html[]		=	'<div class="col-md-12"><h4>'.$one_state.'</h4></div>';

		foreach ($cities as $one_city)
			{
			$disabled			= false;

			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, "SELECT ID FROM $wpdb->posts 
												WHERE post_type = '".ROVER_IDX_CUSTOM_POST_DYNAMIC_META."' 
												AND post_status = 'publish'
												AND post_name = '".preg_replace('/[^a-zA-Z0-9]/', '', $one_state.$one_city)."'");

			$dyn_meta			= $wpdb->get_row("SELECT ID FROM $wpdb->posts 
												WHERE post_type = '".ROVER_IDX_CUSTOM_POST_DYNAMIC_META."' 
												AND post_status = 'publish'
												AND post_name = '".preg_replace('/[^a-zA-Z0-9]/', '', $one_state.$one_city)."'");

			if (!is_null($dyn_meta))
				$disabled		= true;

			$checkbox_html[]	=	'<div class="col-md-4'.(($disabled) ? ' disabled' : '').'">
										<div class="checkbox">
											<label>
												<input name="city[]" type="checkbox" value="'.esc_html($one_city).'" '.(($disabled) ? 'disabled' : '').' /> '.esc_html($one_city).'
											</label>
										</div>
									</div>';
			}
		}

	echo 		'<div class="modal fade" id="roveridx_seo_dynamic_city_dialog">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
								<h4 class="modal-title">Create Dynamic Meta Definitions for Selected Cities</h4>
							</div>
							<div class="modal-body">
								<form class="form-inline" role="form">
									'.implode('', $checkbox_html).'
									<div class="clearfix"></div>
								</form>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default sel-all pull-left">Select All</button>
								<button type="button" class="btn btn-default sel-none pull-left">Select None</button>

								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								<button type="button" class="btn btn-primary">Create</button>
							</div>
						</div><!-- /.modal-content -->
					</div><!-- /.modal-dialog -->
				</div><!-- /.modal -->';
	}

function roveridx_seo_dynamic_subdivision_dialog($regions_array)
	{
	require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

	global					$wpdb, $rover_idx_content;

	$rover_content			= $rover_idx_content->rover_content(	'ROVER_COMPONENT_GET_STATES_AND_SUBDIVISIONS', 
																array(
																		'region'		=> $regions_array[0], 
																		'regions'		=> implode(',',$regions_array)
																	)
																);

	$prev_city				= null;
	$checkbox_html			= array();
	$locations				= explode(',', $rover_content['the_html']);
	sort($locations);

	foreach ($locations as $one_location)
		{
		$disabled			= false;
		$location_data		= explode('__', $one_location);
		$state				= $location_data[0];
		$city				= $location_data[1];
		$subdivisions		= explode('|', $location_data[2]);

		$checkbox_html[]	=	'<div class="col-md-12"><h4>'.$city.'</h4></div>';

		foreach ($subdivisions as $one_subdivision)
			{
			$disabled		= false;

			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, "SELECT ID FROM $wpdb->posts 
												WHERE post_type = '".ROVER_IDX_CUSTOM_POST_DYNAMIC_META."' 
												AND post_status = 'publish'
												AND post_name = '".preg_replace('/[^a-zA-Z0-9]/', '', ($state.'subdivision'.$subdivision))."'");

			$dyn_meta		= $wpdb->get_row("SELECT ID FROM $wpdb->posts 
												WHERE post_type = '".ROVER_IDX_CUSTOM_POST_DYNAMIC_META."' 
												AND post_status = 'publish'
												AND post_name = '".preg_replace('/[^a-zA-Z0-9]/', '', ($state.'subdivision'.$one_subdivision))."'");
	
			if (!is_null($dyn_meta))
				$disabled	= true;

			$checkbox_html[]=	'<div class="col-md-4'.(($disabled) ? ' disabled' : '').'">
									<div class="checkbox">
										<label>
											<input name="subdivision[]" type="checkbox" value="'.esc_html($state.'__'.$city.'__'.$one_subdivision).'" '.(($disabled) ? 'disabled' : '').' /> '.esc_html($one_subdivision).'
										</label>
									</div>
								</div>';
			}
		}

	echo 		'<div class="modal fade" id="roveridx_seo_dynamic_subdivision_dialog">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
								<h4 class="modal-title">Create Dynamic Meta Definitions for Selected Subdivisions</h4>
							</div>
							<div class="modal-body">
								<form class="form-inline" role="form">
									'.implode('', $checkbox_html).'
									<div class="clearfix"></div>
								</form>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default sel-all pull-left">Select All</button>
								<button type="button" class="btn btn-default sel-none pull-left">Select None</button>

								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								<button type="button" class="btn btn-primary">Create</button>
							</div>
						</div><!-- /.modal-content -->
					</div><!-- /.modal-dialog -->
				</div><!-- /.modal -->';
	}



/*************************************************/
//	Callbacks
/*************************************************/

function rover_idx_seo_callback()	{

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	$seo_opts						= get_option(ROVER_OPTIONS_SEO);

	$seo_opts['crawler_redirect']	= sanitize_text_field( $_POST['crawler_redirect'] );

	$r								= update_option(ROVER_OPTIONS_SEO, $seo_opts);

	$responseVar = array(
	                    'success'	=> true
	                    );

    echo json_encode($responseVar);
	
	die();
	}

function rover_idx_do_sitemap_callback() {

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	require_once ROVER_IDX_PLUGIN_PATH.'rover-sitemap.php';

	$success					= roveridx_refresh_sitemap($force_refresh = true);

	$sitemap_opts				= get_option(ROVER_OPTIONS_SEO);
	$all_selected_regions		= rover_get_selected_regions();
	$history					= roveridx_build_sitemap_history($all_selected_regions, $sitemap_opts);

	$responseVar = array(
	                    'html'		=> $history['html'],
	                    'success'	=> true
	                    );

    echo json_encode($responseVar);
	
	die();
	}


function rover_idx_sitemap_history_callback() {

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	$sitemap_opts				= get_option(ROVER_OPTIONS_SEO);
	$all_selected_regions		= rover_get_selected_regions();
	$history					= roveridx_build_sitemap_history($all_selected_regions, $sitemap_opts);

	$responseVar = array(
	                    'html'		=> $history['html'],
						'never'		=> $history['never'],
	                    'success'	=> true
	                    );

    echo json_encode($responseVar);
	
	die();
	}

function rover_idx_create_city_dynamic_definitions_callback()	{

	global						$wpdb;

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	$added						= 0;
	$cities						= array();
	foreach (explode('&', sanitize_text_field( $_POST['cities'] ) ) as $post_key => $post_val)
		{
		$one_row				= explode('=', $post_val);
		$one_location_data		= explode('__', $one_row[1]);
		$state					= $one_location_data[0];
		$city					= $one_location_data[1];

		$dyn_meta				= $wpdb->get_row("SELECT ID FROM $wpdb->posts 
												WHERE post_type = '".ROVER_IDX_CUSTOM_POST_DYNAMIC_META."' 
												AND post_status = 'publish'
												AND post_name = '".preg_replace('/[^a-zA-Z0-9]/', '', $state.$city)."'");

		if (is_null($dyn_meta))
			{
			//	Doesn't exist - add it

			$id = wp_insert_post( array(
									  'comment_status' => 'closed',	
									  'ping_status'    => 'closed',
									  'post_content'   => '[rover_crm_inbound]',
									  'post_name'      => preg_replace('/[^a-zA-Z0-9]/', '', $state.'/'.$city),
									  'post_status'    => 'publish',
									  'post_title'     => $state.'/'.$city,
									  'post_type'      => ROVER_IDX_CUSTOM_POST_DYNAMIC_META
									  ), $wp_error );

			if ($id)
				{
				add_post_meta($id, 'rover_idx_page_title', 'Homes for sale in '.$city);
				$added++;
				}
			}
		}

	$responseVar = array(
	                    'success'	=> $added,
	                    'tbody'		=> rover_idx_refresh_dynamic_definitions()
	                    );

    echo json_encode($responseVar);
	
	die();
	}

function rover_idx_create_subdivision_dynamic_definitions_callback()	{

	global						$wpdb;

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	$added						= 0;
	$cities						= array();
	foreach (explode('&', sanitize_text_field( $_POST['subdivisions'] ) ) as $post_key => $post_val)
		{
		$one_row				= explode('=', $post_val);
		$one_location_data		= explode('__', $one_row[1]);
		$state					= $one_location_data[0];
		$city					= $one_location_data[1];
		$subdivision			= $one_location_data[2];

		$dyn_meta				= $wpdb->get_row("SELECT ID FROM $wpdb->posts 
												WHERE post_type = '".ROVER_IDX_CUSTOM_POST_DYNAMIC_META."' 
												AND post_status = 'publish'
												AND post_name = '".preg_replace('/[^a-zA-Z0-9]/', '', $state.'subdivision'.$subdivision)."'");

		if (is_null($dyn_meta))
			{
			//	Doesn't exist - add it

			$id = wp_insert_post( array(
									  'comment_status' => 'closed',
									  'ping_status'    => 'closed',
									  'post_content'   => '[rover_crm_inbound]',
									  'post_name'      => preg_replace('/[^a-zA-Z0-9]/', '', $state.'subdivision'.$subdivision),	
									  'post_status'    => 'publish',
									  'post_title'     => $state.'/subdivision/'.$subdivision,	
									  'post_type'      => ROVER_IDX_CUSTOM_POST_DYNAMIC_META
									  ), $wp_error );

			if ($id)
				{
				add_post_meta($id, 'rover_idx_page_title', 'Homes for sale in '.$city.' subdivision of '.$subdivision);
				$added++;
				}
			}
		}

	$responseVar = array(
	                    'success'	=> $added,
	                    'tbody'		=> rover_idx_refresh_dynamic_definitions()
	                    );

    echo json_encode($responseVar);
	
	die();
	}
?>