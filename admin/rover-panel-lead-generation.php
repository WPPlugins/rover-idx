<?php


function roveridx_panel_lead_form($atts) {
	
	?>		
	<div class="wrap <?php echo esc_attr( rover_plugins_identifier() ); ?>" data-page="rover-panel-lead-generation">

		<?php echo roveridx_panel_header('Lead Generation');	?>

		<?php roveridx_create_lead_general_panel($regions_array);	?>

		<?php echo roveridx_panel_footer($panel = 'lead_generation');	?>

	</div><!-- wrap	-->

	<?php
	}


function roveridx_create_lead_general_panel()	{

	require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

	global $rover_idx_content;

	$rover_content	=		$rover_idx_content->rover_content(	'ROVER_COMPONENT_WP_LEAD_GENERATION_PANEL', 
														array('not-region' => 'Not used', 'not-regions' => 'Not Used')
														);
	echo $rover_content['the_html'];
	}




?>