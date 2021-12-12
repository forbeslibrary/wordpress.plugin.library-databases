<?php
/**
 * Template Name: Archives
 *
 * @package LibraryDatabases
 */

require_once dirname( __FILE__ ) . '/../class-database.php';

use ForbesLibrary\WordPress\LibraryDatabases\Database;

get_header(); ?>

<div id="content" role="main">

	<?php the_post(); ?>
	<h1 class="entry-title"><?php post_type_archive_title(); ?></h1>
	<?php
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			$database = new Database( get_post() );
			$database->show();
		}
	}
	?>

</div><!-- #content -->

<?php get_footer(); ?>
