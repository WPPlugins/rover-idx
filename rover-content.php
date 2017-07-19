<?php

class Rover_IDX_Content
	{
	public	$rover_html							= null;

	private $rover_body_class					= null;
	private $rover_title						= null;
	private $rover_meta_desc					= null;
	private	$rover_og_images					= null;
	private $rover_meta_robots					= null;
	private $rover_meta_keywords				= null;
	private	$rover_canonical_url				= null;
	private $rover_component					= null;
	private	$rover_redirect						= null;
	private	$rover_404_regions					= null;

	private $the_slug							= null;

	private	$dynamic_sidebar					= null;

    public static $fetching_api_key				= false;

	function __construct() {

		add_action( 'update_option_permalink_structure' , array($this, 'permalinks_have_been_updated'), 10, 2 );

		}

	public function rover_setup_404()
		{
		add_filter('the_posts',	array($this, 'rover_dynamic_page'));

		$this->rover_idx_setup_dynamic_meta(null);
		}


	public function rover_dynamic_page($posts)	{

		global									$wp, $wp_query, $rover_idx;

		remove_filter('the_posts',	array($this, 'rover_dynamic_page'));	//	Avoid firing twice

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Starting');

		$found_slug								= $this->check_url_for_rover_keys();

		if ($found_slug !== false)
			{
			$this->the_slug						= $found_slug;
			$the_regions						= (is_null($this->rover_404_regions))
														? $this->map_slug_to_region()
														: $this->rover_404_regions;

			$component							= (is_string($found_slug) && strpos($found_slug, 'rover-') !== false)
														? $found_slug
														: 'ROVER_COMPONENT_404';
			define('ROVER_404_REGION', $the_regions);

			$the_rover_content					= $this->rover_content(	$component, array('region' => $the_regions));

			$this->rover_html					= $the_rover_content['the_html'];
			$this->rover_component				= $the_rover_content['the_component'];
			$this->rover_redirect				= $the_rover_content['the_redirect'];

			$this->rover_idx_setup_dynamic_meta($the_rover_content);

			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, strlen($this->rover_html).' bytes received from rover_content');

			if (empty($this->rover_html)) 
				{
				$this->redirect_if_necessary();

				status_header( 404 );
				$wp_query->is_404				= true;

				//	This is a real 404 - let WP do it's thing
				rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Not a Rover Special Page');
				}
			else
				{
				rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'This is a RoverSpecialPage');

				$posts							= array();
				$posts[]						= $this->create_rover_content();

				//	Trick wp_query into thinking this is a page (necessary for wp_title() at least)
				//	Not sure if it's cheating or not to modify global variables in a filter 
				//	but it appears to work and the codex doesn't directly say not to.

				$wp_query->post					= $posts[0]->ID;
				
				$wp_query->is_rover_page		= true;		//	Used for domain '8'
//				$wp_query->found_posts			= 1;
//				$wp_query->post_count			= 1;
//				$wp_query->max_num_pages		= 1;

				add_action('template_include', array($this, 'rover_template_include'), 99);

				//	We want this to be a page - more flexible for setting templates dynamically

				$wp_query->is_page				= true;

				$wp_query->is_single			= false;	//	Applicable to Posts
				$wp_query->is_singular			= true;		//	Applicable to Pages
				$wp_query->is_home				= false;
				$wp_query->is_archive			= false;
				$wp_query->is_attachment		= false;
				$wp_query->is_category			= false;
				//Longer permalink structures may not match the fake post slug and cause a 404 error so we catch the error here
				unset($wp_query->query["error"]);
				$wp_query->query_vars["error"]	= "";
				$wp_query->is_404				= false;
				}
			}
		else 
			{
			//	This is a real 404 - let WP do it's thing
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'No matching slugs - this is not a Rover Special Page');
			}

		return $posts;
		}

	private function create_rover_content()
		{
		global $wpdb, $rover_idx;

		//	If Rover is creating this content, tell WP to skip the annoying 'wpautop'  
		//	function, which loves to wrap double line-breaks in <p> tags

		remove_filter( 'the_content', 'wpautop' ); 
		remove_filter( 'the_excerpt', 'wpautop' );  
//		add_filter('the_title', array($this, 'strip_title'), 10, 2); 

		$post = new stdClass;

		$post->post_author		= get_current_user_id();

		//	The safe name for the post.  This is the post slug.

		$post->post_name		= (string) $this->the_slug;
		$post->post_type		= 'page';

		//	Not sure if this is even important.  But gonna fill it in anyway.

		$post->guid				= get_bloginfo("wpurl") . '/' . $this->the_slug;

		//	The title of the page.

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Calling displayHeaderTitle for '.ROVER_404_REGION);

		if (empty($post->post_title) && !empty($this->rover_title))
			$post->post_title = $this->rover_title;

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Creating Rover Page for '.ROVER_404_REGION.' ('.strlen($this->rover_html).' bytes)');

		$post->post_content		= $this->rover_html;

		//	Fake post ID to prevent WP from trying to show comments for
		//	a post that doesn't really exist.

		$rover_idx->post_id		= get_rover_post_id($rover_idx->roveridx_theming);
		$post->ID				= $rover_idx->post_id;

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'is using post_id '.$post->ID);

		//	Static means a page, not a post.

		$post->post_status		= 'static';

		//	Turning off comments for the post.

		$post->comment_status	= 'closed';

		//	Let people ping the post?  Probably doesn't matter since
		//	comments are turned off, so not sure if WP would even
		//	show the pings.

		$post->ping_status		= 'open';		//	$this->ping_status;

		$post->comment_count	= 0;

		$post->post_date		= current_time('mysql');
		$post->post_date_gmt	= current_time('mysql', 1);

		//	For Rover dynamic pages - let Rover build the meta
		add_filter( 'wpseo_opengraph_title', '__return_false' );
		add_filter( 'wpseo_opengraph_desc', '__return_false' );
		add_filter( 'wpseo_opengraph_url', '__return_false' );
		add_filter( 'wpseo_canonical', '__return_false',  10, 1 );
		
		//	Jetpack
		add_filter( 'jetpack_enable_open_graph', '__return_false' );

		//	Genesis
		remove_action( 'wp_head', 'genesis_robots_meta');
		remove_action( 'wp_head', 'genesis_canonical', 5); 
		remove_action( 'genesis_meta','genesis_robots_meta' );
		remove_action( 'genesis_after_post_content', 'genesis_post_meta' );

		add_filter( 'wpseo_og_article_published_time', '__return_false' );
		add_filter( 'wpseo_og_article_modified_time', '__return_false' );
		add_filter( 'wpseo_og_og_updated_time', '__return_false' );

		$this->roveridx_use_our_og_images();

		remove_action( 'wp_head', 'feed_links_extra', 3 );		// Removes the links to the extra feeds such as category feeds
		remove_action( 'wp_head', 'feed_links', 2 );			// Removes links to the general feeds: Post and Comment Feed
		remove_action( 'wp_head', 'rsd_link');					// Removes the link to the Really Simple Discovery service endpoint, EditURI link
		remove_action( 'wp_head', 'wlwmanifest_link');			// Removes the link to the Windows Live Writer manifest file.
		remove_action( 'wp_head', 'index_rel_link');			// Removes the index link
		remove_action( 'wp_head', 'parent_post_rel_link');		// Removes the prev link
		remove_action( 'wp_head', 'start_post_rel_link');		// Removes the start link
		remove_action( 'wp_head', 'adjacent_posts_rel_link');	// Removes the relational links for the posts adjacent to the current post.
		remove_action( 'wp_head', 'wp_generator');				// Removes the WordPress version i.e. - WordPress 2.8.4

		add_filter('body_class', array($this, 'roveridx_body_class'));

		add_action('wp_head',	array($this, 'roveridx_meta_description'), 1);
		add_action('wp_head',	array($this, 'roveridx_meta_robots'), 5);
		add_action('wp_head',	array($this, 'roveridx_meta_keywords'), 5);
		add_action('wp_head',	array($this, 'roveridx_meta_generator'), 5);
		add_action('wp_head',	array($this, 'roveridx_canonical_url'), 5);

		add_action('wp_head',	array($this, 'roveridx_og_updated_time'), 5);
		add_action('wp_head',	array($this, 'roveridx_og_title'), 5);
		add_action('wp_head',	array($this, 'roveridx_og_description'), 5);

		add_action('wp_head',	array($this, 'roveridx_og_url'), 5);


