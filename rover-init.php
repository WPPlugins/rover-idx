<?php

require_once ROVER_IDX_PLUGIN_PATH.'rover-shared.php';
require_once ROVER_IDX_PLUGIN_PATH.'rover-common.php';
require_once ROVER_IDX_PLUGIN_PATH.'rover-custom-post-types.php';


class Rover_IDX
	{
	public $roveridx_regions		= null;
	public $roveridx_theming		= null;
	public $all_selected_regions	= null;
	public $page_slugs 				= null;
	public $page_primary_slug		= null;
	public $post_id					= null;
	public $ping_status				= 'open';

	function __construct() {

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, __CLASS__);

		add_action( 'wp_enqueue_scripts', 				'roveridx_css_and_js', 99 );		//	load late
//		add_action( 'wp_print_footer_scripts', 			'roveridx_css_and_js_footer' );

		add_action(	'wp_footer',						array($this,	'roveridx_add_login'));
		add_filter( 'wp_nav_menu_items',				array($this,	'roveridx_add_login_to_menu'), 10, 2 );

		add_action(	'do_robots', 						array($this,	'rover_robots'), 100, 0);

		add_action( 'roveridx_cron_hourly',				array($this,	'roveridx_hourly'));
		add_action( 'roveridx_cron_daily',				array($this,	'roveridx_daily'));

		error_log( sprintf( '%1$s <strong>%2$s</strong> %3$s: %4$s\n', basename(__FILE__), 'add_action: roveridx_cron_daily', __LINE__, 'adding action (done)') );

//		add_filter( 'language_attributes',				array($this, 'roveridx_fbml_add_namespace'));
//		add_filter( 'opengraph_type', 					array($this, 'roveridx_fb_og_type' ));

		if ( !defined( 'WP_INSTALLING' ) || WP_INSTALLING === false )
			{
			if ( is_admin() )
				{
				add_action( 'plugins_loaded',			array($this, 'roveridx_init_admin'), 15 );
				}
			else
				{
				add_action( 'plugins_loaded',			array($this, 'roveridx_init_front'));
				}

			add_action( 'wp_loaded',					array($this, 'upgrade_options'));

			$this->roveridx_regions						= @get_option(ROVER_OPTIONS_REGIONS);
			$this->roveridx_theming						= @get_option(ROVER_OPTIONS_THEMING);
			$this->all_selected_regions					= rover_get_selected_regions();
			$this->page_slugs							= self::get_all_region_slugs();
			}
		else
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'WP_INSTALLING is true - skipping roveridx_init_admin() and roveridx_init_front()' );
			}
		}

	public function get_all_region_slugs()	{

		$allSlugs										= array();
		foreach ($this->all_selected_regions as $oneRegion)
			{
			$the_slug									= $this->roveridx_regions['slug'.$oneRegion];

			foreach (explode(',', $the_slug) as $one_slug)	//	Allow one region to span multiple states (UPSTATEMLS)
				{
				$allSlugs[] = $one_slug;
				}
			}

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'pageSlugs = "'.implode(',', $allSlugs).'"');

		return $allSlugs;
		}

	public function upgrade_options()	{

		global								$rover_idx;

		if (!isset($rover_idx->roveridx_regions['domain_id']) || empty($rover_idx->roveridx_regions['domain_id']))
			return false;					/*	not yet setup	*/

		$site_version						= (isset($rover_idx->roveridx_theming['site_version']) && !empty($rover_idx->roveridx_theming['site_version']))
													? $rover_idx->roveridx_theming['site_version']
													: null;

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, ' site_version ['.ROVER_VERSION.'] / ['.$site_version.']');

		if (is_null($site_version) || (version_compare(ROVER_VERSION, $site_version) === 1))
			{
			$theme_opts						= @get_option(ROVER_OPTIONS_THEMING);

			if (ROVER_VERSION == "2.0.0")
				{
				//	We no longer need Bootstrap
				$theme_opts['css_framework']= 'rover';

				require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

				global $rover_idx_content;

				$rover_idx_content->update_site_settings(array('css_framework'	=> 'rover'));
				}

			$theme_opts['site_version']		= ROVER_VERSION;

			update_option(ROVER_OPTIONS_THEMING, $theme_opts);

			$rover_idx->roveridx_theming	= $theme_opts;

			add_action('admin_notices',	array($this, 'roveridx_admin_notice_upgraded'));
			}
		}

	public function roveridx_admin_notice_upgraded()	{

		$class 			= 'notice notice-info rover-notice is-dismissible';
		$message		= 'Rover IDX has been upgraded to '.ROVER_VERSION.'.';

		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
	    }

	public function roveridx_add_login()	{

		global			$rover_idx;

		if (!empty($rover_idx->roveridx_theming['login_button']) && $rover_idx->roveridx_theming['login_button'] != 'none')
			{
			if ($rover_idx->roveridx_theming['login_button'] == 'link')
				{
				echo do_shortcode("[rover_idx_login hide_login_in_footer=true show_login_as_text=true]");
				return;
				}
			else if ($rover_idx->roveridx_theming['login_button'] == 'banner')
				{
				echo $this->roveridx_add_login_dropdown_banner();
				return;
				}
			else if ($rover_idx->roveridx_theming['login_button'] == 'button')
				{
				echo $this->roveridx_add_login_dropdown_button();
				return;
				}
			}

		echo $this->roveridx_do_not_add_login();
		}

	public function roveridx_add_login_dropdown_banner()	{

		$the_html		= array();
		$the_html[]		= '<div id="roverContent" class="rover-framework rover-login-framework rover-login-move rover" data-reg_context="rover-login-framework rover-login-move">';
		$the_html[]		=	'<div id="headerTopLine" class="show_just_this_topline">';
		$the_html[]		=		$this->roveridx_login_dropdown();
		$the_html[]		=		$this->roveridx_saved_search_dropdown();
		$the_html[]		=		$this->roveridx_favorites_dropdown();
		$the_html[]		=		$this->roveridx_msg();
		$the_html[]		=		'<div style="clear:both;"></div>';
		$the_html[]		=	'</div>';
		$the_html[]		=	'<script type="text/javascript">/*<![CDATA[*//*---->*/(function( $ ){$l= $( ".rover-login-move" );if ($l.length){$( "body" ).prepend( $l );$l.show();}})( jQuery );/*--*//*]]>*/</script>';
		$the_html[]		= '</div>';

		$the_html[]		= $this->roveridx_add_authdata();

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'html = '.strlen(implode(',', $the_html)).' bytes');

		return implode('', $the_html);
		}

	public function roveridx_add_login_dropdown_button()	{

		$the_html		= array();
		$the_html[]		= '<div id="roverContent" class="rover-framework rover-login-framework rover-login-move rover" data-reg_context="rover-login-framework rover-login-move">';
		$the_html[]		=	'<div id="headerTopLine" class="show_just_this_topline">';
		$the_html[]		=		$this->roveridx_login_dropdown();
		$the_html[]		=		'<div style="clear:both;"></div>';
		$the_html[]		=	'</div>';
		$the_html[]		=	'<script type="text/javascript">/*<![CDATA[*//*---->*/(function( $ ){$l= $( ".rover-login-move" );if ($l.length){$( "body" ).prepend( $l );$l.show();}})( jQuery );/*--*//*]]>*/</script>';
		$the_html[]		= '</div>';

		$the_html[]		= $this->roveridx_add_authdata();

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'html = '.strlen(implode(',', $the_html)).' bytes');

		return implode('', $the_html);
		}

	private function roveridx_do_not_add_login() {

		$the_html		= array();
		$the_html[]		= $this->roveridx_add_authdata();

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'html = '.strlen(implode(',', $the_html)).' bytes');

		return implode('', $the_html);
		}

	public function roveridx_add_login_to_menu( $items, $args ) {

		global			$rover_idx;

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '');

