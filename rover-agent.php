<?php

require_once 'rover-common.php';
require_once 'rover-sitemap.php';



//	Do the work

$task						= sanitize_text_field( $_POST['task'] );
$sitemapXML 				= sanitize_text_field( $_POST['xmlText'] );
$finishSitemapXML			= rover_idx_validate_post_bool( $_POST['finishSitemapXML'] );
$upload_dir 				= sanitize_text_field( $_POST['dir'] );
$upload_url					= sanitize_text_field( $_POST['url'] );

$sitemapFile				= "rover_sitemap_".sanitize_text_field( $_POST['region'] ).".xml";

$upload_dir 				.= SITEMAP_DIR;
$upload_url					.= SITEMAP_DIR;

rover_error_agent_log(__FILE__, __FUNCTION__, __LINE__, "roverAgent: ".$task);
rover_error_agent_log(__FILE__, __FUNCTION__, __LINE__, "upload dir is ".$upload_dir);
rover_error_agent_log(__FILE__, __FUNCTION__, __LINE__, "upload url is ".$upload_url);

switch ($task)
	{
	case 'buildSitemapStart':
		$bytesWritten		= startRoverSitemap($upload_dir, $sitemapFile);
		$returnStr			= $bytesWritten." bytes written for ".$sitemapFile;
		break;
	case 'buildSitemap':
		$bytesWritten		= addEntryToRoverSitemap($upload_dir, $upload_url, $sitemapFile, $sitemapXML, $finishSitemapXML);		
		$returnStr			= $bytesWritten." bytes written for ".$sitemapFile;
		break;
	default:
		$returnStr			= '"'.$task.'" is an invalid task!';
		rover_error_agent_log(__FILE__, __FUNCTION__, __LINE__, $returnStr);
		break;
	}





$theResult = array(	"retString"		=> utf8_encode($returnStr),
					"sitemap_file"	=> $sitemapFile);

header("Content-Type: application/javascript");	
echo json_encode($theResult);



function rover_error_agent_log($file, $func, $line, $str)	{
	$debug		= intval($_GET[ROVER_DEBUG_KEY]);
	
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
?>