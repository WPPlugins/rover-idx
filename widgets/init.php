<?php
require_once ROVER_IDX_PLUGIN_PATH.'widgets/rover-ask-a-question.php';
require_once ROVER_IDX_PLUGIN_PATH.'widgets/rover-affordability-calc.php';
require_once ROVER_IDX_PLUGIN_PATH.'widgets/rover-mortgage-calc.php';
require_once ROVER_IDX_PLUGIN_PATH.'widgets/rover-search-quick.php';
require_once ROVER_IDX_PLUGIN_PATH.'widgets/rover-search-mlnumber.php';
require_once ROVER_IDX_PLUGIN_PATH.'widgets/rover-search-school.php';
require_once ROVER_IDX_PLUGIN_PATH.'widgets/rover-search-omni.php';
require_once ROVER_IDX_PLUGIN_PATH.'widgets/rover-quick-links.php';
require_once ROVER_IDX_PLUGIN_PATH.'widgets/rover-new-and-updated.php';
require_once ROVER_IDX_PLUGIN_PATH.'widgets/rover-recently-viewed.php';
require_once ROVER_IDX_PLUGIN_PATH.'widgets/rover-featured-listings.php';
require_once ROVER_IDX_PLUGIN_PATH.'widgets/rover-slideshow.php';

