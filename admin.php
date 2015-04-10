<?php
/**
 * Admin interface for the Forbes Databases plugin.
 */

/**
 * @wp-hook admin_menu
 */
function forbes_databases_admin_menu() {
  add_options_page(
    // Page Title
    __('Library Databases Settings'),
    // Menu Title
    __('Library Databases'),
    // Capability
    'manage_options',
    // Menu Slug (also-referred to as option group)
    'forbes_databases_settings_page',
    // Callback
    'forbes_databases_output_settings_page'
  );
}

/**
 * Initializes the settings and fields using the settings API
 *
 * @wp-hook admin_init
 */
function forbes_databases_admin_init() {
  // settings api

  add_settings_section(
    // ID
    'default',
    // Title
    __('In Library Use'),
    // Callback
    'forbes_databases_output_defeault_settings_section',
    // Page
    'forbes_databases_settings_page'
  );

  add_settings_field(
    // ID
    'forbes_databases_settings_ip_addresses',
    // Title
    __('Library Databases In Library Use IP Addresses'),
    // Callback
    'forbes_databases_output_ip_addresses_form_field',
    // Page
    'forbes_databases_settings_page'//,
    // Section
    //'forbes_databases_settings_in_library_section'
  );

  register_setting(
    'forbes_databases_settings_page',
    'forbes_databases_settings_ip_addresses'
  );
}

/**
 * Outputs HTML for the forbes_databases settings page.
 *
 * This is a callback function for the Wordpress Settings API
 */
function forbes_databases_output_settings_page() {
  ?>
  <h1><?php echo __('Library Databases Settings'); ?></h1>
  <form method="POST" action="options.php">
    <?php
    settings_fields( 'forbes_databases_settings_page' );
    do_settings_sections( 'forbes_databases_settings_page' );
    submit_button();
    ?>
  </form>
  <?php
}

/**
 * Outputs HTML for the forbes_databases settings page default section.
 *
 * This is a callback function for the Wordpress Settings API
 */
function forbes_databases_output_defeault_settings_section() {
  echo ''; // no explanatory text for this section
}

/**
 * Outputs HTML for the forbes_databases settings ip address field.
 *
 * This is a callback function for the Wordpress Settings API
 */
function forbes_databases_output_ip_addresses_form_field() {
  ?>
  <textarea
    name="forbes_databases_settings_ip_addresses"
    id="forbes_databases_settings_ip_addresses"
    rows="8"
    cols="20"
    class="code"
  ><?php echo get_option( 'forbes_databases_settings_ip_addresses' ); ?></textarea>
  <p class="description">Please enter each IP address on its own line.<p>
  <?php
}

/**
 * Adds custom CSS to admin pages.
 *
 * @wp-hook admin_head
 */
function forbes_databases_admin_css() {
  ?>
  <style>
    #database-url-meta label { display:block; margin-top:1em; }
    #database-url-meta label:first-child { margin-top: 0; }
    .column-research-area { width: 8em; }
    .column-uam_access { width: 8em; } /* this column created by User Access Manager plugin */
    #dashboard_right_now .forbes_databases-count a:before,
    #dashboard_right_now .forbes_databases-count span:before {
      content: "\f319";
    }
  </style>
  <?php
}

/**
 * Add information about forbes_databases to the glance items.
 *
 * @wp-hook dashboard_glance_items
 */
function forbes_databases_add_glance_items() {
  $pt_info = get_post_type_object('forbes_databases');
  $num_posts = wp_count_posts('forbes_databases');
  $num = number_format_i18n($num_posts->publish);
  $text = _n( $pt_info->labels->singular_name, $pt_info->labels->name, intval($num_posts->publish) ); // singular/plural text label
  echo '<li class="page-count '.$pt_info->name.'-count"><a href="edit.php?post_type=forbes_databases">'.$num.' '.$text.'</li>';
}

/**
 * Save custom fields from forbes_databases edit page.
 *
 * @wp-hook save_post
 */
function forbes_databases_save_details(){
  global $post;

  if (isset($_POST["database_main_url"])) {
    update_post_meta($post->ID, "database_main_url", $_POST["database_main_url"]);
  }
  if (isset($_POST["database_home_use_url"])) {
    update_post_meta($post->ID, "database_home_use_url", $_POST["database_home_use_url"]);
  }
  if (isset($_POST["database_availability"])) {
    update_post_meta($post->ID, "database_availability", $_POST["database_availability"]);
  }
}

