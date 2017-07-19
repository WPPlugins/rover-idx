<?php

function sync_agents()
	{
	$transient_opts					= get_option('roveridx_transient');

	$name_and_email_unique			= array();
	foreach (json_decode($transient_opts) as $one_agent)
		{
		$wp_user_id					= null;
		$agent_page_id				= null;

		//	enabled|region|office_mlsid|agent_mlsid|name|emailaddress

		$agent_parts				= explode("|", $one_agent);
		$agent_enabled				= $agent_parts[0];
		$agent_region				= $agent_parts[1];
		$agent_office_mlsid			= $agent_parts[2];
		$agent_agent_mlsid			= $agent_parts[3];
		$agent_name					= $agent_parts[4];
		$agent_emailaddress			= $agent_parts[5];
		
		$agent_name_trimmed			= preg_replace('/[^a-zA-Z0-9]/', '', $agent_name);
		rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, ' ['.$agent_name.'] ['.$agent_emailaddress.']');
		
		$one_wp_user				= get_user_by( 'email', $agent_emailaddress );
		if ($one_wp_user === false)
			$one_wp_user			= get_user_by( 'login', $agent_name_trimmed );

		if ($one_wp_user !== false)
			$wp_user_id				= $one_wp_user->ID;


		if (is_null($wp_user_id))	//	Create the agent
			{
			$wp_password			= wp_generate_password();
			$user_name				= build_safe_username($agent_name, $agent_emailaddress);

			if (is_null($user_name))
				{
				rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'We were unable to construct a viable username for ['.$agent_name.' / '.$agent_emailaddress.']');
				}
			else
				{
				$wp_user_id			= wp_create_user( $user_name, $wp_password, $agent_emailaddress );

				if ( is_wp_error($wp_user_id) )
					{
					rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'wp_create_user failed ['.$user_name.'] ['.$agent_emailaddress.'] - '.$wp_user_id->get_error_message());
					}
				else
					{
					$user			= new WP_User( $wp_user_id );
					$user->set_role( 'editor' );

					require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

					global			$rover_content;

					$rover_content	= $rover_idx_content->rover_content(	ROVER_COMPONENT_WP_CREATE_GUID, 
																			array(
																				'region'		=> $agent_region, 
																				'agent_email'	=> $agent_emailaddress
																				)
																			);
					$the_guid		= $rover_content['the_html'];

					update_user_meta( $wp_user_id, 'rover_guid', $the_guid );
					}
				}
			}

		if (!is_null($wp_user_id))
			{
			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'Fetching rover_regions for ['.$wp_user_id.'] ['.$agent_emailaddress.']');

			$agent_regions			= json_decode( get_user_meta( $wp_user_id, 'rover_regions', $single = true ) );

			$this_region			= array(
											$agent_region	=> array(
																	'region'					=> $agent_region,
																	'rover_mlsid_office'		=> $agent_office_mlsid,
																	'rover_mlsid_agent'			=> $agent_agent_mlsid,
																	'rover_enabled'				=> $agent_region
																	)
											);

			if (!is_array($agent_regions) || count($agent_regions) === 0)	//	Not yet set
				{
				update_user_meta( $wp_user_id, 'rover_regions', json_encode( $this_region ) );
				update_user_meta( $wp_user_id, 'rover_enabled', $agent_enabled );
				}
			else
				{
				update_user_meta( $wp_user_id, 'rover_regions', json_encode( array_merge( json_decode($agent_regions), $this_region ) ));
				update_user_meta( $wp_user_id, 'rover_enabled', $agent_enabled );
				}
			
			$agent_page_id			= agent_page_exists($agent_name_trimmed);
			if ($agent_page_id === false)
				{
				$agent_page_id	= create_agent_page($agent_name_trimmed, $agent_emailaddress);

				if ($agent_page_id)
					update_user_meta( $wp_user_id, 'rover_agent_page_id', $agent_page_id );
				}
			else
				{
				update_user_meta( $wp_user_id, 'rover_agent_page_id', $agent_page_id );
				}
			}
		}

	delete_option('roveridx_transient');

	return $transient_opts;
	}

