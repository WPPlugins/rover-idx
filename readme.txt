=== Rover IDX ===
Author: Rover IDX, LLC
Contributors: stevemullen
Author URL: http://www.roveridx.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: IDX, MLS, multiple listing service, RETS, IDX plugin, rover idx, idx wordpress, wordpress idx, wordpress mls, idx wordpress plugin, integrated idx, real estate, real estate wordpress, WordPress Plugin, realtor, broker
Requires at least: 3.1
Tested up to: 4.8
Stable tag: 2.0.0.1314

== Description ==

Rover IDX for Wordpress is a mobile responsive real estate IDX Plugin, providing fast, searchable real estate listings for website visitors.  The plugin is highly configurable and flexible, allowing you to create a unique website.  Most data is stored on our highly scalable servers, and renders on your WordPress website with lightning speed.

<h3>Rover IDX for Wordpress</h3>

<h4>Up and running in minutes</h4>
Activate Rover IDX, choose the MLS region, and add the [rover_idx_full_page] shortcode to a page.  Boom - your site is displaying searchable real estate listings.  You can then customize what cities are offered for search, what the search panel looks like, what the listing layout looks like, and how the property details page is displayed.

<h4>Unbelievable levels of customization</h4>
Out of the box, Rover IDX just works.  You do not need to know html, css, or php.  Further, Rover offers many listing layout templates to choose from, so you can customize your listings with a click.  You can also customize the search panel with drag and drop efficiency.  If you do want to get into the nitty gritty, you can create your own layout templates - and we offer basic templates to get you started.

<h4>Maps</h4>
Maps can be integrated into [rover_idx_full_page] as a dynamic search experience.  Define locations (towns, counties, subdivisions) via polygons, and allow visitors to search by clicking on these defined locations.

<h4>CRM</h4>
Rover IDX has an optional CRM, used to track and organize your leads.  See the searches / properties visitors have viewed.  Add Notes and Todo's.  Keep a follow up calendar.

<h4>WordPress Widgets</h4>
Add straightforward widgets for Quick Search, Ask a Question, Featured Listings, Quick Links, and Mortgage and Affordability Calculators.

