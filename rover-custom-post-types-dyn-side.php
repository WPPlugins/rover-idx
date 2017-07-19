<?php

class Rover_IDX_Posts_Dyn_Side
	{
	
	public			$nonce_action		= null;


	function __construct() {

		$this->nonce_action				= plugin_basename(__FILE__);

		add_action(	'init',				array( $this, 'rover_idx_post_types' ));

		add_action(	'add_meta_boxes',	array( $this, 'rover_idx_dyn_side_metaboxes' ));

		add_action(	'save_post',		array( $this, 'rover_idx_dyn_side_save' ));

		add_filter(	'enter_title_here',	array( $this, 'rover_idx_dyn_side_enter_title' ));
		}

	public function rover_idx_post_types() {

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Registering custom posts');

		$ret = register_post_type( 
									ROVER_IDX_CUSTOM_POST_DYNAMIC_SIDEBAR, 
									array(
										'labels'				=> array(
																		'name'					=> __( 'Dynamic Sidebar'),
																		'singular_name'			=> __( 'Dynamic Sidebar' ),
																		'add_new'				=> __( 'Add Dynamic Sidebar' ),
																		'add_new_item'			=> __( 'Add Sidebar HTML for Dynamic Page' ),
																		'edit_item'				=> __( 'Edit Dynamic Sidebar' ),
																		'new_item'				=> __( 'New Dynamic Sidebar' ),
																		'all_items'				=> __( 'All Dynamic Sidebar' ),
																		'view_item'				=> __( 'View' ),
																		'search_items'			=> __( 'Search Dynamic Sidebar' ),
																		'not_found'				=> __( 'No Dynamic Sidebar found' ),
																		'not_found_in_trash'	=> __( 'No Dynamic Sidebar found in Trash' ), 
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
										'taxonomies'			=> array(	'rover-idx-dynamic-sidebar' ),
										'menu_position'			=> null,
										'supports'				=> array(	'title', 'editor' ),
										'rewrite'				=> array(	'slug'				=> 'rover-idx-dynamic-sidebar', 
																			'with_front'		=> false )
										)
									);

		if ( is_wp_error( $ret ) ) {
			echo $ret->get_error_message();
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Registering custom post ['.ROVER_IDX_CUSTOM_POST_DYNAMIC_SIDEBAR.']');
			}
		}
		
	public function rover_idx_dyn_side_enter_title( $input ) {
		global $post_type;
		
		if ( is_admin() && ROVER_IDX_CUSTOM_POST_DYNAMIC_SIDEBAR == $post_type )
			return __( 'Name this Dynamic Sidebar', 'your_textdomain' );
		
		return $input;
		}

	public function rover_idx_dyn_side_metaboxes() {

		add_meta_box(
					'rover_idx_dyn_side_desc', 
					'Description for this dynamic page sidebar', 
					array( $this, 'rover_idx_dyn_side_desc_callback'), 
					ROVER_IDX_CUSTOM_POST_DYNAMIC_SIDEBAR, 
					'normal', 
					'default'
					);
		
		}


	public function rover_idx_dyn_side_desc_callback( $post ) {
	
		wp_nonce_field( $this->nonce_action, 'rover_idx_meta_box_nonce' );

		/*
		 * Use get_post_meta() to retrieve an existing value
		 * from the database and use the value for the form.
		 */
		$value = get_post_meta( $post->ID, 'rover_idx_side_desc', true );

		echo '<input type="text" id="rover_idx_side_desc" name="rover_idx_side_desc" class="form-control" value="' . esc_attr( $value ) . '" style="width:100%;" />';
//		echo '<textarea id="rover_idx_side_desc" name="rover_idx_side_desc" class="form-control"  style="width:100%;" >' . esc_attr( $value ) . '</textarea>';
		}

	public function rover_idx_dyn_side_save($post_id) {
		
		global		$wpdb;

		if ( !wp_verify_nonce( $_POST['rover_idx_meta_box_nonce'], $this->nonce_action )) {
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '['.$post_id.'] nonce is not verified');
			return $post_id;
			}
		
		// Is the user allowed to edit the post or page?

		if ( !current_user_can( 'edit_post', $post_id ))	{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '['.$post_id.'] current_user_cannot edit');
			return $post_id;
			}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '['.$post_id.'] DOING_AUTOSAVE');
			return;
			}


		if ( wp_is_post_revision( $post_id ) )	{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '['.$post_id.'] post is revision');
			return; // Don't store custom data twice
			}


		// We'll put it into an array to make it easier to loop though.

		$fields		= array(
							'rover_idx_sidebar_title',
							'rover_idx_side_desc'
							);

		// Add values of $events_meta as custom fields

		foreach ($fields as $field_name) {

			$field_value	= sanitize_text_field( $_POST[$field_name] );

			if (get_post_meta($post_id, $field_name, FALSE))
				{
				rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '['.$the_post->ID.'] ['.$field_name.'] ['.$field_value.']');

				update_post_meta($post_id, $field_name, $field_value);
				}
			else
				{
				rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '['.$the_post->ID.'] ['.$field_name.'] ['.$field_value.']');

				add_post_meta($post_id, $field_name, $field_value);
				}

			if (!$field_value) 
				{
				delete_post_meta($post_id, $field_name); // Delete if blank
				}
			}

		}


	}

new Rover_IDX_Posts_Dyn_Side();

?>