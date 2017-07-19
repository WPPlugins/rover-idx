<?php
class roveridx_affordability_calc extends WP_Widget 
	{
	function roveridx_affordability_calc() {
		parent::__construct(false, 
							$name = 'Rover Affordability Calculator',
							array(
								'description'	=> 'Rover affordability calculator helps you determine how much house you can afford by analyzing your income, debt, and the current mortgage rates.'));

		}
	function form($instance) { 

		global						$rover_idx, $rover_idx_widgets;
		$instance					= wp_parse_args((array) $instance, 
													array(	'widget_title'				=> 'Affordability Calculator',
															'wrapping_tag'				=> 'aside'));
		$widget_title				= $instance['widget_title'];
		$wrapping_tag				= $instance['wrapping_tag'];
		$ac_advanced				= $instance['ac_advanced'];
		$ac_income					= $instance['ac_income'];
		if (empty($ac_income))
			$ac_income				= 75000;
		$ac_req_login				= $instance['ac_req_login'];
		$ac_rate_30					= $instance['ac_rate_30'];
		if (empty($ac_rate_30))
			$ac_rate_30				= 3.0;
		$ac_down					= $instance['ac_down'];
		if (empty($ac_down))
			$ac_down				= 10;
		$ac_term					= $instance['ac_term'];
		if (empty($ac_term))
			$ac_term				= 30;
		$ac_prop_tax				= $instance['ac_prop_tax'];
		$ac_prop_ins				= $instance['ac_prop_ins'];
		$ac_prop_towns				= $instance['ac_prop_towns'];

		?>
		<aside>
			<p><label for="<?php echo $this->get_field_id('widget_title'); ?>" style="width: 100%;">Title: <input class="widefat" id="<?php echo $this->get_field_id('widget_title'); ?>" name="<?php echo $this->get_field_name('widget_title'); ?>" type="text" value="<?php echo attribute_escape($widget_title); ?>" /></label></p>

			<p><label>Advanced:</label><br />
				<label>
					<input name="<?php echo $this->get_field_name('ac_advanced'); ?>" type="radio" value="false" <?php if ($ac_advanced != 'true'){ echo 'checked="checked"'; } ?> />
					<?php _e('No'); ?>
				</label>
				<label>
					<input name="<?php echo $this->get_field_name('ac_advanced'); ?>" type="radio" value="true" <?php if ($ac_advanced == 'true'){ echo 'checked="checked"'; } ?> />
					<?php _e('Yes'); ?>
				</label>
			</p>

			<p><label>View Matching Listings Requires Login:</label><br />
				<label>
					<input name="<?php echo $this->get_field_name('ac_req_login'); ?>" type="radio" value="false" <?php if ($ac_req_login != 'true'){ echo 'checked="checked"'; } ?> />
					<?php _e('No'); ?>
				</label>
				<label>
					<input name="<?php echo $this->get_field_name('ac_req_login'); ?>" type="radio" value="true" <?php if ($ac_req_login == 'true'){ echo 'checked="checked"'; } ?> />
					<?php _e('Yes'); ?>
				</label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('ac_income'); ?>">Yearly Income ($): <input class="" id="<?php echo $this->get_field_id('ac_income'); ?>" name="<?php echo $this->get_field_name('ac_income'); ?>" type="text" value="<?php echo attribute_escape($ac_income); ?>" /></label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('ac_rate_30'); ?>">30 Year Fixed Rate (%): <input class="" id="<?php echo $this->get_field_id('ac_rate_30'); ?>" name="<?php echo $this->get_field_name('ac_rate_30'); ?>" type="text" value="<?php echo attribute_escape($ac_rate_30); ?>" /></label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('ac_down'); ?>">Down Payment (%): <input class="" id="<?php echo $this->get_field_id('ac_down'); ?>" name="<?php echo $this->get_field_name('ac_down'); ?>" type="text" value="<?php echo attribute_escape($ac_down); ?>" /></label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('ac_term'); ?>">Term (years): <input class="" id="<?php echo $this->get_field_id('ac_term'); ?>" name="<?php echo $this->get_field_name('ac_term'); ?>" type="text" value="<?php echo attribute_escape($ac_term); ?>" /></label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('ac_prop_tax'); ?>">Property Tax Rate (%): <input class="" id="<?php echo $this->get_field_id('ac_prop_tax'); ?>" name="<?php echo $this->get_field_name('ac_prop_tax'); ?>" type="text" value="<?php echo attribute_escape($ac_prop_tax); ?>" /></label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('ac_prop_ins'); ?>">Property Insurance ($): <input class="" id="<?php echo $this->get_field_id('ac_prop_ins'); ?>" name="<?php echo $this->get_field_name('ac_prop_ins'); ?>" type="text" value="<?php echo attribute_escape($ac_prop_ins); ?>" /></label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('ac_prop_towns'); ?>">Specific cities/towns: </label>
				<div class="widefat"><textarea id="<?php echo $this->get_field_id('ac_prop_towns'); ?>" name="<?php echo $this->get_field_name('ac_prop_towns'); ?>" style="width:100%;"><?php echo $this->display_towns($ac_prop_towns); ?></textarea></div>
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
	
	function display_towns($ac_prop_towns)
		{
		if (empty($ac_prop_towns))
			return null;

//		$new_towns								= array();
//		foreach(rover_make_array($ac_prop_towns) as $one_town)
//			{
//			$new_towns[]						= '"'.$one_town.'"';
//			}
//
//		return '['.implode(',', $new_towns).']';


	
		return implode(',', ((is_array($ac_prop_towns)) ? $ac_prop_towns : explode(',', $ac_prop_towns)));


//		$new_towns								= array();
//		foreach(json_decode($ac_prop_towns) as $one_town)
//			{
//			$new_towns[]						= $one_town;
//			}
//
//		return implode(',', $new_towns);
		}

	function create_prepopulate_list($all_cities)
		{
		$all_the_cities							= array();

		foreach ($all_cities as $one_city)
			{
			if (strpos($one_city, '__') !== false)
				{
				$location						= explode('__', $one_city);
				$state							= $location[0];
				$city							= $location[1];
				$all_the_cities[$city]			= $city;
				}
			else if (strpos($one_city, '|') !== false)
				{
				$location						= explode('|', $one_city);
				$id								= $location[0];		//	(City, Area...)
				$city							= $location[1];
				$all_the_cities[$city]			= $id;
				}
			else
				{
				$all_the_cities[$one_city]		= $one_city;
				}
			}

		return $all_the_cities;
		}

	function update($new_instance, $old_instance) { 

		global 									$rover_idx_widgets;

		$instance								= array();
//		$instance								= $rover_idx_widgets->rover_widget_update($new_instance);

		$instance['widget_title']				= strip_tags($new_instance['widget_title']);
		$instance['wrapping_tag']				= strip_tags($new_instance['wrapping_tag']);
		$instance['ac_advanced']				= strip_tags($new_instance['ac_advanced']);
		
		$instance['ac_income']					= strip_tags($new_instance['ac_income']);
		$instance['ac_req_login']				= strip_tags($new_instance['ac_req_login']);

		$instance['ac_rate_30']					= strip_tags($new_instance['ac_rate_30']);
		$instance['ac_down']					= strip_tags($new_instance['ac_down']);
		$instance['ac_term']					= strip_tags($new_instance['ac_term']);

		$instance['ac_prop_tax']				= strip_tags($new_instance['ac_prop_tax']);
		$instance['ac_prop_ins']				= strip_tags($new_instance['ac_prop_ins']);
//		$instance['ac_prop_towns']				= json_encode($new_instance['ac_prop_towns']);
		$instance['ac_prop_towns']				= $new_instance['ac_prop_towns'];

		return $instance;
		}

	function widget($args, $instance) { 

		global									$rover_idx_widgets;

		extract( $args );

//		foreach($args as $arg_key => $arg_val)
//			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '['.$arg_key.'] => ['.$arg_val.']');

//		foreach($instance as $arg_key => $arg_val)
//			rover_idx_error_log(__FILE__, __FUNCTION__, __LINE__, '['.$arg_key.'] => ['.$arg_val.']');

		$widget_title							= @$instance['widget_title'];
//		$wrapping_tag							= @$instance['wrapping_tag'];
//		if (empty($wrapping_tag))
//			$wrapping_tag						= 'aside';
		$ac_advanced							= @$instance['ac_advanced'];
		if (empty($ac_advanced))
			$ac_advanced						= @$ac_advanced;

		$ac_income								= @$instance['ac_income'];
		if (empty($ac_income))
			$ac_income							= @$ac_income;

		$ac_req_login							= @$instance['ac_req_login'];
		if (empty($ac_req_login))
			$ac_req_login						= @$ac_req_login;

		$ac_rate_30								= @$instance['ac_rate_30'];
		if (empty($ac_rate_30))
			$ac_rate_30							= 3.0;

		$ac_down								= $instance['ac_down'];
		if (empty($ac_down))
			$ac_down							= 10;
		$ac_term								= $instance['ac_term'];
		if (empty($ac_term))
			$ac_term							= 30;

		$ac_prop_tax							= @$instance['ac_prop_tax'];
		if (empty($ac_prop_tax))
			$ac_prop_tax						= 2.0;

		$ac_prop_ins							= @$instance['ac_prop_ins'];
		if (empty($ac_prop_ins))
			$ac_prop_ins						= 100;

		$ac_prop_towns							= (empty($ac_prop_towns))
														? @$instance['ac_prop_towns']
														: $ac_prop_towns;

/*
	args
	
	[name] => Home calculator
    [id] => home-calculator
    [description] => This is the home calculator.
    [class] => 
    [before_widget] =>
    
    instance
    
    [widget_title] => Affordability Calculator
    [wrapping_tag] => 
    [ac_advanced] => true
    [ac_req_login] => false
    [ac_rate_30] => 4.25
    [ac_prop_tax] => 
    [ac_prop_ins] => 
*/

		$content_settings						= array_merge(
													$rover_idx_widgets->standard_widget_fields_for_rover($instance),
													array(
														'ac_advanced'				=> $ac_advanced,
														'ac_req_login'				=> $ac_req_login,
														'ac_income'					=> $ac_income,
														'ac_rate_30'				=> $ac_rate_30,
														'ac_down'					=> $ac_down,
														'ac_term'					=> $ac_term,
														'ac_prop_tax'				=> $ac_prop_tax,
														'ac_prop_ins'				=> $ac_prop_ins,
														'ac_prop_towns'				=> $ac_prop_towns,
														));

		require_once ROVER_IDX_PLUGIN_PATH.'rover-content.php';

		global									$rover_idx_content;

		$the_rover_content						= $rover_idx_content->rover_content(	
														'ROVER_COMPONENT_AFFORDABILITY_CALCULATOR', 
														$content_settings);

		echo $before_widget;
		echo 	$before_title.$widget_title.$after_title;
		echo 	$the_rover_content['the_html'];
		echo $after_widget;
		}
	}
?>