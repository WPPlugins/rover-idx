<?php
class roveridx_quick_links extends WP_Widget 
	{
	public	$all_search_fields		= null;
	
	function __construct() {
											
		parent::__construct(false, 
							$name = 'Rover - Quick Links',
							array(
								'description'	=> 'Display links for towns and neighborhoods'));

		}
	function form($instance) { 

		global						$rover_idx, $rover_idx_widgets;

		$instance					= wp_parse_args((array) $instance, 
													array(	'widget_title'					=> 'Quick Links',
															'quick_search_include_areas'	=> 'false',
															'quick_search_include_counts'	=> 'false'));

		$wrapping_tag				= $instance['wrapping_tag'];

		$widget_title				= $instance['widget_title'];
		$region						= $instance['region'];
		$plugin_height				= $instance['plugin_height'];
		$cities						= $instance['cities'];
		$quick_search_include_areas	= $instance['quick_search_include_areas'];
		$quick_search_include_counts= $instance['quick_search_include_counts'];
		$quick_search_links_per_row	= $instance['quick_search_links_per_row'];

		?>
		<aside>
			<p>
				<label for="<?php echo $this->get_field_id('widget_title'); ?>" style="width: 100%;">Title: <br />
					<input class="widefat" id="<?php echo $this->get_field_id('widget_title'); ?>" name="<?php echo $this->get_field_name('widget_title'); ?>" type="text" value="<?php echo attribute_escape($widget_title); ?>" />
				</label>
			</p>

			<?php
				if (count($rover_idx->all_selected_regions) > 1)
					{
					$rover_idx_widgets->add_regions_selector(
														$this->get_field_id('region'), 
														$this->get_field_name('region'),
														$region);
					}
			?>

			<p><label for="<?php echo $this->get_field_id('cities'); ?>" style="width: 100%;">
					Cities/Towns: 
					<input class="widefat" id="<?php echo $this->get_field_id('cities'); ?>" name="<?php echo $this->get_field_name('cities'); ?>" type="text" value="<?php echo attribute_escape($cities); ?>" />
				</label>
			</p>

			<p><label>Include Neighborhoods for Specified Cities:</label><br />
				<label for="<?php echo $this->get_field_id('quick_search_include_areas'); ?>">
					<input id="<?php echo $this->get_field_id('quick_search_include_areas'); ?>" name="<?php echo $this->get_field_name('quick_search_include_areas'); ?>" type="radio" value="false" <?php if ($quick_search_include_areas != 'true'){ echo 'checked="checked"'; } ?> />
					<?php _e('No'); ?>
				</label>
				<label for="<?php echo $this->get_field_id('quick_search_include_areas'); ?>">
					<input id="<?php echo $this->get_field_id('quick_search_include_areas'); ?>" name="<?php echo $this->get_field_name('quick_search_include_areas'); ?>" type="radio" value="true" <?php if ($quick_search_include_areas == 'true'){ echo 'checked="checked"'; } ?> />
					<?php _e('Yes'); ?>
				</label>
			</p>

			<p><label>Include Active Listing Count:</label><br />
				<label for="<?php echo $this->get_field_id('quick_search_include_counts'); ?>">
					<input id="<?php echo $this->get_field_id('quick_search_include_counts'); ?>" name="<?php echo $this->get_field_name('quick_search_include_counts'); ?>" type="radio" value="false" <?php if ($quick_search_include_counts != 'true'){ echo 'checked="checked"'; } ?> />
					<?php _e('No'); ?>
				</label>
				<label for="<?php echo $this->get_field_id('quick_search_include_counts'); ?>">
					<input id="<?php echo $this->get_field_id('quick_search_include_counts'); ?>" name="<?php echo $this->get_field_name('quick_search_include_counts'); ?>" type="radio" value="true" <?php if ($quick_search_include_counts == 'true'){ echo 'checked="checked"'; } ?> />
					<?php _e('Yes'); ?>
				</label>
			</p>

			<p><label>Links per row:</label>
				<label for="<?php echo $this->get_field_id('quick_search_links_per_row'); ?>">
					<select id="quick_search_links_per_row" name="quick_search_links_per_row">
						<?php foreach (array(1,2,3,4,5,6) as $per_row)
							echo	'<option value="'.$per_row.'" '.(($quick_search_links_per_row == $per_row) ? 'selected="selected"' : '').'>'.$per_row.'</option>';
						?>
					</select>
				</label>
			</p>

			<p><label for="<?php echo $this->get_field_id('plugin_height'); ?>">Widget height (px or %): <input class="" id="<?php echo $this->get_field_id('plugin_height'); ?>" name="<?php echo $this->get_field_name('plugin_height'); ?>" type="text" value="<?php echo attribute_escape($plugin_height); ?>" /></label></p>

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

		global 										$rover_idx_widgets;
		$instance									= $rover_idx_widgets->rover_widget_update($new_instance);

		//	Add items specific to this widget here

		$cities										= array();
		foreach (explode(',', $new_instance['cities']) as $one_city)
			$cities[]								= trim($one_city);

		$instance['cities']							= strip_tags(implode(',', $cities));
		$instance['quick_search_include_areas']		= $new_instance['quick_search_include_areas'];
		$instance['quick_search_include_counts']	= $new_instance['quick_search_include_counts'];
		$instance['quick_search_links_per_row']		= $new_instance['quick_search_links_per_row'];

		return $instance;
		}
	function widget($args, $instance) { 

		global $rover_idx_widgets;

		if ($rover_idx_widgets->display_widget_on_this_page($instance))
			{
			extract( $args );

			$widget_title					= @$instance['widget_title'];
			$region							= @$instance['region'];
			$plugin_height					= @$instance['plugin_height'];
			$cities							= @$instance['cities'];
			$quick_search_include_areas		= @$instance['quick_search_include_areas'];
			$quick_search_include_counts	= @$instance['quick_search_include_counts'];
			$quick_search_links_per_row		= @$instance['quick_search_links_per_row'];
//			$wrapping_tag					= @$instance['wrapping_tag'];
	
			$content_settings				= array_merge(
												$rover_idx_widgets->standard_widget_fields_for_rover($instance),
												array(
													'plugin_type'					=> 'quickSearchLinks', 
													'quick_search_include_areas'	=> $quick_search_include_areas, 
													'quick_search_include_counts'	=> $quick_search_include_counts,
													'quick_search_links_per_row'	=> $quick_search_links_per_row,
													'cities'						=> $cities,
													'all_cities'					=> (empty($cities)) ? null : $cities,
													'group_by'						=> ($quick_search_include_areas) ? 'city,area' : 'city'
													));

			require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

			global							$rover_idx_content;

			$the_rover_content				= $rover_idx_content->rover_content(	
													'ROVER_COMPONENT_PLUGIN', 
													$content_settings);

//			echo '<'.$wrapping_tag.' class="widget widget_rover_quick_links"><h3 class="widget-title">'.$widget_title.'</h3>'.$the_rover_content['the_html'].'</'.$wrapping_tag.'>';
			echo $before_widget;
			echo 	$before_title.$widget_title.$after_title;
			echo 	$the_rover_content['the_html'];
			echo $after_widget;
			}
		}
	}
?>