function agent_page_exists($agent_name_trimmed)
	{
	global				$wpdb;

	$agent_page			= $wpdb->get_row("SELECT ID FROM $wpdb->posts 
										WHERE post_type = '".ROVER_IDX_CUSTOM_POST_AGENT."' 
										AND post_title = '".$agent_name_trimmed."'");

	if (is_null($agent_page))
		{
echo 'agent page does not exist<br>';
		return false;
		}

	return true;
	}

function create_agent_page($agent_name_trimmed, $agent_emailaddress)
	{
	$agent_page_id		= wp_insert_post( array(
												  'comment_status'				=> 'closed',
												  'ping_status'					=> 'closed',
												  'post_status'					=> 'publish',
												  'post_title'					=> $agent_name_trimmed,
												  'post_content'				=> '[rover_idx_agents listing_agent_email="'.$agent_emailaddress.'"]',
												  'post_date' 					=> date('Y-m-d H:i:s'),			
												  'post_type'					=> ROVER_IDX_CUSTOM_POST_AGENT
												  ), $wp_error = true );

	rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, 'wp_create_user succeeded ['.$wp_user_id.']');

	return $agent_page_id;
	}

function build_safe_username($fullname, $email)	{

	//	Our being here means we already know 
	//	the email address is not registered!
	//	So we just focus on the username

	if (!username_exists( $fullname ))
		{
		return $fullname;
		}

	$pos = strpos($email, '@');
	if ($pos !== false)
		{
		$email_try	= str_replace(array('_', '.'), ' ', substr($email, 0, $pos));
		if (!username_exists( $email_try ))
			{
			return $email_try;
			}
		}

	for ($x = 1; $x < 99; $x++)
		{
		$fullname_try	= $fullname.'_'.$x;
		if (!username_exists( $fullname_try ))
			{
			return $fullname_try;
			}
		}

	return null;
	}

function agent_style()
	{
	return	'<style type="text/css">

				.rover-one-agent	{
					border-top: 1px solid #eee;
					margin-bottom:30px;
					padding-top: 20px;
					width:100%;
					}

				.rover-one-agent-thumb {
					float:left;
					box-sizing:
					border-box;
					max-width:300px;
					text-align:center;
					width:30%;
					}

				.rover-one-agent-desc {
					float:left;
					box-sizing:border-box;
					text-align:left;
					width:70%;
					}

			</style>';
	}

function display_agents_list($atts)
	{
	$the_html						= array();
	$the_html[]						= agent_style();
	$wp_users						= get_users( );

	foreach ( $wp_users as $user ) {

		$agent_meta					=	get_user_meta( $user->ID );

		if (isset($agent_meta['rover_enabled']) && $agent_meta['rover_enabled'][0] == 'enabled')
			{
			$one_agent				=	agent_list_html();

			$a_link					=	(isset($agent_meta['rover_agent_page_id']) && !empty($agent_meta['rover_agent_page_id'][0] ))
												? get_permalink($agent_meta['rover_agent_page_id'][0])
												: null;

			$one_agent				=	str_replace('AGENT_THUMB',			get_avatar( $user->user_email, $size = 100),	$one_agent);
			$one_agent				=	str_replace('AGENT_LINK',			$a_link,										$one_agent);
			$one_agent				=	str_replace('ROVER_AGENT_NAME',		ucwords($user->nickname),						$one_agent);
			$one_agent				=	str_replace('ROVER_AGENT_EMAIL',	$user->user_email,								$one_agent);
			$one_agent				=	str_replace('ROVER_AGENT_PHONE',	$agent_meta['rover_phone'][0],					$one_agent);
			$one_agent				=	str_replace('ROVER_AGENT_DESC',		$agent_meta['description'][0],					$one_agent);

			$the_html[]				=	$one_agent;
			}
		}

	return implode('', $the_html);
	}



