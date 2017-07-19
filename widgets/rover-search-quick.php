<?php
class roveridx_search_quick extends WP_Widget 
	{
	public	$all_search_fields		= null;
	
	function __construct() {

		$this->all_search_fields	= array('area'						=>	'Area',
											'city'						=>	'City',
											'county'					=>	'County',
											'beds'						=>	'Beds',
											'baths'						=>	'Baths',
											'price'						=>	'Price',
											'prop_type'					=>	'Property Type',
											'mlnumber'					=>	'ML Number',
											'street'					=>	'Street'
											);
											
		parent::__construct(false, 
							$name = 'Rover - Quick Search',
							array(
								'description'	=> 'Sidebar search by City, Bedrooms, Bathrooms, and Price'));

		}
	function form($instance) { 
		global						$rover_idx, $rover_idx_widgets;

		$instance					= wp_parse_args((array) $instance, 
													array(	'widget_title'				=> 'Quick Search',
															'button_style'				=> 'style_rover',
															'wrapping_tag'				=> 'aside'));

		$widget_title				= $instance['widget_title'];
		$region						= $instance['region'];
		$all_cities					= $instance['all_cities'];
		$all_areas					= $instance['all_areas'];
		$available_search_fields	= $instance['available_search_fields'];
		if (empty($available_search_fields))
			$available_search_fields	= implode(',', array_keys($this->all_search_fields));
		$search_orientation			= $instance['search_panel_orientation'];
		$search_panel_price			= $instance['search_panel_price'];
		$button_style				= $instance['button_style'];
		$wrapping_tag				= $instance['wrapping_tag'];
		$search_always_redirect		= $instance['search_always_redirect'];
		?>
		<aside id="<?php echo $this->get_field_id('rover-quick-search'); ?>">
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

			<p><label>Display these search fields:</label><br />

				<?php
					$none_selected = true;
					foreach ($this->all_search_fields as $field_key => $field_label) {
						if ($instance[$field_key] == 1)
							{
							$none_selected = false;
							break;
							}
						}	
				?>
			
				<ol id="<?php echo $this->get_field_id('rover-all-search-fields'); ?>" class="rover-all-search-fields">
				<?php foreach (explode(',', $available_search_fields) as  $field_key) {	?>
					<li class="rover-search-field" data-val="<?php echo $field_key; ?>">
						<label for="<?php echo $this->get_field_id($field_key); ?>">
							<input id="<?php echo $this->get_field_id($field_key); ?>" name="<?php echo $this->get_field_name($field_key); ?>" type="checkbox" value="1" <?php if ($instance[$field_key] == 1 || $none_selected){ echo 'checked="checked"'; } ?> />
							<?php _e($this->all_search_fields[$field_key]); ?><br />
						</label>
						<i class="fa fa-bars" aria-hidden="true"></i>
					</li>
				<?php }	?>
				</ol>
				<input type="hidden" class="available_search_fields" id="<?php echo $this->get_field_id('available_search_fields'); ?>" name="<?php echo $this->get_field_name('available_search_fields'); ?>" type="checkbox" value="<?php echo attribute_escape($available_search_fields); ?>" />
			</p>

			<p><label for="<?php echo $this->get_field_id('all_cities'); ?>" style="width: 100%;">Cities/Towns: <input class="widefat" id="<?php echo $this->get_field_id('all_cities'); ?>" name="<?php echo $this->get_field_name('all_cities'); ?>" type="text" value="<?php echo attribute_escape($all_cities); ?>" /></label></p>

			<p><label for="<?php echo $this->get_field_id('all_areas'); ?>" style="width: 100%;">Areas: <input class="widefat" id="<?php echo $this->get_field_id('all_areas'); ?>" name="<?php echo $this->get_field_name('all_areas'); ?>" type="text" value="<?php echo attribute_escape($all_areas); ?>" /></label></p>

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

			<?php
			if (false)
				{
			?>
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
				}
			?>

			<p>
				<label>
					<input id="<?php echo $this->get_field_id('search_always_redirect'); ?>" name="<?php echo $this->get_field_name('search_always_redirect'); ?>" type="checkbox" value="1" <?php if ($search_always_redirect == 1){ echo 'checked="checked"'; } ?> />
					<?php _e('Redirect search to new page'); ?><br />
				</label>
			</p>
			<style type="text/css">
				.rover-all-search-fields {
					margin-left: 0px;
					padding: 4px;
					background: #FFF;
					border: 1px solid #DDD;
					list-style-type: none;
					}
				.rover-search-field label {
					display: inline-block;
					text-align: left;
					}
				i.fa.fa-bars {
					cursor: move;
				    cursor: -webkit-grabbing;
					text-align: right;
					float: right;
					}
			</style>
			<script type="text/javascript" src="<?php echo ROVER_ENGINE_CURRENT_PROTOCOL.ROVER_VERSION; ?>/js/rover_ui_sortable.js"></script>
			<script>
				(function( $ ){

					$p				= $("#<?php echo $this->get_field_id('rover-quick-search'); ?>");
					var all			= document.getElementById("<?php echo $this->get_field_id('rover-all-search-fields'); ?>");
					Sortable.create( 
									all,
									{
									handle: ".fa.fa-bars",
									onEnd: function (e) {

										var k	= [];
										$p.find(".rover-search-field").each(function(i, e) {
											var v = $(e).data('val');
											if (v && v.length)
												k[k.length] = v;
											});

										$p.find(".available_search_fields").val( k.join(',') );
										}
									}
								);
					})( jQuery );
			</script>

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

		$all_cities								= array();
		$all_areas								= array();

		foreach ($this->all_search_fields as $field_key => $field_label) 
			$instance[$field_key]				= strip_tags($new_instance[$field_key]);

		foreach (explode(',', $new_instance['all_cities']) as $one_city)
			$all_cities[]						= trim($one_city);
		$instance['all_cities']					= strip_tags(implode(',', $all_cities));

		foreach (explode(',', $new_instance['all_areas']) as $one_area)
			$all_areas[]						= trim($one_area);
		$instance['all_areas']					= strip_tags(implode(',', $all_areas));

		$instance['available_search_fields']	= strip_tags($new_instance['available_search_fields']);
		if (empty($available_search_fields))
			$available_search_fields			= implode(',', array_keys($this->all_search_fields));

		$instance['search_panel_orientation']	= strip_tags($new_instance['search_panel_orientation']);
		$instance['search_panel_price']			= strip_tags($new_instance['search_panel_price']);
		$instance['button_style']				= strip_tags($new_instance['button_style']);
		$instance['search_always_redirect']		= strip_tags($new_instance['search_always_redirect']);

		return $instance;
		}
	function widget($args, $instance) { 

		global $rover_idx_widgets, $rover_idx_content;

		if ($rover_idx_widgets->display_widget_on_this_page($instance))
			{
			extract( $args );

			$widget_title						= @$instance['widget_title'];

			$all_cities							= @$instance['all_cities'];
			$all_areas							= @$instance['all_areas'];
			$search_orientation					= @$instance['search_panel_orientation'];
			$search_panel_price					= @$instance['search_panel_price'];
			$button_style						= @$instance['button_style'];
			$search_always_redirect				= @$instance['search_always_redirect'];

			$added_row							= false;
			$search_fields						= array();

			$available_search_fields			= $instance['available_search_fields'];
			if (empty($available_search_fields))
				$available_search_fields		= implode(',', array_keys($this->all_search_fields));

			//	These fields can be re-ordered
			foreach (explode(',', $available_search_fields) as $field_key) 
				{
				if (@$instance[$field_key] == '1')
					{
					if (!$added_row && in_array($field_key, array('mlnumber', 'street')))
						{
						$search_fields[]		= 'newrow';
						$added_row				= true;
						}

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



			if (count($search_fields) === 0)	//	None selected
				{
				foreach ($this->all_search_fields as $field_key => $field_label) 
					{
					if ($field_key == 'price')
						{
						$search_fields[]		= ($search_panel_price == 'dropdown')
														? 'price'
														: 'price_range';
						}

					if (!in_array($field_key, array('area')))
						$search_fields[]		= $field_key;
					}
				}

			$search_fields[]					= 'newrow';

			if ($search_always_redirect == 1)
				{
				$search_fields[]				= 'searchbutton';
				}

			$content_settings					= array_merge(
													$rover_idx_widgets->standard_widget_fields_for_rover($instance),
													array(
														'search_panel_layout'		=> 'custom', 
														'search_panel_widget'		=> 'quick_search',
														'template_fields'			=> implode(',', $search_fields), 
														'all_cities'				=> $all_cities,
														'all_areas'					=> $all_areas,
														'price_as_slider'			=> false,
														'beds_as_slider'			=> false,
														'baths_as_slider'			=> false,
														'search_always_redirect'	=> $search_always_redirect,
														'hide_clear'				=> 'true',
														'hide_save_search'			=> 'true',
														'search_panel_orientation'	=> (empty($search_orientation)) ? 'vertical' : $search_orientation,
														'button_style'				=> $button_style,
														));

			require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

			global								$rover_idx_content;

			$the_rover_content					= $rover_idx_content->rover_content(	
														'ROVER_COMPONENT_SEARCH_PANEL', 
														$content_settings);

//			echo '<'.$wrapping_tag.' class="widget widget_rover_search_quick"><h3 class="widget-title">'.$widget_title.'</h3>'.$the_rover_content['the_html'].'</'.$wrapping_tag.'>';
			echo $before_widget;
			echo 	$before_title.$widget_title.$after_title;
			echo 	$the_rover_content['the_html'];
			echo $after_widget;
			}
		}
	}
?>