<?php
/*
Template Name: Content Library Database
Description: Displays the content of a single library database
*/
global $more;
$content_array = get_extended($post->post_content);
?>
<article id="post-<?php the_ID(); ?>" class="lib_databases post hentry">
<header>
  <?php echo Library_Databases_Helpers::get_availability_icon($post); ?>
  <div class="title_area">
  <?php if (Library_Databases_Helpers::is_inaccessible(get_post())): ?>
  <h2 class="entry-title lib_databases_database_unavailable">
    <?php the_title(); ?>
    <span> (available in library)</span>
  </h2>
  <?php else: ?>
  <h2 class="entry-title">
    <a href="<?php echo Library_Databases_Helpers::get_database_url($post); ?>">
    <?php if (has_post_thumbnail( $post->ID )) {
      $image_attributes = wp_get_attachment_image_src( get_post_thumbnail_id(), 'thumbnail');
      $feature_image_url = $image_attributes[0];
      $title = get_the_title();
      echo "<img src=\"$feature_image_url\" class=\"lib_database_feature_icon\" alt=\"$title\" title=\"$title\">";
  } else {
    the_title();
  }?>
  </a>
  </h2>
  <?php endif; ?>
  <div class="quick_links">
    <span class="permalink"><a href="<?php the_permalink(); ?>">🔗 permalink</a></span>
    <?php if (! Library_Databases_Helpers::is_inaccessible(get_post())): ?>
    <span class="database-link"><a href="<?php echo Library_Databases_Helpers::get_database_url($post); ?>">↗ visit</a></span>
    <?php endif; ?>
    <?php if ($content_array['extended'] && !$more): ?>
    <span class="learn-more-link"><a href="<?php the_permalink(); ?>">❓ learn more</a></span>
    <?php endif; ?>
  </div>
  </div>
</header>
<div class="entry-content">
  <?php echo apply_filters('the_content', $content_array ['main']); ?>
  <?php $availability_text = Library_Databases_Helpers::get_description_for_post($post); ?>
  <?php if ($availability_text): ?>
    <p class="lib_databases_availability_text">
      <?php echo $availability_text; ?>
    </p>
  <?php endif; ?>
  <?php if ($more):?>
    <?php echo apply_filters('the_content', $content_array ['extended']); ?>
  <?php endif; ?>
</div>
<?php if (is_user_logged_in()): ?>
  <footer class="entry-utility"><span class="edit-link"><?php edit_post_link('Edit Database'); ?></span></footer>
<?php endif; ?>
</article>
