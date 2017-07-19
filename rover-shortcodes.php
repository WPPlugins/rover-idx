<?php

class Rover_IDX_Shortcodes
	{
	function __construct() {

		add_shortcode( 'rover_idx_full_page', 			array($this, 'rover_idx_full_page') );

		add_shortcode( 'rover_idx_results',				array($this, 'rover_idx_results') );
		add_shortcode( 'rover_idx_results_as_table',	array($this, 'rover_idx_results_as_table') );
		add_shortcode( 'rover_idx_results_as_map',		array($this, 'rover_idx_results_as_map') );

		add_shortcode( 'rover_idx_property_details',	array($this, 'rover_idx_property_details') );

		add_shortcode( 'rover_idx_search_panel',		array($this, 'rover_idx_search_panel') );

		add_shortcode( 'rover_idx_navbar',				array($this, 'rover_idx_navbar') );

		add_shortcode( 'rover_idx_links', 				array($this, 'rover_idx_links') );

		add_shortcode( 'rover_idx_plugin', 				array($this, 'rover_idx_plugin') );

		add_shortcode( 'rover_idx_report', 				array($this, 'rover_idx_report') );

		add_shortcode( 'rover_idx_cta', 				array($this, 'rover_idx_cta') );
		add_shortcode( 'rover_idx_contact', 			array($this, 'rover_idx_contact') );
		add_shortcode( 'rover_idx_register', 			array($this, 'rover_idx_register') );

		add_shortcode( 'rover_idx_slider', 				array($this, 'rover_idx_slider') );
		add_shortcode( 'rover_idx_searchslider', 		array($this, 'rover_idx_searchslider') );

		add_shortcode( 'rover_idx_marketconditions',	array($this, 'rover_idx_marketconditions') );
		add_shortcode( 'rover_idx_unsubscribe',			array($this, 'rover_idx_unsubscribe') );

		add_shortcode( 'rover_idx_widget',				array($this, 'rover_idx_widget') );

		add_shortcode( 'rover_idx_settings',			array($this, 'rover_idx_settings') );

		add_shortcode( 'rover_idx_agent',				array($this, 'rover_idx_agent') );
		add_shortcode( 'rover_idx_agents',				array($this, 'rover_idx_agents') );

		/*	SEO Rets			*/

		add_shortcode( 'sr-listings',					array($this, 'seo_rets_listings') );
		add_shortcode( 'sr-list',						array($this, 'seo_rets_links') );

		/*	Diverse Solutions	*/

		add_shortcode( 'idx-listings',					array($this, 'dsidxpress_listings') );
		add_shortcode( 'idx-quick-search',				array($this, 'dsidxpress_search') );

		/*	Do not texturize Rover shortcodes!	*/

		add_filter( 'no_texturize_shortcodes',			array($this, 'rover_no_wptexturize' ) );
		}

	/*	shortcodes	*/

	function rover_idx_full_page($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=  $rover_idx_content->rover_content('ROVER_COMPONENT_FULL_PAGE', $atts);
		return $the_rover_content['the_html'];
		}
	function rover_idx_results($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_RESULTS', $atts);
		return $the_rover_content['the_html'];
		}
	function rover_idx_results_as_table($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_RESULTS_AS_TABLE', $atts);
		return $the_rover_content['the_html'];
		}
	function rover_idx_results_as_map($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_RESULTS_AS_MAP', $atts);
		return $the_rover_content['the_html'];
		}
	function rover_idx_property_details($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_PROP_DETAILS', $atts);
		return $the_rover_content['the_html'];
		}
	function rover_idx_search_panel($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_SEARCH_PANEL', $atts);
		return $the_rover_content['the_html'];
		}
	function rover_idx_navbar($atts)		{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_NAVBAR', $atts);
		return $the_rover_content['the_html'];
		}
	function rover_idx_links($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$atts['plugin_type']	= 'quickSearchLinks';
		$atts['plugin_height']	= 'auto';
		$atts['all_cities']		= ($atts['object'] == 'city') 
										? '*' 
										: $atts['all_cities'];
		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_PLUGIN', $atts);
		return $the_rover_content['the_html'];
		}
	function rover_idx_plugin($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_PLUGIN', $atts);
		return $the_rover_content['the_html'];
		}
	function rover_idx_report($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_REPORT', $atts);
		return $the_rover_content['the_html'];
		}
	function rover_idx_cta($atts)		{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_CTA', $atts);
		return $the_rover_content['the_html'];
		}
	function rover_idx_contact($atts)		{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_CONTACT', $atts);
		return $the_rover_content['the_html'];
		}
	function rover_idx_register($atts)		{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_REGISTER', $atts);
		return $the_rover_content['the_html'];
		}
	function rover_idx_slider($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_SLIDER', $atts);
		return $the_rover_content['the_html'];
		}
	function rover_idx_searchslider($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_SEARCHSLIDER', $atts);
		return $the_rover_content['the_html'];
		}
	function rover_idx_marketconditions($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_MARKET_CONDITIONS', $atts);
		return $the_rover_content['the_html'];
		}
	function rover_idx_unsubscribe($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_UNSUBSCRIBE', $atts);
		return $the_rover_content['the_html'];
		}

	function rover_idx_settings($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_SETTINGS_PANEL', $atts);
		return $the_rover_content['the_html'];
		}

	function rover_idx_agent($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_AGENT_DETAIL_PAGE', $atts);
		return $the_rover_content['the_html'];
		}

	function rover_idx_agents($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content		=   $rover_idx_content->rover_content('ROVER_COMPONENT_AGENT_LIST', $atts);
		return $the_rover_content['the_html'];
		}



	/*	Map other vendors shortcodes to our functions	*/

	function seo_rets_listings($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';
		require_once ROVER_IDX_PLUGIN_PATH.'rover-shortcodes-seorets.php';

		global $rover_idx_content;

		$seorets				= new Rover_IDX_Shortcodes_SEORETS();
		$atts					= $seorets->map_seorets_to_rover($atts);

		$the_rover_content		= $rover_idx_content->rover_content('ROVER_COMPONENT_RESULTS_AS_TABLE', $atts);

		return $the_rover_content['the_html'];
		}

	function seo_rets_links($atts)		{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';
		require_once ROVER_IDX_PLUGIN_PATH.'rover-shortcodes-seorets.php';

		global $rover_idx_content;

		$seorets				= new Rover_IDX_Shortcodes_SEORETS();
		$atts					= $seorets->map_seorets_to_rover($atts);

		$atts['plugin_type']	= 'quickSearchLinks';
		$atts['plugin_height']	= 'auto';
		$atts['all_cities']		= ($atts['object'] == 'city') 
										? '*' 
										: null;
		$atts['quick_search_include_counts']			= $atts['include_counts'];
		$atts['quick_search_include_areas']				= $atts['include_areas'];

		$the_rover_content		= $rover_idx_content->rover_content('ROVER_COMPONENT_PLUGIN', $atts);
		return $the_rover_content['the_html'];
		}

	function dsidxpress_listings($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';
		require_once ROVER_IDX_PLUGIN_PATH.'rover-shortcodes-dsidxpress.php';

		global $rover_idx_content;

		$dsidxpress				= new Rover_IDX_Shortcodes_DS();
		$atts					= $dsidxpress->map_dsidxpress_to_rover($atts);

		$the_rover_content		= $rover_idx_content->rover_content('ROVER_COMPONENT_RESULTS', $atts);

		return $the_rover_content['the_html'];
		}

	function dsidxpress_search($atts)	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		if (isset($atts['format']) && ($atts['format'] == 'horizontal') )
			{
			$atts['search_panel_layout']		= 'custom';
			$atts['city_control_style']			= 1;
			$atts['prop_type_control_style']	= 1;
			$atts['template_fields']			= 'buildCitySelect,buildBeds,buildBaths,buildPrice,buildSqFt';
			$atts['all_per_row']				= 6;
			}
		else
			{
			$atts['search_panel_layout']		= 'custom';
			$atts['city_control_style']			= 1;
			$atts['city_buttons_per_row']		= 1;
			$atts['prop_type_control_style']	= 1;
			$atts['proptype_buttons_per_row']	= 1;
			$atts['template_fields']			= 'buildCitySelect,buildBeds,buildBaths,buildPrice,buildSqFt,buildAcres';
			}

		unset($atts['format']);

		$the_rover_content						= $rover_idx_content->rover_content('ROVER_COMPONENT_SEARCH_PANEL', $atts);

		return $the_rover_content['the_html'];
		}

	function rover_idx_widget($atts) {

		global $wp_widget_factory;

		extract(shortcode_atts(array(
			'widget_name' => FALSE
		), $atts));

		$widget_name = wp_specialchars($widget_name);

		if (!is_a($wp_widget_factory->widgets[$widget_name], 'WP_Widget')):
			$wp_class = 'WP_Widget_'.ucwords(strtolower($class));

			if (!is_a($wp_widget_factory->widgets[$wp_class], 'WP_Widget')):
				return '<p>'.sprintf(__("%s: Widget class not found. Make sure this widget exists and the class name is correct"),'<strong>'.$class.'</strong>').'</p>';
			else:
				$class = $wp_class;
			endif;
		endif;

		ob_start();
		the_widget($widget_name, $atts, array('widget_id'=>'arbitrary-instance-'.$id,
			'before_widget' => '',
			'after_widget' => '',
			'before_title' => '',
			'after_title' => ''
		));
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
		}

	function rover_no_wptexturize( $shortcodes ) {

		$shortcodes									= array();
		$shortcodes[]								= 'rover_idx_full_page';
		$shortcodes[]								= 'sr-listings';
		$shortcodes[]								= 'sr-list';

		$shortcodes[]								= 'rover_idx_results';

		$shortcodes[]								= 'rover_idx_results_as_table';

		$shortcodes[]								= 'rover_idx_results_as_map';

		$shortcodes[]								= 'rover_idx_property_details';

		$shortcodes[]								= 'rover_idx_search_panel';

		$shortcodes[]								= 'rover_idx_navbar';

		$shortcodes[]								= 'rover_idx_links';
		$shortcodes[]								= 'rover_idx_contact';
		$shortcodes[]								= 'rover_idx_register';
		$shortcodes[]								= 'rover_idx_plugin';
		$shortcodes[]								= 'rover_idx_report';
		$shortcodes[]								= 'rover_idx_cta';
		$shortcodes[]								= 'rover_idx_slider';
		$shortcodes[]								= 'rover_idx_searchslider';
		$shortcodes[]								= 'rover_idx_marketconditions';
		$shortcodes[]								= 'rover_idx_unsubscribe';
		$shortcodes[]								= 'rover_idx_widget';

		$shortcodes[]								= 'rover_idx_settings';
		$shortcodes[]								= 'rover_idx_agent';
		$shortcodes[]								= 'rover_idx_agents';

		return $shortcodes;
		}

	}

new Rover_IDX_Shortcodes();

?>