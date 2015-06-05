<?php
/**
 * Plugin Name: Library Databases
 * Plugin URI: https://github.com/forbeslibrary/wordpress.plugin.library-databases
 * Author: Benjamin Kalish
 * Author URI: https://github.com/bkalish
 * Description: Provides easy access to and organization of a library databases and other electronic resources on the web.
 * Version: 1.0.0-dev
 */

require_once( dirname( __FILE__ ) . '/helpers.php' );
require_once( dirname( __FILE__ ) . '/shortcodes.php' );
if ( is_admin() ) {
  require_once(dirname( __FILE__ ) . '/admin.php');
}

// action hooks
add_action('init', 'forbes_databases_init');
add_action('admin_init', 'forbes_databases_admin_init');
add_action('admin_menu', 'forbes_databases_admin_menu');
add_action('add_meta_boxes', 'forbes_databases_add_meta_boxes');
add_action('save_post', 'forbes_databases_save_details');
add_action('manage_forbes_databases_posts_custom_column', 'forbes_databases_custom_columns');
add_action('admin_head', 'forbes_databases_admin_css' );
add_action('wp_head', 'forbes_databases_public_css');
add_action('dashboard_glance_items', 'forbes_databases_add_glance_items');

// filter hooks
add_filter('manage_forbes_databases_posts_columns', 'forbes_databases_manage_columns');
add_filter('single_template', 'forbes_database_single_template');

// shortcode hooks
add_shortcode( 'forbes_database_list', 'forbes_database_list_shortcode_handler' );
add_shortcode( 'forbes_database_feature', 'forbes_database_feature_shortcode_handler' );
add_shortcode( 'forbes_database_select', 'forbes_database_select_shortcode_handler' );

/**
 * Registers the custom post type forbes_databases and the custom taxonomy research-area.
 *
 * @wp-hook init
 */
function forbes_databases_init() {
  $labels = array(
    'name' => _x('Databases', 'post type general name'),
    'singular_name' => _x('Database', 'post type singular name'),
    'add_new' => _x('Add New', 'portfolio item'),
    'add_new_item' => __('Add New Database'),
    'edit_item' => __('Edit Database'),
    'new_item' => __('New Database'),
    'view_item' => __('View Database Page'),
    'search_items' => __('Search Databases'),
    'not_found' =>  __('Nothing found'),
    'not_found_in_trash' => __('Nothing found in Trash'),
    'parent_item_colon' => ''
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' =>  true,
    'capability_type' => 'post',
    'hierarchical' => false,
    'menu_icon' => 'dashicons-admin-site',
    'menu_position' => 5, // admin menu appears after Posts but before Media
    'supports' => array('title','editor','thumbnail')
  );

  register_post_type( 'forbes_databases' , $args );
  register_taxonomy("research-area", array("forbes_databases"), array("label" => "Research Areas", "singular_label" => "Research Area", 'hierarchical'=>True, 'show_ui'=>True));

}

/**
 * Adds custom CSS to public pages.
 *
 * @wp-hook wp_head
 */
function forbes_databases_public_css() {
  ?>
  <style>
    #content .forbes_databases_database_unavailable { color:#888; }
    #content .forbes_databases_database_unavailable span { font-size:small; }
    #content .forbes_databases_availability_text { font-style:italic; color:#555; }
    #content .forbes_databases_availability_text a { font-weight:bold; }
    .ico_in-library, .ico_state-wide, .ico_cwmars, .ico_bpl-ecard, .ico_forbes-card, .ico_anywhere {
      display: inline-block;
      background-image: url(<?php echo plugins_url('img/database-availability.png',__FILE__ )?>);
      background-repeat: no-repeat
    }

    .ico_in-library {
      background-position: -0px -0px;
      height: 69px;
      width: 64px
    }

    .ico_state-wide {
      background-position: -64px -0px;
      height: 64px;
      width: 64px
    }

    .ico_cwmars {
      background-position: -128px -0px;
      height: 64px;
      width: 64px
    }

    .ico_bpl-ecard {
      background-position: -0px -69px;
      height: 64px;
      width: 64px
    }

    .ico_forbes-card {
      background-position: -64px -69px;
      height: 64px;
      width: 64px
    }

    .ico_anywhere {
      background-position: -128px -69px;
      height: 64px;
      width: 64px
    }
  </style>
  <?php
}

/**
 * Use a special template for showing a single forbes_database on a page.
 *
 * @wp-hook single_template
 */
function forbes_database_single_template($template){
  global $post;

  if ($post->post_type == 'forbes_databases') {
     $template = dirname( __FILE__ ) . '/single-forbes-database.php';
  }
  return $template;
}
