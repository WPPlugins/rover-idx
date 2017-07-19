<?php

require_once ROVER_IDX_PLUGIN_PATH.'admin/rover-templates.php';

// Render the Plugin options form
function roveridx_panel_help_form() {

	$theHTML 	=		roveridx_panel_header('Help');

	$theHTML	.=		'<div style="min-height:800px;">';
	$theHTML	.=			'<iframe src="https://roveridx.com/rover-shortcodes/#main" style="width:100%;height:800px;"></iframe>';
	$theHTML	.=		'</div>';

	$theHTML 	.=		roveridx_panel_footer('help');

	echo $theHTML;
	}
?>