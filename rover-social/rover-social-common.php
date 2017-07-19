<?php
require_once ABSPATH.'wp-admin/includes/image.php';		//	Needed for wp_generate_attachment_metadata()
require_once ABSPATH.'wp-admin/includes/taxonomy.php';	//	Needed for wp_create_category()
if( !class_exists( 'WP_Http' ) )
	require_once( ABSPATH . WPINC. '/class-http.php' );


class roveridx_social		{

	private	$last_search_date				= null;
	private	$now_search_date				= null;

	private	$all_updated_properties			= null;
	private $mult_title						= null;
	private $mult_link						= null;
	private $mult_desc						= null;

	private $thumb_width					= 110;
	private $image_width					= 400;

	private	$wp_is_enabled					= false;
	private $wp_post_author					= null;
	private $post_to_wp_comments			= false;

	private	$fb_is_enabled					= false;
	private $fb_oauth_exceptions			= 0;
	private $fb_access_token				= null;
	private $fb_app_id						= 374057345957459;
	private $fb_app_secret					= '2bc5721d4143ab1352fe8031a713cb6a';

	private	$gp_is_enabled					= false;
	private	$tw_is_enabled					= false;
	
	private	$email_on_error					= false;
	private	$email_on_error_to_user			= false;
	private	$email_on_post					= false;
	private	$email_on_post_to_user			= false;

	private $settings						= null;
	
	private $url_ends_in_slash				= true;
	

