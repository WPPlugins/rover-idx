<?php
class roveridx_ask_a_question extends WP_Widget 
	{
	function __construct() { 

		parent::__construct(false, 
							$name = 'Rover - Ask a Question',
							array(
								'description'	=> 'Present a dialog that allows a visitor to ask a question, which is emailed to the responsible agent'));

		}
	function form($instance) { 
		$instance					= wp_parse_args((array) $instance, 
													array(	'widget_title'				=> 'Ask a Question',
															'button_label'				=> 'Ask a Question',
															'wrapping_tag'				=> 'aside'));

		$widget_title				= $instance['widget_title'];
		$wrapping_tag				= $instance['wrapping_tag'];
		$button_label				= $instance['button_label'];
		$tags						= array('aside', 'div', 'section', 'p');
		?>
		<aside>
			<p><label for="<?php echo $this->get_field_id('widget_title'); ?>" style="width: 100%;">Title: <input class="widefat" id="<?php echo $this->get_field_id('widget_title'); ?>" name="<?php echo $this->get_field_name('widget_title'); ?>" type="text" value="<?php echo attribute_escape($widget_title); ?>" /></label></p>

			<p><label for="<?php echo $this->get_field_id('widget_title'); ?>">Button Label: <input class="widefat" id="<?php echo $this->get_field_id('button_label'); ?>" name="<?php echo $this->get_field_name('button_label'); ?>" type="text" value="<?php echo attribute_escape($button_label); ?>" /></label></p>
		</aside>
		<?php
		}
	function update($new_instance, $old_instance) {

		global 									$rover_idx_widgets;

		$instance								= array();
		$instance								= $rover_idx_widgets->rover_widget_update($new_instance);

		$instance['widget_title']				= strip_tags($new_instance['widget_title']);
		$instance['button_label']				= strip_tags($new_instance['button_label']);
		$instance['wrapping_tag']				= strip_tags($new_instance['wrapping_tag']);

		return $instance;
		}
	function widget($args, $instance) { 

		global $rover_idx_content;

		extract( $args );

		$widget_title				= @$instance['widget_title'];
		$button_label				= @$instance['button_label'];
//		$wrapping_tag				= @$instance['wrapping_tag'];
//		if (empty($wrapping_tag))
//			$wrapping_tag			= 'aside';

		echo $before_widget;
		echo 	$before_title.$widget_title.$after_title;
		echo 	'<input type="button" class="btn btn-default" onclick="roveridx.action(\'clientInterested\');" value="'.$button_label.'" />';
		echo $after_widget;
		}
	}
?>