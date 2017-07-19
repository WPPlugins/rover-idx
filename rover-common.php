<?php

define('ROVERIDX_HOST_PATH',						'/home/roveridx/public_html/hosting/');
define('ROVER_DEBUG_KEY',							'roveridx_debug');
define('ROVERIDX_NONCE',							'roveridx-security-key');
define('ROVERIDX_DEF_POST_ID',						-487);

define('WP_TEMPLATE_KEY',							'_wp_page_template');
define('ROVERIDX_META_PAGE_ID',						'_roveridx_page_id');

define('ROVERIDX_FBML_NS_URI',						'http://www.facebook.com/2008/fbml');


function roveridx_get_version()	{
	return ROVER_VERSION;
	}

function roveridx_val_is_checked($settings, $key)	{
	return (is_array($settings) && array_key_exists($key, $settings) && $settings[$key] == true) 
					? 'checked=checked' 
					: '';
	}
function roveridx_val_is_selected($settings, $key, $val_to_compare)	{
	return (is_array($settings) && array_key_exists($key, $settings) && $settings[$key] == $val_to_compare) 
					? 'selected=selected' 
					: '';
	}
function roveridx_get_val($settings, $key)	{
	return (is_array($settings) && array_key_exists($key, $settings)) 
					? $settings[$key] 
					: '';
	}
	
function get_rover_post_id($theming_options) {
	if (!empty($theming_options) && is_array($theming_options) && array_key_exists('rover_post_id', $theming_options))
		$rover_post_id			= $theming_options['rover_post_id'];		//	WooThemes 'Empire' conflicts with -1, so we're using a more unique value
	else
		$rover_post_id			= ROVERIDX_DEF_POST_ID;

	return $rover_post_id;
	}

function rover_get_selected_regions()	{
	$allSelectedRegions	= array();
	$roveridx_options	= get_option(ROVER_OPTIONS_REGIONS);

	if ($roveridx_options === false || !is_array($roveridx_options))
		return $allSelectedRegions;

	foreach ($roveridx_options as $oneKey => $oneVal)
		{
//		rover_error_log(__FILE__, __FUNCTION__, __LINE__, ' '.$oneKey.' / '.$oneVal);

		if ($oneVal == 'region' && $oneVal != 'url_ends_with_slash')
			{
			$allSelectedRegions[] = $oneKey;
//			rover_error_log(__FILE__, __FUNCTION__, __LINE__, ' region ('.$oneKey.') is selected');
			}
		}

	return $allSelectedRegions;
	}

function rover_clean_domain($domain)
	{
	$clean_domain = str_replace(
								array('http://', 'https://', 'www.', '//'), 
								'', 
								$domain
								);

	if ('/' == substr($clean_domain, -1, 1))
		$clean_domain			= substr($clean_domain, 0, -1);	//	Return all but last char

	return strtolower($clean_domain);
	}

function rover_cookies()
	{
	if (!isset($_COOKIE) || !is_array($_COOKIE))
		{
//		rover_error_log(__FILE__, __FUNCTION__, __LINE__, '$ _COOKIE is not an array');
		return null;
		}

	if (count($_COOKIE) === 0)
		{
//		rover_error_log(__FILE__, __FUNCTION__, __LINE__, '$ _COOKIE has ZERO entries');
		return null;
		}
	
	$the_cookies = array();
	$rover_cookie_key = 'rover_';
	$len = strlen($rover_cookie_key);
	foreach ($_COOKIE as $key => $value)
		{
		$sub = substr($key, 0, $len);
//		rover_error_log(__FILE__, __FUNCTION__, __LINE__, 'comparing '.$rover_cookie_key.' to '.$sub);
		if (strcasecmp($rover_cookie_key, $sub) === 0)
			{
			$the_cookies[] = $key.'='.urlencode($value);
			}
		}

//	rover_error_log(__FILE__, __FUNCTION__, __LINE__, 'rover_cookies is '.implode(';', $the_cookies));

	return implode(';', $the_cookies);
	}