//		if (in_array($this->the_slug, $this->rover_standard_slugs))		//	We don't want Googlebot to crawl roverControlPanel
//			add_action('wp_head', array($this, 'roveridx_meta_nofollow'));

		return($post);		
		}

	public function roveridx_meta_description() {	
		echo "<meta name='description' content='".$this->rover_meta_desc."' />\n";
		}
	public function roveridx_meta_robots() {	
		if (!empty($this->rover_meta_robots))
			echo "<meta name='robots' content='".$this->rover_meta_robots."' />\n";
		}
	public function roveridx_meta_keywords() {	
		if (!empty($this->rover_meta_keywords))
			echo "<meta name='keywords' content='".$this->rover_meta_keywords."' />\n";
		}
	public function roveridx_meta_generator() {
		echo "<meta name='generator' content='Rover IDX ".roveridx_get_version()."' />\n";
		}
	public function roveridx_canonical_url() {

		if (!empty($this->rover_canonical_url))
			{
			echo "<link rel='canonical' href='".$this->rover_canonical_url."' />\n";
			}
		else
			{
			global							$wp;
			
			$url_ends_with_slash			= true;
			$perm							= get_option('permalink_structure');
			if ($perm && substr($perm, -1) != '/')
				$url_ends_with_slash		= false;

			$url							= ($url_ends_with_slash)
													? trailingslashit($url)
													: $url;

			echo "<link rel='canonical' href='".$url."' />\n";
			}
		}

	public function roveridx_og_updated_time()	{

		//	-0001-11-30T00:00:00+00:00
		
		echo "<meta property='og:updated_time' content='".date('Y-m-dTH:i:s+00:00')."' />\n";

		}

	public function roveridx_og_title() {

		echo "<meta property='og:title' content='".strip_tags( $this->rover_title )."'>\n";

		}

	public function roveridx_og_description() {

		echo "<meta property='og:description' content='".$this->rover_meta_desc."'>\n";

		}

	public function roveridx_og_images() {

		foreach(explode(',', $this->rover_og_images) as $one_img)
			echo "<meta property='og:image' content='".$one_img."'>\n";

		}

	public function roveridx_og_url()	{

		echo "<meta property='og:url' content='".$this->rover_canonical_url."'>\n";

		}

	public function roveridx_use_our_og_images() {

		if (!empty($this->rover_og_images))
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'starting');

			add_filter( 'wpseo_opengraph_image', '__return_false' );
			add_filter( 'jetpack_enable_open_graph', '__return_false' );

			add_action( 'wp_head',	array($this, 'roveridx_og_images'), 10);
			}
		else
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'not using og_images');
			}
		}

	public function roveridx_body_class($classes) {

		if ($this->rover_body_class)
			{
			if (is_array($classes))
				$classes[]	= $this->rover_body_class;
			else
				$classes	= array($this->rover_body_class);
			}

		return $classes;
		}

	private function use_dynamic_sidebar($component)	{
		
		if ($component == 'ROVER_COMPONENT_404')
			{
			global							$rover_idx_dynamic_meta;

			$this->dynamic_sidebar			= $rover_idx_dynamic_meta->get_sidebar();

			if (!is_null($this->dynamic_sidebar))
				return true;
			}

		return false;
		}

	public function update_site_settings($atts)	{

		if (is_array($atts) && count($atts))
			{
//			$the_rover_content				= $this->rover_content(
//																'ROVER_COMPONENT_UPDATE_SITE_SETTINGS', 
//																array_merge(
//																	array(
//																		'not-region'	=> 'Not used', 
//																		'not-regions'	=> 'Not Used'),
//																	$atts
//																	)
//																);
			$the_rover_content				= $this->rover_content(
																'ROVER_COMPONENT_UPDATE_SITE_SETTINGS', 
																$atts
																);
			}

		}

	private function get_api_key()	{

		global								$rover_idx;

        if ( self::$fetching_api_key )
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'already fetching');
			return null;
			}

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'starting');

		//	Delete this section after the few sites are upgraded and executed

		if (empty($rover_idx->roveridx_regions['api_key']) && !empty($rover_idx->roveridx_regions['salt']))
			{
			$rr['api_key']					= $rover_idx->roveridx_regions['salt'];
			unset($rr['salt']);
	
			$r								= update_option(ROVER_OPTIONS_REGIONS, $rr);
			$rover_idx->roveridx_regions	= @get_option(ROVER_OPTIONS_REGIONS);
			}

		//	if necessary, fetch a new api key

		if (empty($rover_idx->roveridx_regions['api_key']))
			{
			self::$fetching_api_key			= true;

			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Fetching new API key');

			$the_rover_content				= $this->rover_content(
																'ROVER_COMPONENT_GET_API_KEY', 
																array('not-region' => 'Not used', 'not-regions' => 'Not Used')
																);

			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Received new API key ['.$the_rover_content['the_html'].']');

			$api_key						= $the_rover_content['the_html'];

			if (!empty($api_key))
				{
				$rr							= @get_option(ROVER_OPTIONS_REGIONS);
				$rr['api_key']				= $api_key;
				$r							= update_option(ROVER_OPTIONS_REGIONS, $rr);
				if ($r)
					rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Saved new API key to Region options');

				return $api_key;
				}
			}
	
		if (empty($rover_idx->roveridx_regions['api_key']))
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'failed');
			return null;
			}

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Returning ['.$rover_idx->roveridx_regions['api_key'].']');

		return $rover_idx->roveridx_regions['api_key'];
		}

	private function check_js_version($newest_js_ver)	{

		global								$rover_idx;

		$current_js_ver						= (isset($rover_idx->roveridx_theming['js_version']))
													? $rover_idx->roveridx_theming['js_version']
													: ROVER_JS_VERSION;

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, ' latest_js_ver ['.$newest_js_ver.'] / ['.$current_js_ver.']');

		if ((version_compare($newest_js_ver, $current_js_ver) !== 0))
			{
			$theme_opts						= @get_option(ROVER_OPTIONS_THEMING);

			$theme_opts['js_version']		= $newest_js_ver;
			update_option(ROVER_OPTIONS_THEMING, $theme_opts);

			$rover_idx->roveridx_theming	= $theme_opts;
			}
		}

	private function rover_idx_setup_dynamic_meta($the_rover_content = null)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-dynamic-meta.php';

		global								$rover_idx_dynamic_meta;

		if (is_null($this->rover_body_class))
			{
			$this->rover_body_class			= $rover_idx_dynamic_meta->body_class;
			}

		if (is_null($this->rover_title))
			{
			if (!is_null($rover_idx_dynamic_meta) && !empty($rover_idx_dynamic_meta->title_tag))
				$this->rover_title			= $rover_idx_dynamic_meta->title_tag;
			else if (!is_null($the_rover_content) && !empty($the_rover_content['the_title']))
				$this->rover_title			= $the_rover_content['the_title'];
			}

		if (is_null($this->rover_meta_desc))
			{
			if (!is_null($rover_idx_dynamic_meta) && !empty($rover_idx_dynamic_meta->meta_desc))
				$this->rover_meta_desc		= $rover_idx_dynamic_meta->meta_desc;
			else if (!is_null($the_rover_content) && !empty($the_rover_content['the_meta_desc']))
				$this->rover_meta_desc		= $the_rover_content['the_meta_desc'];
			}

		if (is_null($this->rover_og_images))
			{
			$this->rover_og_images			= $the_rover_content['the_og_images'];
			}

		if (is_null($this->rover_meta_robots))
			{
			$this->rover_meta_robots		= $rover_idx_dynamic_meta->meta_robots;
			}

		if (is_null($this->rover_meta_keywords))
			{
			$this->rover_meta_keywords		= $rover_idx_dynamic_meta->meta_keywords;
			}

		if (is_null($this->rover_canonical_url))
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'rover_idx_dynamic_meta->canonical_url ['.$rover_idx_dynamic_meta->canonical_url.'] ');
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'get_site_url() ['.get_site_url().'] ');
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '_SERVER[REQUEST_URI] ['.$_SERVER['REQUEST_URI'].'] ');

			$this->rover_canonical_url		= (empty($rover_idx_dynamic_meta->canonical_url))
													? (get_site_url().$_SERVER['REQUEST_URI'])
													: $rover_idx_dynamic_meta->canonical_url;

			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'rover_idx_dynamic_meta->canonical_url ['.$this->rover_canonical_url.'] ');
			}

		}

	public function check_url_for_rover_keys()	{

		global								$wp, $rover_idx;

		//	Check if the requested page matches our target 

		$the_url_parts						= (empty($wp->request))
													? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
													: $wp->request;

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'url ['.$the_url_parts.'] from '.((empty($wp->request)) ? 'REQUEST_URI' : 'wp->request'));

		$url_parts							= explode('/', $the_url_parts);
		foreach ($url_parts as $url_part)
			{
			//	So we don't serve up dynamic pages for example.com/2015/04/ma (it looks like Google likes 
			//	to crawl these, and just specifying the state takes forever with MIDFLORIDA

//			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Looking at urlpart '.$url_part);
			$url_part						= str_replace('/', '', $url_part);

			$found_slug						= $this->match_slug($rover_idx->page_slugs, $url_part);
			if ($found_slug !== false)
				return $found_slug;

			$found_slug						= $this->match_region_slug($rover_idx->all_selected_regions, $url_part);
			if ($found_slug !== false)
				return $found_slug;

			$found_slug						= $this->match_standard_page_slug($url_part);
			if ($found_slug !== false)
				return $found_slug;

			}

