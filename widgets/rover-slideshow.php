<?php
class roveridx_slideshow extends WP_Widget 
	{
	public	$all_status_fields		= null;
	
	function __construct() {

		$this->all_status_fields	= array('active'					=>	'Active',
											'pending'					=>	'Pending',
											'sold'						=>	'Sold'
											);

		parent::__construct(false, 
							$name = 'Rover - Slideshow',
							array(
								'description'	=> 'Display rotating images of specific listings, by mlnumbers or listing office / agent mlsid'));

		}
	function form($instance) { 

		global						$rover_idx, $rover_idx_widgets;

		$instance					= wp_parse_args((array) $instance, 
													array(	'widget_title'					=> 'Featured Properties',
															'thumb_width'					=> 200));

		$wrapping_tag				= $instance['wrapping_tag'];

		$widget_title				= $instance['widget_title'];
		$region						= $instance['region'];
		$plugin_height				= $instance['plugin_height'];
		$thumb_width				= $instance['thumb_width'];
		$listing_office_mlsid		= $instance['listing_office_mlsid'];
		$listing_agent_mlsid		= $instance['listing_agent_mlsid'];
		$mlnumbers					= $instance['mlnumbers'];
		$status_active				= $instance['status_active'];
		$status_pending				= $instance['status_pending'];
		$status_sold				= $instance['status_sold'];
		$slide_caption				= $instance['slide_caption'];
		$pause_on_hover				= $instance['pause_on_hover'];

		?>
		<aside>
			<p><label for="<?php echo $this->get_field_id('widget_title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('widget_title'); ?>" name="<?php echo $this->get_field_name('widget_title'); ?>" type="text" value="<?php echo attribute_escape($widget_title); ?>" /></label></p>

			<?php
				if (count($rover_idx->all_selected_regions) > 1)
					{
					$rover_idx_widgets->add_regions_selector(
														$this->get_field_id('region'), 
														$this->get_field_name('region'),
														$region);
					}
			?>

			<p><label for="<?php echo $this->get_field_id('listing_office_mlsid'); ?>">Listing Office MLSID: <input class="" id="<?php echo $this->get_field_id('listing_office_mlsid'); ?>" name="<?php echo $this->get_field_name('listing_office_mlsid'); ?>" type="text" value="<?php echo attribute_escape($listing_office_mlsid); ?>" /></label></p>

			<p><label for="<?php echo $this->get_field_id('listing_agent_mlsid'); ?>">Listing Agent MLSID: <input class="" id="<?php echo $this->get_field_id('listing_agent_mlsid'); ?>" name="<?php echo $this->get_field_name('listing_agent_mlsid'); ?>" type="text" value="<?php echo attribute_escape($listing_agent_mlsid); ?>" /></label></p>

			<p><label for="<?php echo $this->get_field_id('mlnumbers'); ?>">MLNumbers: <input class="" id="<?php echo $this->get_field_id('mlnumbers'); ?>" name="<?php echo $this->get_field_name('mlnumbers'); ?>" type="text" value="<?php echo attribute_escape($mlnumbers); ?>" /></label></p>

			<p><label>Statuses:</label><br />

				<?php
					$none_selected = true;
					foreach ($this->all_status_fields as $field_key => $field_label)
						{
						if ($instance[$field_key] == 1)
							{
							$none_selected = false;
							break;
							}
						}	
				?>
			
				<ol id="<?php echo $this->get_field_id('rover-all-status-fields'); ?>" class="rover-all-status-fields">
				<?php foreach (array_keys($this->all_status_fields) as  $field_key) {	?>
					<li class="rover-status-field" data-val="<?php echo $field_key; ?>">
						<label for="<?php echo $this->get_field_id($field_key); ?>">
							<input id="<?php echo $this->get_field_id($field_key); ?>" name="<?php echo $this->get_field_name($field_key); ?>" type="checkbox" value="1" <?php if ($instance[$field_key] == 1 || $none_selected){ echo 'checked="checked"'; } ?> />
							<?php _e($this->all_status_fields[$field_key]); ?><br />
						</label>
					</li>
				<?php }	?>
				</ol>
				<div class="help-block">Statuses other than Active must be shared by your local MLS.</div>
			</p>

			<p><label for="<?php echo $this->get_field_id('thumb_width'); ?>">Initial Image Width: <input class="" id="<?php echo $this->get_field_id('thumb_width'); ?>" name="<?php echo $this->get_field_name('thumb_width'); ?>" type="text" value="<?php echo attribute_escape($thumb_width); ?>" /></label></p>

			<p><label>Display Address as Caption:</label><br />
				<label for="<?php echo $this->get_field_id('slide_caption'); ?>">
					<input id="<?php echo $this->get_field_id('slide_caption'); ?>" name="<?php echo $this->get_field_name('slide_caption'); ?>" type="radio" value="true" <?php if ($slide_caption != 'false'){ echo 'checked="checked"'; } ?> />
					<?php _e('Yes'); ?>
				</label>
				<label for="<?php echo $this->get_field_id('slide_caption'); ?>">
					<input id="<?php echo $this->get_field_id('slide_caption'); ?>" name="<?php echo $this->get_field_name('slide_caption'); ?>" type="radio" value="false" <?php if ($slide_caption == 'false'){ echo 'checked="checked"'; } ?> />
					<?php _e('No'); ?>
				</label>
			</p>

			<p><label>Pause on Hover:</label><br />
				<label>
					<input name="<?php echo $this->get_field_name('pause_on_hover'); ?>" type="radio" value="true" <?php if ($pause_on_hover != 'false'){ echo 'checked="checked"'; } ?> />
					<?php _e('Yes'); ?>
				</label>
				<label>
					<input name="<?php echo $this->get_field_name('pause_on_hover'); ?>" type="radio" value="false" <?php if ($pause_on_hover == 'false'){ echo 'checked="checked"'; } ?> />
					<?php _e('No'); ?>
				</label>
			</p>

			<?php
				$rover_idx_widgets->widget_page_display_options(
														$instance,
														$this
														);
			?>

			<style type="text/css">
				.rover-all-status-fields {
					margin-left: 0px;
					padding: 4px;
					background: #FFF;
					border: 1px solid #DDD;
					list-style-type: none;
					}
				.rover-status-field label {
					display: inline-block;
					text-align: left;
					}
				.rover-all-status-fields li {
					list-style-type: none;
					}
			</style>
		</aside>
		<?php
		}
	function update($new_instance, $old_instance) {

		global $rover_idx_widgets;

		$instance						= $rover_idx_widgets->rover_widget_update($new_instance);

		//	Add items specific to this widget here

		$instance['slide_caption']		= strip_tags($new_instance['slide_caption']);
		$instance['pause_on_hover']		= strip_tags($new_instance['pause_on_hover']);

		foreach (array_keys($this->all_status_fields) as  $field_key)
			{
			$instance[$field_key]		= strip_tags($new_instance[$field_key]);
			$instance[$field_key]		= strip_tags($new_instance[$field_key]);
			$instance[$field_key]		= strip_tags($new_instance[$field_key]);
			}

		return $instance;
		}
	function widget($args, $instance) { 

		global $rover_idx_widgets;

		if ($rover_idx_widgets->display_widget_on_this_page($instance))
			{
			extract( $args );

			$widget_title				= @$instance['widget_title'];
			$thumb_width				= @$instance['thumb_width'];
			$slide_caption				= @$instance['slide_caption'];
			$pause_on_hover				= @$instance['pause_on_hover'];
			
			$statuses					= array();
			foreach (array_keys($this->all_status_fields) as  $field_key)
				{
				if (@$instance[$field_key] == '1')
					$statuses[]			= $field_key;
				}

			if (count($statuses) === 0)
				$statuses[]				= 'active';

			$content_settings			= array_merge(
											$rover_idx_widgets->standard_widget_fields_for_rover($instance),
											array(
												'plugin_type'					=> 'featuredListings', 
												'row_style'						=> 'slideshow',
												'status'						=> implode(',', $statuses),
												'thumb_width'					=> $thumb_width,
												'slide_caption'					=> $slide_caption,
												'pause_on_hover'				=> $pause_on_hover
												));

			require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

			global						$rover_idx_content;

			$the_rover_content			= $rover_idx_content->rover_content(	
												'ROVER_COMPONENT_PLUGIN', 
												$content_settings);

			echo $before_widget;
			echo 	$before_title.$widget_title.$after_title;
			echo 	$the_rover_content['the_html'];
			echo $after_widget;
			}
		}
	}
?>