class Rover_IDX_Widgets
	{
	function __construct() {

		add_action( 'widgets_init',						create_function('', 'return register_widget("roveridx_ask_a_question");') );
		add_action( 'widgets_init',						create_function('', 'return register_widget("roveridx_mortgage_calc");') );
		add_action( 'widgets_init',						create_function('', 'return register_widget("roveridx_affordability_calc");') );

		add_action( 'widgets_init',						create_function('', 'return register_widget("roveridx_search_quick");') );
		add_action( 'widgets_init',						create_function('', 'return register_widget("roveridx_search_mlnumber");') );
		add_action( 'widgets_init',						create_function('', 'return register_widget("roveridx_search_school");') );
		add_action( 'widgets_init',						create_function('', 'return register_widget("roveridx_search_omni");') );

		add_action( 'widgets_init',						create_function('', 'return register_widget("roveridx_quick_links");') );
		add_action( 'widgets_init',						create_function('', 'return register_widget("roveridx_new_and_updated");') );
		add_action( 'widgets_init',						create_function('', 'return register_widget("roveridx_recently_viewed");') );
		add_action( 'widgets_init',						create_function('', 'return register_widget("roveridx_featured_listings");') );

		add_action( 'widgets_init',						create_function('', 'return register_widget("roveridx_slideshow");') );
		}
		
	function add_regions_selector($id, $name, $selected_region)	{
		global		$rover_idx;

		?>
		<p><label>Region:</label>
			<select id="<?php echo $id; ?>" name="<?php echo $name; ?>">
				<?php foreach ($rover_idx->all_selected_regions as $oneRegion)
					echo	'<option value="'.$oneRegion.'" '.(($selected_region == $oneRegion) ? 'selected="selected"' : '').'>'.$oneRegion.'</option>';
				?>
			</select>
		</p>
		<?php
		}

	function add_tag_selector($id, $name, $wrapping_tag)	{
		global		$rover_idx;
		$tags		= array('aside', 'div', 'section', 'p');

		if (empty($wrapping_tag))
			$wrapping_tag = 'aside';
		?>
		<p><label>Theme wants wrapping tag to be:</label>
			<select id="<?php echo $id; ?>" name="<?php echo $name; ?>">
				<?php foreach ($tags as $one_tag)
				echo	'<option value="'.$one_tag.'" '.(($wrapping_tag == $one_tag) ? 'selected="selected"' : '').'>'.$one_tag.'</option>';					
				?>
			</select>
		</p>
		<?php
		}

	function widget_page_display_options($instance, $widget)	{

		$pages			= get_pages(); 
		$pages_display	= @$instance['pages_display_mode'];
		$pages_list_of	= @$instance['pages_list_of'];
		$pages_list_of	= explode(',', $pages_list_of);

		?>
		<p><label>Display this Widget on:</label>

			<div class="rover_pager widefat" style="background: #FFF;border: 1px solid #DDD;"> 
				<label><input type="radio" name="<?php echo $widget->get_field_name('pages_display_mode'); ?>" value="" <?php echo (empty($pages_display) ? 'checked="checked"': ''); ?> /> All Pages</label><br />
				<label><input type="radio" name="<?php echo $widget->get_field_name('pages_display_mode'); ?>" value="2" <?php echo (($pages_display == 2) ? 'checked="checked"': ''); ?> /> Only these Pages</label><br />
				<label><input type="radio" name="<?php echo $widget->get_field_name('pages_display_mode'); ?>" value="3" <?php echo (($pages_display == 3) ? 'checked="checked"': ''); ?> /> Not these Pages</label><br />
			</div>

			<div id="rover-page-selector" style="<?php echo (empty($pages_display) ? 'display:none': ''); ?>"> 
				<p>Pages:</p>
				<div style="max-height:240px;margin-left: 20px;padding: 4px;background: #FFF;border: 1px solid #DDD;overflow-y:auto;"> 
					<?php 
					$n = 1;
					foreach ( $pages as $page ) {
						?>
						<label><input name="<?php echo $widget->get_field_name('pages_list_of'.$n++); ?>" type="checkbox" value="<?php echo $page->ID; ?>" <?php echo ((in_array($page->ID, $pages_list_of)) ? 'checked="checked"': ''); ?> /> <?php echo $page->post_title; ?></label><br />
						<?php
						}
					?>
				</div>
			</div>
			<script>
				jQuery(document).ready(function(){
					jQuery(".rover_pager input").on('change', function(e) {
					
						var d = jQuery(e.currentTarget).parents(".widget-content").find("#rover-page-selector");
						if (e.currentTarget.defaultValue > 0)
							d.show();
						else
							d.hide();
						});
					});
			</script>
		</p>
		<?php
		}

	function rover_widget_fields()	{
		return array(
					'plugin_height',
					'thumb_width',
					'pages_display_mode',
					'pages_list_of',
					'region',
					'listing_office_mlsid', 
					'listing_agent_mlsid',
					'mlnumbers',
					'prev_days',

					'widget_title',				//	Don't need to be stored.  Not in standard_widget_fields
					'wrapping_tag'

					);
		}
	
	function rover_widget_update($new_instance)	{

		$instance	= array();

		foreach ($this->rover_widget_fields() as $one_field)
			$instance[$one_field]				= strip_tags($new_instance[$one_field]);
		
		//	Process the list of pages we want to appear / not appear on
		$pages_list		= array();
		$the_key		= 'pages_list_of';
		$the_key_len	= strlen($the_key);
		foreach ($new_instance as $inst_key => $inst_val)
			{
			if (substr($inst_key, 0, $the_key_len) === $the_key)
				$pages_list[]	= $inst_val;
			}
		$instance['pages_list_of']				= implode(',', $pages_list);

		return $instance;
		}

	function standard_widget_fields_for_rover($instance)	{
		return array(
					'plugin_height'				=> @$instance['plugin_height'],
					'plugin_style'				=> 'flat',
					'thumb_width'				=> @$instance['thumb_width'],
					'pages_display_mode'		=> @$instance['pages_display_mode'],
					'pages_list_of'				=> @$instance['pages_list_of'],
					'region'					=> @$instance['region'],

					'listing_office_mlsid'		=> @$instance['listing_office_mlsid'], 
					'listing_agent_mlsid'		=> @$instance['listing_agent_mlsid'],
					'mlnumbers'					=> @$instance['mlnumbers'],
					'prev_days'					=> @$instance['prev_days']					
					);
		}

	function display_widget_on_this_page($instance)	{
		global				$post;
		
		$pages_display_mode	= @$instance['pages_display_mode'];
		$pages_list_of		= @$instance['pages_list_of'];
		$pages_list_of		= explode(',', $pages_list_of);

		if ($pages_display_mode == 2)							//	Display on these specific pages
			{
			if (in_array($post->ID, $pages_list_of) )
				return true;
			else
				return false;
			}
		else if ($pages_display_mode == 3)						//	Do not display on these specific pages
			{
			if (in_array($post->ID, $pages_list_of) )
				return false;
			}

		return true;										//	Everything else is always displayed
		}
	}

global $rover_idx_widgets;
$rover_idx_widgets = new Rover_IDX_Widgets();

?>