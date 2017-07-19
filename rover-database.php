<?php

function update_rover_tables()
	{
	create_tracking_table();

	rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Setting \'roveridx_db_version\' to '.ROVER_VERSION);

	update_option("roveridx_db_version", ROVER_VERSION);
	}
	
function create_tracking_table()
	{
	global $wpdb;

	$sql = "CREATE TABLE ".roveridx_tracking_table()." ( 
				id VARCHAR(20) NOT NULL,
				time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				UNIQUE KEY id (id) );";
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	
	if (!empty($wpdb->last_error))
		wp_die($wpdb->last_error.' / '.$sql);
	}
function roveridx_tracking_table_exists()
	{
	global $wpdb;

	$rows = $wpdb->get_results("SELECT `TABLE_NAME` FROM information_schema.tables WHERE table_schema = '".DB_NAME."' AND table_name = '".roveridx_tracking_table()."'");

	return ((count($rows)) ? true : false);
	}
function roveridx_already_in_tracking($id, $region) {
	global $wpdb;
	$key	= $id.'-'.$region;

 	$sql =	"SELECT * FROM ".roveridx_tracking_table()." WHERE id = '$key'";	
	$rows = $wpdb->get_results( $sql );

	return ((count($rows)) ? true : false);
	}

function roveridx_update_tracking($id, $region) {
	global $wpdb;
	$key	= $id.'-'.$region;

	//	Purge old tracking records
	$seven_days_ago	= date ( 'Y-m-j' , strtotime ( '-7 days' , strtotime ( date('Y-m-d') ) ) );
	$wpdb->query( $wpdb->prepare( "DELETE FROM ".roveridx_tracking_table()." WHERE time = '%s'", date('Y-m-d', $seven_days_ago) ));

	return $wpdb->insert( roveridx_tracking_table(), array( 'id' => $key, 'time' => current_time('mysql') ) );
	}
function roveridx_tracking_table()	{
	global $wpdb;

	$rover_prefix			= 'roveridx_';
	$rover_tracking_table	= $wpdb->prefix . $rover_prefix . 'tracking';

	return $rover_tracking_table;
	}
function roveridx_set_template_meta($roveridx_post_id, $template_file = null)
	{
	rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Starting');

	//	Don't 'add_post_meta' - it keeps adding entries to wp-postmeta

	if (function_exists('genesis_unregister_layout'))		//	StudioPress Genesis platform
		{
		update_post_meta( $roveridx_post_id, '_genesis_layout',	'full-width-content' );
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Using (Genesis) update_post_meta to set _genesis_layout to "full-width-content"');
		}
	else if (function_exists('woo_post_meta'))				//	WooThemes framework
		{
		update_post_meta( $roveridx_post_id, '_layout', 		'layout-full' );
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Using (Woo) update_post_meta to set _layout to "layout-full"');
		}

	if (!is_null($template_file))
		{
		//	Do this in all cases, even if we are StudioPress or Woothemes

		$template_file			= sanitize_file_name($template_file);

		update_post_meta($roveridx_post_id, WP_TEMPLATE_KEY, $template_file);

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Using (Generic) update_post_meta to set template to "'.$template_file.'"');
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'template is actually "'.get_post_meta($roveridx_post_id, WP_TEMPLATE_KEY, $single = true).'"');
		}
	}
function roveridx_setup_rover_page()	{

	global 		$wpdb;
	$post_id 	= null;

	$one_row = $wpdb->get_row( "SELECT * FROM $wpdb->postmeta WHERE meta_key = '".ROVERIDX_META_PAGE_ID."'" );
	
	//	If we've setup a rover post_id in the past, use it

	if (is_null($one_row))
		{
		$rover_post					= array();
		$rover_post['post_title']	= 'Rover IDX page';
		$rover_post['post_content']	= 'This post will be deleted immediately';
		$rover_post['post_type']	= 'page';
		$rover_post['post_status']	= 'draft';
		$rover_post['post_author']	= 0;
	
		//	Insert the post into the database
		$post_id					= wp_insert_post( $rover_post );
		if (!is_wp_error($post_id)) {

			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Create post_meta '.ROVERIDX_META_PAGE_ID.' ['.$post_id.']');

			//	Add postmeta
			update_post_meta($post_id, ROVERIDX_META_PAGE_ID, $post_id);

			//	Delete that post - we don't need it anymore.  We're only reserving the post_id for our exclusive use
			wp_delete_post($post_id, $force_delete = false);
			}
		}
	else
		{
		$post_id = $one_row->post_id;
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Using already-existing rover page id '.$post_id);
		}

	return $post_id;
	}
	
