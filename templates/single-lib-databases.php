<?php
/*
Template Name: Single Library Database
*/
$post = get_post();
$archive_link = get_post_type_archive_link( $post->post_type );
$name = get_post_type_object($post->post_type)->labels->name;

get_header();
?>
<div class="crumbs breadcrumbs" id="breadcrumbs"><a href="<?php echo $archive_link; ?>">
<?php echo $name; ?></a>→<?php echo get_the_title(); ?>
</div>
<div id="content">
<?php load_template( dirname( __FILE__ ) . '/partials/content-library-database.php', False ); ?>
</div>
<?php
get_footer();