function rover_plugins_identifier()
	{
	$rover_plugins			= array('rover-framework', 'rover-admin-framework');

	if (is_plugin_active('rover-idx/roveridx.php'))
		$rover_plugins[]	= 'rover-idx';

	if (is_plugin_active('rover-crm/rover-crm.php'))
		$rover_plugins[]	= 'rover-crm';

	return implode(' ', $rover_plugins);
	}

function rover_idx_error_log($file, $func, $line, $str)	{

	if (rover_idx_is_debuggable())
		{
		error_log( 
			sprintf( '%1$s %2$s %3$s: %4$s\r\n', 
					basename($file),
					$func, 
					$line,
					$str));
//			error_log( 
//				sprintf( '%1$s: %2$s %3$s %4$s: %5$s\r\n', 
//						date('Y-m-d H:i:s'),
//						basename($file),
//						$func, 
//						$line,
//						$str),
//				3,
//				"rover_crm_error_log");
		}

	}

function rover_idx_curr_url()	{

	$url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
	$url .= ( $_SERVER["SERVER_PORT"] != 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
	$url .= $_SERVER["REQUEST_URI"];

	return $url;
	}

function rover_idx_is_debuggable()	{

	if (defined('WP_DEBUG') && WP_DEBUG === true)
		return true;

	if (defined('ROVER_IDX_DEBUG') && ROVER_IDX_DEBUG === true)
		return true;

	$debug = @$_GET['roveridx_debug'];

	if (isset($debug) && $debug > 0)
		return true;

	return false;
	}


function rover_idx_validate_post_bool($key)	{

	if (!isset($_POST[$key]))
		return false;

	return ($_POST[$key] === true || $_POST[$key] == 'true' || $_POST[$key] == 1)
		? true
		: false;

	}

function rover_idx_validate_post_yes_no($key)	{

	if (!isset($_POST[$key]))
		return 'No';

	return (strcasecmp($_POST[$key], 'Yes') === 0)
		? 'Yes'
		: 'No';

	}

function rover_parse_url($var)
	{
	/**
	*  Use this function to parse out the query array element from
	*  the output of parse_url().
	*/
//	$var  = parse_url($var, PHP_URL_QUERY);
	$var  = html_entity_decode($var);
	$var  = explode('&', $var);
	$arr  = array();

	if (is_array($var))
		{
		foreach($var as $val)
			{
			$x          = explode('=', $val);
			$arr[$x[0]] = $x[1];
			}
		}
	unset($val, $x, $var);
	return $arr;
	}


function strip_cross_domain_parenthesis_from_JSON($result)		//	Remove leading and trailing parenthesis that we get from cross-domain json
	{
	$pos = strpos($result, '({');		//	Left ({
	if ($pos !== false)
		$result = substr($result,  $pos+1);

	$pos = strrpos($result, '});');		//	Right })
	if ($pos !== false)
		$result = substr($result,  0, $pos+1);

	return $result;
	}


function rover_contrast_color($hexcolor){

	$hexcolor		= str_replace('#', '', $hexcolor);
	$len			= strlen($hexcolor);

	if ($len === 3)
		$hexcolor	= $hexcolor . $hexcolor;

	if ($len === 6)
		{
		$r = hexdec(substr($hexcolor,0,2));
		$g = hexdec(substr($hexcolor,2,2));
		$b = hexdec(substr($hexcolor,4,2));

		$yiq = (($r*299)+($g*587)+($b*114))/1000;

		return ($yiq >= 128) ? 'black' : 'white';
		}

	return 'white';
	}

function roveridx_css_and_js() {

	global						$rover_idx;

	$upload_dir					= wp_upload_dir();
	$is_rover_admin				= false;

	if (is_admin())
		{
		//	Only bother to load our jQuery when we are on our pages
		
		if (is_array($_GET) && array_key_exists('page', $_GET))
			{
			$the_page			= $_GET['page'];

			if (strpos($the_page, 'rover') !== false)
				{
				$is_rover_admin	= true;
				}
			}
		}

	rover_load_bootstrap($is_rover_admin);

	rover_load_nested_sortable();

	rover_load_rover_js();

	rover_load_google();

	add_action( 'wp_footer', 'rover_load_facebook_js');

	rover_remove_emojis();


	//	************	CSS		***************

	rover_load_rover_css();

	rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Loading CSS');



	if (is_admin())
		{
		$css_url	= ((is_ssl()) ? ROVER_ENGINE_SSL : ROVER_CSS_AND_JS)
							. ROVER_VERSION
							. '/css/rover_wp_admin.min.css';
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Queueing '.$css_url);
		wp_register_style('roveridx-admin-style', $css_url, array(), null, 'all');
		wp_enqueue_style('roveridx-admin-style' );


		$screen			= get_current_screen();
		if ($screen->base === 'dashboard')
			rover_load_flot();
		}

	}

function roveridx_custom_js() {

	global			$rover_idx;

	$allowed_tags	= array(
							'script'	=>	array( 'type' => true, 'id' => true, 'class' => true )
							);
	
	rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'custom_js is '.strlen($rover_idx->roveridx_theming['custom_js']).' bytes');

	$custom_js			= @$rover_idx->roveridx_theming['custom_js'];
	if (strpos($custom_js, '<script') !== false)
		{
		echo wp_kses(stripslashes($custom_js), $allowed_tags) . "\n";
		}
	else
		{
		$output				= array();
		$output[]			= '<script type="text/javascript">';
		$output[]			= stripslashes($custom_js);
		$output[]			= '</script>';
		echo wp_kses(implode('', $output), $allowed_tags) . "\n"; 
		}
	}