	function __construct()	{

		//	Find all updated properties based on search criteria here
		
		$this->settings						= get_option(ROVER_OPTIONS_SOCIAL);
		if (is_array($this->settings))
			{
			$this->wp_is_enabled			= (array_key_exists('post_to_wp', $this->settings) && $this->settings['post_to_wp'] == true) ? true : false;
			$this->fb_is_enabled			= (array_key_exists('post_to_fb', $this->settings) && $this->settings['post_to_fb'] == true) ? true : false;
			$this->gp_is_enabled			= (array_key_exists('post_to_gp', $this->settings) && $this->settings['post_to_gp'] == true) ? true : false;
			$this->tw_is_enabled			= (array_key_exists('post_to_tw', $this->settings) && $this->settings['post_to_tw'] == true) ? true : false;

			$this->wp_post_author			= (array_key_exists('post_to_wp_as_user', $this->settings) && is_numeric($this->settings['post_to_wp_as_user']))
													? $this->settings['post_to_wp_as_user'] 
													: 1;		//	Admin

			$this->post_to_wp_comments		= (array_key_exists('post_to_wp_comments', $this->settings) && $this->settings['post_to_wp_comments'] == true)
													? true 
													: false;	//	Default is 'No'



			$this->email_on_error			= (array_key_exists('email_on_error', $this->settings) && $this->settings['email_on_error'] == true) ? true : false;
			$this->email_on_error_to_user	= (array_key_exists('email_on_error_to_user', $this->settings)) 
													? $this->settings['email_on_error_to_user'] 
													: get_bloginfo('admin_email');

			$this->email_on_post			= (array_key_exists('email_on_post', $this->settings) && $this->settings['email_on_post'] == true) ? true : false;
			$this->email_on_post_to_user	= (array_key_exists('email_on_post_to_user', $this->settings)) 
													? $this->settings['email_on_post_to_user'] 
													: get_bloginfo('admin_email');
			}

		$permalink_structure				= get_option('permalink_structure');
		if (is_null($permalink_structure) && substr($permalink_structure, -1) != '/')
			$this->url_ends_in_slash		= false;

		$search_since						= $this->get_last_search_date();

		$this->get_updated_properties($search_since);
		
		//	done
		}
	function __destruct()	{

		//	Update the search_datetime, so we don't find these same properties again

		if ($this->has_updated_properties())
			{
			$this->update_tracking();

			$key								= $this->get_last_search_date_key();
			$this->settings[$key]				= $this->get_now_search_date();
			$this->settings['fb_access_token']	= $this->fb_access_token;

			update_option(ROVER_OPTIONS_SOCIAL, $this->settings);
			}
		}
	public function has_updated_properties() {
		if (is_null($this->all_updated_properties))
			$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'all_updated_properties is null');
		else
			{
			$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'all_updated_properties has '.count($this->all_updated_properties).' items');
			}

		return (!is_null($this->all_updated_properties) && count($this->all_updated_properties)) ? true : false;
		}
	private function all_updated_properties() {
		return $this->all_updated_properties;
		}
	private function get_last_search_date_key()	{
		return 'search_datetime_';
		}

	private function get_last_search_date()	{
		if (is_null($this->last_search_date))
			{
			//	Get timestamp from Rover SQL Server.  This is important, because the timestamps 
			//	we get must be in the same TZ as the timestamps we send to fetch data.

			date_default_timezone_set('UTC');
			$search_now_datetime_unix	= strtotime('now');
			$this->now_search_date		= date('Y-m-d H:i:s', $search_now_datetime_unix);
			$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'it is currently '.$this->now_search_date);

			$key = $this->get_last_search_date_key();
	
			$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, $key);
	
			if (is_array($this->settings) && array_key_exists($key, $this->settings))
				{
				$search_datetime_unix	= strtotime($this->settings[$key]);
				$this->last_search_date	= date('Y-m-d H:i:s', $search_datetime_unix);

				$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'key exists, using '.$this->last_search_date);
				}
			else
				{
				$search_datetime_unix	= strtotime("-1 day");
				$this->last_search_date	= date('Y-m-d H:i:s', $search_datetime_unix);
	
				$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'key does not exist, creating '.$this->last_search_date);
				}
			}
		
		return $this->last_search_date;
		}
	private function get_now_search_date()	{
		return $this->now_search_date;
		}

	private function get_updated_properties($search_since)	{
		if (!$this->wp_is_enabled() &&
			!$this->fb_is_enabled() &&
			!$this->gp_is_enabled() &&
			!$this->tw_is_enabled())
			{
			$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'social updates are disabled...Terminating.');
			return false;
			}

		$all_selected_regions			= rover_get_selected_regions();

		$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'Looking for updates since '.$search_since);
		$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'There are '.count($all_selected_regions).' selected regions ['.implode(',', $all_selected_regions).']');

		//	Specify HTML email message format

		if (function_exists('add_filter'))
			add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));

		foreach ($all_selected_regions as $one_region)
			{
			$url						= 	ROVER_ENGINE_SSL.ROVER_VERSION.'/php/__json/_roverSocial.php';
			$url						.=		'?region='.$one_region;
			$url						.=		'&domain='.get_site_url();
			$url						.=		'&since='.urlencode($search_since).'&format=json';

			$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, $url);

			$ch							= curl_init();

			curl_setopt ($ch, CURLOPT_URL, $url );
			curl_setopt ($ch, CURLOPT_HEADER, false);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_HTTPHEADER, array ('Accept: application/json', 'Content-Type: application/json', 'Expect:'));

			$result						= curl_exec ($ch);		//	if the CURLOPT_RETURNTRANSFER option is set, it will return the result on success, FALSE on failure.
			if ($result === false)
				{
				$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'curl_exec failed');
				return;
				}

			$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'Raw output = '.number_format(strlen($result)).' bytes');
			curl_close ($ch);

			$result						= strip_cross_domain_parenthesis_from_JSON($result);
			$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'Output after strip_cross_domain_parenthesis_from_JSON() = '.$result);

			$result_decoded				= json_decode($result, true);	
	
			if ($result_decoded === null)
				{
				$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'xml did not decode correctly');
				$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, $result);
	
				if (function_exists('wp_mail') && $this->email_on_error)
					{
					wp_mail($this->email_on_error_to_user, 
							get_site_url().': RoverIDX Social: xml did not decode correctly', 
							'Oops');
					}
	
				return false;
				}

			$this->all_updated_properties	= array();

			if (is_array($result_decoded) && 
				array_key_exists('properties', $result_decoded) &&
				count($result_decoded['properties']))
				{
				$all_updated_properties			= $result_decoded['properties'];

				//	Remove properties that have already been posted

				foreach ($result_decoded['properties'] as $prop)
					{
					if (roveridx_already_in_tracking($prop['id'], $prop['region']))
						$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, $prop['id'].' has already been posted');
					else
						$this->all_updated_properties[] = $prop;
					}

				if (count($this->all_updated_properties) === 0)
					{
					$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'There are 0 property updates');
					return false;
					}
				
				if (count($this->all_updated_properties) > 1)
					{
					$this->mult_title	= $result_decoded['mult_properties_title'];
					$this->mult_link	= html_entity_decode( $result_decoded['mult_properties_link'] );
					$this->mult_desc	= $result_decoded['mult_properties_desc'];
					}

				//	The remaining properties can be posted

				$theHTML		=	'<div id="rover-properties">';
				foreach ($this->all_updated_properties as $prop)
					{
					$theHTML	.= '<div style="width:700px;">';
					$theHTML	.= 		'<div style="float:left;width:130px;margin:0 10px;">';
					$theHTML	.= 			'<img src="'.$prop['image'].'" width="'.$this->thumb_width.'" /></a>';
					$theHTML	.= 		'</div>';
					$theHTML	.= 		'<div style="float:left;width:500px;margin-left:10px;">';
					$theHTML	.= 			'<div>'.$prop['type'].'</div>';
					$theHTML	.= 			'<a href="'.get_site_url().'/'.$prop['link'].'" target="_blank">'.$prop['address'].'</a><br>';
					$theHTML	.= 			'<div>Updated: '.$prop['upd_date'].' (SQL time)</div>';
					$theHTML	.= 			'<div style="color:#444;">'.$prop['desc'].'</div>';
					$theHTML	.= 		'</div>';
					$theHTML	.= '</div>';
					$theHTML	.= '<hr style="color:#999;">';
					$theHTML	.= '<div style="clear:both;margin-bottom:10px;"></div>';
					}
				
				$theHTML		.= '</div>';
				$theHTML		.= '<div style="margin-top:20px;">';
				$theHTML		.=		'Search Since Time: '.$this->get_last_search_date().'<br />';
				$theHTML		.=		'Now is: '.$this->get_now_search_date();
				$theHTML		.= '</div>';

				if ($this->email_on_post)
					{
					wp_mail($this->email_on_post_to_user, 
							get_site_url().': RoverIDX Social: We have '.count($this->all_updated_properties).' updated properties', 
							$theHTML);
					}
				}
			}
		}

	private function refresh_facebook_token($code) {
		
		// If we get a code, it means that we have re-authed the user 
		//and can get a valid access_token. 
		
		$token_url	= "https://graph.facebook.com/oauth/access_token"
						. "?client_id=" . $this->fb_app_id
						. $app_id . "&redirect_uri=" . urlencode($my_url) 
						. "&client_secret=" . $this->fb_app_secret 
						. "&code=" . $code . "&display=popup";

		$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'token_url: '.$token_url);

		if ($does_not_work === true)
			{
			$response = file_get_contents($token_url);	//	Generating ->	HTTP request failed! HTTP/1.0 400 Bad Request
			$params = null;
			parse_str($response, $params);
			$access_token = $params['access_token'];
			}
		else
			{
			$response = $this->curl_file_get_contents($token_url);

			$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'response is '.$response);

			$result_oauth = json_decode($response, true);
			if (is_array($result_oauth))
				{
				if (array_key_exists('error', $result_oauth))
					{
					$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, $result_oauth['message'].' / '.$result_oauth['type'].' / '.$result_oauth['code']);
					return null;
					}
				else
					{
					$access_token = $result_oauth['access_token'];
					}
				}
			else
				{
				$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'Decoded result is not an array - failing');
				return null;
				}
			}



		//	Verify that this token works
		
	    $token_url	= "https://graph.facebook.com/me"
	    				. "?access_token=" . $access_token;

	    $response = $this->curl_file_get_contents($token_url);

	    $result_oauth = json_decode($response, true);
		if (is_array($result_oauth) && array_key_exists('error', $result_oauth))
			{
			$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, $result_oauth['message'].' / '.$result_oauth['type'].' / '.$result_oauth['code']);
			return null;
			}

		return $access_token;
		}
	private function curl_file_get_contents($url) {
		//	note this wrapper function exists in order to circumvent PHP’s strict obeying of HTTP error 
		//	codes.  In this case, Facebook returns error code 400 which PHP obeys and wipes out the response.

		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_URL, $url);
		$contents = curl_exec($c);
		$err  = curl_getinfo($c,CURLINFO_HTTP_CODE);
		curl_close($c);
		if ($contents) 
			return $contents;
		else
			return false;
		}

	public function wp_is_enabled() {
		return $this->wp_is_enabled;
		}
	public function fb_is_enabled() {
		return $this->fb_is_enabled;
		}
	public function gp_is_enabled() {
		return $this->gp_is_enabled;
		}
	public function tw_is_enabled() {
		return $this->tw_is_enabled;
		}

	public function wp_post_author() {
		return $this->wp_post_author;
		}

	public function update_tracking()	{
		$rows_affected = 0;

		$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'Updating tracking for '.count($this->all_updated_properties).' properties');

		foreach ($this->all_updated_properties() as $one_property)
			{
			$rows_affected += roveridx_update_tracking($one_property['id'], $one_property['region']);
			}

		return $rows_affected;
		}

	public function post_to_wordpress() {
		$ping_status		= get_option('default_ping_status');
		
		foreach ($this->all_updated_properties() as $one_property)
			{
			$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, $one_property['address']);

			$wp_post_content		=	'<a href="'.get_site_url().'/'.$one_property['link'].'" target="_blank">'.$one_property['address'].' offered at '.$one_property['price'].'</a>';
			$wp_post_content		.=	'<div style="margin:20px 0 0 0;">'.$one_property['desc'].'</div>';

			//	These Posts will all have a Rover category stamp (ease of finding them all later)

			if ($one_property['type'] == 'New')
				$wp_cat		= 'New Property';
			else if ($one_property['type'] == 'Price')
				$wp_cat		= 'Updated Property';
			else if ($one_property['type'] == 'Sold')
				$wp_cat		= 'Sold Property';				
			else if ($one_property['type'] == 'OpenHouse')
				$wp_cat		= 'Open House';	
			else 
				$wp_cat		= 'Property';

			$cat_id			= get_cat_ID($wp_cat);
			if (!$cat_id)
				$cat_id		= wp_create_category($wp_cat);

			$rover_cat		= 'Rover IDX Property';
			$rover_cat_id	= get_cat_ID($rover_cat);
			if (!$rover_cat_id)
				$rover_cat_id = wp_create_category($rover_cat);

			$rover_post		= array(
									'post_title'		=> $one_property['title'],
									'post_content'		=> $wp_post_content,
									'post_status'		=> 'publish',
									'post_author'		=> $this->wp_post_author(),
									'ping_status'		=> $ping_status,
									'post_category'		=> array($cat_id, $rover_cat_id),
									'comment_status'	=> ($this->post_to_wp_comments) ? 'open' : 'closed',
									'filter' 			=> true		//	Don't sanitize the post_content
									);

			$id				= wp_insert_post( $rover_post );
			
			if (is_wp_error($id)) {
				$errors = $id->get_error_messages();
				foreach ($errors as $error) {
					$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'wp_error: '.$error);
					}
				}
			else
				{
//				$wp_filetype = wp_check_filetype(basename($one_property['image']), null );
//				$attachment = array('post_mime_type'	=> $wp_filetype['type'],
//									'post_title'		=> preg_replace('/\.[^.]+$/', '', basename($filename)),
//									'post_content'		=> '',
//									'post_status'		=> 'inherit'
//									);
//
//				$attach_id = wp_insert_attachment( $attachment, $one_property['image'], $id );
//
//				$attach_data = wp_generate_attachment_metadata( $attach_id, $one_property['image'] );
//				wp_update_attachment_metadata( $attach_id, $attach_data );
//
//				add_post_meta($id, '_thumbnail_id', $attach_id, true);


				$photo = new WP_Http();
				$photo = $photo->request( $one_property['image'] );
				if( $photo['response']['code'] == 200 )
					{
					$attachment = wp_upload_bits( $one_property['mlnumber'].'.jpg', null, $photo['body'], date("Y-m", strtotime( $photo['headers']['last-modified'] ) ) );
					if( empty( $attachment['error'] ) )
						{
						$filetype				= wp_check_filetype( basename( $attachment['file'] ), null );

						$postinfo				= array(
													'post_mime_type'	=> $filetype['type'],
													'post_title'		=> 'image for '.$one_property['title'],
													'post_content'		=> '',
													'post_status'		=> 'inherit',
													);

						$filename				= $attachment['file'];
						$attach_id				= wp_insert_attachment( $postinfo, $filename, $postid );
						$attach_data			= wp_generate_attachment_metadata( $attach_id, $filename );
						wp_update_attachment_metadata( $attach_id,  $attach_data );


						//	Insert the image at the beginning of the post

						$post					= get_post($id, 'ARRAY_A');
						$image					= wp_get_attachment_image_src( $attach_id );
						if ($image)
							{
							if ($image[1] <= $this->image_width)			//	If image provided is smaller than what we wanted
								{
								$image_width	=	$image[1];
								$image_height	=	$image[2];
								}
							else
								{
								$image_width	=	$this->image_width;		//	Proportional resizing
								$image_height	=	(($this->image_width / $image[1]) * $image[2]);
								}

							$image_tag			=	'<a href="'.get_site_url().'/'.$one_property['link'].'" target="_blank"><img src="'.$image[0].'" width="'.$image_width.'" height="'.$image_height.'" class="alignleft" /></a>';
	
							//	Add image above the content
							$post['post_content'] = $image_tag . $post['post_content'];
							

							//	Add image as Featured Image
							set_post_thumbnail( $id, $attach_id );

							$post_id			= wp_update_post( $post );
							}
						}
					else
						{
						$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'wp_upload_bits returned error: '.$attachment['error']);
						}
					}
				else
					{
					$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'WP_Http request returned error: '.$photo['response']['code']);
					}
				}
			}
		}
	public function post_to_facebook() {
		require_once 'facebook.php';

		try {		
			$facebook = new Facebook(array(
										'appId'  		=> $this->fb_app_id,
										'secret' 		=> $this->fb_app_secret,
										'fileUpload'	=> true
										));
			
			if (array_key_exists('fb_access_token', $this->settings))
				{
				$this->fb_access_token	= $this->settings['fb_access_token'];

				if ($this->settings['fb_id'])
					{
					$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'Facebook ID: '.$this->settings['fb_id']);
					$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'Facebook Token: '.$this->fb_access_token);

//					foreach ($this->all_updated_properties() as $one_property)
//						{
						do {

							if ($this->fb_oauth_exceptions >= 2)
								break;

							$all_updated	= $this->all_updated_properties();
							$one_property	= $all_updated[0];

							$short_desc	= substr($one_property['desc'], 0, 155);	//	Desc has 155 char limit in Facebook
							$pos		= strpos($short_desc, '. ');				//	Attempt to limit it by the end of a sentence.
							if ($pos !== false)
								$short_desc = substr($short_desc, 0, $pos);

							$url 		=	"https://graph.facebook.com/".$this->settings['fb_id']."/feed";


							if (count($this->all_updated_properties()) > 1)											//	Facebook combines multiple posts if posted within minutes of each other.
								{																					//	Avoid that by posting summary of updated properties for this hour.
								$name		=	$this->build_mult_title();
								$link		=	$this->build_mult_link().'/page_title/'.urlencode($name);
								$desc		=	$this->build_mult_desc();
								}
							else
								{
								$name		=	$one_property['title'];
								$link		=	$one_property['link'].'/page_title/'.urlencode($one_property['title']);
								$desc		=	$short_desc;
								}

							$prop_link		=	get_site_url();
							$prop_link		.=	(substr($prop_link, -1) == '/')	? '' : '/';
							$prop_link		.=	$link;
							$prop_link		.=	($this->url_ends_in_slash)	? '/' : '';

							$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'prop_link: '.$prop_link);

							$fb_post	=	array(
												'access_token'	=> $this->fb_access_token,
												'name'			=> $name,										//	The Message to be posted above the actual link post
//												'name'			=> $one_property['title'],						//	Title of the URL to be posted
												'link'			=> $prop_link,									//	Direct (Full) URL of the Link to be posted
												'picture'		=> $one_property['image'],						//	Absolute URL of the accompanying image to be posted
												'description'	=> $desc										//	A short description text about the post
												);

							$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'message - '.$fb_post['message']);
							$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'link - '.$fb_post['link']);
							$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'description - '.$fb_post['description']);
							$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'picture - '.$fb_post['picture']);

							$status = $facebook->api('/'.$this->settings['fb_id'].'/feed', 'POST', $fb_post);
							if ($status)
								{
								if (count($this->all_updated_properties()) > 1)
									{
									if ($this->email_on_post)
										{
										wp_mail($this->email_on_post_to_user, 
												get_site_url().': RoverIDX Social: Facebook Post successful', 
												'Rover IDX posted '.$this->mult_link.' properties to Facebook');
										}
									}
								else
									{
									if ($this->email_on_post)
										{
										wp_mail($this->email_on_post_to_user, 
												get_site_url().': RoverIDX Social: Facebook Post successful', 
												'Rover IDX posted '.$one_property['link'].' properties to Facebook');
										}
									}

								$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'Facebook Post successful');
								break;
								}
							else
								{
								if ($this->email_on_error)
									{
									wp_mail($this->email_on_error_to_user, 
											get_site_url().': RoverIDX Social: Facebook Post failure', 
											'Rover IDX encountered ['.$status.'] when posting to Facebook');
									}

								$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'Facebook Post failed - '.$status);
								$this->fb_oauth_exceptions++;

								//	Get a refreshed token, and try again
									
								$this->fb_access_token	= $facebook->getAccessToken();
								}
							} while ( true );
