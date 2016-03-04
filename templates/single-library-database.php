<?php
/*
Template Name: Single Library Database
*/
$post = get_post();

get_header();
?>
<div id="content">
<?php echo Library_Databases_Helpers::display($post); ?>
</div>
<?php
get_footer();