function is_rover_panel($panel)
	{
	global		$wp;

	$the_url_parts			= (empty($wp->request))
									? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
									: $wp->request;

	foreach (explode('/', $the_url_parts) as $url_part)
		{
		if (strcmp($url_part, $panel) === 0)
			{
			return true;
			}
		}

	return false;
	}	

function rover_file_url($file)
	{
	return	((is_ssl()) ? ROVER_ENGINE_SSL : ROVER_CSS_AND_JS)
							. ROVER_VERSION
							. $file;
	}

function rover_load_google()
	{
	global					$rover_idx;

	if (@$rover_idx->roveridx_theming['load_google_api'] != 'No')
		{
		$google_key			= (isset($rover_idx->roveridx_theming['google_map_key']) && !empty($rover_idx->roveridx_theming['google_map_key']))
									? $rover_idx->roveridx_theming['google_map_key']
									: 'AIzaSyAfoeHhLqqz4APobPZUakPxQ_-YXwYA9uk';

		$google_libraries	= (isset($rover_idx->roveridx_theming['load_google_libraries']) && !empty($rover_idx->roveridx_theming['load_google_libraries']))
									? '&libraries='.$rover_idx->roveridx_theming['load_google_libraries']
									: null;

		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Loading Google API ['.$google_libraries.']');

		wp_register_script( 'rover-google-js', 
							'https://maps.googleapis.com/maps/api/js?key='.$google_key.$google_libraries,
							$dep = array(), 
							$ver = null, 
							$in_footer = false);
		wp_enqueue_script( 'rover-google-js' );
		}
	}

