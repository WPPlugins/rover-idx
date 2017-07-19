<?php
class roveridx_search_omni extends WP_Widget 
	{
	public	$all_omni_fields		= null;
	public	$all_addl_fields		= null;
	
	function __construct() {

		$this->all_omni_fields		= array(
											'mlnumber'					=> 'MLNumber',
											'streetname'				=> 'StreetName',
											'neighborhood'				=> 'Neighborhood',
											'city'						=> 'City',
											'county'					=> 'County',
											'zip'						=> 'Zip'
											);

		$this->all_addl_fields		= array('buildBeds'					=>	'Beds',
											'buildBaths'				=>	'Baths',
											'buildPrice'				=>	'Price',
											'buildPropertyTypeAsSelect'	=>	'Property Type'
											);

		parent::__construct(false, 
							$name = 'Rover - Omni Search',
							array(
								'description'	=> 'Sidebar universal search - type mlnumber, streetname, neighborhood, city, county, or zip'));

		}
	function form($instance) { 
		global						$rover_idx, $rover_idx_widgets;

		$instance					= wp_parse_args((array) $instance, 
													array(	'widget_title'				=> 'Omni Search',
															'omni_help'					=> 'Search by mlnumber, streetname, neighborhood, city, county, or zip',
															'button_style'				=> 'style_rover',
															'wrapping_tag'				=> 'aside'));

		$widget_title				= $instance['widget_title'];
		$region						= $instance['region'];
		$limit_to					= $instance['limit_to'];
		$omni_help					= $instance['omni_help'];
		$search_orientation			= $instance['search_panel_orientation'];
		$search_panel_price			= $instance['search_panel_price'];
		$button_style				= $instance['button_style'];
		$wrapping_tag				= $instance['wrapping_tag'];
		$search_always_redirect		= $instance['search_always_redirect'];
		$none_selected				= (empty($limit_to))
											? true
											: false;
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

			<p><label for="<?php echo $this->get_field_id('omni_help'); ?>" style="width: 100%;">Help Text: <input class="widefat" id="<?php echo $this->get_field_id('omni_help'); ?>" name="<?php echo $this->get_field_name('omni_help'); ?>" type="text" value="<?php echo attribute_escape($omni_help); ?>" /></label></p>

			<p><label data-limit="<?php echo $limit_to; ?>">Limit Omni to these fields:</label><br />

				<div style="margin-left: 20px;padding: 4px;background: #FFF;border: 1px solid #DDD;">
				<?php
					$n = 1;
					foreach ($this->all_omni_fields as $field_key => $field_label) {	?>
						<?php
						$selected	= ((strpos($limit_to, $field_key) !== false) || (empty($limit_to)))
										? 'checked="checked"'
										: '';
						?>
						<label>
							<input name="<?php echo $this->get_field_name('limit_to'.$n++); ?>" type="checkbox" value="<?php echo $field_key; ?>" <?php echo $selected; ?> />
							<?php _e($field_label); $field_num++; ?>
						</label><br />
				<?php }	?>
				</div>
			</p>

			<p><label>Display additional search fields:</label><br />

				<?php
					$none_selected = true;
					foreach ($this->all_addl_fields as $field_key => $field_label) {
						if ($instance[$field_key] == 1)
							{
							$none_selected = false;
							break;
							}
						}	
				?>
			
				<div style="margin-left: 20px;padding: 4px;background: #FFF;border: 1px solid #DDD;">
				<?php foreach ($this->all_addl_fields as $field_key => $field_label) {	?>
					<label for="<?php echo $this->get_field_id($field_key); ?>">
						<input id="<?php echo $this->get_field_id($field_key); ?>" name="<?php echo $this->get_field_name($field_key); ?>" type="checkbox" value="1" <?php if ($instance[$field_key] == 1 || $none_selected){ echo 'checked="checked"'; } ?> />
						<?php _e($field_label); ?><br />
					</label>
				<?php }	?>
				</div>
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

			<p><label>Price:</label><br />
				<label for="<?php echo $this->get_field_id('search_panel_price'); ?>">
					<input id="<?php echo $this->get_field_id('search_panel_price'); ?>" name="<?php echo $this->get_field_name('search_panel_price'); ?>" type="radio" value="range_slider" <?php if ($search_panel_price != 'dropdown'){ echo 'checked="checked"'; } ?> />
					<?php _e('Range Slider'); ?>
				</label>
				<label for="<?php echo $this->get_field_id('search_panel_price'); ?>">
					<input id="<?php echo $this->get_field_id('search_panel_price'); ?>" name="<?php echo $this->get_field_name('search_panel_price'); ?>" type="radio" value="dropdown" <?php if ($search_panel_price == 'dropdown'){ echo 'checked="checked"'; } ?> />
					<?php _e('Dropdown'); ?>
				</label>
			</p>

			<p>
				<label>
					<input id="<?php echo $this->get_field_id('search_always_redirect'); ?>" name="<?php echo $this->get_field_name('search_always_redirect'); ?>" type="checkbox" value="1" <?php if ($search_always_redirect == 1){ echo 'checked="checked"'; } ?> />
					<?php _e('Redirect search to new page'); ?><br />
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

		$limit_to_list	= array();
		$the_key		= 'limit_to';
		$the_key_len	= strlen($the_key);
		foreach ($new_instance as $inst_key => $inst_val)
			{
			if (substr($inst_key, 0, $the_key_len) === $the_key)
				$limit_to_list[]				= $inst_val;
			}
		$instance['limit_to']					= implode(',', $limit_to_list);

		foreach ($this->all_addl_fields as $field_key => $field_label) 
			$instance[$field_key]				= strip_tags($new_instance[$field_key]);

		$instance['search_panel_orientation']	= strip_tags($new_instance['search_panel_orientation']);
		$instance['search_panel_price']			= strip_tags($new_instance['search_panel_price']);
		$instance['button_style']				= strip_tags($new_instance['button_style']);
		$instance['omni_help']					= strip_tags($new_instance['omni_help']);
		$instance['search_always_redirect']		= strip_tags($new_instance['search_always_redirect']);

		return $instance;
		}

	function widget($args, $instance) { 

		global $rover_idx_widgets;

		if ($rover_idx_widgets->display_widget_on_this_page($instance))
			{
			extract( $args );

			$widget_title				= @$instance['widget_title'];
			$added_row					= false;
			$search_fields				= array();

			$limit_to					= @$instance['limit_to'];
			$omni_help					= @$instance['omni_help'];
			$search_orientation			= @$instance['search_panel_orientation'];
			$search_panel_price			= @$instance['search_panel_price'];
			$button_style				= @$instance['button_style'];
//			$wrapping_tag				= @$instance['wrapping_tag'];
			$search_always_redirect		= @$instance['search_always_redirect'];

			$search_fields[]			= 'buildOmniSearch';
			$search_fields[]			= 'buildNewRow';

			foreach ($this->all_addl_fields as $field_key => $field_label) 
				{
				if (@$instance[$field_key] == '1')
					{
					if ($field_key == 'price')
						{
						$search_fields[]		= ($search_panel_price == 'dropdown')
														? 'price'
														: 'price_range';
						}
					else
						{
						$search_fields[]		= $field_key;
						}
					}
				}

			if ($search_always_redirect == 1)
				{
				$search_fields[]		= 'buildGoButton';
				}

			$content_settings			= array_merge(
											$rover_idx_widgets->standard_widget_fields_for_rover($instance),
											array(
												'search_panel_layout'		=> 'custom', 
												'template_fields'			=> implode(',', $search_fields), 
												
												'city_control_style'		=> 6,
												'omni_limit_to'				=> $limit_to,
												'omni_help'					=> $omni_help,
												'hide_clear'				=> 'true',
												'button_style'				=> $button_style,
												'search_panel_orientation'	=> (empty($search_orientation)) ? 'vertical' : $search_orientation,
												'search_always_redirect'	=> $search_always_redirect,
												));

			require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

			global						$rover_idx_content;

			$the_rover_content			= $rover_idx_content->rover_content(	
												'ROVER_COMPONENT_SEARCH_PANEL', 
												$content_settings);

//			echo '<'.$wrapping_tag.' class="widget widget_rover_omni_quick"><h3 class="widget-title">'.$widget_title.'</h3>'.$the_rover_content['the_html'].'</'.$wrapping_tag.'>';
			echo $before_widget;
			echo 	$before_title.$widget_title.$after_title;
			echo 	$the_rover_content['the_html'];
			echo $after_widget;
			}
		}
	}
?>