<?php
class roveridx_new_and_updated extends WP_Widget 
	{
	public	$all_search_fields		= null;
	
	function __construct() {
											
		parent::__construct(false, 
							$name = 'Rover - New and Updated',
							array(
								'description'	=> 'Display properties new or updated in previous n days'));

		}
	function form($instance) { 

		global						$rover_idx, $rover_idx_widgets;

		$instance					= wp_parse_args((array) $instance, 
													array(	'widget_title'					=> 'New and Updated',
															'prev_days'						=> 7,
															'plugin_limit'					=> 30,
															'thumb_width'					=> 50
															));

		$wrapping_tag				= $instance['wrapping_tag'];

		$widget_title				= $instance['widget_title'];
		$region						= $instance['region'];
		$plugin_height				= $instance['plugin_height'];
		$plugin_limit				= $instance['plugin_limit'];
		$all_cities					= $instance['all_cities'];
		$prev_days					= $instance['prev_days'];
		$thumb_width				= $instance['thumb_width'];

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

			<p><label for="<?php echo $this->get_field_id('all_cities'); ?>" style="width: 100%;">Cities/Towns: <input class="widefat" id="<?php echo $this->get_field_id('all_cities'); ?>" name="<?php echo $this->get_field_name('all_cities'); ?>" type="text" value="<?php echo attribute_escape($all_cities); ?>" /></label></p>

			<p><label>Updated in previous:
				<select id="<?php echo $this->get_field_id('prev_days'); ?>" name="<?php echo $this->get_field_name('prev_days'); ?>">
					<?php for ($n = 1; $n <=31; $n++)
						echo	'<option value="'.$n.'" '.(($prev_days == $n) ? 'selected="selected"' : '').'>'.$n.'</option>';
					?>
				</select> days</label>
			</p>

			<p><label for="<?php echo $this->get_field_id('plugin_limit'); ?>" style="width: 100%;">Maximum Properties: <input class="" id="<?php echo $this->get_field_id('plugin_limit'); ?>" name="<?php echo $this->get_field_name('plugin_limit'); ?>" type="text" value="<?php echo attribute_escape($plugin_limit); ?>" /></label></p>

			<p><label for="<?php echo $this->get_field_id('plugin_height'); ?>" style="width: 100%;">Widget height (px or %): <input class="" id="<?php echo $this->get_field_id('plugin_height'); ?>" name="<?php echo $this->get_field_name('plugin_height'); ?>" type="text" value="<?php echo attribute_escape($plugin_height); ?>" /></label></p>

			<p><label for="<?php echo $this->get_field_id('thumb_width'); ?>" style="width: 100%;">Initial Image Width: <input class="" id="<?php echo $this->get_field_id('thumb_width'); ?>" name="<?php echo $this->get_field_name('thumb_width'); ?>" type="text" value="<?php echo attribute_escape($thumb_width); ?>" /></label></p>

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

		$all_cities									= array();
		foreach (explode(',', $new_instance['all_cities']) as $one_city)
			$all_cities[]							= trim($one_city);

		$instance['all_cities']						= strip_tags(implode(',', $all_cities));
		$instance['plugin_limit']					= $new_instance['plugin_limit'];

		return $instance;
		}
	function widget($args, $instance) { 

		global $rover_idx_widgets;

		if ($rover_idx_widgets->display_widget_on_this_page($instance))
			{
			extract( $args );

			$widget_title					= @$instance['widget_title'];
			$all_cities						= @$instance['all_cities'];
			$prev_days						= @$instance['prev_days'];
			$plugin_limit					= @$instance['plugin_limit'];
			$widget_title					= (empty($prev_days))
													? $widget_title
													: $widget_title . ' - Last ' . $prev_days . ' days';
//			$wrapping_tag					= @$instance['wrapping_tag'];
	
			$content_settings				= array_merge(
												$rover_idx_widgets->standard_widget_fields_for_rover($instance),
												array(
													'plugin_type'					=> 'newAndUpdated', 
													'plugin_limit'					=> $plugin_limit, 
													'cities'						=> $all_cities
													));

			require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

			global							$rover_idx_content;

			$the_rover_content				= $rover_idx_content->rover_content(	
													'ROVER_COMPONENT_PLUGIN', 
													$content_settings);

//			echo '<'.$wrapping_tag.' class="widget widget_rover_new_and_updated"><h3 class="widget-title">'.$widget_title.'</h3>'.$the_rover_content['the_html'].'</'.$wrapping_tag.'>';
			echo $before_widget;
			echo 	$before_title.$widget_title.$after_title;
			echo 	$the_rover_content['the_html'];
			echo $after_widget;
			}
		}
	}
?>