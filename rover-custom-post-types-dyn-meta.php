<?php


class Rover_IDX_Posts_Dyn_Meta
	{
	
	public			$nonce_action		= null;


	function __construct() {

		$this->nonce_action				= plugin_basename(__FILE__);

		add_action(	'init',				array( $this, 'rover_idx_post_types' ));
		
		add_action(	'add_meta_boxes',	array( $this, 'rover_idx_dyn_meta_metaboxes' ));
		
		add_action(	'save_post',		array( $this, 'rover_idx_dyn_meta_save' ));

		add_filter(	'enter_title_here',	array( $this, 'rover_idx_dyn_meta_enter_title' ));
		}

	public function rover_idx_post_types() {

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Registering custom posts');

		$ret = register_post_type( 
									ROVER_IDX_CUSTOM_POST_DYNAMIC_META, 
									array(
										'labels'				=> array(
																		'name'					=> __( 'Dynamic Meta'),
																		'singular_name'			=> __( 'Dynamic Meta' ),
																		'add_new'				=> __( 'Add Dynamic Meta' ),
																		'add_new_item'			=> __( 'Add Meta Values for Dynamic Page' ),
																		'edit_item'				=> __( 'Edit Dynamic Meta' ),
																		'new_item'				=> __( 'New Dynamic Meta' ),
																		'all_items'				=> __( 'All Dynamic Meta' ),
																		'view_item'				=> __( 'View' ),
																		'search_items'			=> __( 'Search Dynamic Meta' ),
																		'not_found'				=> __( 'No Dynamic Meta found' ),
																		'not_found_in_trash'	=> __( 'No Dynamic Meta found in Trash' ), 
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
										'taxonomies'			=> array(	'rover-idx-dynamic-meta' ),
										'menu_position'			=> null,
										'supports'				=> array(	'title' ),
										'rewrite'				=> array(	'slug'				=> 'rover-idx-dynamic-meta', 
																			'with_front'		=> false )
										)
									);

		if ( is_wp_error( $ret ) ) {
			echo $ret->get_error_message();
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Registering custom post ['.ROVER_IDX_CUSTOM_POST_DYNAMIC_META.']');
			}

		}
		
	public function rover_idx_dyn_meta_enter_title( $input ) {
		global $post_type;
		
		if ( is_admin() && ROVER_IDX_CUSTOM_POST_DYNAMIC_META == $post_type )
			return __( 'Enter Dynamic Page URL', 'your_textdomain' );
		
		return $input;
		}

	public function rover_idx_dyn_meta_metaboxes() {

		add_meta_box(
					'rover_idx_dyn_meta_page_title', 
					'&lt;title&gt; tag value for this dynamic page', 
					array( $this, 'rover_idx_dyn_meta_page_title_callback'), 
					ROVER_IDX_CUSTOM_POST_DYNAMIC_META, 
					'normal', 
					'default'
					);
		add_meta_box(
					'rover_idx_dyn_meta_desc', 
					'Meta Description for this dynamic page', 
					array( $this, 'rover_idx_dyn_meta_desc_callback'), 
					ROVER_IDX_CUSTOM_POST_DYNAMIC_META, 
					'normal', 
					'default'
					);
		add_meta_box(
					'rover_idx_dyn_meta_robots', 
					'Modify default robots settings for this page', 
					array( $this, 'rover_idx_dyn_meta_robots_callback'), 
					ROVER_IDX_CUSTOM_POST_DYNAMIC_META, 
					'normal', 
					'default'
					);
		add_meta_box(
					'rover_idx_dyn_meta_keywords', 
					'Modify keywords for this page', 
					array( $this, 'rover_idx_dyn_meta_keywords_callback'), 
					ROVER_IDX_CUSTOM_POST_DYNAMIC_META, 
					'normal', 
					'default'
					);
		add_meta_box(
					'rover_idx_dyn_body_class', 
					'Body Class - Add additional class(es) to the body tag of this dynamic page', 
					array( $this, 'rover_idx_dyn_body_class_callback'), 
					ROVER_IDX_CUSTOM_POST_DYNAMIC_META, 
					'normal', 
					'default'
					);
		add_meta_box(
					'rover_idx_dyn_canonical_url', 
					'Canonical URL - Avoid duplicate content by telling search engines which URL it <i>should</i> have for the current page.', 
					array( $this, 'rover_idx_dyn_canonical_url_callback'), 
					ROVER_IDX_CUSTOM_POST_DYNAMIC_META, 
					'normal', 
					'default'
					);
		add_meta_box(
					'rover_idx_dyn_sidebar', 
					'Custom sidebar - Select a dynamic sidebar to be displayed on this dynamic page.  This is a great lead generation tool.', 
					array( $this, 'rover_idx_dyn_sidebar_callback'), 
					ROVER_IDX_CUSTOM_POST_DYNAMIC_META, 
					'normal', 
					'default'
					);
		
		}

	public function rover_idx_dyn_meta_page_title_callback( $post ) {
	
		// Add an nonce field so we can check for it later.
		wp_nonce_field( $this->nonce_action, 'rover_idx_meta_box_nonce' );

		/*
		 * Use get_post_meta() to retrieve an existing value
		 * from the database and use the value for the form.
		 */
		$value			= get_post_meta( $post->ID, 'rover_idx_page_title', true );

		echo '<input type="text" id="rover_idx_page_title" name="rover_idx_page_title" class="form-control" value="' . esc_attr( $value ) . '" style="width:25%;" />';
		}

	public function rover_idx_dyn_meta_desc_callback( $post ) {
	
		/*
		 * Use get_post_meta() to retrieve an existing value
		 * from the database and use the value for the form.
		 */
		$value			= get_post_meta( $post->ID, 'rover_idx_meta_desc', true );

		echo '<input type="text" id="rover_idx_meta_desc" name="rover_idx_meta_desc" class="form-control" value="' . esc_attr( $value ) . '" style="width:50%;" />';
		}

	public function rover_idx_dyn_meta_robots_callback( $post ) {
	
		/*
		 * Use get_post_meta() to retrieve an existing value
		 * from the database and use the value for the form.
		 */
		$robots			= get_post_meta( $post->ID, 'rover_idx_meta_robots', true );

		echo '<div class="container-fluid">
				<div class="row checkbox">
					<div class="col-md-3">
						<label>
							<input type="checkbox" id="rover_idx_noindex" name="rover_idx_noindex"  value="1" '.((strpos($robots, 'noindex') !== false) ? 'checked' : '').' /> noindex 
						</label>
					</div>
					<div class="col-md-9">
						<small>Do not index the contents of this page</small>
					</div>
				</div>
				<div class="row checkbox">
					<div class="col-md-3">
						<label>
							<input type="checkbox" id="rover_idx_nofollow" name="rover_idx_nofollow" value="1" '.((strpos($robots, 'nofollow') !== false) ? 'checked' : '').' /> nofollow 
						</label>
					</div>
					<div class="col-md-9">
						<small>Do not follow links on this page</small>
					</div>
				</div>
				<div class="row checkbox">
					<div class="col-md-3">
						<label>
							<input type="checkbox" id="rover_idx_noarchive" name="rover_idx_noarchive" value="1" '.((strpos($robots, 'noarchive') !== false) ? 'checked' : '').' /> noarchive 
						</label>
					</div>
					<div class="col-md-9">
						<small>Do not cache the contents of this page</small>
					</div>
				</div>
			</div>';
		}

	public function rover_idx_dyn_meta_keywords_callback( $post ) {
	
		/*
		 * Use get_post_meta() to retrieve an existing value
		 * from the database and use the value for the form.
		 */
		$value			= get_post_meta( $post->ID, 'rover_idx_meta_keywords', true );

		echo '<input type="text" id="rover_idx_meta_keywords" name="rover_idx_meta_keywords" class="form-control" value="' . esc_attr( $value ) . '" style="width:50%;" />';
		}
		
	public function rover_idx_dyn_body_class_callback($post)	{

		$value			= get_post_meta( $post->ID, 'rover_idx_body_class', true );

		echo '<input type="text" id="rover_idx_body_class" name="rover_idx_body_class" class="form-control" value="' . esc_attr( $value ) . '" style="width:25%;" />';
		}

	public function rover_idx_dyn_canonical_url_callback($post)	{

		$value			= get_post_meta( $post->ID, 'rover_idx_canonical_url', true );

		echo '<input type="text" id="rover_idx_canonical_url" name="rover_idx_canonical_url" class="form-control" value="' . esc_attr( $value ) . '" style="width:50%;" />';
		}

	public function rover_idx_dyn_sidebar_callback($post)	{

		global			$wpdb, $wp;

		$value			=	get_post_meta( $post->ID, 'rover_idx_dyn_sidebar', true );
		
		$all_sidebars	=	$wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts 
											WHERE post_type = '".ROVER_IDX_CUSTOM_POST_DYNAMIC_SIDEBAR."' 
											AND post_status = 'publish'");
		$all_sidebars	=	(is_null($all_sidebars))
								? array()
								: $all_sidebars;
		

		$the_html		=	array();
		$the_html[]		=	'<select name="rover_idx_dyn_sidebar">';
		$the_html[]		= 		'<option value=""></option>';
		foreach ($all_sidebars as $one_sidebar)	{
			$sel		=		($value == $one_sidebar->ID) ? 'selected' : '';
			$the_html[]	=		'<option value="'.$one_sidebar->ID.'" '.$sel.'>'.$one_sidebar->post_title.'</option>';
			}
		$the_html[]		=	'</select>';
		$the_html[]		=	'<div class="help-block">Custom sidebars are defined on the Dynamic Page Sidebars tab in <a href="'.admin_url('admin.php?page=rover-panel-seo').'">Rover IDX &gt;&gt; SEO</a></div>';
		
		echo implode('', $the_html);
		}

	public function rover_idx_dyn_meta_save($post_id) {
		
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

		$post_title			= preg_replace('/[^a-zA-Z0-9]/', '', $the_post->post_title);
		$wpdb->update(
					$wpdb->posts,
					array( 'post_name'	=> $post_title),
					array( 'ID'			=> $post_id),
					array( '%s' ),
					array( '%d' )
					);
		
		// OK, we're authenticated: we need to find and save the data

		// We'll put it into an array to make it easier to loop though.

		$fields		= array(
							'rover_idx_page_title', 
							'rover_idx_meta_desc', 
							'rover_idx_meta_robots', 
							'rover_idx_meta_keywords', 
							'rover_idx_body_class',
							'rover_idx_canonical_url',
							'rover_idx_dyn_sidebar'
							);

		// Add values of $events_meta as custom fields

		foreach ($fields as $field_name) {

			$field_value	= ($field_name == 'rover_idx_meta_robots')
									? $this->get_robots()
									: sanitize_text_field( $_POST[$field_name] );

			if (get_post_meta($post_id, $field_name, FALSE))
				{
				update_post_meta($post_id, $field_name, $field_value);
				}
			else
				{
				add_post_meta($post_id, $field_name, $field_value);
				}

			if (!$field_value) 
				{
				delete_post_meta($post_id, $field_name); // Delete if blank
				}
			}

		}

	public function get_robots() {

		$index_meta					= array();

		$index_meta[]				= ($_POST['rover_idx_noindex'] == '1')
											? 'noindex'
											: 'index';
		$index_meta[]				= ($_POST['rover_idx_nofollow'] == '1')
											? 'nofollow'
											: 'follow';
		$index_meta[]				= ($_POST['rover_idx_noarchive'] == '1')
											? 'noarchive'
											: 'archive';
		return implode(', ', $index_meta);
		}

	}

new Rover_IDX_Posts_Dyn_Meta();

?>