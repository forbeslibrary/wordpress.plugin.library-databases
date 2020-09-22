<?php
/*
Template Name: Archives
*/
get_header(); ?>

<div id="container">
	<div id="content" role="main">

		<?php the_post(); ?>
		<h1 class="entry-title"><?php the_title(); ?></h1>

		<?php if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				echo Library_Databases_Helpers::display(get_post());
			}
		} ?>

	</div><!-- #content -->
</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