<h4>Conveniently Add Shortcodes</h4>
Within the Wordpress visual editor, Rover IDX offers a add-a-shortcode button with the kitchen sink of parameters.  Just adjust the parameters you care about, and remove or ignore the rest.  [Rover IDX shortcode guide](https://roveridx.com/rover-shortcodes/ "Rover IDX shortcode guide")

<h4>Unbelievable SEO</h4>
All Rover IDX pages are SEO friendly.  Clean urls, and there are no iframes or subdomains.  Rover IDX also adds structured data where appropriate.  All the property detail pages are in your domain, and Googlebot will find them.  And Rover IDX maintains a sitemap of all the active listings represented by your website, and the various crawlers are updated every 24 hours with that updated sitemap.

<h4>Mobile friendly</h4>
All Rover IDX pages are mobile friendly, as long as the Wordpress theme you've chosen is also mobile friendly.  Rover IDX has a Mobile settings page that allows you to tune mobile behavior. 

<h4>CSS Frameworks</h4>
By default, Rover IDX creates beautiful pages.  If your site is already using Bootstrap, or you would like Rover to create it's elements using Bootstrap, simply choose 'Bootstrap' in the CSS Framework section of the Styling >> Quick Start page.


== Installation ==

1. Upload the `rover-idx` folder to the `/wp-content/plugins/` directory
1. Activate the Rover IDX plugin through the 'Plugins' menu in WordPress
1. Configure the plugin by going to the `Rover IDX` menu that appears in your admin menu

== Frequently Asked Questions ==

= Does Rover IDX work with any theme? =

Yes, as long as the theme follows Wordpress conventions.  Themes created for Wordpress 3+ are better.

= How is Rover IDX priced?  =

See https://roveridx.com/products/pricing/ for pricing.

= How many websites does my Rover IDX subscription allow?  =

You can run one subscription of Rover IDX on up to 5 websites for the active agent.

= If I have multiple websites with Rover IDX, can I tie them together? =

Yes!  Rover IDX has a 'link this website' feature that allows you and your clients to log into any of the websites and manage their settings and criteria.

= Does Rover IDX work with my MLS region? =

We work with about 50 MLS's, and that list is growing every month.  If we don't currently have your MLS region, we are happy to investigate adding it.  We do all the paperwork (except your signatures), and setting up a new region usually takes 2-3 days.

== Screenshots ==

1. Setting up Rover IDX is a breeze
2. Type in the name or broker last name of the office, and select the active agents.  You can set meta data for the agents as well.
3. Choose from any of the listing layout templates that ship with Rover IDX, or build your own.
4. Super easy to add searchable pages - [rover_idx_full_page] creates a fully searchable page with search panel, listing results, and in 2-column mode a sidebar with highlighted listings.
5. Simple, clean layouts for highly usable pages.
6. Don't forget to include a map.  Visitors love maps!
7. Easy add links to your sidebar, footer, or page.  These links take advantage of the Rover IDX dynamic url feature.  If the page does not exist in your website, Rover IDX will parse the url, hoping that the url is calling for searchable listings specified by the url.  See https://roveridx.com/features/roverurl/ for more information.

== Changelog ==

= 2.0.0.1314 =

* prepare for new feature - custom, non-MLS listings
* default system pages (/rover-control-panel/, /rover-custom-listing-panel/) to `naked` page template for maximum screen real estate
* improved reliability of automatic sitemap generation

= 2.0.0.1313 =

* added 'Search Panel Orientation' radio button to Rover Omni Search, Rover - Search by MLS Number, and Rover - Search by School widgets

= 2.0.0.1312 =

* quick search widget now defaults to price range dropdown, which is more mobile friendly
* declare support for Wordpress 4.8

= 2.0.0.1309 =

* added help text to Rover IDX >> SEO >> Dynamic Page Definitions
* improved reliability of automatic sitemap generation

= 2.0.0.1308 =

* quick fix for regression in 1307

= 2.0.0.1307 =

* Improve reliability of automatic sitemap generation
* Fix error when loading rover-control-panel using the `naked_page.php` template

= 2.0.0.1306 =

* PHP warning for admin panel
* Load help (Rover IDX >> Help) over https

= 2.0.0.1304 =

* Remove empty 'Last' label from recently viewed widget
* Resolve issue with selected cities not being passed to new and updated widget

= 2.0.0.1302 =

* Improve custom template - no longer need to create a page using that template to see template in dropdown
* Default dynamic page post_type to page
* Follow WP codex example to return custom template, and not exit

= 2.0.0.1301 =

* Fix strpos() issue for older Rover version upgrades

= 2.0.0.1294 =

* Fix uncommon path that could result in css not loaded for back end plugin

= 2.0.0.1274 =

* New search panels!
* Facebook Login!
* Property Highlight Banners!
* Map polygon search for visitors!
* Automatically add Login / Register to Wordpress menu!
* New rover_idx_registration shortcode - highly customizable!
* New rover_idx_contact shortcode - highly customizable!

= 1.2.6.1733 =

* Resolve parse error on old unsupported php versions

= 1.2.6.1731 =

* Improve saving of Styling settings to Wordpress options.

= 1.2.6.1605 =

* Rover IDX will now process Diverse Solutions shortcodes and IDX Data Filters.

= 1.2.6.1604 =

* Avoid duplicating Rover shortcode button on Visual tab when editing Post / Page
* Add 'listings per row' to Styling >> Listings page settings
* Use key 'search_panel_layout' for all quick search widgets
* Allow "Print" to work correctly when in a property details dialog (worked correctly in a new window)

= 1.2.6 =

* Although Rover IDX is 8 years old and used in many real estate sites, this is the initial Wordpress repository release.

== Upgrade Notice ==