//		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Does not match any slugs ('.implode(', ', $rover_idx->all_slugs).')');

		return false;
		}
	
	private function match_slug($slugs, $url_part)
		{
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'slugs ['.implode(',', $slugs).']');

		foreach ($slugs as $one_slug)
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Comparing '.$url_part.' to '.$one_slug);

			if (strcasecmp($url_part, $one_slug) === 0)
				{
				rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Found slug ('.$one_slug.')');
				return $one_slug;
				}
			}

		return false;
		}

	private function match_region_slug($slugs, $url_part)
		{
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'slugs ['.implode(',', $slugs).']');

		$matched_parts					= array();
		foreach ($slugs as $one_slug)
			{
			foreach(explode(',', $url_part) as $one_segment_of_part)
				{
				rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Comparing '.$url_part.' to '.$one_slug);
	
				if (strcasecmp($one_segment_of_part, $one_slug) === 0)
					{
					rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Found slug ('.$one_slug.')');
					$matched_parts[]	= $one_slug;
					}
				}
			}

		if (count($matched_parts))
			{
			$this->rover_404_regions	= implode(',', $matched_parts);
			return $matched_parts;
			}

		return false;
		}

	private function match_standard_page_slug($url_part)
		{
		if (strlen($url_part) === 0)
			return false;

		$rover_slugs			= array(
										'rentalcode', 'mlnumber','saved-search', 'rover-',
										'listing-agent-mlsid', 'listing-office-mlsid', 
										'agent-detail', 'idx', 'archived-email', 'my-favorite-listings'
										);

		foreach ($rover_slugs as $one_rover_slug)
			{
			if (substr_compare($url_part, $one_rover_slug, 0, min(strlen($one_rover_slug), strlen($url_part))) === 0)
				{
				rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, $url_part.' may be a Rover standard slug');
				return $url_part;
				}
			}

		return false;
		}

	private function map_slug_to_region()	{

		global $rover_idx;

		$found_slug	= $this->match_standard_page_slug($this->the_slug);
		if ($found_slug !== false)
			{
			$this->rover_404_regions	= $rover_idx->all_selected_regions[0];				//	Use 1st region selected

			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Slug "'.$this->the_slug.'" is a rover_standard_slug "'.$this->rover_404_regions.'"');

			return $this->rover_404_regions;
			}

		$found_slug	= $this->match_slug($rover_idx->all_selected_regions, $this->the_slug);
		if ($found_slug !== false)
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Slug "'.$this->the_slug.'" is region "'.$the_regions.'"');

			return $this->the_slug;
			}

		//	Map the slug we hit to the region (CA == STAR, MA == MLSPIN …)

		foreach ($rover_idx->all_selected_regions as $oneRegion)
			{
			if (isset($rover_idx->roveridx_regions[$oneRegion]))
				{
				foreach (explode(',', $rover_idx->roveridx_regions['slug'.$oneRegion]) as $one_slug)
					{
					if ($one_slug == $this->the_slug)
						{
						rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Slug "'.$this->the_slug.'" maps to Region "'.$oneRegion.'"');
						return $oneRegion;
						}
					}
				}
			}

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'No region slugs match "'.$this->the_slug.'"');

		return null;
		}

	public function rover_template_include()
		{
		global						$rover_idx;

		$path_to_template			= null;
		$template_is_set			= false;
		$template_exists			= false;
		$html_fragment				= substr($this->rover_html, 0, 100);

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Component is '.$this->rover_component.' ['.$html_fragment.']');

		$page_template				= @$rover_idx->roveridx_theming['template'];

		if (in_array($this->rover_component, array('rover-control-panel', 'rover-custom-listing-panel')))
			{
			$path_to_template		= ROVER_IDX_PLUGIN_PATH . 'templates/naked_page.php';
			$template_is_set		= true;
			}
		else
			{
			if (strpos($html_fragment, 'rover-prop-detail-framework') !== false)
				$page_template		= $rover_idx->roveridx_theming['property_template'];
			else if (strpos($html_fragment, 'rover-market-conditions-framework') !== false)
				$page_template		= $rover_idx->roveridx_theming['mc_template'];
			else if (strpos($html_fragment, 'rover-report-framework') !== false)
				$page_template		= $rover_idx->roveridx_theming['rep_template'];

			//	Retrieve stylesheet directory Path for the current theme/child theme
			$path_to_template		= get_stylesheet_directory() . '/' . $page_template;
			}


		//	User is printing page - Retrieve stripped template from Rover
		if ((strcmp($page_template, 'rover-naked') === 0) || (is_array($_GET) && array_key_exists('print', $_GET)))
			$path_to_template		= ROVER_IDX_PLUGIN_PATH . 'templates/naked_page.php';

		if (!empty($page_template))
			$template_is_set		= true;

		if (file_exists($path_to_template))
			$template_exists		= true;

		if (!$template_is_set)
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'roveridx_theming is empty - failed setting desired template');

		if (!$template_exists)
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'roveridx_theming ['.$path_to_template.'] does not exist - failed setting desired template');

		if ($template_is_set && $template_exists)
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Setting template to ['.$path_to_template.']');
			return $path_to_template;
			}
		else
			{
			global $wpdb;

			$path_to_template	=  get_page_template();

			if (!file_exists($path_to_template))
				{
				rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Default template ['.$path_to_template.'] not found.  Giving up.');
				return false;
				}

			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Using template ['.$path_to_template.']');

			if (!empty($path_to_template) && file_exists($path_to_template))
				{
				//	We don't want to go down this path every time, just because the website designer 
				//	hasn't selected a 'template' page.  So set it, and let them change it if they ever
				//	get around to it.

				$current_theme_options						= get_option(ROVER_OPTIONS_THEMING);
				$current_theme_options['theme']				= 'unused';

				update_option(ROVER_OPTIONS_THEMING, $current_theme_options );

				$rover_idx->roveridx_theming				= $current_theme_options;

				return $path_to_template;
				}
			else
				{
				if (empty($path_to_template))
					rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Fatal error: [path_to_template] is empty!');
				if (file_exists($path_to_template))
					rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Fatal error: ['.$path_to_template.'] does not exist!');
				}
			}
		}

	private function redirect_if_necessary()
		{
		if ($this->rover_redirect === false)
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'rover_redirect is false ');
		else if ($this->rover_redirect === null)
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'rover_redirect is null ');
		else if (empty($this->rover_redirect))
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'rover_redirect is empty ');
		else
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'rover_redirect is ['.$this->rover_redirect.'] ');

		if ($this->rover_redirect !== false)
			{
			if (empty($this->rover_redirect))
				{
				//	This is a non-active listing page, and a crawler is the requestor.
				//	We can redirect to the Home page, or a 404 page.  Simply doing nothing
				//	will fall through to the 404 page.

				$seo_opts									= @get_option(ROVER_OPTIONS_SEO);

				if ($seo_opts['crawler_redirect'] == "404")
					{
					return false;												//	Redirect to 404 page
					}
				else if ($seo_opts['crawler_redirect'] == "home")
					{
					rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Redirecting to '.get_site_url());
//die('Redirecting to '.get_site_url());
					wp_redirect( get_site_url(), 301 );							//	Redirect to 'Home' page
					exit;
					}
				else 		//	specific
					{
					rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Redirecting to '.$seo_opts['crawler_redirect']);
//die('Redirecting to '.get_site_url());
					wp_redirect( $seo_opts['crawler_redirect'], 301 );			//	Redirect to specific page
					exit;
					}
				}
			else
				{
				rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Redirecting to '.$this->rover_redirect);
//die('Redirecting to '.$this->rover_redirect);

				wp_redirect( $this->rover_redirect, 301 );		//	Redirect to specified page
				exit;
				}
			}

		return false;
		}

	private function permalinks_have_been_updated( $oldvalue, $_newvalue )
		{
		$url_ends_with_slash		= true;
		if ($perm && substr($_newvalue, -1) != '/')
			$url_ends_with_slash	= false;

		$tmp						= get_option(ROVER_OPTIONS_REGIONS);
		$tmp['url_ends_with_slash']	= $url_ends_with_slash;

		update_option(ROVER_OPTIONS_REGIONS, $tmp);
		}

	public function roveridx_meta_nofollow()	{
		echo '<meta name="robots" value="noindex,nofollow" role="roveridx">';
		}

	public function strip_title($title, $id = null) {
		return strip_tags($title);
		}

	private function get_transient_key($component, $path_url)	{

		global							$wpdb;

		$key							= null;

		//	40 chars or less		

		$key							= (is_multisite())
												? sha1(str_replace('ROVER_COMPONENT_', '', $component).'|'.$wpdb->siteid.'|'.$path_url)
												: sha1(str_replace('ROVER_COMPONENT_', '', $component).'|'.$path_url.'|');

		return $key;
		}

	private function get_transient_content($key)	{

		global							$rover_idx;

		if (@$rover_idx->roveridx_theming['enable_transient_cache'] != 'Yes')
			return false;

		if (is_multisite())
			return get_site_transient($key);

		return get_transient($key);
		}

	private function set_transient_content($key, $data, $expiration)	{

		global							$rover_idx;

		$ret							= false;

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'transient caching is ['.@$rover_idx->roveridx_theming['enable_transient_cache'].']');

		if (@$rover_idx->roveridx_theming['enable_transient_cache'] != 'Yes')
			return false;

		$ret							= (is_multisite())
												? set_site_transient($key, $data, $expiration)
												: set_transient($key, $data, $expiration);

		if ($ret === true)
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'transient data was successfully saved for ['.$key.']');
		else
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'transient data was not saved for ['.$key.']');

		return $ret;
		}

	private function del_transient_content($key, $data)	{

		if (is_multisite())
			return delete_site_transient($key);

		return delete_transient($key);
		}

	private function translate_component($component)	{

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, ' ['.$component.']');

		if ($component == 'ROVER_COMPONENT_404')
			{
			//	For certain types of pages, skip the 404 engine

			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, ' Comparing ['.$this->the_slug.'] with [agent-detail]');
			if ($this->the_slug == 'agent-detail')
				{
				rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, ' ['.$this->the_slug.'] returning [ROVER_COMPONENT_AGENT_DETAIL_PAGE]');
				return 'ROVER_COMPONENT_AGENT_DETAIL_PAGE';
				}
			}

		return $component;
		}

	private function has_quotes($att_val)	{

		$all_quotes								= array('"', "“", "”", "‘", "’", "&#8221;", "&#8243;");

		foreach($all_quotes as $one_quote)
			{
			if (strpos($att_val, $one_quote) !== false)
				return true;
			}

		return false;
		}

	private function clean_curly_quotes($atts)	{

		$new_atts								= array();
		$correcting_key							= null;
		$corrected_vals							= array();
		$all_quotes								= array('"', "“", "”", "‘", "’", "&#8221;", "&#8243;");

		foreach($atts as $att_key => $att_val)
			{
			if (!is_array($att_val))
				{
				$att_val						= urldecode($att_val);
	//			$val_contains_quote				= (!preg_match('#^[“”"‘\']#', $att_val))
	//													? true
	//													: false;
				$val_contains_quote				= $this->has_quotes($att_val);
	
				rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, ' ['.$att_key.'] => ['.$att_val.'] val_contains_quote ['.(($val_contains_quote) ? 'true' : 'false').']');
	
				if (!is_numeric($att_key) && count($corrected_vals) && $correcting_key != $att_key)	//	changed key
					{
					$new_atts[$correcting_key]	= implode(' ', $corrected_vals);
					rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, ' ['.$correcting_key.'] => ['.$new_atts[$correcting_key].']');
					$correcting_key				= null;
					$corrected_vals				= array();
					}
	
				//	[0] => items_per_page=48
				if (is_numeric($att_key) && strpos($att_val, "=") !== false)
					{
					$att_parts					= explode("=", $att_val);
					$new_atts[$att_parts[0]]	= str_replace($all_quotes, "", $att_parts[1]);
					rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, ' ['.$att_parts[0].'] => ['.str_replace(array('"', "“", "”", "‘", "’"), "", $att_parts[1]).']');
					}
				//	[street] => ‘eel
				else if (!is_numeric($att_key) && $val_contains_quote)
					{
					$correcting_key				= $att_key;
					$corrected_vals[]			= str_replace($all_quotes, "", $att_val);
					rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, ' ['.$correcting_key.'] => ['.implode(' ', $corrected_vals).']');
					}
				//	[0] => point"
				else if (is_numeric($att_key))
					{
					$corrected_vals[]			= str_replace($all_quotes, "", $att_val);
	
					if ($val_contains_quote)
						{
						$new_atts[$correcting_key]	= implode(' ', $corrected_vals);
						rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, ' ['.$correcting_key.'] => ['.$new_atts[$correcting_key].']');
						$correcting_key			= null;
						$corrected_vals			= array();
						}
					}
				else
					{
					$new_atts[$att_key]			= $att_val;
					}
				}
			//	normal
			else
				{
				$new_atts[$att_key]				= $att_val;
				}
			}

		return $new_atts;
		}

	public function rover_content($component, $atts = null)	{

		global				$rover_idx, $post;

		$page				= (isset($post)) 
									? $post->ID 
									: get_rover_post_id($rover_idx->roveridx_theming);
		$uri				= $_SERVER['REQUEST_URI'];
		$path_url			= parse_url($uri, PHP_URL_PATH);
		$query_url			= parse_url($uri, PHP_URL_QUERY);
		$api_key			= $this->get_api_key();
		$transient_exp		= 60*60*12;
		$transient_key		= $this->get_transient_key($component, $path_url);

		if (($ret_data		= $this->get_transient_content($transient_key)) === false)
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'transient data for ['.$transient_key.'] was not found');

			$vars_array		= array('is_wp'				=>	true,
									'signature'			=>	'67d14e7729d3a8446ebf5e5e97f684db',
									'domain_id'			=>	$rover_idx->roveridx_regions['domain_id'],
									'domain'			=>	get_site_url(),
									'page'				=>	$page,
									'api_key'			=>	$api_key,
	//								'our-comp'			=>	$component,							//	Does not appear to be used anywhere
									'user_agent'		=>	$_SERVER['HTTP_USER_AGENT'],
									'user_ip'			=>	$_SERVER['REMOTE_ADDR'],
									'server_ip'			=>	$_SERVER['SERVER_ADDR'],
									'wp_path_url'		=>	$path_url,
									'wp_query_url'		=>	$query_url,
									'force_crawler'		=>	intval(@$_GET['crawler']),					//	'?crawler=1'
									'dynamic_sidebar'	=>	$this->use_dynamic_sidebar($component),
									'wp_permalinks'		=>	get_option('permalink_structure'));
	
			if ($this->is_rover_admin_panel($component))
				{
				$vars_array['wp_regions']				=	$rover_idx->roveridx_regions;
				$vars_array['wp_jq_theme']				=	$rover_idx->roveridx_theming['theme'];	//	string
				}
	
			if ( is_user_logged_in() )
				{
				$current_user							=	wp_get_current_user();
				$guid									=	get_user_meta($current_user->ID, 'rover_guid', $single = true);
				if (!empty($guid))
					$vars_array['guid']					=	$guid;
				}

			if (empty($atts))
				{
				$atts									= $vars_array;
				}
			else
				{
				$atts									= $this->clean_curly_quotes($atts);
				$atts									= array_merge($atts, $vars_array);
				}

			//	If no 'region' parameter is specified in shortcode, assume the first 'region' in roveridx_regions
			if (array_key_exists('region', $atts) === false || empty($atts['region']))
				{
	//			$all_selected_regions					= rover_get_selected_regions();
				$atts['region']							= $rover_idx->all_selected_regions[0];
	
				if (array_key_exists('region', $atts))
					rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '[key does not exist] Forcing region to '.$rover_idx->all_selected_regions[0]);
				if (empty($atts['region']))
					rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '[empty] Forcing region to '.$rover_idx->all_selected_regions[0]);
				}
	
			if (array_key_exists(ROVER_DEBUG_KEY, $_GET) === true)
				{
				$atts[ROVER_DEBUG_KEY]					= intval($_GET[ROVER_DEBUG_KEY]);
				}
	
			if (rover_idx_is_debuggable())
				{
				if (is_array($atts))
					{
					foreach ($atts as $atts_key => $atts_val)
						{
						if (is_string($atts_val))
							rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, $atts_key.' => '.$atts_val);
						}
					}
	
		//		$btd = debug_backtrace();
		//		$btd_str = null;
		//		foreach ($btd as $btdKey => $btdVal)
		//			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '['.$btdKey.'] File: '.$btdVal['file'].' / Function: '.$btdVal['function'].' / Line: '.$btdVal['line']);
				}
	
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Loading component using curl');
	
	
			$post_str	= http_build_query($atts);
	
			$url		= ((is_ssl()) ? ROVER_ENGINE_SSL : ROVER_ENGINE)
								. ROVER_VERSION
								. '/php/rover-cross-domain-component.php?component='.$this->translate_component($component);
	
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, ((rover_idx_is_debuggable()) ? $url.'&'.$post_str : $url));
	
			if ($this->test_for_modsec($post_str))
				{
				return array(
							'the_html'	=> '<div style="color:red;margin:40px auto;text-align:center;">This request appears to be an attempt at SQL injection attacks, cross-site scripting, or a path traversal attacks.</div>'
							);
				}

			$ch									= curl_init();
	
			curl_setopt_array( $ch, array(
										CURLOPT_URL				=> $url,
										CURLOPT_RETURNTRANSFER	=> true,
										CURLOPT_CONNECTTIMEOUT	=> 5,
										CURLOPT_TIMEOUT			=> 15,
										CURLOPT_COOKIE			=> rover_cookies(),
										CURLOPT_HTTPHEADER		=> array(
																		'Content-Type: application/x-www-form-urlencoded',
																		'Content-Length: '.strlen($post_str)
																		),
										CURLOPT_POST			=> true,
										CURLOPT_POSTFIELDS		=> $post_str,
										CURLOPT_FAILONERROR		=> true
										) );
	
	
			$ret_data	= curl_exec($ch);
			if ($ret_data === false)
				rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'curl_exec error ['.curl_errno($ch).']: '.curl_error($ch));
	
			curl_close ($ch);
	
			$this->set_transient_content($transient_key, $ret_data, $transient_exp);
			}

		$rover_content							= json_decode($ret_data, true);

		if (is_null($rover_content))
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'json_decode() failed on ['.$ret_data.']');
			}
		else
			{
			$this->rover_og_images				= null;

			$rover_content['the_html']			= str_replace('ROVER_DYNAMIC_SIDEBAR', $this->dynamic_sidebar, $rover_content['the_html']);

			if (isset($rover_content['the_og_images']) && !empty($rover_content['the_og_images']))
				{
				$this->rover_og_images			= $rover_content['the_og_images'];

//				$this->roveridx_use_our_og_images();
				}

			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'the_html is '.strlen($rover_content['the_html']).' bytes');
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'the_og_images are '.strlen($rover_content['the_og_images']).' bytes');

			$this->check_js_version($rover_content['the_js_ver']);
			}

		return $rover_content;
		}

	private function is_rover_admin_panel($component)	{
	
		if (in_array(
					$component, 
					array(
						'ROVER_COMPONENT_SETUP_GENERAL_PANEL',
						'ROVER_COMPONENT_WP_SETUP_PANEL',
						'ROVER_COMPONENT_WP_THEME_PANEL',
						'ROVER_COMPONENT_WP_SEARCH_PANEL',
						'ROVER_COMPONENT_WP_ENGINE_PANEL',
						'ROVER_COMPONENT_WP_SOCIAL_PANEL',
						'ROVER_COMPONENT_WP_SEO_PANEL',
						'ROVER_COMPONENT_WP_MOBILE_PANEL',
						'ROVER_COMPONENT_EMAIL_TEMPLATES'
						)
					))
			{
			return true;		//	Force 'remote' for WP Plugin setup panels
			}
	
		return false;
		}

	private function test_for_modsec($post_str)
		{
		/*	Test for modsec	*/

		$pattern	= "(insert[[:space:]]+into.+values|select.*from.+[a-z|A-Z|0-9]|select.+from|bulk[[:space:]]+insert|union.+select|convert.+\\\\(.*from))";

		if (preg_match($pattern, $post_str) == 1)
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'security alert!');
			return true;

			wp_mail("info@roveridx.com", 
					get_site_url().': post_str will trigger modsec', 
					$post_str);
			}

		return false;
		}
	}

global $rover_idx_content;
$rover_idx_content = new Rover_IDX_Content();

?>