function rover_load_bootstrap($is_rover_admin)
	{
	global					$rover_idx;

	$do_it					= false;

	if ( is_admin() )	//	On a Rover settings page in Admin.  Purposefully do not load Bootstrap if we are in admin, but not on a Rover page
		{
		$do_it				= ($is_rover_admin) ? true : false;
		}
	else if (is_rover_panel('rover-control-panel'))
		{
		$do_it				= true;
		}
	else if (is_rover_panel('rover-custom-listing-panel'))
		{
		$do_it				= true;
		}
	else if (is_rover_panel('rover-market-conditions'))
		{
		$do_it				= true;
		}
	else if (@$rover_idx->roveridx_theming['load_admin_bootstrap'] == 'No')
		{
		$do_it				= false;
		}

	if ($do_it)
		{
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Loading Bootstrap ');
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '['.rover_file_url('/js/bootstrap/css/bootstrap.min.css').']');
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '['.rover_file_url('/js/bootstrap/js/bootstrap.min.js').']');

		wp_register_style(	'rover-bootstrap-css', 
							rover_file_url('/js/bootstrap/css/bootstrap.min.css'),
							array(), 
							$ver = null, 
							'all');
		wp_enqueue_style(	'rover-bootstrap-css' );

		wp_register_script( 'rover-bootstrap-js', 
							rover_file_url('/js/bootstrap/js/bootstrap.min.js'),
							$dep = array(), 
							$ver = null, 
							$in_footer = true);
		wp_enqueue_script( 'rover-bootstrap-js' );
		}
	}


function rover_load_rover_js()
	{
	global					$rover_idx;

	$load_consolidated		= true;

	if (isset($_GET['jsmin']) && in_array($_GET['jsmin'], array('0')))
		{
		$load_consolidated	= false;
		}

	$js_ver					= (isset($rover_idx->roveridx_theming['js_version']) && !empty($rover_idx->roveridx_theming['js_version']))
									? $rover_idx->roveridx_theming['js_version']
									: ROVER_JS_VERSION;

	if ($load_consolidated)
		{
		wp_register_script( 'rover-boot-js', rover_file_url('/js/'.$js_ver.'/rover_consolidated.min.js'), $dep = array(), $ver = null, $in_footer = true);
		wp_enqueue_script( 'rover-boot-js' );
		}
	else
		{
		wp_register_script( 'rover-boot-js', rover_file_url('/js/'.$js_ver.'/rover.js'), $dep = array(), $ver = null, $in_footer = true);
		wp_enqueue_script( 'rover-boot-js' );

		wp_register_script( 'rover-boot-dialog-js', rover_file_url('/js/'.$js_ver.'/rover_ui_dialog.js'), $dep = array(), $ver = null, $in_footer = true);
		wp_enqueue_script( 'rover-boot-dialog-js' );
		}
	}

