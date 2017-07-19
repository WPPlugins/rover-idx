<?php

require_once ROVER_IDX_PLUGIN_PATH.'admin/rover-templates.php';

// Render the Plugin options form
function roveridx_panel_upgrade_form() {

	require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

	global				$rover_idx_content;

	$latest_ver			=	roveridx_latest_version();
	$button_label		=	'Upgrade';
	$show_button		=	true;
	$regions_array		=	rover_get_selected_regions();

	$the_html			=	array();
	$the_html[]			=	'<div class="wrap '.esc_attr( rover_plugins_identifier() ).'">';
	$the_html[]			=		roveridx_panel_header('Upgrade');

	$rover_content		=		$rover_idx_content->rover_content(	'ROVER_COMPONENT_WP_UPGRADE_PANEL', 
															array(
																'region'	=> $regions_array[0], 
																'regions'	=> implode(',',$regions_array)
																)
															);
	$the_html[]			=		$rover_content['the_html'];

	$the_html[]			=		'<div id="rover-upgrade-panel-content" style="display:none;">';

	$ret				=			version_is_up_to_date($latest_ver);
	if ($ret === true)
		{
		$the_html[]		=			'<h4>Congratulations - you are running the newest Rover IDX!</h4>';
		$button_label	= 			'Reinstall '.$latest_ver;
		}
	else if ($ret == -1)	//	running older version
		{
		$the_html[]		=			'<h4>Rover IDX version <span style="font-size:1.2em;">'.$latest_ver.'</span> is available.  Would you like to upgrade?</h4>';
		$button_label	=			'Upgrade to '.$latest_ver;
		}
	else
		{
		$the_html[]		=			'<h4>You are running Rover IDX version '.ROVER_VERSION_FULL.', which is newer than the latest publicly available version '.$latest_ver.'.</h4>';
		$show_button	=			false;
		}

	if (class_exists('ZipArchive'))
		{
		if ($show_button)
			{
			$the_html[]	=			'<p class="submit">';
			$the_html[]	=				'<a href="javascript:roveridx_upgrade_plugin(\''.wp_create_nonce(ROVERIDX_NONCE).'\')" class="button upgrade">'.__($button_label, 'rover-idx' ).'</a> 
										<div><span class="rover-msg-icon" style="display:none;"><i class="fa fa-refresh fa-spin"></i></span><span class="rover-msg-text"></span></div>
										<br />
										<div class="rover-log" style="border:1px solid #ccc;font: 12px courier;max-height:400px;overflow-y:auto;padding:5px;"></div>
									</p>';
			}
		}
	else
		{
		$the_html[]		=			'<p style="color:red">';
		$the_html[]		=				'<strong>ZipArchive</strong> is not installed on this server.  Upgrades will need to be performed via FTP.  Here is the latest <b>Rover IDX for Wordpress</b> plugin at <a href="http://c.roveridx.com/latest/'.ROVER_VERSION.'/rover-idx.zip">rover-idx.zip</a><br />';
		$the_html[]		=			'</p>';
		}


	$the_html[]			=			roveridx_panel_footer($panel = 'upgrade');
	$the_html[]			=		'</div>';
	$the_html[]			=	'</div>';

	$the_html[]			=	'<script type="text/javascript">

								jQuery(document).ready(function($){

									$("div.panel-body div.col-md-12").html( $("#rover-upgrade-panel-content").html() );

							 		function roveridx_upgrade_plugin( nonce ) {
							 			$.post( ajaxurl, {
							 				action: \'rover_idx_upgrade\',
							 				security: nonce
							 				}, function(data) {
							 	
							 					if (data.ret == true) 
							 						$(\'.rover-msg-text\').addClass(\'jquerygreen\').html(\'The Rover IDX plugin has been upgraded!\');
							 					else 
							 						$(\'.rover-msg-text\').addClass(\'red\').html(data.ret);
							 	
							 					$(\'.rover-log\').html(data.log);
							 	
							 					}, "json");
							 			}

						 			});

						 	</script>';

	echo implode('', $the_html);
	}

function rover_idx_upgrade_callback() {

	check_ajax_referer(ROVERIDX_NONCE, 'security');

	$roverUpgrade 	= new RoverIDXUpgrade;
	$r				= $roverUpgrade->do_upgrade();

	$responseVar	= array(
							'ret'		=> $r,
							'log'		=> $roverUpgrade->log_get()
							);

	echo json_encode($responseVar);
	
	die();
	}

add_action('wp_ajax_rover_idx_upgrade', 'rover_idx_upgrade_callback');

function roveridx_latest_version()
	{
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, ROVER_ENGINE_SSL.'latest/ver.txt');
	curl_setopt ($ch, CURLOPT_HEADER, 0);

	ob_start();

	curl_exec ($ch);
	curl_close ($ch);
	$latest_ver_string = ob_get_contents();

	ob_end_clean();
	
	return $latest_ver_string;
	}

function version_is_up_to_date($latest_ver)
	{
	error_log( sprintf( '%1$s: %2$s %3$s %4$s: %5$s\r\n', date('Y-m-d H:i:s'), basename(__FILE__), __FUNCTION__, __LINE__, 'Comparing '.ROVER_VERSION_FULL.' with '.$latest_ver));

	$ret = version_compare(ROVER_VERSION_FULL, $latest_ver);

	if ($ret == 0)
		return true;

	return $ret;
	}




class RoverIDXUpgrade {

	private static $roveridx_plugin_name	= 'Rover IDX';
	private static $roveridx_plugin_dir		= 'rover-idx';
	private static $roveridx_plugin_id		= 'rover-idx/roveridx.php';
	private static $roveridx_plugin_files	= array();
	private static $roveridx_plugin_error	= null;
	private static $roveridx_plugin_log		= array();


	public static function show_success_message() {
		echo '<div class="updated fade"><p><strong>The Rover IDX plugin has been upgraded. <a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=rover-panel-setup.php">Get started</a> now!</strong></p></div>';
	}

	public static function show_error_message() {
		echo '<div class="error"><p>'.self::$roveridx_plugin_error.'</p></div>';
	}

	public static function set_error($str) {
		self::$roveridx_plugin_error	= $str;
		return self::$roveridx_plugin_error;
	}

	public static function log_add($str) {
		self::$roveridx_plugin_log[]	= $str;
	}

	public static function log_get() {
		return implode('<br>', self::$roveridx_plugin_log);
	}

	public static function do_upgrade() {

		if (!is_plugin_active(self::$roveridx_plugin_id) )			//	This pretty much must be true for us to be here
			{
			self::log_add( '<span style="color:red;">'.self::$roveridx_plugin_name.' is not active</span>');
			return self::set_error(self::$roveridx_plugin_name.' is not active');
			}

		if (!is_writable(ROVER_IDX_PLUGIN_PATH) ) 
			return self::set_error(self::$roveridx_plugin_name.' '.ROVER_IDX_PLUGIN_PATH.' is not writable');

		if ( !class_exists('ZipArchive') )
			return self::set_error('ZipArchive is not installed on this server');

		//	De-activate the plugin we are about to upgrade

		$network_wide = is_plugin_active_for_network( self::$roveridx_plugin_id );
		do_action( 'deactivate_plugin', self::$roveridx_plugin_id, $network_wide );

		if (!is_plugin_active(self::$roveridx_plugin_id) )			//	This pretty much must be true for us to be here
			self::log_add( '<span style="color:green;">'.self::$roveridx_plugin_name.' has been deactivated</span>');

		//	Move current plugin to /archive folder

		self::roveridx_move_to_archive(ROVER_IDX_PLUGIN_PATH);

		//	Grab the updated plugin, and unzip it into the correct folder

		$latest_ver	= (version_is_up_to_date(roveridx_latest_version()) > 0)	//	running a newer-than-released version
							? ROVER_VERSION_FULL
							: roveridx_latest_version();

		$zip_loc	= dirname(ROVER_IDX_PLUGIN_PATH) . "/rover-idx.zip";
		$zip_url	= ROVER_ENGINE_SSL.'latest/'.$latest_ver.'/rover-idx.zip';

		error_log( sprintf( '%1$s: %2$s %3$s %4$s: %5$s\r\n', date('Y-m-d H:i:s'), basename(__FILE__), __FUNCTION__, __LINE__, 'Fetching '.$zip_url));

		$ch			= curl_init($zip_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$fileData	= curl_exec($ch);
		file_put_contents($zip_loc, $fileData);
		curl_close ($ch);



		/****************************/
		/*	This is the real work	*/
		/****************************/

		$r			= self::roveridx_unpack_upgrade($zip_loc);
		if ($r !== true)
			return self::$roveridx_plugin_error;

		//	Re-activate the plugin

		do_action( 'activate_plugin', self::$roveridx_plugin_id, $network_wide );
		if (did_action( 'activate_plugin' ))
			{
			self::log_add( '<span style="color:green;">'.self::$roveridx_plugin_name.' has been re-activated</span>');
			return true;
			}
		else
			{
			return self::set_error(self::$roveridx_plugin_name.' did not restart');
			}


	return self::set_error(self::$roveridx_plugin_name.' - Unknown error');
	}

	private function roveridx_move_to_archive($dir)	{

		$upload_dir			= wp_upload_dir();
		$archive_folder		= $upload_dir['path'] . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR . 'rover-idx' . roveridx_get_version() . DIRECTORY_SEPARATOR;

		self::log_add( 'Archiving previous version of plugin to <strong>'.$archive_folder.'</strong>');

		self::roveridx_scan_plugin_files($dir);

		foreach (self::$roveridx_plugin_files as $one_file)
			{
			$one_file_with_archive_path		= str_replace(	ROVER_IDX_PLUGIN_PATH, 
															$archive_folder, 
															$one_file);

//			error_log( sprintf( '%1$s: %2$s %3$s %4$s: %5$s\r\n', date('Y-m-d H:i:s'), basename(__FILE__), __FUNCTION__, __LINE__, $one_file_with_archive_path));

			$the_dir		= dirname($one_file_with_archive_path);
			if (!file_exists($the_dir))
				mkdir($the_dir, 0755, $recursive = true);

			copy($one_file, $one_file_with_archive_path);
			}
	}

	private function roveridx_scan_plugin_files($dir)	{
		$directoryHandle	= opendir($dir);
		$archive_tag		= 'archive';
		$archive_len		= strlen($archive_tag);

		while ($file = readdir($directoryHandle)) 
			{ 
			if ($file != '.' && $file != '..') 
				{
				//	Guarantee that each path we save ends with a slash

				if (substr($dir, -1) == DIRECTORY_SEPARATOR)
					$path = $dir . $file; 
				else
					$path = $dir . "/" . $file; 

				if (substr($file, 0, $archive_len) != $archive_tag)
					{
					if (is_dir($path)) 
						{
						self::roveridx_scan_plugin_files($path);
						}
					else
						{
						self::$roveridx_plugin_files[] = $path;
						}
					}
				}
			}

		closedir($directoryHandle);
	}

	private function roveridx_unpack_upgrade($zip_loc)	{

		$macosx_tag						= '__MACOSX';

		if (!file_exists($zip_loc))
			return self::set_error('The downloaded plugin package cannot be located');
			
		$zip							= new ZipArchive;
		$s								= $zip->open($zip_loc);
		error_log( sprintf( '%1$s: %2$s %3$s %4$s: %5$s\r\n', date('Y-m-d H:i:s'), basename(__FILE__), __FUNCTION__, __LINE__, 'Opening '.$zip_loc));
		if ($s !== true)
			return self::set_error('Could not open ' . $zip_loc . ' ['.$s.'] ' . self::zip_error($s) );

		$file_list_minus_macosx	= array();
		for ( $i = 0; $i < $zip->numFiles; $i++ )
			{ 
			$stat						= $zip->statIndex( $i );

			$file_list_minus_macosx[]	= str_replace($macosx_tag, "", $stat['name']);
			error_log( sprintf( '%1$s: %2$s %3$s %4$s: %5$s\r\n', date('Y-m-d H:i:s'), basename(__FILE__), __FUNCTION__, __LINE__, $stat['name']));
			}

		error_log( sprintf( '%1$s: %2$s %3$s %4$s: %5$s\r\n', date('Y-m-d H:i:s'), basename(__FILE__), __FUNCTION__, __LINE__, 'Removing '.ROVER_IDX_PLUGIN_PATH));
		self::log_add( '<span style="color:gray;">Removing '.ROVER_IDX_PLUGIN_PATH.'</span>');
		self::rrmdir(ROVER_IDX_PLUGIN_PATH);

		$plugin_dir						= dirname(ROVER_IDX_PLUGIN_PATH) . DIRECTORY_SEPARATOR;
		error_log( sprintf( '%1$s: %2$s %3$s %4$s: %5$s\r\n', date('Y-m-d H:i:s'), basename(__FILE__), __FUNCTION__, __LINE__, 'Extracting to '.$plugin_dir.' ['.count($file_list_minus_macosx).' files]'));

		self::log_add( '<span style="color:blue;">Extracting ['.number_format(count($file_list_minus_macosx)).'] files to '.$plugin_dir.'</span>');

//		$r = $zip->extractTo($plugin_dir, $file_list_minus_macosx);
		$extraction_errors				= 0;
		for($i = 0; $i < count($file_list_minus_macosx); $i++) {
			$r							= $zip->extractTo($plugin_dir, array($file_list_minus_macosx[$i]));

			if ($r === false)
				self::log_add( '<span style="color:red;">Extraction of '.$file_list_minus_macosx[$i].' failed</span>');
			else
				self::log_add( '<span style="color:gray;">Extracted '.$file_list_minus_macosx[$i].'</span>');
			}

		$zip->close();

		if ($extraction_errors === 0)
			self::log_add( '<span style="color:green;">Extraction complete</span>');
		else
			return self::set_error('The ZIP extraction failed');

		error_log( sprintf( '%1$s: %2$s %3$s %4$s: %5$s\r\n', date('Y-m-d H:i:s'), basename(__FILE__), __FUNCTION__, __LINE__, 'Deleting '.$zip_loc));
		unlink($zip_loc);

		return true;
	}

	public function zip_error($code)
		{
		switch ($code)
			{
			case 0:
			return 'No error';

			case 1:
			return 'Multi-disk zip archives not supported';

			case 2:
			return 'Renaming temporary file failed';

			case 3:
			return 'Closing zip archive failed';

			case 4:
			return 'Seek error';

			case 5:
			return 'Read error';

			case 6:
			return 'Write error';

			case 7:
			return 'CRC error';

			case 8:
			return 'Containing zip archive was closed';

			case 9:
			return 'No such file';

			case 10:
			return 'File already exists';

			case 11:
			return 'Can\'t open file';

			case 12:
			return 'Failure to create temporary file';

			case 13:
			return 'Zlib error';

			case 14:
			return 'Malloc failure';

			case 15:
			return 'Entry has been changed';

			case 16:
			return 'Compression method not supported';

			case 17:
			return 'Premature EOF';

			case 18:
			return 'Invalid argument';

			case 19:
			return 'Not a zip archive';

			case 20:
			return 'Internal error';

			case 21:
			return 'Zip archive inconsistent';

			case 22:
			return 'Can\'t remove file';

			case 23:
			return 'Entry has been deleted';

			default:
			return 'An unknown error has occurred('.intval($code).')';
			}
		}

	private function rrmdir($dir) {

 		foreach ( glob($dir . '/*') as $file ) {
			if ( is_dir($file) )
				{
				self::rrmdir($file);
				}
			else
				{
				if (unlink($file) === false)
					self::log_add( 'Unable to delete '.$file.']');
				}
			}

	if (rmdir($dir) === false)
		self::log_add( 'Unable to remove directory '.$file.']');
	}

}


//register_activation_hook(__FILE__, array('RoverIDXUpgrade', 'init'));	
?>