//echo '<h4>'.__FUNCTION__.'</h4>';
//echo '<pre>';
//echo print_r($args);
//echo '</pre>';
		$add_it			= false;
		if (isset($rover_idx->roveridx_theming['login_button']))
			{
			foreach (explode(',', $rover_idx->roveridx_theming['login_button']) as $one_menu)
				{
//				rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Compare ['.$args->theme_location.'] with one_menu ['.$one_menu.']');
				rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Compare ['.$args->menu->slug.'] with one_menu ['.$one_menu.']');

//				if (($one_menu == $args->theme_location) || (str_replace('menu-', '', $one_menu) == $args->theme_location))
//				if (stripos($one_menu, $args->theme_location) !== false)
				if ((stripos($one_menu, $args->theme_location) !== false) || (stripos($args->menu->slug, $one_menu) !== false))
					{
					rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Adding Rover Login / Register menu');
					$add_it	= true;
					break;
					}
				}
			}

		if ($add_it)
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'adding <li>');

			$items		.= '<li class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-type-rover-login">';
			$items		.=	'<a href="#" class="rover-login-label">Login/Register</a>';
			$items		.= 	'<ul class="sub-menu rover-framework rover-login-framework">';
			$items		.= 		'<li class="showIfNotLoggedIn"><a href="#" onclick="roverLogin();return false;">Login</a></li>';
			$items		.= 		'<li class="showIfNotLoggedIn"><a href="#" onclick="roverRegister();return false;">Register</a></li>';
			$items		.= 		'<li class="showIfClient"><a href="\rover-control-panel">Settings</a></li>';
			$items		.= 		'<li class="showIfClient"><a href="\rover-control-panel/my-favorites/">My Favorites</a></li>';
			$items		.= 		'<li class="showIfClient"><a href="\rover-control-panel/my-saved-searches/">My Saved Searches</a></li>';
			$items		.= 		'<li class="showIfClient"><a href="#" onclick="roverLogout();return false;">Logout</a></li>';
			$items		.= 	'</ul>';
			$items		.= '</li>';
			}
		
		$items			.= $this->roveridx_add_authdata();

	    return $items;
		}

	private function roveridx_login_dropdown()	{

		$the_html[]		=		'<div class="dropdown rover_login_dropdown floatRight">';
		$the_html[]		=			'<a href="#" id="rover-login" class="rover-button-dropdown rover-background rover-button" rel="nofollow" style="">';
		$the_html[]		=				'<span class="rover-button-dropdown-label rover-login-label rover-nowrap">Login/Register</span> ';
		$the_html[]		=				'<span class="fa fa-caret-down">&nbsp;</span>';
		$the_html[]		=			'</a>';
		$the_html[]		=			'<ul class="rover-dropdown-ul right" style="display:none;">';
		$the_html[]		=				'<li class="showIfNotLoggedIn"><a href="#" onclick="roverLogin();return false;">Login</a></li>';
		$the_html[]		=				'<li class="showIfNotLoggedIn"><a href="#" onclick="roverRegister();return false;">Register</a></li>';

		$the_html[]		=				'<li class="showIfClient"><a href="/rover-control-panel">Settings</a></li>';
		$the_html[]		=				'<li class="showIfClient"><a href="/rover-control-panel/my-favorites/">My Favorites</a></li>';
		$the_html[]		=				'<li class="showIfClient"><a href="/rover-control-panel/my-saved-searches/">My Saved Searches</a></li>';

		$the_html[]		=				'<li class="showIfAgent"><a href="/rover-rental-panel/" rel="nofollow">Rental Panel</a></li>';
		$the_html[]		=				'<li class="showIfAgent"><a href="/rover-report-panel/" rel="nofollow">Report Panel</a></li>';
		$the_html[]		=				'<li class="showIfAgent"><a href="/rover-market-conditions/" rel="nofollow">Market Conditions</a></li>';

		$the_html[]		=				'<li class="showIfClient"><a href="#" onclick="roverLogout();return false;">Logout</a></li>';
		$the_html[]		=			'</ul>';
		$the_html[]		=		'</div>';
		
		return implode('', $the_html);
		}
	
	private function roveridx_msg()	{

		$the_html[]		=		'<p class="rover-msg">';
		$the_html[]		=			'<span class="rover-msg-icon" style="display: none;">';
		$the_html[]		=				'<i class="fa fa-spinner fa-pulse fa-spin"></i>';
		$the_html[]		=			'</span>';
		$the_html[]		=			'<span class="rover-msg-text" style="display: inline;"></span>';
		$the_html[]		=		'</p>';

		return implode('', $the_html);
		}

	public function roveridx_saved_search_dropdown()	{

		$the_html[]		=		'<div class="dropdown rover_saved_search_dropdown rover_saved_search_count floatRight">';
		$the_html[]		=			'<a href="#" id="rover-login" class="rover-button-dropdown rover-background rover-button" rel="nofollow" style="">';
		$the_html[]		=				'<span class="rover-button-dropdown-label rover-nowrap">Saved Searches (0)</span> ';
		$the_html[]		=				'<span class="fa fa-caret-down">&nbsp;</span>';
		$the_html[]		=			'</a>';
		$the_html[]		=			'<ul class="rover-dropdown-ul right" style="display:none;">';
		$the_html[]		=			'</ul>';
		$the_html[]		=		'</div>';
		
		return implode('', $the_html);
		}

	public function roveridx_favorites_dropdown()	{

		$the_html[]		=		'<div class="dropdown rover_saved_search_dropdown rover_favorite_count floatRight">';
		$the_html[]		=			'<a href="#" id="rover-login" class="rover-button-dropdown rover-background rover-button" rel="nofollow" style="">';
		$the_html[]		=				'<span class="rover-button-dropdown-label rover-nowrap">Favorites (0)</span> ';
		$the_html[]		=				'<span class="fa fa-caret-down">&nbsp;</span>';
		$the_html[]		=			'</a>';
		$the_html[]		=			'<ul class="rover-dropdown-ul right" style="display:none;">';
		$the_html[]		=			'</ul>';
		$the_html[]		=		'</div>';
		
		return implode('', $the_html);
		}

	public function roveridx_add_authdata() {

		global			$rover_idx;

		$the_html		= array();
		$the_html[]		= '<div class="rover-default-auth" ';
		$the_html[]		=	'data-all_regions="'.implode(',', $rover_idx->all_selected_regions).'" ';
		$the_html[]		=	'data-css_framework="'.$rover_idx->roveridx_theming['css_framework'].'" ';
		$the_html[]		=	'data-domain="'.rover_clean_domain(get_site_url()).'" ';
		$the_html[]		=	'data-domain_id="'.$rover_idx->roveridx_regions['domain_id'].'" ';
		$the_html[]		=	'data-fav_requires_login="open" ';
		$the_html[]		=	'data-is_multi_region="'.((count($rover_idx->all_selected_regions) > 1) ? 'true' : 'false').'" ';
		$the_html[]		=	'data-js_min="true" ';
		$the_html[]		=	'data-is_logged_in="false" ';
		$the_html[]		=	'data-load_js_on_demand="" ';
		$the_html[]		=	'data-logged_in_email="" ';
		$the_html[]		=	'data-logged_in_user_id="" ';
		$the_html[]		=	'data-logged_in_authkey="" ';
		$the_html[]		=	'data-logged_in_user_is_agent="false" ';
		$the_html[]		=	'data-logged_in_user_is_rental_agent="false" ';
		$the_html[]		=	'data-logged_in_user_is_broker="false" ';
		$the_html[]		=	'data-logged_in_user_is_admin="false" ';
		$the_html[]		=	'data-page_url="/" ';
		$the_html[]		=	'data-pdf_requires_login="open" ';
		$the_html[]		=	'data-prop_anon_views_curr="0" ';
		$the_html[]		=	'data-prop_details_as_dialog="false" ';
		$the_html[]		=	'data-prop_requires_login="open" ';
		$the_html[]		=	'data-region="'.$rover_idx->all_selected_regions[0].'" ';
		$the_html[]		=	'data-register_before_or_after_prop_display="after" ';
		$the_html[]		=	'data-items="25">';
		$the_html[]		= '</div>';

		return implode('', $the_html);
		}

	public function roveridx_fbml_add_namespace( $output ) {

		//	Does not W3C validate
	
		$output .= ' xmlns:fb="' . esc_attr(ROVERIDX_FBML_NS_URI) . '"';

		return $output;
		}

	public function roveridx_fb_og_type( $type ) {
	    if (is_singular())
	        $type = "article";
	    else 
			$type = "blog";
	    return $type;
	    }

	public function roveridx_init_admin()
		{
		require_once ROVER_IDX_PLUGIN_PATH.'admin/rover-admin-init.php';
		}

	public function roveridx_init_front() 
		{
		global						$wp, $post;

		require_once ROVER_IDX_PLUGIN_PATH.'widgets/init.php';
		
		$http_accept				= strtolower($_SERVER['HTTP_ACCEPT']);
		$is_valid_request			= (strpos($http_accept, "text/html") === false && strpos($http_accept, "*/*") === false)
											? false
											: true;

		if ($is_valid_request)
			require_once ROVER_IDX_PLUGIN_PATH.'rover-shortcodes.php';
		else
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Not loading shortcodes. ['.$http_accept.'] is not a valid request.');

	    if (!session_id()) {
	        @session_start();
		    }

		$curr_path					= parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

		//	We don't seem to have access to $post->ID this early.  So we have to test if the page
		//	exists manually.  If it does exist, DO NOT EXECUTE the 404 code.

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Starting...');
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'REQUEST_URI ['.$_SERVER["REQUEST_URI"].']');
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'parsed REQUEST_URI ['.$curr_path.']');
		
		if (is_admin())
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'true - do not do_rover_404');
			}

		if (is_category())
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'true - do not do_rover_404');
			}

		if (($the_page				= get_page_by_path($curr_path, OBJECT)) !== null)
			{
			//	This page may contain one or more shortcodes

			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'path ['.$curr_path.'] exists in WP as page ['.$the_page->ID.']');
			}
		else
			{
			if ($is_valid_request)
				{
				require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

				global $rover_idx_content;

				add_filter('do_parse_request', function($do_parse, $wp) {			//	Skip parse_request(), which may send an early 404 header

					global $rover_idx_content;

					$found_slug					= $rover_idx_content->check_url_for_rover_keys();

					if ($found_slug !== false)
						{
						//	https://roots.io/routing-wp-requests/

//						$wp->query_vars			= ['post_type' => 'page'];
						$wp->query_vars			= array('post_type' => 'page');

						return false;
						}

					return true;

					}, 10, 2);

				$wp_query->query_vars["error"]	= "";								//	Make sure this is not set to 404 until after we've checked for dynamic


				rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'path ['.$curr_path.'] does not exist in WP');

				$rover_idx_content->rover_setup_404();
				}
			else
				{
				rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'This REQUEST is looking for an image - ignore!');
				}
			}

		//	Cron jobs

		if ( !wp_next_scheduled('roveridx_cron_daily') ) {
			wp_schedule_event( time(), 'daily', 'roveridx_cron_daily' );
			}

		if ( !wp_next_scheduled('roveridx_cron_hourly') ) {
			wp_schedule_event( time(), 'hourly', 'roveridx_cron_hourly' );
			}
		}

	public function roveridx_hourly() {
	
		error_log( 
				sprintf( '%1$s <strong>%2$s</strong> %3$s: %4$s\n', 
						basename(__FILE__),
						__FUNCTION__, 
						__LINE__,
						'HOURLY')
				);

		require_once ROVER_IDX_PLUGIN_PATH.'rover-social/rover-social-common.php';

		roveridx_refresh_social();
		}
	
	public function roveridx_daily() {
	
		error_log( 
				sprintf( '%1$s <strong>%2$s</strong> %3$s: %4$s\n', 
						basename(__FILE__),
						__FUNCTION__, 
						__LINE__,
						'DAILY')
				);
	
		require_once ROVER_IDX_PLUGIN_PATH.'rover-sitemap.php';
	
		roveridx_refresh_sitemap();
		}

	public function rover_robots() {
//		header( 'Content-Type: text/plain; charset=utf-8' );
		
		global						$rover_idx;
		$sitemap_opts				= get_option(ROVER_OPTIONS_SEO);

		if ($sitemap_opts === false)
			return;

		if (!is_array($sitemap_opts))
			return;

		do_action( 'do_robotstxt' );
		
		$output						= null;
		$public						= get_option( 'blog_public' );
		if ( '0' != $public ) {
		
			foreach ($rover_idx->all_selected_regions as $one_region)
				{		
				if (array_key_exists($one_region, $sitemap_opts))
					{
					$output .= "Sitemap: ".$sitemap_opts[$one_region]['url']."\n";
					}
				}

			}
		
		echo apply_filters('robots_txt', $output, $public);
		}

	}



global			$rover_idx;

if (!is_object($rover_idx))
	{
	$rover_idx	= new Rover_IDX();
	}


?>