function rover_load_nested_sortable()
	{
	//	Rover IDX >> Styling >> Search Panel >> Custom Locations depends on jquery.mjs.nestedSortable.js, which depends on jquery_ui

	$is_rover_styling		= false;

	if (is_admin())
		{
		//	Only bother to load our jQuery when we are on our pages

		if (is_array($_GET) && array_key_exists('page', $_GET))
			{
			$the_page		= $_GET['page'];

			if (strpos($the_page, 'rover-panel-styling') !== false)
				{
				$is_rover_styling	= true;
				}
			}
		}

	if ( $is_rover_styling )
		{
		wp_register_style('roveridx-jq-theme', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/smoothness/jquery-ui.css',	$deps = array(), $ver = ROVER_VERSION, $media = 'all');
		wp_enqueue_style('roveridx-jq-theme');

		$js_ui				= array(
									'jquery-ui-core',
									'jquery-ui-sortable',
									'jquery-ui-draggable',
									'jquery-ui-droppable'
									);

		foreach ($js_ui as $one_lib)
			wp_enqueue_script($one_lib);
		}
	}

function rover_scripts_async( $tag, $handle, $src ) {

	if ( strpos($handle, 'rover-boot') !== false )
		return str_replace( '<script', '<script async', $tag );

	if ( strpos($handle, 'rover-google-js') !== false )
		return str_replace( '<script', '<script defer async', $tag );

	return $tag;
	}

add_filter( 'script_loader_tag', 'rover_scripts_async', 10, 3 );


function rover_load_facebook_js()
	{
	$social_opts					= @get_option(ROVER_OPTIONS_SOCIAL);

	global							$rover_idx;

	$js_ver							= (isset($rover_idx->roveridx_theming['js_version']) && !empty($rover_idx->roveridx_theming['js_version']))
											? $rover_idx->roveridx_theming['js_version']
											: ROVER_JS_VERSION;

	//	Used by:
	//		php/__settings/_social.php
	//		php/__json/_userFBLogin.php

	$app_id							= null;
	if (is_array($social_opts) && isset($social_opts['facebook_app']) && $social_opts['facebook_app'] == 'enabled')
		{
		if (is_array($social_opts) && isset($social_opts['fb_app_id']) && !empty($social_opts['fb_app_id']))
			$app_id					= $social_opts['fb_app_id'];
		}

	if (!is_null($app_id))
		{
		$the_js						= array();

										// Load the SDK asynchronously
		$the_js[]					= '(function(d, s, id) {
											var js, fjs = d.getElementsByTagName(s)[0];
											if (d.getElementById(id)) return;
											js		= d.createElement(s);
											js.id	= id;
											js.src	= "https://connect.facebook.net/en_US/sdk.js";
											fjs.parentNode.insertBefore(js, fjs);

											var rfl	= document.getElementById("rover-facebook-login");
											if (rfl)
												rfl.style.display = "block";

										}(document, "script", "rover-fb-jssdk"));';


		$the_js[]					= 'function rover_inner_html(id, str)	{
											var el = document.getElementById(id);
											if (el)
												el.innerHTML = str;
											}';

										// This is called with the results from from FB.getLoginStatus().
		$the_js[]					= 'function statusChangeCallback(response) {
											console.log("statusChangeCallback");
											console.log(response);';
											// The response object is returned with a status field that lets the
											// app know the current login status of the person.
											// Full docs on the response object can be found in the documentation
											// for FB.getLoginStatus().
		$the_js[]					= 	'if (response.status === "connected") {
											// Logged into your app and Facebook.
											testAPI();
											document.body.className += " rover-fb-connected";
											}
										else if (response.status === "not_authorized") {
											// The person is logged into Facebook, but not your app.
											rover_inner_html("fb-status", "Please log into this app.");
											}
										else {
											// The person is not logged into Facebook, so we"re not sure if
											// they are logged into this app or not.
											document.body.className += " rover-fb-connected";
											rover_inner_html("fb-status", "Please log into Facebook.");
											}
										}';

										// This function is called when someone finishes with the Login
										// Button.  See the onlogin handler attached to it in the sample
										// code below.
		$the_js[]					= 	'function checkLoginState() {
											FB.getLoginStatus(function(response) {
												statusChangeCallback(response);
												});
											}';

		$the_js[]					= 	'window.fbAsyncInit = function() {

											FB.init({
													appId      : "'.$app_id.'",
													cookie     : true,  // enable cookies to allow the server to access the session
													xfbml      : true,  // parse social plugins on this page
													version    : "v2.8" // use graph api version 2.8
													});';

											// Now that we"ve initialized the JavaScript SDK, we call 
											// FB.getLoginStatus().  This function gets the state of the
											// person visiting this page and can return one of three states to
											// the callback you provide.  They can be:
											//
											// 1. Logged into your app ("connected")
											// 2. Logged into Facebook, but not your app ("not_authorized")
											// 3. Not logged into Facebook and can"t tell if they are logged into
											//    your app or not.
											//
											// These three cases are handled in the callback function.

		$the_js[]						= 	'FB.getLoginStatus(function(response) {
												statusChangeCallback(response);
												});';

		$the_js[]						= '};';


										// Here we run a very simple test of the Graph API after login is
										// successful.  See statusChangeCallback() for when this call is made.
		$the_js[]						= 'function testAPI() {

											FB.api("/me", {fields: "name,first_name,last_name,email,link,picture"}, function(response) {

												console.log("Successful Facebook login for: " + response.first_name + " " + response.last_name + " [" + response.email + "]");

												roveridx.ajax(	"_userFBLogin.php",
																jQuery.extend(response, {loadingDiv:false}),
																function(data) {

																	data.dialog_id	= "rover_login";
																	roveridx.load_js("'.ROVER_CSS_AND_JS.ROVER_VERSION.'/js/'.$js_ver.'/rover_connect.js", function(){

																		jQuery(document).trigger("roveridx.login_complete", data);

																		});
																	});
						
												rover_inner_html("fb-status", "Thanks for logging in, " + response.name + "!");
												});
											}';

		$the_js[]						= 'function publish_to_fb() {
											FB.api(
												"/me/feed?message=<include message=\"\" content=\"\" here=\"\">", "Post", 
												{ access_token : the_access_token }, 
												function(response) {
													//	Handle Response which will contain a Post ID if successful 
													} 
												);
											}';

		?>
		<script type="text/javascript" class="<?php echo __FUNCTION__; ?>">
			<?php echo implode('', $the_js); ?>
		</script>
		<?php
		}
	}

