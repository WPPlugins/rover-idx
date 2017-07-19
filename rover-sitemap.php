<?php

require_once 'rover-common.php';
define('SITEMAP_DIR',		"/rover_idx_sitemap");

class Rover_IDX_SITEMAP {

	private	$sitemap_opts					= null;
	private	$sitemap_file					= null;
	private	$upload_dir						= null;
	private	$upload_url						= null;

	function __construct()	{

		}

	public function build($force_refresh)
		{
		$this->sitemap_opts					= get_option(ROVER_OPTIONS_SEO);

		if ($this->sitemap_is_disabled())
				{
				$this->log(__FUNCTION__, __LINE__, 'Sitemap refresh is disabled');
				return false;
				}

		$all_selected_regions				= rover_get_selected_regions();
	
		foreach ($all_selected_regions as $one_region)
			{
			$successful_notifications		= array();
			$this->sitemap_file				= "rover_sitemap_".$one_region.".xml";
	
			if ($force_refresh || $this->should_we_build_new_sitemap($one_region))
				{
				if (($result_decoded = $this->fetch_sitemap_data($one_region)) === false)
					return;

				$this->log(__FUNCTION__, __LINE__, 'sitemap = '.$this->sitemap_file	);

				$this->log(__FUNCTION__, __LINE__, 'Output of json_decode() has '.count($result_decoded).' items');
				$this->log(__FUNCTION__, __LINE__, 'Output keys = '.array_keys($result_decoded));

				$bytesWritten				= 0;
				$wp_upload_dir 				= wp_upload_dir();									//	path to upload directory
				$this->upload_dir 			= (empty($wp_upload_dir['basedir']))
													? dirname(__FILE__)							//	We only end up here if wp_upload_dir() failed (unlikely)
													: $wp_upload_dir['basedir'].SITEMAP_DIR;

				$this->upload_url			= (empty($wp_upload_dir['baseurl']))
													? dirname(__FILE__)
													: $wp_upload_dir['baseurl'].SITEMAP_DIR;


				$this->log(__FUNCTION__, __LINE__, 'count			= '.$result_decoded['count']);
				$this->log(__FUNCTION__, __LINE__, 'numBatches		= '.$result_decoded['numBatches']);
				$this->log(__FUNCTION__, __LINE__, 'batchSize		= '.$result_decoded['batchSize']);
				$this->log(__FUNCTION__, __LINE__, 'sitemapArr		= '.count($result_decoded['sitemapArr']).' items');
				$this->log(__FUNCTION__, __LINE__, 'path will be	= '.$this->upload_dir.'/'.$this->sitemap_file	);
				$this->log(__FUNCTION__, __LINE__, 'url will be		= '.$this->upload_url.'/'.$this->sitemap_file	);

				/*
					Create the sitemap file
				*/

				$sitemap_url_gz				= $this->sitemap_file_write($result_decoded['sitemapArr']);

				$search_engines				= array(
													'Google'	=> 'http://www.google.com/webmasters/tools/ping?sitemap=',
													'Bing'		=> 'http://www.bing.com/webmaster/ping.aspx?siteMap=',
													'Yahoo'		=> 'http://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap=',	//	Yahoo has merged with Bing ??
													'Ask'		=> 'http://submissions.ask.com/ping?sitemap='
													);
			
				/*
					http://freds_real_estate.com/wp-content/uploads/rover_idx_sitemap/rover_sitemap_DESM.xml.gz
				*/

				$successful_notifications	= array();
				foreach ($search_engines as $search_engine_name => $search_engine_submission_url)
					{
					$ping_url				= $search_engine_submission_url.$sitemap_url_gz;

					if ($this->notify_search_engine($ping_url, $search_engine_name))
						{
						$successful_notifications[] = $search_engine_name;

						$this->log(__FUNCTION__, __LINE__, $sitemap_url_gz.' submitted to '.$search_engine_name);
						}
					else
						{
						$this->log(__FUNCTION__, __LINE__, 'Attempt to ping '.$search_engine_name.' using '.$ping_url.' has failed');
						}
					}

				if (function_exists('wp_mail'))
					{
//						wp_mail('info@roveridx.com', 
//								get_site_url().': RoverIDX has refreshed sitemap '.$this->sitemap_file, 
//								'Sitemap '.$this->upload_url.'/'.$this->sitemap_file.' refreshed on '.date('Y-m-d H:i:s').' ('.number_format($bytesWritten).' bytes written)<br><br>'.
//								'Successfully notified '.count($successful_notifications).' search engines ('.implode(',', $successful_notifications).')<br><br>');

					$this->sitemap_opts[$one_region]['desc']	= esc_html( number_format(count($result_decoded['sitemapArr'])).' properties' );
					$this->sitemap_opts[$one_region]['url']		= esc_url( $this->upload_url.'/'.$this->sitemap_file.'.gz' );
					}		

				//	'desc' was set, above

				$this->sitemap_opts[$one_region]['timestamp']	= date('M d Y H:i:s');

				update_option(ROVER_OPTIONS_SEO, $this->sitemap_opts);
				}
			}
		}

	private function sitemap_is_disabled()
		{
		if (is_array($this->sitemap_opts) && array_key_exists('disabled', $this->sitemap_opts))
			{
			if ($this->sitemap_opts['disabled'] == true)	//	'Disable Sitemap' is true in SEO Panel
				{
				$this->log(__FUNCTION__, __LINE__, 'Sitemap refresh is disabled');
				return true;
				}
			}

		return false;
		}

