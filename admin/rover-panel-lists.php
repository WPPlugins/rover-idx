<?php

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Rover_List_Table extends WP_List_Table {

	private						$cpt	= null;
	private						$top	= null;
	private						$bottom	= null;

	/**
	 * Constructor, we override the parent to pass our own arguments
	 * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	 */
	function __construct($opts	= null) {

		$this->cpt				= (is_array($opts) && isset($opts['cpt']))
										? $opts['cpt']
										: null;

		$this->top				= (is_array($opts) && isset($opts['top']))
										? $opts['top']
										: null;

		$this->bottom			= (is_array($opts) && isset($opts['bottom']))
										? $opts['bottom']
										: null;

		$table_opts				= array(
										'singular'	=> 'wp_list_text_link',		// Singular label
										'plural'	=> 'wp_list_test_links',	// Plural label, also this well be one of the table css class
										'top'		=> $this->top,
										'bottom'	=> $this->bottom,
										'ajax'		=> false					// We won't support Ajax for this table
										);

		parent::__construct( $table_opts );
	}

	function extra_tablenav( $which ) {

		if ( $which == "top" && !empty($this->top) ){
			//The code that goes before the table is here
			echo 	$this->top;
		}

		if ( $which == "bottom" && !empty($this->bottom) ){
			//The code that goes after the table is there
			echo 	$this->bottom;
		}
	}

	/**
	 * Define the columns that are going to be used in the table
	 * @return array $columns, the array of columns to use with the table
	 */
	function get_columns() {
		if ($this->cpt == ROVER_IDX_CUSTOM_POST_DYNAMIC_META)
			{
			return $columns				= array(
				'col_post_id'			=>__('ID'),
				'col_post_url'			=>__('URL'),
				'col_post_title'		=>__('Title'),
				'col_post_desc'			=>__('Description')
				);
			}
		else if ($this->cpt == ROVER_IDX_CUSTOM_POST_DYNAMIC_SIDEBAR)
			{
			return $columns				= array(
				'col_post_id'			=>__('ID'),
				'col_post_title'		=>__('Title'),
				'col_post_desc'			=>__('Description')
				);
			}
	}

	/**
	 * Decide which columns to activate the sorting functionality on
	 * @return array $sortable, the array of columns that can be sorted by the user
	 */
	public function get_sortable_columns() {
		if ($this->cpt == ROVER_IDX_CUSTOM_POST_DYNAMIC_META)
			{
			return $sortable			= array(
				'col_post_id'			=>'post_id',
				'col_post_url'			=>'post_url',
				'col_post_title'		=>'post_title',
				'col_post_desc'			=>'post_desc'
				);
			}
		else if ($this->cpt == ROVER_IDX_CUSTOM_POST_DYNAMIC_SIDEBAR)
			{
			return $sortable			= array(
				'col_post_id'			=>'post_id',
				'col_post_title'		=>'post_title',
				'col_post_desc'			=>'post_desc'
				);
			}
	}

	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 */
	function prepare_items($sql = null) {

		global		$wpdb, $_wp_column_headers;

		$screen		= get_current_screen();

		/* -- Preparing your query -- */
		
		$query		= (is_null($sql))
							? "SELECT * FROM $wpdb->links"
							: $sql;

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Query ['.$query.']');
		

		/* -- Ordering parameters -- */
		//Parameters that are going to be used to order the result
		$orderby	= !empty($_GET["orderby"]) 
							? sanitize_text_field($_GET["orderby"]) 
							: 'ASC';
		$order		= !empty($_GET["order"]) 
							? sanitize_text_field($_GET["order"]) 
							: '';
		if (!empty($orderby) & !empty($order))
			{
			$query.=' ORDER BY '.$orderby.' '.$order;
			}

		/* -- Pagination parameters -- */
		//Number of elements in your table?
		$totalitems	= $wpdb->query($query); //return the total number of affected rows
		//How many to display per page?
		$perpage	= 100;
		//Which page is this?
		$paged		= !empty($_GET["paged"]) 
							? sanitize_text_field($_GET["paged"]) 
							: '';
		//Page Number
		if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
		//How many pages do we have in total?
		$totalpages	= ceil($totalitems/$perpage);
		//adjust the query to take pagination into account
		if(!empty($paged) && !empty($perpage)){
			$offset	= ($paged-1) * $perpage;
			$query	.=	' LIMIT '.(int)$offset.','.(int)$perpage;
		}

		/* -- Register the pagination -- */
		$this->set_pagination_args( array(
				"total_items" => $totalitems,
				"total_pages" => $totalpages,
				"per_page" => $perpage,
			) );

		//The pagination links are automatically built according to those parameters

		/* -- Register the Columns -- */
//		$columns		= $this->get_columns();
//		$_wp_column_headers[$screen->id]=$columns;

		$columns				= $this->get_columns();
		$hidden					= array();
		$sortable				= $this->get_sortable_columns();
		$this->_column_headers	= array($columns, $hidden, $sortable);
 


		/* -- Fetch the items -- */
		$this->items	= $wpdb->get_results($query);
		
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Found ['.count($this->items).' rows]');

	}

	/**
	 * Display the rows of records in the table
	 * @return string, echo the markup of the rows
	 */
	function display_rows() {

		//Get the records registered in the prepare_items method
		$records = $this->items;

		//Get the columns registered in the get_columns and get_sortable_columns methods
		list( $columns, $hidden ) = $this->get_column_info();

		$row_num	= 0;

		//Loop for each record
		if(!empty($records))
			{
			foreach($records as $rec){

				rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Row ['.$row_num++.']');

				//Open the line
				echo '<tr id="record_'.$rec->ID.'">';
				foreach ( $columns as $column_name => $column_display_name ) {

					rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '   Column ['.$column_name.'] colun_display_name ['.$column_display_name.']');

					//Style attributes for each col
					$class		= ($column_name == 'col_post_desc')
										? "class='$column_name column-$column_name rover-nowrap'"
										: "class='$column_name column-$column_name'";
					$style		= "";
					if ( in_array( $column_name, $hidden ) ) 
						$style	= ' style="display:none;"';

					$attributes	= $class . $style;

					//edit link
					$editlink	= admin_url('/post.php?action=edit&post='.(int)$rec->ID);

					if ($this->cpt == ROVER_IDX_CUSTOM_POST_DYNAMIC_META)
						{
						switch ( $column_name ) 
							{
							case "col_post_id":  
								echo '<td '.$class . $style.' style="width:30px;">'.stripslashes($rec->ID).'</td>';
								break;
							case "col_post_url": 
								echo '<td '.$class . $style.'><a href="'.$editlink.'">'.stripslashes($rec->post_title).'</a></td>';
								break;
							case "col_post_title": 
								$val = get_post_meta( $rec->ID, 'rover_idx_page_title', true );
								echo '<td '.$class . $style.'>'.stripslashes($val).'</td>';
								break;
							case "col_post_desc": 
								$val = get_post_meta( $rec->ID, 'rover_idx_desc', true );
								echo '<td '.$class . $style.'>'.stripslashes($val).'</td>';
								break;
							}
						}
					else if ($this->cpt == ROVER_IDX_CUSTOM_POST_DYNAMIC_SIDEBAR)
						{
						switch ( $column_name ) 
							{
							case "col_post_id":  
								echo '<td '.$class . $style.' style="width:30px;"><a href="'.$editlink.'">'.stripslashes($rec->ID).'</a></td>';
								break;
							case "col_post_url": 
								echo '<td '.$class . $style.'></td>';
								break;
							case "col_post_title": 
								echo '<td '.$class . $style.'><a href="'.$editlink.'">'.stripslashes($rec->post_title).'</a></td>';
								break;
							case "col_post_desc": 
								$val = get_post_meta( $rec->ID, 'rover_idx_side_desc', true );
								echo '<td '.$class . $style.'>'.stripslashes($val).'</td>';
								break;
							}
						}
					}

				//Close the line
				echo'</tr>';
				}
			}
	}

}

?>