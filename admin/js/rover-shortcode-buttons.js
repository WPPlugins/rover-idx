(function() {
	tinymce.PluginManager.add('roveridx_shortcode_button', function( editor, url ) {

		editor.addButton( 'roveridx_shortcode_button', {
//			text:	'Rover IDX',
			type:	'menubutton',
            icon:	'roveridx-shortcode-icon',
			menu:	[
				{
					text:	'Full search / results page',
					menu: [
						{
							text:	'Basic usage',
							value:	'[rover_idx_full_page]',
							icon:	'rover fa fa-globe',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						},
						{
							text:	'Full Page with many configurable options',
							value:	'[rover_idx_full_page all_cities="replace with comma-separated list of all cities" cities="replace with comma-separated list of SELECTED cities" prop_types="singlefamily,condo,land" items_per_page="32" min_price="150000" max_price="800000"  plugin_type="clientFavorites,newAndUpdated" sort_by="SortPrice"]',
							icon:	'rover fa fa-globe',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						},
						{
							text:	'Full Page with map above listings',
							value:	'[rover_idx_results all_cities="replace with comma-separated list of all cities" cities="replace with comma-separated list of SELECTED cities" prop_types="singlefamily,condo,land" min_price="150000" max_price="800000"]',
							icon:	'rover fa fa-globe',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						},
						{
							text:	'Full Page with search on top',
							value:	'[rover_idx_full_page all_cities="replace with comma-separated list of all cities" cities="replace with comma-separated list of SELECTED cities" prop_types="singlefamily,condo,land" min_price="150000" max_price="800000" search_on_top="true"]',
							icon:	'rover fa fa-globe',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						},
						{
							text:	'Full Page - Lakefront',
							value:	'[rover_idx_full_page all_cities="replace with comma-separated list of all cities" cities="replace with comma-separated list of SELECTED cities" prop_types="singlefamily,condo,land" lakefront="true"]',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						},
						{
							text:	'Full Page - Foreclosed',
							value:	'[rover_idx_full_page all_cities="replace with comma-separated list of all cities" cities="replace with comma-separated list of SELECTED cities" prop_types="singlefamily,condo,land" foreclosed="true"]',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						}
					]
				},
				{
					text:	'Search Results',
					menu: [
						{
							text:	'Basic usage',
							value:	'[rover_idx_results_as_table]',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						},
						{
							text:	'Properties displayed as photo cubes',
							value:	'[rover_idx_results_as_table cities="replace with comma-separated list of SELECTED cities" prop_types="singlefamily,land" min_price="150000"]',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						},
						{
							text:	'Map results (no search panel)',
							value:	'[rover_idx_results_as_map cities="replace with comma-separated list of SELECTED cities" prop_types="singlefamily,land" min_price="150000" map_width="800" map_range="30"]',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						}
					]
				},
				{
					text:	'Search panel',
					menu: [
						{
							text:	'Basic usage',
							value:	'[rover_idx_search_panel]',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						},
						{
							text:	'Many Configurable Options',
							value:	'[rover_idx_search_panel all_cities="replace with comma-separated list of all cities" cities="replace with comma-separated list of SELECTED cities" prop_types="singlefamily,condo,land" min_price="150000" max_price="800000"]',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						},
						{
							text:	'Quick Search',
							value:	'[rover_idx_search_panel template="custom" template_fields="cities,prop_types,beds,baths,price_range,mlnumber,street" all_prop_types="singlefamily,condo,land" prop_types="singlefamily,condo,land" min_price="150000" max_price="800000"]',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						},
						{
							text:	'Search Panel - using range sliders and checkboxes',
							value:	'[rover_idx_search_panel city_control_style="2" beds_as_slider="true" baths_as_slider="true" price_as_slider="true"]',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						},
						{
							text:	'Search Panel - using buttons and select dropdowns',
							value:	'[rover_idx_search_panel city_control_style="4"',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						}
					]
				},
				{
					text:	'Sidebar plugin',
					menu: [
						{
							text:	'Basic usage',
							value:	'[rover_idx_plugin plugin_type="newAndUpdated"]',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						},
						{
							text:	'New and Updated Listings',
							value:	'[rover_idx_plugin plugin_type="newAndUpdated" date_added="7days"]',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						},
						{
							text:	'List of links to cities',
							value:	'[rover_idx_plugin plugin_type="quickSearchLinks" quick_search_links_per_row="1" quick_search_include_counts="true"]',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						},
						{
							text:	'List of links to cities, including areas/neighborhoods',
							value:	'[rover_idx_plugin plugin_type="quickSearchLinks" quick_search_links_per_row="1" quick_search_include_areas="true" quick_search_include_counts="true"]',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						}
					]
				},
				{
					text:	'Slideshow',
//					value:	'[rover_idx_slider]',
//					onclick: function(e) {
//						e.stopPropagation();
//						e.preventDefault();
//						editor.insertContent(this.value());
//					},
					menu: [
						{
							text:	'Basic usage',
							value:	'[rover_idx_slider]',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						},
						{
							text:	'Slideshow',
							value:	'[rover_idx_slider pause_on_hover="true" slide_caption="true"]',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						},
						{
							text:	'Slideshow with Search Panel',
							value:	'[rover_idx_search_slider cities="comma-separated list of SELECTED cities" all_prop_types="single_family,condo,land" prop_types="singlefamily"]',
							onclick: function(e) {
								editor.insertContent(this.value());
							}
						}
					]
				},
				{
					text:	'Display a call-to-action',
					value:	'[rover_idx_cta text="Sign Up for New Listings" tag="button" color="red" background="#4F85BB"]',
					onclick: function(e) {
						editor.insertContent(this.value());
					}
				},
				{
					text:	'------------------------',
					onclick: function(e) {
					}
				},
				{
					text:	'Full Documentation',
					onclick: function() {
						window.open('http://roveridx.com/rover-shortcodes/','_blank');
					}
				}
		   ]
		});
	});
})();