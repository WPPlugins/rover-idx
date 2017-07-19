<?php

require_once ROVER_IDX_PLUGIN_PATH.'admin/rover-templates.php';


add_action('wp_ajax_rover_idx_migrate_ds', 'rover_idx_migrate_ds_callback');
add_action('wp_ajax_rover_idx_dismiss_ds', 'rover_idx_dismiss_ds_callback');


function roveridx_migrate_ds_panel_form($atts) {

	?>
	
	<div id="wp_defaults" class="wrap <?php echo esc_attr( rover_plugins_identifier() ); ?>" data-page="rover-panel-migrate-ds">

		<?php echo roveridx_panel_header('MigrateDS'); ?>

		<div id="rover-migrate-ds-panel">

			<?php 
			global 			$wpdb;
	
			$rows			= $wpdb->get_results( "SELECT * FROM $wpdb->posts 
												WHERE post_type = 'ds-idx-listings-page'
												AND post_status = 'publish'" );
	
			?>

		</div>

		</h4><?php echo number_format(count($rows)); ?> Diverse Solutions IDX pages exist on this site.</h4>  

		<p>Would you like to convert <?php echo (count($rows) == 1) ? 'it' : 'them' ?> to Rover IDX pages?</p>

		<button class="btn btn-primary rover-idx-migrate-ds">Convert</button>

		<div class="rover-idx-migrate-ds-log"></div>

		<?php echo roveridx_panel_footer($panel = 'migrate_ds');	?>

		<script type="text/javascript">

			(function( $ ){

				$('.rover-idx-migrate-ds').click( function(e) {
				
					// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
					$.ajax({
						type:		'POST',
						dataType:	'json',
						url:		ajaxurl, 
						data:		{
									action:		'rover_idx_migrate_ds',
									security:	$('#wp_security').val()
									},
						success: function (data) { 
							if (data.success == true)
								{
								$('.rover-idx-migrate-ds-log').html(data.html);
								}
							else 
								{
								$('.rover-idx-migrate-ds-log').html('Migration failed');
								}				
							}
						});

					});

				})( jQuery );

		</script>
	
	</div>

<?php

	}



function rover_idx_migrate_ds_callback() {

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	global 				$wpdb;
	$success			= true;
	$the_html			= array();
	$num_inserted		= 0;

	$parent_id			= add_parent_ds_page();

	foreach( $wpdb->get_results( "SELECT * FROM $wpdb->posts 
											WHERE post_type = 'ds-idx-listings-page'
											AND post_status = 'publish'" ) as $one_row)
		{
		$new_shortcode	= add_rover_idx_shortcode($one_row->ID);
		$the_content	= $one_row->post_content . $new_shortcode;

		//	First - To avoid name conflicts, make this DS custom post a sub-page to the parent_id we just created

		add_post_meta($one_row->ID, 'prev-parent-id', $one_row->post_parent);
		$updated_id		= wp_update_post(array(
											'ID'			=> $one_row->ID,
											'post_parent'	=> $parent_id,
											));

		if (is_wp_error($updated_id)) {
			foreach ($updated_id->get_error_messages() as $error) {
				$the_html[]	= '['.$one_row->ID.'] Error setting parent: ['.$error.']';
				}
			}

		//	Second - create the new Rover page

		$post_id		= wp_insert_post(
										array(
											'comment_status'	=>	'closed',
											'ping_status'		=>	'closed',
											'post_author'		=>	$one_row->post_author,
											'post_date'			=>	$one_row->post_date,
											'post_content'		=>	$the_content,
											'post_title'		=>	$one_row->post_title,
											'post_excerpt'		=>	$one_row->post_excerpt,
											'post_status'		=>	'publish',
											'post_name'			=>	$one_row->post_name,
											'post_parent'		=>	$one_row->post_parent,
											'post_type'			=>	'page',
										)
									);

		if (is_wp_error($post_id)) {
			$errors = $id->get_error_messages();
			foreach ($errors as $error) {
				$the_html[]	= '['.$one_row->ID.'] Error setting parent: ['.$error.']';
				}
			}
		else
			{
			$num_inserted++;

			add_post_meta($post_id, 'rover-idx-migrated', 'ds');

			foreach( $wpdb->get_results( "SELECT `meta_key`, `meta_value`
												FROM $wpdb->postmeta
												WHERE `post_id` = $one_row->ID") as $one_meta)
				{
				add_post_meta($post_id, $one_meta->meta_key, $one_meta->meta_value);
				}

			if (!is_null($post_id))
				$the_html[]	= 'Created ['.$post_id.'] and added '.$new_shortcode;
			}

		}

//	if ($num_inserted)
		update_option( 'roveridx_has_diverse_solutions', 'no' );

    echo json_encode(array(
	                    'success'				=> $success,
	                    'parent_id'				=> $parent_id,
	                    'html'					=> implode('', $the_html)
	                    ));
	
	die();
	}


function rover_idx_dismiss_ds_callback()
	{
	update_option( 'roveridx_dismissed_diverse_solutions', 'yes' );
	}

function add_parent_ds_page()
	{
	$parent_title		= 'Migrated Diverse Solutions IDX pages';

	$parent_page		= get_page_by_title( $parent_title );

	if (is_null($parent_page))
		{
		$parent_id		= wp_insert_post(
										array(
											'comment_status'	=>	'closed',
											'ping_status'		=>	'closed',
											'post_author'		=>	$one_row->post_author,
											'post_content'		=>	'DS custom post types parent document',
											'post_title'		=>	$parent_title,
											'post_status'		=>	'publish',
											'post_name'			=>	'DS Custom Post IDX pages',
											'post_type'			=>	'page',
										)
									);
		}
	else
		{
		$parent_id		= $parent_page->ID;
		}

	return $parent_id;
	}

function add_rover_idx_shortcode($post_id)
	{
	$ds_filters				= get_post_meta( $post_id, $key = 'dsidxpress-assembled-url', $single = true );

	if (!empty($ds_filters))
		{
		$rover_params		= array();
		$rover_shortcode	= array();
		$ds_filters			= urldecode($ds_filters);

		parse_str(parse_url( $ds_filters, PHP_URL_QUERY ), $ds_filters);

		foreach($ds_filters as $one_key => $one_val)
			{
			if (strpos($one_key, 'idx-q-PropertyTypes') !== false)
				{
				if ($one_val == '511')
					$rover_params['prop_types'][]	= 'condo';
				else if ($one_val == '512')
					$rover_params['prop_types'][]	= 'townhouse';
				else if ($one_val == '513')
					{
					$rover_params['prop_types'][]	= 'condo';
					$rover_params['prop_types'][]	= 'singlefamily';
					}
				else if ($one_val == '516')
					$rover_params['prop_types'][]	= 'singlefamily';
				else if ($one_val == '517')
					$rover_params['prop_types'][]	= 'fractional';
				else if ($one_val == '805')
					$rover_params['prop_types'][]	= 'land';
				else if ($one_val == '806')
					$rover_params['prop_types'][]	= 'mobilehome';
				else if ($one_val == '807')
					$rover_params['prop_types'][]	= 'resincome';
				}

			if (strpos($one_key, 'idx-q-Cities') !== false)
				{
				$rover_params['cities'][]			= $one_val;
				}

			if (strpos($one_key, 'idx-q-Communities') !== false)
				{
				$rover_params['areas'][]			= $one_val;
				}

			if (strpos($one_key, 'idx-q-Counties') !== false)
				{
				$rover_params['counties']	= $one_val;
				}

			if (strpos($one_key, 'idx-q-States') !== false)
				{
				$rover_params['state']	= $one_val;
				}

			if (strpos($one_key, 'idx-q-ZipCodes') !== false)
				{
				$rover_params['zipcode'][]			= $one_val;
				}

			if (strpos($one_key, 'idx-q-MlsNumbers') !== false)
				{
				$rover_params['mlnumber']			= $one_val;
				}

			if (strpos($one_key, 'idx-q-BedsMin') !== false)
				{
				$rover_params['min_beds']			= $one_val;
				}

			if (strpos($one_key, 'idx-q-BedsMax') !== false)
				{
				$rover_params['max_beds']			= $one_val;
				}

			if (strpos($one_key, 'idx-q-BathsMin') !== false)
				{
				$rover_params['min_baths']			= $one_val;
				}

			if (strpos($one_key, 'idx-q-BathsMax') !== false)
				{
				$rover_params['max_baths']			= $one_val;
				}

			if (strpos($one_key, 'idx-q-PriceMin') !== false)
				{
				$rover_params['min_price']			= $one_val;
				}

			if (strpos($one_key, 'idx-q-PriceMax') !== false)
				{
				$rover_params['max_price']			= $one_val;
				}

			if (strpos($one_key, 'idx-q-ImprovedSqFtMin') !== false)
				{
				$rover_params['sqft']				= $one_val;
				}

			if (strpos($one_key, 'idx-q-LotSqFtMin') !== false)
				{
				$rover_params['acres']				= (intval($one_val) > 0)
															? round(intval($one_val) / 43560, 2)
															: 0;
				}

			if (strpos($one_key, 'idx-q-YearBuiltMin') !== false)
				{
				$rover_params['min_year_built']			= $one_val;
				}

			if (strpos($one_key, 'idx-q-YearBuiltMax') !== false)
				{
				$rover_params['max_year_built']			= $one_val;
				}

			if (strpos($one_key, 'idx-q-ListingAgentID') !== false)
				{
				$rover_params['listing_agent_mlsid']	= $one_val;
				}

			if (strpos($one_key, 'idx-q-ListingOfficeID') !== false)
				{
				$rover_params['listing_office_mlsid']	= $one_val;
				}

			if (strpos($one_key, 'idx-q-ListingStatuses') !== false)
				{
				if ($one_val == '2' || $one_val == '4')
					$rover_params['status']			= 'Pending';
				else if ($one_val == '8')
					$rover_params['status']			= 'Sold';
				}

			if (strpos($one_key, 'idx-q-RadiusLatitude') !== false)
				{
				$lat								= $one_val;
				$lng								= null;
				$radius								= null;

				foreach($ds_filters as $radius_key => $radius_val)
					{
					if (strpos($radius_key, 'idx-q-RadiusLongitude') !== false)
						$lng						= $radius_val;
					if (strpos($radius_key, 'idx-q-RadiusDistanceInMiles') !== false)
						$radius						= $radius_val;
					}

				if (!is_null($lat) && !is_null($lng) && !is_null($radius))
					$rover_params['find_closest']		= $lat.'|'.$lng.'|'.$radius;
				}


			if (strpos($one_key, 'idx-d-SortOrders') !== false)
				{
				if (strpos($one_key, 'Column') !== false)
					{
					if ($one_val == 'Price')
						$order_by					= 'ListingPrice';
					}
				else if (strpos($one_key, 'Direction') !== false)
					{
					if ($one_val == 'ASC')
						$order_by					= 'ListingPrice';
					}
				}
			}

		$rover_shortcode[]							= '[rover_idx_results ';

		foreach ($rover_params as $one_key => $one_val)
			{
			$rover_shortcode[]						= (is_array($one_val))
															? ($one_key.'="'.implode(',', $rover_params[$one_key]).'"')
															: ($one_key.'="'.$one_val.'"');
			}

		$rover_shortcode[]							= ']';

		return implode(' ', $rover_shortcode);
		}

	return null;
	}

?>