<?php

class Rover_IDX_Dashboard
	{

	public				$dyn_meta			= null;

	function __construct() {


		}

	public function dashboard_active_summary()	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global			$rover_idx_content;

		$rover_content	= $rover_idx_content->rover_content(
															'ROVER_COMPONENT_DASHBOARD_ACTIVE_LISTINGS', 
															array('not-region' => 'Not used', 'not-regions' => 'Not Used')
															);
		echo $rover_content['the_html'];

		}

	public function dashboard_activity()	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global			$rover_idx_content;

		$rover_content	= $rover_idx_content->rover_content(
															'ROVER_COMPONENT_DASHBOARD_ACTIVITY', 
															array('not-region' => 'Not used', 'not-regions' => 'Not Used')
															);
		echo $rover_content['the_html'];

		}

	public function dashboard_mail()	{

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global			$rover_idx_content;

		$rover_content	= $rover_idx_content->rover_content(
															'ROVER_COMPONENT_DASHBOARD_MAIL', 
															array('not-region' => 'Not used', 'not-regions' => 'Not Used')
															);
		echo $rover_content['the_html'];

		}
	}

global $rover_idx_dashboard;
$rover_idx_dashboard	= new Rover_IDX_Dashboard();
?>