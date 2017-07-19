<?php
class roveridx_search_mlnumber extends WP_Widget 
	{
	function __construct() { 

		parent::__construct(false, 
							$name = 'Rover - Search By MLS Number',
							array(
								'description'	=> 'Add simple MLNumber search to the sidebar.  As you type the MLNumber, a dropdown will show matching \'live\' mlnumbers.  Pressing the Search button will redirect to the matching property page'));

		}
	function form($instance) { 
		global						$rover_idx, $rover_idx_widgets;

		$instance					= wp_parse_args((array) $instance, 
													array(	'widget_title'				=> 'Search by MLS Number',
															'mlnumber_label'			=> 'MLS Number', 
															'mlnumber_label_position'	=> 'left',
															'button_style'				=> 'style_rover',
															'wrapping_tag'				=> 'aside'));
		$widget_title				= $instance['widget_title'];
		$region						= $instance['region'];
		$mlnumber_label				= $instance['mlnumber_label'];
		$mlnumber_label_position	= $instance['mlnumber_label_position'];
		$search_orientation			= $instance['search_panel_orientation'];
		$button_style				= $instance['button_style'];
		$wrapping_tag				= $instance['wrapping_tag'];

		?>
		<aside>
			<p><label for="<?php echo $this->get_field_id('widget_title'); ?>" style="width: 100%;">Title: <input class="widefat" id="<?php echo $this->get_field_id('widget_title'); ?>" name="<?php echo $this->get_field_name('widget_title'); ?>" type="text" value="<?php echo attribute_escape($widget_title); ?>" /></label></p>

			<?php
				if (count($rover_idx->all_selected_regions) > 1)
					{
					$rover_idx_widgets->add_regions_selector(
														$this->get_field_id('region'), 
														$this->get_field_name('region'),
														$region);
					}
			?>

			<p><label for="<?php echo $this->get_field_id('mlnumber_label'); ?>">Text Label: <input class="widefat" id="<?php echo $this->get_field_id('mlnumber_label'); ?>" name="<?php echo $this->get_field_name('mlnumber_label'); ?>" type="text" value="<?php echo attribute_escape($mlnumber_label); ?>" /></label></p>

			<p><label>Text Position:</label>
				<select id="<?php echo $this->get_field_id('mlnumber_label_position'); ?>" name="<?php echo $this->get_field_name('mlnumber_label_position'); ?>">
					<option value="left"  <?php if ($mlnumber_label_position == 'left')  echo 'selected="selected"';?>>Left</option>
					<option value="right" <?php if ($mlnumber_label_position == 'right') echo 'selected="selected"';?>>Right</option>
				</select>
			</p>

			<p><label>Orientation:</label><br />
				<label for="<?php echo $this->get_field_id('search_panel_orientation'); ?>">
					<input id="<?php echo $this->get_field_id('search_panel_orientation'); ?>" name="<?php echo $this->get_field_name('search_panel_orientation'); ?>" type="radio" value="vertical" <?php if ($search_orientation != 'horizontal'){ echo 'checked="checked"'; } ?> />
					<?php _e('Vertical'); ?>
				</label>
				<label for="<?php echo $this->get_field_id('search_panel_orientation'); ?>">
					<input id="<?php echo $this->get_field_id('search_panel_orientation'); ?>" name="<?php echo $this->get_field_name('search_panel_orientation'); ?>" type="radio" value="horizontal" <?php if ($search_orientation == 'horizontal'){ echo 'checked="checked"'; } ?> />
					<?php _e('Horizontal'); ?>
				</label>
			</p>

			<p><label>Search Button style:</label><br />
				<label for="<?php echo $this->get_field_id('button_style'); ?>">
					<input id="<?php echo $this->get_field_id('button_style'); ?>" name="<?php echo $this->get_field_name('button_style'); ?>" type="radio" value="style_rover" <?php if ($button_style == 'style_rover'){ echo 'checked="checked"'; } ?> />
					<?php _e('Rover-ized'); ?>
				</label>
				<label for="<?php echo $this->get_field_id('button_style'); ?>">
					<input id="<?php echo $this->get_field_id('button_style'); ?>" name="<?php echo $this->get_field_name('button_style'); ?>" type="radio" value="style_native" <?php if ($button_style == 'style_native'){ echo 'checked="checked"'; } ?> />
					<?php _e('Inherit from theme'); ?>
				</label>
			</p>
			<?php
//				$rover_idx_widgets->add_tag_selector(
//														$this->get_field_id('wrapping_tag'), 
//														$this->get_field_name('wrapping_tag'),
//														$wrapping_tag);
			?>
			<?php
				$rover_idx_widgets->widget_page_display_options(
														$instance,
														$this
														);
			?>
		</aside>
		<?php
		}
	function update($new_instance, $old_instance) {

		global 									$rover_idx_widgets;
		$instance								= $rover_idx_widgets->rover_widget_update($new_instance);

		//	Add items specific to this widget here

		$instance['mlnumber_label']				= strip_tags($new_instance['mlnumber_label']);
		$instance['mlnumber_label_position']	= strip_tags($new_instance['mlnumber_label_position']);
		$instance['search_panel_orientation']	= strip_tags($new_instance['search_panel_orientation']);
		$instance['button_style']				= strip_tags($new_instance['button_style']);

		return $instance;
		}
	function widget($args, $instance) { 

		global $rover_idx_widgets;

		if ($rover_idx_widgets->display_widget_on_this_page($instance))
			{
			extract( $args );

			$widget_title				= @$instance['widget_title'];
			$mlnumber_label				= @$instance['mlnumber_label'];
			$mlnumber_label_position	= @$instance['mlnumber_label_position'];
			$button_style				= @$instance['button_style'];
			$search_orientation			= @$instance['search_panel_orientation'];
			$wrapping_tag				= @$instance['wrapping_tag'];
	
			$content_settings			= array_merge(
											$rover_idx_widgets->standard_widget_fields_for_rover($instance),
											array(
												'search_panel_layout'		=> 'custom', 
												'template_fields'			=> 'buildMLNumber', 
												'search_control_no_style'	=> 'true',
												'hide_clear'				=> 'true',
												'mlnumber_label'			=> $mlnumber_label, 
												'mlnumber_label_position'	=> $mlnumber_label_position,
												'search_panel_orientation'	=> (empty($search_orientation)) ? 'vertical' : $search_orientation,
												'button_style'				=> $button_style,
												));

			require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

			global						$rover_idx_content;

			$the_rover_content			= $rover_idx_content->rover_content(	
												'ROVER_COMPONENT_SEARCH_PANEL', 
												$content_settings);

//			echo '<'.$wrapping_tag.' class="widget widget_rover_search_mlnumber"><h3 class="widget-title">'.$widget_title.'</h3>'.$the_rover_content['the_html'].'</'.$wrapping_tag.'>';
			echo $before_widget;
			echo 	$before_title.$widget_title.$after_title;
			echo 	$the_rover_content['the_html'];
			echo $after_widget;
			}
		}
	}
?>