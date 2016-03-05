<?php
/*
Template Name: Content Library Database
Description: Displays the content of a single library database
*/
?>
<article id="post-<?php the_ID(); ?>" class="lib_databases post hentry">
  <?php if (Library_Databases_Helpers::is_inaccessible(get_post())): ?>
  <h2 class="entry-title lib_databases_database_unavailable">
    <?php echo Library_Databases_Helpers::get_availability_icon($post); ?>
    <?php the_title(); ?>
    <span> (available in library)</span>
  </h2>
  <?php else: ?>
  <h2 class="entry-title">
    <a href="<?php echo Library_Databases_Helpers::get_database_url($post); ?>">
    <?php echo Library_Databases_Helpers::get_availability_icon($post); ?>
    <?php the_title(); ?>
    <?php if (has_post_thumbnail( $post->ID )) {
      $image_attributes = wp_get_attachment_image_src( get_post_thumbnail_id(), 'thumbnail');
      $feature_image_url = $image_attributes[0];
      echo "<img src=\"$feature_image_url\" class=\"lib_database_feature_icon\">";
  }?>
  </a>
  </h2>
  <?php endif; ?>
<div class="entry-content">
  <?php echo apply_filters('the_content', $post->post_content); ?>
  <?php $availability_text = Library_Databases_Helpers::get_description_for_post($post); ?>
  <?php if ($availability_text): ?>
    <p class="lib_databases_availability_text">
      <?php echo $availability_text; ?>
    </p>
  <?php endif; ?>
</div>
<?php if (is_user_logged_in()): ?>
  <footer class="entry-utility"><span class="edit-link"><?php edit_post_link('Edit Database'); ?></span></footer>
<?php endif; ?>
</article>