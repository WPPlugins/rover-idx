<?php

class Rover_IDX_Dynamic_Meta
	{

	public				$dyn_meta			= null;
	public				$body_class			= null;
	public				$title_tag			= null;
	public				$meta_desc			= null;
	public				$meta_robots		= null;
	public				$meta_keywords		= null;
	public				$canonical_url		= null;
	public				$sidebar_id			= null;

	function __construct() {

		global			$wpdb, $wp;

//		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'request  ['.$wp->request.']');
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'PHP_SELF ['.$_SERVER['PHP_SELF'].']');
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'REQUEST_URI ['.$_SERVER['REQUEST_URI'].']');
		
//		$the_uri		= $wp->request;
//		$the_uri		= explode('?', $_SERVER['REQUEST_URI']);
//		$the_uri		= $the_uri[0];
		$uri			= $_SERVER['REQUEST_URI'];
		$path_url		= parse_url($uri, PHP_URL_PATH);

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '['."SELECT ID FROM $wpdb->posts 
											WHERE post_type = '".ROVER_IDX_CUSTOM_POST_DYNAMIC_META."' 
											AND post_status = 'publish'
											AND post_name = '".preg_replace('/[^a-zA-Z0-9]/', '', $path_url)."'".']');

		$this->dyn_meta	= $wpdb->get_row("SELECT ID FROM $wpdb->posts 
											WHERE post_type = '".ROVER_IDX_CUSTOM_POST_DYNAMIC_META."' 
											AND post_status = 'publish'
											AND post_name = '".preg_replace('/[^a-zA-Z0-9]/', '', $path_url)."'");

		if (is_null($this->dyn_meta))
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '0 rows found');	
			}	
		else
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, count($this->dyn_meta).' rows found');
			
			$meta							= get_post_meta($this->dyn_meta->ID);

			$this->body_class				= esc_attr( $meta['rover_idx_body_class'][0] );
			$this->title_tag				= esc_html( $meta['rover_idx_page_title'][0] );
			$this->meta_desc				= esc_attr( $meta['rover_idx_meta_desc'][0] );
			$this->meta_robots				= esc_attr( $meta['rover_idx_meta_robots'][0] );
			$this->meta_keywords			= esc_attr( $meta['rover_idx_meta_keywords'][0] );
			$this->canonical_url			= esc_url( $meta['rover_idx_canonical_url'][0] );
			$this->sidebar_id				= esc_attr( $meta['rover_idx_dyn_sidebar'][0] );

			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'body_class    ['.$this->body_class.']');
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'title_tag   	 ['.$this->title_tag.']');
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'meta_desc   	 ['.$this->meta_desc.']');
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'meta_robots 	 ['.$this->meta_robots.']');
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'meta_keywords ['.$this->meta_keywords.']');
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'canonical_url ['.$this->canonical_url.']');
			}
		}

	public function get_sidebar()	{

		global			$wpdb, $wp;

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'fetching post id ['.$this->sidebar_id.']');

		if (!empty($this->sidebar_id) && is_numeric($this->sidebar_id))
			{
			$sidebar_html					= $wpdb->get_var("SELECT post_content FROM $wpdb->posts 
															WHERE ID = '".$this->sidebar_id."'
															AND post_type = '".ROVER_IDX_CUSTOM_POST_DYNAMIC_SIDEBAR."' 
															AND post_status = 'publish'");

			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'sidebar_html ['.$sidebar_html.']');

			return $sidebar_html;
			}

		return null;
		}


	}

global $rover_idx_dynamic_meta;
$rover_idx_dynamic_meta	= new Rover_IDX_Dynamic_Meta();
?>