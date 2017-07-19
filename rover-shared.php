<?php
if (!defined('ROVER_VERSION'))
	define('ROVER_VERSION', 									'2.0.0');

if (!defined('ROVER_VERSION_MINOR'))
	define('ROVER_VERSION_MINOR', 								'1314');

if (!defined('ROVER_JS_VERSION'))
	define('ROVER_JS_VERSION', 									'1730167');

if (!defined('ROVER_VERSION_FULL'))
	define('ROVER_VERSION_FULL', 								'2.0.0.1314');

##############################


if (!defined('ROVER_CSS_AND_JS'))
	define('ROVER_CSS_AND_JS',									'http://cdn.c.roveridx.com/');

if (!defined('ROVER_ENGINE'))
	define('ROVER_ENGINE',										'http://c.roveridx.com/');

if (!defined('ROVER_ENGINE_SSL'))
	define('ROVER_ENGINE_SSL',									'https://c.roveridx.com/');

if (!defined('ROVER_ENGINE_CURRENT_PROTOCOL'))
	define('ROVER_ENGINE_CURRENT_PROTOCOL',						'//c.roveridx.com/');

if (!defined('ROVER_CDN_ENGINE_CURRENT_PROTOCOL'))
	define('ROVER_CDN_ENGINE_CURRENT_PROTOCOL',					'//cdn.c.roveridx.com/');

if (!defined('ROVER_OPTIONS_THEMING'))
	define('ROVER_OPTIONS_THEMING',								'roveridx_theming');

if (!defined('ROVER_OPTIONS_REGIONS'))
	define('ROVER_OPTIONS_REGIONS',								'roveridx_regions');

if (!defined('ROVER_OPTIONS_SEO'))
	define('ROVER_OPTIONS_SEO',									'roveridx_seo');

if (!defined('ROVER_OPTIONS_SOCIAL'))
	define('ROVER_OPTIONS_SOCIAL',								'roveridx_social');

if (!defined('ROVER_INSTALLATION_SOURCE'))
	define('ROVER_INSTALLATION_SOURCE',							'installation-source-native-repo');


if (!defined('ROVER_DEFAULT_CSS_FRAMEWORK'))
	define('ROVER_DEFAULT_CSS_FRAMEWORK',						'rover');

if (!defined('ROVER_DEFAULT_FULL_PAGE_LAYOUT'))
	define('ROVER_DEFAULT_FULL_PAGE_LAYOUT',					'map-search-grid');

if (!defined('ROVER_DEFAULT_SEARCH_LAYOUT'))
	define('ROVER_DEFAULT_SEARCH_LAYOUT',						'default_horizontal');

if (!defined('ROVER_DEFAULT_LISTING_LAYOUT'))
	define('ROVER_DEFAULT_LISTING_LAYOUT',						'cube_clean');

if (!defined('ROVER_DEFAULT_PROPERTY_LAYOUT'))
	define('ROVER_DEFAULT_PROPERTY_LAYOUT',						'rover-by-category-nearby');

if (!defined('ROVER_DEFAULT_MAX_IMG_WIDTH'))
	define('ROVER_DEFAULT_MAX_IMG_WIDTH',						500);

if (!defined('ROVER_DEFAULT_LISTING_PER_ROW'))
	define('ROVER_DEFAULT_LISTING_PER_ROW',						50);	//	default to 2 per row so we look ok on narrow pages with sidebars.

if (!defined('ROVER_DEFAULT_REFRESH_ANIMATION'))
	define('ROVER_DEFAULT_REFRESH_ANIMATION',					'fade');

if (!defined('ROVER_DEFAULT_REFRESH_SCROLL'))
	define('ROVER_DEFAULT_REFRESH_SCROLL',						true);


if (!defined('ROVER_TEMPLATE_EMAIL_USER_WELCOME'))
	{
	define('ROVER_TEMPLATE_EMAIL_USER_WELCOME',					1);
	define('ROVER_TEMPLATE_EMAIL_AGENT_WELCOME',				2);
	define('ROVER_TEMPLATE_EMAIL_USER_REGISTERED',				3);
	define('ROVER_TEMPLATE_PROPERTY_DETAIL',					4);
	define('ROVER_TEMPLATE_FULL_PAGE_DESKTOP',					5);
	define('ROVER_TEMPLATE_FULL_PAGE_MOBILE',					6);
	define('ROVER_TEMPLATE_LISTING_LAYOUT',						7);

	define('ROVER_TEMPLATE_TYPE_EMAIL_USER_WELCOME',			'welcome_user');
	define('ROVER_TEMPLATE_TYPE_EMAIL_AGENT_WELCOME',			'welcome_agent');
	define('ROVER_TEMPLATE_TYPE_EMAIL_USER_REGISTERED',			'registered_user');
	define('ROVER_TEMPLATE_TYPE_EMAIL_PROPERTY_CONTACT',		'property_contact');
	define('ROVER_TEMPLATE_TYPE_PROPERTY_DETAIL',				'prop_layout');

	define('ROVER_TEMPLATE_TYPE_FULL_PAGE',						'full_page_layout');
	define('ROVER_TEMPLATE_TYPE_FULL_PAGE_DESKTOP',				'full_page_desktop');
	define('ROVER_TEMPLATE_TYPE_FULL_PAGE_MOBILE',				'full_page_mobile');
	define('ROVER_TEMPLATE_TYPE_LISTING_LAYOUT',				'listing_layout');
	define('ROVER_TEMPLATE_TYPE_SIDEBAR_LAYOUT',				'sidebar_layout');
	define('ROVER_TEMPLATE_TYPE_REGISTRATION_LAYOUT',			'registration_layout');
	define('ROVER_TEMPLATE_TYPE_LOGIN_LAYOUT',					'login_layout');
	define('ROVER_TEMPLATE_TYPE_CONTACT_LAYOUT',				'contact_layout');
	define('ROVER_TEMPLATE_TYPE_INFOWINDOW_LAYOUT',				'infowindow_layout');
	define('ROVER_TEMPLATE_TYPE_SEARCH_LAYOUT',					'search_layout');
	define('ROVER_TEMPLATE_TYPE_LISTING_NAV_LAYOUT',			'listing_nav_layout');

	define('ROVER_TEMPLATE_TYPE_AGENT_LIST',					'agent_list');
	define('ROVER_TEMPLATE_TYPE_AGENT_DETAIL_PAGE',				'agent_detail_page');
	}




?>