	private function should_we_build_new_sitemap($one_region)
		{
		$build_it	= true;
	
		if (is_array($this->sitemap_opts) && 
			array_key_exists($one_region, $this->sitemap_opts) &&
			array_key_exists('timestamp', $this->sitemap_opts[$one_region]))
			{
			$last_successful_date	= strtotime($this->sitemap_opts[$one_region]['timestamp']);
	
			if (date('Y') == date('Y', $last_successful_date)	&& 
				date('m') == date('m', $last_successful_date)	&&
				date('d') == date('d', $last_successful_date))
				{
				$this->log(__FUNCTION__, __LINE__, 'We already built a '.$one_region.' sitemap today (on '.$this->sitemap_opts[$one_region]['timestamp'].')');
				$build_it	= false;
				}
			else
				{
				$this->log(__FUNCTION__, __LINE__, 'timestamp '.$this->sitemap_opts[$one_region]['timestamp'].' is not today.  We will build a sitemap');
				}
			}
		else
			{
			if (is_array($this->sitemap_opts))
				{
				if (isset($this->sitemap_opts[$one_region]) && array_key_exists('timestamp', $this->sitemap_opts[$one_region]))
					$this->log(__FUNCTION__, __LINE__, '"timestamp" is not a key in sitemaps_opts['.$one_region.'] - we will build sitemap');
				else if (array_key_exists($one_region, $this->sitemap_opts))
					$this->log(__FUNCTION__, __LINE__, $one_region.' is not a key in sitemaps_opts - we will build sitemap');
				}
			else
				{
				$this->log(__FUNCTION__, __LINE__, 'sitemaps_opts is not an array - we will build sitemap');
				}
			}
	
		return $build_it;
		}

	private function fetch_sitemap_data($one_region)
		{
		$url								= ROVER_ENGINE_SSL.ROVER_VERSION.'/php/__json/_roverSitemap.php?region='.$one_region.'&domain='.get_site_url().'&page='.basename(__FILE__).'&format=json';

		$this->log(__FUNCTION__, __LINE__, 'url = '.$url);

		$ch									= curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_HEADER, false);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_HTTPHEADER, array ('Accept: application/json', 'Content-Type: application/json', 'Expect:'));

		$result								= curl_exec ($ch);		//	if the CURLOPT_RETURNTRANSFER option is set, it will return the result on success, FALSE on failure.
		if ($result === false)
			{
			$this->log(__FUNCTION__, __LINE__, 'curl_exec failed');
			return;
			}

		$this->log(__FUNCTION__, __LINE__, 'Raw output = '.number_format(strlen($result)).' bytes');

		curl_close ($ch);

		$result								= strip_cross_domain_parenthesis_from_JSON($result);

		$result_decoded						= json_decode($result, true);		

		if ($result_decoded === null)
			{
			$this->log(__FUNCTION__, __LINE__, 'xml did not decode correctly');
			$this->log(__FUNCTION__, __LINE__, $result);
			return false;
			}

		if (!is_array($result_decoded))
			{
			$this->log(__FUNCTION__, __LINE__, 'Output is not an array - aborting sitemap creation');
			return false;
			}
		else if (!array_key_exists('sitemapArr', $result_decoded))
			{
			$this->log(__FUNCTION__, __LINE__, 'is an array but does not contain key "sitemapArr" - aborting sitemap creation');
			return false;
			}
		else if (!is_array($result_decoded['sitemapArr']))
			{
			$this->log(__FUNCTION__, __LINE__, ' "sitemapArr" is not an array - aborting sitemap creation');
			return false;
			}

		return $result_decoded;
		}

	private function sitemap_file_write($sitemap_items)
		{
		
		$sitemap_begin						= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		$sitemap_end						= '</urlset>';

		if (!is_dir( $this->upload_dir ))
			mkdir( $this->upload_dir, 0755, true );

		//	Create sitemap

		$fp									= fopen( $this->upload_dir."/".$this->sitemap_file, "w+" );	//	Overwrite if exists
		$bytesWritten						= fwrite( $fp, $sitemap_begin . implode('', $sitemap_items) . $sitemap_end);
		fclose($fp);

		//	Create gz sitemap

		$data								= implode("", file($this->upload_dir."/".$this->sitemap_file));
		$gzdata								= gzencode($data, 9);

		$sitemap_url_gz						= $this->upload_url.'/'.$this->sitemap_file.'.gz';
		$sitemap_path_gz					= $this->upload_dir."/".$this->sitemap_file.".gz";

		$fp_gz								= fopen($sitemap_path_gz, "w");
		fwrite($fp_gz, $gzdata);
		fclose($fp_gz);

		return $sitemap_url_gz;
		}	

	function notify_search_engine($sitemap_url) {
	
		$curl_handle = curl_init();
		curl_setopt($curl_handle,CURLOPT_URL,$sitemap_url);
		curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
		curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
		$buffer = curl_exec($curl_handle);
		curl_close($curl_handle);
	
		if (empty($buffer))
			return false;
		
		return true;
		}

	private function log($func, $line, $str)	{

		$debug		= intval(@$_GET[ROVER_DEBUG_KEY]);

		if (!empty($debug) && $debug > 0)
			{
			error_log( 
				sprintf( '%1$s <strong>%2$s</strong> %3$s: %4$s\n', 
						basename(__FILE__),
						$func, 
						$line,
						$str));
			}
	
		}
	}

function roveridx_refresh_sitemap($force_refresh = false) {


	$roverSITEMAP		= new Rover_IDX_SITEMAP();

	$roverSITEMAP->build($force_refresh);

	}



?>