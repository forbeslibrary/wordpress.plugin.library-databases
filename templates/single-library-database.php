<?php
/*
Template Name: Single Library Database
*/
$post = get_post();

get_header();
?>
<div id="content">
<?php load_template( dirname( __FILE__ ) . '/partials/content-library-database.php', False ); ?>
</div>
<?php
get_footer();
