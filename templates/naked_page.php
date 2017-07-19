<?php
/**
 * Rover Naked Page Template
 *
 * This template is used for the dynamic pages with no header, no footer, no sidebar
 *
 */

roveridx_css_and_js();

global	$rover_idx_content;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<article <?php post_class(); ?>>
	
		<header>
			<h2><?php the_title( $title_before, $title_after ); ?></h2>
		</header>
	
		<section class="entry">
		    <?php
	   		echo $rover_idx_content->rover_html;
		    ?>
		</section>
	
		<div class="fix"></div>
	
	</article><!-- /.post -->

	<?php wp_footer(); ?>

</body>