function roveridx_create_quick_setup_pages($settings)
	{
	$the_html							= array();
	
	if ($settings['create_home'] == 'true')
		{
		$ret							= roveridx_create_page(
															$title		= 'Home',
															$parent		= 0,
															$content	= '[rover_idx_slider]',
															$meta		= 'home'
															);

		$the_html[]						= $ret['html'];
		}

	if ($settings['create_listings'] == 'true')
		{
		$selected_cities				= roveridx_get_listing_cities($settings);

		$ret							= roveridx_create_page(
															$title		= $settings['listings_parent'],
															$parent		= 0,
															$content	= '[rover_idx_full_page]',
															$meta		= 'listings'
															);

		$the_html[]						= $ret['html'];
		$parent_id						= $ret['id'];
		
		foreach($selected_cities as $one_city)
			{
			$ret						= roveridx_create_page(
															$title		= $settings['listings_parent'].' in '.$one_city,
															$parent		= $parent_id,
															$content	= '[rover_idx_full_page cities="'.$one_city.'"]',
															$meta		= 'listings-'.strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $one_city))
															);

			$the_html[]					= $ret['html'];
			}
		}

	if ($settings['create_contact'] == 'true')
		{
		$ret							= roveridx_create_page(
															$title		= 'Contact',
															$parent		= 0,
															$content	= '[rover_idx_contact]',
															$meta		= 'contact'
															);

		$the_html[]						= $ret['html'];
		}

	return '<div>'.implode('<br>', $the_html).'</div>';
	}

function roveridx_find_quick_pages($meta)
	{
	global								$wpdb;

	$existing_pages						= $wpdb->get_results( "
															SELECT      p.*
															FROM        $wpdb->posts as p
															INNER JOIN  $wpdb->postmeta as m ON (m.post_id = p.ID AND m.meta_key = 'rover_quickstart_page')
															WHERE       p.post_type = 'page'
															AND			p.post_status = 'publish'
															AND			m.meta_value LIKE '".$meta."%'
															"); 


	return $existing_pages;
	}

function roveridx_create_page($title, $parent, $content, $meta)
	{
	$the_html							= array();

	$existing_pages						= roveridx_find_quick_pages($meta);

	if (count($existing_pages) === 0)
		{
		$post_id						= wp_insert_post(
														array(
															'comment_status'	=>	'closed',
															'ping_status'		=>	'closed',
															'post_author'		=>	$one_row->post_author,
															'post_date'			=>	$one_row->post_date,
															'post_content'		=>	$content,
															'post_title'		=>	$title,
															'post_status'		=>	'publish',
															'post_name'			=>	$title,
															'post_parent'		=>	$parent,
															'post_type'			=>	'page',
															'meta_input'		=> array(
																					'rover_quickstart_page' => $meta
																					)
														)
													);

		if (is_wp_error($post_id)) {
			$errors						= $id->get_error_messages();
			foreach ($errors as $error) {
				$the_html[]				= '['.$title.'] Error creating page: ['.$error.']';
				}
			}
		else
			{
			if (!is_null($post_id))
				$the_html[]				= 'Created ['.$post_id.'] ['.$title.'] with '.$content;
			}
		}
	else
		{
		$the_html[]						= '['.$title.'] with meta ['.$meta.'] already exists!';
		}

	return array(
				'id'	=> $post_id,
				'html'	=> implode('', $the_html)
				);
	}

function roveridx_get_listing_cities($settings)
	{
	require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

	global								$rover_idx_content;

	$rover_content						= $rover_idx_content->rover_content('ROVER_COMPONENT_GET_QUICKSTART_CITIES', 
														array('for_cities' => $settings['for_cities'])
														);

	return explode(',', $rover_content['the_html']);
	}

function roveridx_fetch_quick_setup_pages()
	{
	$the_html							= array();

	$home_rows							= roveridx_find_quick_pages('home');
	$listing_rows						= roveridx_find_quick_pages('listings');
	$contact_rows						= roveridx_find_quick_pages('contact');

	if (count($home_rows))
		$the_html[]						= number_format((count($home_rows))).' Home page';

	if (count($listing_rows))
		$the_html[]						= number_format((count($listing_rows))).' Listing pages';

	if (count($contact_rows))
		$the_html[]						= number_format((count($contact_rows))).' Contact page';

	return implode(', ', $the_html);
	}

function roveridx_reset_quick_setup_pages()
	{
	$the_html							= array();

	$home_rows							= roveridx_find_quick_pages('home');
	$listing_rows						= roveridx_find_quick_pages('listings');
	$contact_rows						= roveridx_find_quick_pages('contact');

	if (count($home_rows))
		{
		foreach($home_rows as $one_row)
			{
			$the_html[]					= 'Deleting Home ['.$one_row->ID.']';
			wp_delete_post($one_row->ID);
			}
		}

	if (count($listing_rows))
		{
		foreach($listing_rows as $one_row)
			{
			$the_html[]					= 'Deleting Listing ['.$one_row->ID.']';
			wp_delete_post($one_row->ID);
			}
		}

	if (count($contact_rows))
		{
		foreach($contact_rows as $one_row)
			{
			$the_html[]					= 'Deleting Contact ['.$one_row->ID.']';
			wp_delete_post($one_row->ID);
			}
		}

	$the_html[]							= 'Done';

	return implode('<br>', $the_html);
	}
?>