function display_agent_page($atts)
	{
	$wp_users						= get_users( );

	foreach ( $wp_users as $user ) {

		if (isset($atts['listing_agent_email']) && !empty($atts['listing_agent_email']) && (strcasecmp($atts['listing_agent_email'], $user->user_email) === 0))
			{
			$agent_meta				=	get_user_meta( $user->ID );

			if (isset($agent_meta['rover_enabled']) && $agent_meta['rover_enabled'][0] == 'enabled')
				{
				$one_agent			=	agent_page_html();

				$one_agent			=	str_replace('AGENT_THUMB',			get_avatar( $user->user_email, $size = 100),	$one_agent);
				$one_agent			=	str_replace('ROVER_AGENT_NAME',		ucwords($user->nickname),						$one_agent);
				$one_agent			=	str_replace('ROVER_AGENT_EMAIL',	$user->user_email,								$one_agent);
				$one_agent			=	str_replace('ROVER_AGENT_PHONE',	$agent_meta['rover_phone'][0],					$one_agent);
				$one_agent			=	str_replace('ROVER_AGENT_DESC',		$agent_meta['description'][0],					$one_agent);
				$one_agent			=	str_replace('ROVER_AGENT_LISTINGS',	agent_listings($agent_meta),					$one_agent);
				}
			else
				{
				$one_agent			=	ucwords($user->nickname).' is not active';
				}

			return agent_style().$one_agent;
			}
		}

	return null;
	}

function agent_listings($agent_meta)
	{
	$the_html						=	array();
	$regions						= 	json_decode($agent_meta['rover_regions'][0]);
	
	foreach ($regions as $one_region => $agent_info_for_region)
		{
		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global $rover_idx_content;

		$the_rover_content			=   $rover_idx_content->rover_content(
																		ROVER_COMPONENT_RESULTS_AS_TABLE, 
																		$atts = array(
																					'region'				=> $one_region,
																					'listing_agent_mlsid'	=> $agent_info_for_region->rover_mlsid_agent
																					)
																		);
		return $the_rover_content['the_html'];
		}

	return implode('', $the_html);
	}

function agent_list_html()
	{
	$the_html						=	array();

	$the_html[]						= 	'<div class="rover-one-agent">											
											<div class="rover-one-agent-thumb">
												<a href="AGENT_LINK">AGENT_THUMB</a>
												<h3><a href="AGENT_LINK">ROVER_AGENT_NAME</a></h3>
												<div class="phone">ROVER_AGENT_PHONE</div>
												<div class="email"><a class="value" title="email" href="mailto:ROVER_AGENT_EMAIL">ROVER_AGENT_EMAIL</a></div>
											</div>

											<div class="rover-one-agent-desc">
												<p>ROVER_AGENT_DESC</p>
											</div>
											<div style="clear:both"></div>
										</div>';	

	return implode('', $the_html);
	}

function agent_page_html()
	{
	$the_html						=	array();

	$the_html[]						= 	'<div class="rover-agent" style="width:100%;">
											<div style="clear:both;box-sizing:border-box;width:100%;">

												<h3>ROVER_AGENT_NAME</h3>

												<div style="float:left;box-sizing:border-box;max-width:200px;width:25%;">
													AGENT_THUMB
												</div>

												<div style="float:left;box-sizing:border-box;text-align:right;width:75%;">
													<div class="tel"><span class="value">ROVER_AGENT_PHONE</span></div>

													<div class="email-address-block"><span class="email"><a class="value" title="email." href="mailto:ROVER_AGENT_EMAIL">ROVER_AGENT_EMAIL</a></span></div>
												</div>
											</div>

											<div style="clear:both;box-sizing:border-box;margin-top:30px;text-align:right;width:100%;">
												<p>ROVER_AGENT_DESC</p>
											</div>

											<div style="clear:both;box-sizing:border-box;margin-top:30px;width:100%;">
												<h3 style="clear:both;">Featured Listings</h3>
												ROVER_AGENT_LISTINGS
											</div>
										</div>';	

	return implode('', $the_html);
	}
?>