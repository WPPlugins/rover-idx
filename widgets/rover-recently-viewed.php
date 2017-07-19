<?php
class roveridx_recently_viewed extends WP_Widget 
	{
	public	$all_search_fields		= null;
	
	function __construct() {
											
		parent::__construct(false, 
							$name = 'Rover - Recently Viewed',
							array(
								'description'	=> 'Display properties recently viewed by visitor'));

		}
	function form($instance) { 

		global						$rover_idx, $rover_idx_widgets;

		$instance					= wp_parse_args((array) $instance, 
													array(	'widget_title'					=> 'Recently Viewed',
															'prev_days'						=> 7,
															'thumb_width'					=> 50
															));

		$wrapping_tag				= $instance['wrapping_tag'];

		$widget_title				= $instance['widget_title'];
		$region						= $instance['region'];
		$plugin_height				= $instance['plugin_height'];
		$all_cities					= $instance['all_cities'];
		$prev_days					= $instance['prev_days'];

		?>
		<aside>
			<p><label for="<?php echo $this->get_field_id('widget_title'); ?>" style="width: 100%;">Title: <input class="widefat" id="<?php echo $this->get_field_id('widget_title'); ?>" name="<?php echo $this->get_field_name('widget_title'); ?>" type="text" value="<?php echo attribute_escape($widget_title); ?>" /></label></p>

			<?php
				//	Don't check region.  We are finding properties via cookies.
				if (false && count($rover_idx->all_selected_regions) > 1)
					{
					$rover_idx_widgets->add_regions_selector(
														$this->get_field_id('region'), 
														$this->get_field_name('region'),
														$region);
					}
			?>

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

		global 									$rover_idx_widgets;
		$instance								= $rover_idx_widgets->rover_widget_update($new_instance);

		//	Add items specific to this widget here

		return $instance;
		}
	function widget($args, $instance) { 

		global $rover_idx_widgets;

		if ($rover_idx_widgets->display_widget_on_this_page($instance))
			{
			extract( $args );

			$widget_title					= @$instance['widget_title'];
			$widget_title					= $widget_title;
//			$wrapping_tag					= @$instance['wrapping_tag'];
	
			$content_settings				= array_merge(
												$rover_idx_widgets->standard_widget_fields_for_rover($instance),
												array(
													'plugin_type'						=> 'recentlyViewed'
													));

			require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

			global							$rover_idx_content;

			$the_rover_content				= $rover_idx_content->rover_content(	
													'ROVER_COMPONENT_PLUGIN', 
													$content_settings);

//			echo '<'.$wrapping_tag.' class="widget widget_rover_recently_viewed"><h3 class="widget-title">'.$widget_title.'</h3>'.$the_rover_content['the_html'].'</'.$wrapping_tag.'>';
			echo $before_widget;
			echo 	$before_title.$widget_title.$after_title;
			echo 	$the_rover_content['the_html'];
			echo $after_widget;
			}
		}
	}
?>