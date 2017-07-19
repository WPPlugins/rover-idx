<?php


class Rover_IDX_Posts_Agent
	{
	
	public			$nonce_action		= null;


	function __construct() {

		$this->nonce_action				= plugin_basename(__FILE__);

		add_action(	'init',				array( &$this, 'rover_idx_post_types' ));
	
		add_action(	'save_post',		array( &$this, 'rover_idx_agent_save' ));
		}

	public function rover_idx_post_types() {

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Registering custom posts');

		$ret = register_post_type( 
									ROVER_IDX_CUSTOM_POST_AGENT, 
									array(
										'labels'				=> array(
																		'name'					=> __( 'Agent'),
																		'singular_name'			=> __( 'Agent' ),
																		'add_new'				=> __( 'Add Agent' ),
																		'add_new_item'			=> __( 'Add Meta Values for Agent' ),
																		'edit_item'				=> __( 'Edit Agent' ),
																		'new_item'				=> __( 'New Agent' ),
																		'all_items'				=> __( 'All Agent' ),
																		'view_item'				=> __( 'View' ),
																		'search_items'			=> __( 'Search Agent' ),
																		'not_found'				=> __( 'No Agent found' ),
																		'not_found_in_trash'	=> __( 'No Agent found in Trash' ), 
																		'parent_item_colon'		=> __( '' )
						//												'menu_name'				=> __( 'Rover IDX' )
																		),
										'public'				=> true,
										'publicly_queryable'	=> true,
										'show_ui'				=> true, 
										'show_in_menu'			=> false, 
										'query_var'				=> true,
										'capability_type'		=> 'post',
										'map_meta_cap'			=> true,
										'has_archive'			=> true, 
										'hierarchical'			=> false,
										'taxonomies'			=> array(	'agent-detail-page' ),
										'menu_position'			=> null,
										'supports'				=> array(	'title' ),
										'rewrite'				=> array(	'slug'				=> 'agent-detail-page', 
																			'with_front'		=> false )
										)
									);

		if ( is_wp_error( $ret ) ) {
			echo $ret->get_error_message();
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Registering custom post ['.	ROVER_IDX_CUSTOM_POST_AGENT.']');
			}

		}


	public function rover_idx_agent_save($post_id) {
		
		global		$wpdb;

		if ( !wp_verify_nonce( $_POST['rover_idx_meta_box_nonce'], $this->nonce_action )) {
			return $post_id;
			}

		// Is the user allowed to edit the post or page?

		if ( !current_user_can( 'edit_post', $post_id ))
			return $post_id;

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
			}


		if ( wp_is_post_revision( $post_id ) )
			return; // Don't store custom data twice


		//	The 'url' is saved in the post_title column.  But the post_name column is indexed.
		$the_post	= get_post($post_id); 

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '['.$the_post->ID.'] ['.$the_post->post_title.']');
		}

	}

new Rover_IDX_Posts_Agent();

?>