/**
 * Adds custom fields to the forbes_databases edit page.
 *
 * @wp-hook add_meta_boxes
 */
function forbes_databases_add_meta_boxes(){
  add_meta_box(
    "database-availability-meta",
    __("Database Availability"),
    "forbes_databases_editbox_database_availability",
    "forbes_databases",
    "side",
    "high"
  );
  add_meta_box(
    "database-url-meta",
    __("Database URL"),
    "forbes_databases_editbox_database_urls",
    "forbes_databases",
    "side",
    "high"
  );
}

/**
 * Outputs the contents of each custom column on the forbes_databases admin page.
 *
 * @wp-hook manage_forbes_databases_posts_custom_column
 */
function forbes_databases_custom_columns($column){
  global $post;

  switch ($column) {
    case "description":
      the_excerpt();
      break;
    case 'research-area':
      echo implode(', ', wp_get_post_terms($post->ID, 'research-area', array("fields" => "names")));
      break;
  }
}

/**
 * Customizes the columns on the forbes_databases admin page.
 *
 * @wp-hook manage_forbes_databases_posts_columns
 */
function forbes_databases_manage_columns($columns){
  $columns = array_merge( $columns, array(
    'title' => __('Database Title'),
    'research-area' => __('Research Area'),
    'description' => __('Description'),
  ));

  return $columns;
}

/**
 * Returns the html for the database urls box on the forbes_databases edit page.
 */
function forbes_databases_editbox_database_urls(){
  global $post;
  $custom = get_post_custom($post->ID);
  if (isset($custom["database_main_url"])) {
    $database_main_url = $custom["database_main_url"][0];
  } else {
    $database_main_url = "";
  }
  if (isset($custom["database_home_use_url"])) {
    $database_home_use_url = $custom["database_home_use_url"][0];
  } else {
    $database_home_use_url = "";
  }
  ?>
  <label><?php echo __('Main URL'); ?>:</label>
  <input name="database_main_url" value="<?php echo $database_main_url; ?>" />
  <label><?php echo __('Home Use URL (if different)'); ?>:</label>
  <input name="database_home_use_url" value="<?php echo $database_home_use_url; ?>" />
  <?php
}

/**
 * Returns the html for the database availability box on the forbes_databases edit page.
 */
function forbes_databases_editbox_database_availability(){
  global $post;
  $custom = get_post_custom($post->ID);
  if (isset($custom["database_availability"])) {
    $database_availability = $custom["database_availability"][0];
  } else {
    $database_availability = "";
  }
  ?>
  <label for="forbes-database_availability-state-wide">
    <?php echo __('Free State Wide'); ?>
  </label>
  <input id="forbes-database_availability-state-wide" type="radio" name="database_availability" value="state-wide" <?php if ($database_availability=='state-wide'):?>checked<?php endif;?> ><br>
  <label for="forbes-database_availability-cwmars">
    <?php echo __('Free With C/W Mars Card'); ?>
  </label>
  <input id="forbes-database_availability-cwmars" type="radio" name="database_availability" value="cwmars" <?php if ($database_availability=='cwmars'):?>checked<?php endif;?> ><br>
  <label for="forbes-database_availability-forbes-card">
    <?php echo __('Free With Forbes Card'); ?>
  </label>
  <input id="forbes-database_availability-forbes-card" type="radio" name="database_availability" value="forbes-card" <?php if ($database_availability=='forbes-card'):?>checked<?php endif;?> ><br>
  <label for="forbes-database_availability-bpl-ecard">
    <?php echo __('Free With BPL ECard'); ?>
  </label>
  <input id="forbes-database_availability-bpl-ecard" type="radio" name="database_availability" value="bpl-ecard" <?php if ($database_availability=='bpl-ecard'):?>checked<?php endif;?> ><br>
  <label for="forbes-database_availability-in-library">
    <?php echo __('Free In Library'); ?>
  </label>
  <input id="forbes-database_availability-in-library" type="radio" name="database_availability" value="in-library" <?php if ($database_availability=='in-library'):?>checked<?php endif;?> ><br>
  <label for="forbes-database_availability-anywhere">
    <?php echo __('Free Anywhere'); ?>
  </label>
  <input id="forbes-database_availability-anywhere" type="radio" name="database_availability" value="anywhere" <?php if ($database_availability=='anywhere'):?>checked<?php endif;?> ><br>
  <?php
}
