<?php
class roveridx_mortgage_calc extends WP_Widget 
	{
	function __construct() {
		parent::__construct(false, 
							$name = 'Rover Mortgage Calculator',
							array(
								'description'	=> 'Add mortgage calculator to the sidebar'));

		}
	function form($instance) { 

		global						$rover_idx, $rover_idx_widgets;
		$instance					= wp_parse_args((array) $instance, 
													array(	'widget_title'				=> 'Mortgage Calculator',
															'wrapping_tag'				=> 'aside'));
		$widget_title				= $instance['widget_title'];
		$wrapping_tag				= $instance['wrapping_tag'];
		$mc_price					= $instance['mc_price'];
		if (empty($mc_price))
			$mc_price				= 200000;
		$mc_down					= $instance['mc_down'];
		if (empty($mc_down))
			$mc_down				= 10;
		$mc_rate_30					= $instance['mc_rate_30'];
		if (empty($mc_rate_30))
			$mc_rate_30				= 3;
		$mc_term					= $instance['mc_term'];
		if (empty($mc_term))
			$mc_term				= 30;

		?>
		<p><label for="<?php echo $this->get_field_id('widget_title'); ?>" style="width: 100%;">Title: <input class="widefat" id="<?php echo $this->get_field_id('widget_title'); ?>" name="<?php echo $this->get_field_name('widget_title'); ?>" type="text" value="<?php echo attribute_escape($widget_title); ?>" /></label></p>

		<p><label for="<?php echo $this->get_field_id('mc_price'); ?>">Default Price: <input class="" id="<?php echo $this->get_field_id('mc_price'); ?>" name="<?php echo $this->get_field_name('mc_price'); ?>" type="text" value="<?php echo attribute_escape($mc_price); ?>" /></label></p>

		<p><label for="<?php echo $this->get_field_id('mc_rate_30'); ?>">30 Year Fixed Rate (%): <input class="" id="<?php echo $this->get_field_id('mc_rate_30'); ?>" name="<?php echo $this->get_field_name('mc_rate_30'); ?>" type="text" value="<?php echo attribute_escape($mc_rate_30); ?>" /></label></p>

		<p><label for="<?php echo $this->get_field_id('mc_down'); ?>">Down Payment (%): <input class="" id="<?php echo $this->get_field_id('mc_down'); ?>" name="<?php echo $this->get_field_name('mc_down'); ?>" type="text" value="<?php echo attribute_escape($mc_down); ?>" /></label></p>

		<p><label for="<?php echo $this->get_field_id('mc_term'); ?>">Term: <input class="" id="<?php echo $this->get_field_id('mc_term'); ?>" name="<?php echo $this->get_field_name('mc_term'); ?>" type="text" value="<?php echo attribute_escape($mc_term); ?>" /></label></p>

		<?php
			$rover_idx_widgets->widget_page_display_options(
													$instance,
													$this
													);
		?>
		<?php
		}
	function update($new_instance, $old_instance) { 
		$instance								= array();

		global 									$rover_idx_widgets;
		$instance								= $rover_idx_widgets->rover_widget_update($new_instance);

		$instance['widget_title']				= strip_tags($new_instance['widget_title']);
		$instance['wrapping_tag']				= strip_tags($new_instance['wrapping_tag']);
		$instance['mc_price']					= strip_tags($new_instance['mc_price']);
		$instance['mc_down']					= strip_tags($new_instance['mc_down']);
		$instance['mc_rate_30']					= strip_tags($new_instance['mc_rate_30']);
		$instance['mc_term']					= strip_tags($new_instance['mc_term']);

		return $instance;
		}

	function widget($args, $instance) { 

		global									$rover_idx_widgets;

		extract( $args );

		$widget_title							= @$instance['widget_title'];

		$mc_price								= @$instance['mc_price'];
		if (empty($mc_price))
			$mc_price							= 200000;

		$mc_down								= @$instance['mc_down'];
		if (empty($mc_down))
			$mc_down							= 10;

		$mc_rate_30								= @$instance['mc_rate_30'];
		if (empty($mc_rate_30))
			$mc_rate_30							= 3.0;

		$mc_term								= @$instance['mc_term'];
		if (empty($mc_term))
			$mc_term							= 30;

		$content_settings						= array_merge(
													$rover_idx_widgets->standard_widget_fields_for_rover($instance),
													array(
														'ListingPrice'				=> $mc_price,
														'mortgage_down'				=> $mc_down,
														'mortgage_rate'				=> $mc_rate_30,
														'mortgage_term'				=> $mc_term
														));

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global									$rover_idx_content;

		$the_rover_content						= $rover_idx_content->rover_content(	
														'ROVER_COMPONENT_MORTGAGE_CALCULATOR', 
														$content_settings);

		echo $before_widget;
		echo 	$before_title.$widget_title.$after_title;
		echo 	$the_rover_content['the_html'];
		echo $after_widget;
		}
	}
?>