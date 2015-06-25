<?php
/**
 * Helper functions for the Library Databases plugin.
 */

/**
 * Returns image tag for the availability icon.
 */
function lib_databases_get_availability_icon($post) {
  return Library_Databases_Categories::get_image_for_post();
}

/**
 * Returns a statement about the availability and funding source of the database.
 *
 * deprecated. Should not be called by new code, but still used by update.php
 */
function lib_databases_get_availability_text($post) {
  $custom = get_post_custom($post->ID);
  $availability = $custom['database_availability'][0];
  switch ($availability) {
    case 'state-wide':
      $text = "Free for all Massachusetts residents. Provided by the Massachusetts Board of Library Commissioners and the Massachusetts Library System.";
      break;
    case 'cwmars':
      $text = "Free with any C/W MARS library card, and provided by the C/W MARS library network.";
      break;
    case 'forbes-card':
      $text = "Provided by Forbes Library. Remote access requires a Forbes Library card.";;
      break;
    case 'bpl-ecard':
      $text = 'Provided by the Boston Public Library and available for free with a BPL eCard. Individuals who live in, own property in, or commute to work in Massachusetts may <a href="https://www.bpl.org/contact/form_ecard.php">register for an eCard.</a>';
      break;
    case 'in-library':
      $text = "Provided by Forbes Library and available for use in the library.";
      break;
    default:
      $text = '';
  }
  return $text;
}

/**
 * Is the database inaccessible to the user?
 *
 * Returns TRUE for remote users if the database is in library use only.
 */
function lib_databases_is_inaccessible($post) {

  if (Library_Databases_Categories::is_post_restricted_by_ip() && !lib_databases_user_in_library()) {
    return TRUE;
  }
  return FALSE;
}

/**
 * Does the database require a BPL card?
 *
 * Returns TRUE for remote users if the database is provided by BPL.
 */
function lib_databases_requires_bpl_card($post) {
  $custom = get_post_custom($post->ID);
  $availability = $custom["database_availability"][0];
  if ($availability == 'bpl-ecard') {
    return TRUE;
  }
  return FALSE;
}

/**
 * Is the user in the library?
 */
function lib_databases_user_in_library() {
  $in_library_ip_addresses = explode(
    "\n",
    str_replace(
      "\r",
      '',
      get_option( 'lib_databases_settings_ip_addresses' ))
  );
  $remote_address =  $_SERVER['REMOTE_ADDR'];
  return in_array($remote_address, $in_library_ip_addresses);
}

/**
 * Returns the URL needed to access the database.
 *
 * The URL returned will be the home use url if and only it has been
 * defined and the user is outside of the library.
 */
function lib_databases_get_database_url($post) {

  $custom = get_post_custom($post->ID);
  $database_main_url = $custom["database_main_url"][0];
  $database_home_use_url = $custom["database_home_use_url"][0];
  $database_availability = $custom["database_availability"][0];
  if ($database_home_use_url && !lib_databases_user_in_library()) {
     return $database_home_use_url;
  }
  return $database_main_url;
}

/**
 * Returns a simple HTML rendering of the database.
 */
function lib_databases_display($post) {
  ob_start();?>
  <article id="post-<?php the_ID(); ?>" class="lib_databases post hentry">
    <?php if (lib_databases_is_inaccessible(get_post())): ?>
    <h2 class="entry-title lib_databases_database_unavailable">
      <?php echo lib_databases_get_availability_icon($post); ?>
      <?php the_title(); ?>
      <span> (available in library)</span>
    </h2>
    <?php else: ?>
    <h2 class="entry-title">
      <a href="<?php echo lib_databases_get_database_url($post); ?>">
      <?php echo lib_databases_get_availability_icon($post); ?>
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
    <?php $availability_text = Library_Databases_Categories::get_description_for_post($post);
    if ($availability_text) { echo '<p class="lib_databases_availability_text">' . $availability_text . '</p>'; } ?>
  </div>
  <?php if (is_user_logged_in()): ?>
    <footer class="entry-utility"><span class="edit-link"><?php edit_post_link('Edit Database'); ?></span></footer>
  <?php endif; ?>
  </article><?php
  return ob_get_clean();
}

/**
 * Returns a wp_query object for the passed shortcode attributes.
 */
function lib_databases_query($atts) {
  extract( shortcode_atts( array(
    'research_area' => null,
    'exclude_free' => null,
  ), $atts ) );

  $query_args = array(
    'post_type' => 'lib_databases',
    'orderby' => 'title',
    'order' => 'ASC',
    'posts_per_page'=>-1,
    );

  if ($research_area) {
    $query_args['tax_query'] = array( array('taxonomy' => 'lib_databases_research_areas', 'field'=>'slug', 'include_children'=>FALSE, 'terms' => $research_area) );
  }

  if ($exclude_free) {
    $query_args['meta_query'] = array( array('key' => 'database_availability', 'value'=>'anywhere', 'compare'=>'!=') );
  }

  $the_query = new WP_Query( $query_args );

  return $the_query;
}
