<?php
/*
Template Name: Single Library Database
*/
$post = get_post();

get_header();
?>
<div id="content">
<?php echo lib_databases_display($post); ?>
</div>
<?php
get_footer();