//						}
					}
				else	//	Not logged in
					{
					if ($this->email_on_error)
						{
						wp_mail($this->email_on_error_to_user, 
								get_site_url().': RoverIDX Social: Not logged in to Facebook', 
								'Rover would like to post updates to Facebook, but the Facebook settings were not finalized - Not logged in to Facebook');
						}
					}
				}
			else
				{
				if ($this->email_on_error)
					{
					wp_mail($this->email_on_error_to_user, 
							get_site_url().': RoverIDX Social: Facebook not been setup', 
							'Facebook updates have been enabled, but Facebook credentials have not been setup yet.');
					}
				}
			}
		catch(FacebookApiException $e) {
			$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'Facebook Exception: '.$e->getType().' / '.$e->getMessage());
			}
		}
	public function post_to_googleplus() {
		foreach ($this->all_updated_properties() as $one_property)
			{

			}
		}
	public function post_to_twitter() {
		foreach ($this->all_updated_properties() as $one_property)
			{

			}
		}

	private function build_mult_title()
		{
		$props_new	= 0;
		$props_upt	= 0;
		$props_sold	= 0;
		$props_open	= 0;

		if (count($this->all_updated_properties()) < 2)
			return null;

		foreach ($this->all_updated_properties() as $one_property)
			{
			$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'prop type: '.$one_property['type']);

			if ($one_property['type'] == 'New')
				$props_new++;
			else if ($one_property['type'] == 'Price')
				$props_upt++;
			else if ($one_property['type'] == 'Sold')
				$props_sold++;
			else if ($one_property['type'] == 'OpenHouse')
				$props_open++;
			}

		$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'props_new : '.$props_new);
		$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'props_upt : '.$props_upt);
		$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'props_sold: '.$props_sold);

		$theTitle = array();
		if ($props_new > 0)
			{
			$theTitle[]	= ($props_new === 1) ? 
									'1 New Listing' 
									: ($props_new.' New Listings');
			}

		if ($props_upt > 0)
			{
			$theTitle[]	= ($props_upt === 1) 
									? '1 Updated Listing' 
									: ($props_upt.' Updated Listings');
			}

		if ($props_sold > 0)
			{
			$theTitle[]	= ($props_sold === 1) 
									? '1 Sold Listing' 
									: ($props_sold.' Sold Listings');
			}

		if ($props_open > 0)
			{
			$theTitle[]	= ($props_open === 1) 
									? '1 Open House' 
									: ($props_open.' Open Houses');
			}
		
		$this->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'title has '.count($theTitle).' items: '.implode(', ', $theTitle));

		return implode(', ', $theTitle);
		}
	private function build_mult_link()
		{
		if (count($this->all_updated_properties()) < 2)
			return null;

		$mlnumbers = array();
		foreach ($this->all_updated_properties() as $one_property)
			{
			$mlnumbers[] = $one_property['mlnumber'];
			}

		return ('mlnumber/'.implode(',', $mlnumbers));
		}
	private function build_mult_desc()
		{
		$props_new		= 0;
		$props_upt		= 0;
		$props_sold		= 0;
		$props_open		= 0;
		$cities_new		= array();
		$cities_upt		= array();
		$cities_sold	= array();
		$cities_open	= array();
		$theDesc		= array();

		if (count($this->all_updated_properties()) < 2)
			return null;

		foreach ($this->all_updated_properties() as $one_property)
			{
			if ($one_property['type'] == 'New')
				{
				$props_new++;
				$cities_new[] = $one_property['city'];
				}
			else if ($one_property['type'] == 'Price')
				{
				$props_upt++;
				$cities_upt[] = $one_property['city'];
				}
			else if ($one_property['type'] == 'Sold')
				{
				$props_sold++;
				$cities_sold[] = $one_property['city'];
				}
			else if ($one_property['type'] == 'OpenHouse')
				{
				$props_open++;
				$cities_open[] = $one_property['city'];
				}
			}

		$cities_new		= array_unique($cities_new);
		$cities_upt		= array_unique($cities_upt);
		$cities_sold	= array_unique($cities_sold);
		$cities_open	= array_unique($cities_open);

		if ($props_new > 0)
			{
			sort($cities_new);
			$theDesc[]	= ($props_new === 1)	
								? ('1 New Listing in '.implode(',', $cities_new))
								: ($props_new.' New Listings in '.implode(',', $cities_new));
			}

		if ($props_upt > 0)
			{
			sort($cities_upt);
			$theDesc[]	= ($props_upt === 1)	
								? ('1 Updated Listing in '.implode(',', $cities_upt))
								: ($props_upt.' Updated Listings in '.implode(',', $cities_upt));
			}

		if ($props_sold > 0)
			{
			sort($cities_sold);
			$theDesc[]	= ($props_sold === 1)	
								? ('1 Sold Listing in '.implode(',', $cities_sold))
								: ($props_sold.' Sold Listings in '.implode(',', $cities_sold));
			}

		if ($props_open > 0)
			{
			sort($cities_open);
			$theDesc[]	= ($props_open === 1)	
								? ('1 Open House in '.implode(',', $cities_open))
								: ($props_open.' Open Houses in '.implode(',', $cities_open));
			}

		return implode('<br>', $theDesc);
		}


	function rover_error_social_log($file, $func, $line, $str)	{
		$debug		= intval(@$_GET[ROVER_DEBUG_KEY]);

		if (!empty($debug) && $debug > 0)
			{
			error_log( 
				sprintf( '%1$s <strong>%2$s</strong> %3$s: %4$s\n', 
						basename($file),
						$func, 
						$line,
						$str));
			}
	
		}
	}

function roveridx_refresh_social() {

	require_once ROVER_IDX_PLUGIN_PATH.'rover-database.php';

	//	*******
	//	It's time to make the donuts
	//	*******

	$rover_idx_social = new roveridx_social();
	if ($rover_idx_social->has_updated_properties())
		{
		if ($rover_idx_social->wp_is_enabled())
			$rover_idx_social->post_to_wordpress();

		if ($rover_idx_social->fb_is_enabled())
			$rover_idx_social->post_to_facebook();

		if ($rover_idx_social->gp_is_enabled())
			$rover_idx_social->post_to_googleplus();

		if ($rover_idx_social->tw_is_enabled())
			$rover_idx_social->post_to_twitter();
		}
	else
		{
		$rover_idx_social->rover_error_social_log(__FILE__, __FUNCTION__, __LINE__, 'There are no updates at this time.');
		}
	}

?>