function rover_load_rover_css()
	{
	global					$rover_idx;

	$js_ver					= (isset($rover_idx->roveridx_theming['js_version']) && !empty($rover_idx->roveridx_theming['js_version']))
									? $rover_idx->roveridx_theming['js_version']
									: ROVER_JS_VERSION;

	if (@$rover_idx->roveridx_theming['load_fontawesome'] != 'No')
		{		
		wp_register_style('roveridx-style', rover_file_url('/css/'.$js_ver.'/rover_and_fontawesome.min.css'), array(), null, 'all');
		wp_enqueue_style('roveridx-style' );
		}
	else
		{
		wp_register_style('roveridx-style', rover_file_url('/css/'.$js_ver.'/rover.min.css'), array(), null, 'all');
		wp_enqueue_style('roveridx-style' );
		}

	if (is_array($_GET) && array_key_exists('print', $_GET))
		{	
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Queueing '.$css_url);
		wp_register_style('roveridx-style-print', rover_file_url('/css/'.$js_ver.'/rover-print.css'), array(), null, 'all');
		wp_enqueue_style('roveridx-style-print' );
		}


	$custom_js			= @$rover_idx->roveridx_theming['custom_js'];
	if (!empty($custom_js))
		{
		$in_header		= @$rover_idx->roveridx_theming['custom_js_in_header'];

		$js_hook		= ($in_header === 'Yes')
								? 'wp_head'
								: 'wp_footer';

		add_action( $js_hook, 'roveridx_custom_js' );
		}
	}

function rover_remove_emojis()
	{
	global					$rover_idx;

	if (@$rover_idx->roveridx_theming['load_emojis'] == 'No')
		{
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('wp_print_styles', 'print_emoji_styles');
		}
	}

function rover_load_flot()
	{
	wp_register_script(		'flot', 
							((is_ssl()) ? ROVER_ENGINE_SSL : ROVER_ENGINE)
									. ROVER_VERSION
									. '/js/flot/jquery.flot.js',
							$dep = array('jquery'), 
							$ver = null, 
							$in_footer = true);
	wp_register_script(		'flotstack', 
							((is_ssl()) ? ROVER_ENGINE_SSL : ROVER_ENGINE)
									. ROVER_VERSION
									. '/js/flot/jquery.flot.stack.js',
							$dep = array('flot'), 
							$ver = null, 
							$in_footer = true);
	wp_register_script(		'flotcat', 
							((is_ssl()) ? ROVER_ENGINE_SSL : ROVER_ENGINE)
									. ROVER_VERSION
									. '/js/flot/jquery.flot.categories.js',
							$dep = array('flot'), 
							$ver = null, 
							$in_footer = true);
	wp_register_script(		'flotresize', 
							((is_ssl()) ? ROVER_ENGINE_SSL : ROVER_ENGINE)
									. ROVER_VERSION
									. '/js/flot/jquery.flot.resize.js',
							$dep = array('flot'), 
							$ver = null, 
							$in_footer = true);

	wp_enqueue_script(		'flot');
	wp_enqueue_script(		'flotstack');
	wp_enqueue_script(		'flotcat');
	wp_enqueue_script(		'flotresize');
	}
?>