<?php
/**
 * Plugin Name: Library Databases
 * Plugin URI: https://github.com/forbeslibrary/wordpress.plugin.library-databases
 * Author: Benjamin Kalish
 * Author URI: https://github.com/bkalish
 * Description: Provides easy access to and organization of a library databases and other electronic resources on the web.
 * Version: 1.1.0
 * License: GNU General Public License v2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

class Library_Databases_Plugin {
  function __construct() {
    $this->load_dependencies();
    $this->add_actions();
    $this->add_filters();
    $this->add_shortcodes();
    register_activation_hook(__FILE__, array($this, 'flush_rewrites'));
    register_activation_hook(__FILE__, array($this, 'update'));
  }

  function load_dependencies() {
    require_once( dirname( __FILE__ ) . '/categories.php' );
    new Library_Databases_Categories();
    require_once( dirname( __FILE__ ) . '/research-areas.php' );
    new Library_Databases_Research_Areas();
    require_once( dirname( __FILE__ ) . '/helpers.php' );
    require_once( dirname( __FILE__ ) . '/shortcodes.php' );
    if ( is_admin() ) {
      require_once(dirname( __FILE__ ) . '/admin.php');
      $this->admin = new Library_Databases_Plugin_Admin();
    }
  }

  function add_actions() {
    add_action('init', array($this, 'init'));
    add_action('wp_head', array($this, 'public_css'));
    add_action('pre_get_posts', array($this, 'archive_sort_order'));
  }

  function add_filters() {
    add_filter('single_template', array($this, 'single_template'));
    add_filter('archive_template', array($this, 'archive_template'));
  }

  function add_shortcodes() {
    add_shortcode( 'lib_database_list', array('Library_Databases_Shortcodes', 'lib_database_list'));
    add_shortcode( 'lib_database_select', array('Library_Databases_Shortcodes', 'lib_database_select'));
  }

  /**
   * Flush rewrite rules on plugin activation
   *
   * This is registered with register_activation_hook for this file
   */
  function flush_rewrites() {
    $this->init();
    $library_databases_categories = new Library_Databases_Categories();
    $library_databases_categories->init();
    flush_rewrite_rules();
  }

  /**
   * Update the plugin
   *
   * This is registered with register_activation_hook for this file
   */
  function update() {
    require_once( dirname( __FILE__ ) . '/update.php' );
    $update_tool = new Library_Databases_Update_Tool();
    $update_tool->update();
  }

  /**
   * Initializes the plugin.
   *
   * @wp-hook init
   */
  function init() {
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
      'has_archive' => true,
      'labels' => $labels,
      'public' => true,
      'publicly_queryable' => true,
      'show_ui' => true,
      'query_var' => true,
      'rewrite' =>  array('slug' => 'databases', 'with_front' => false),
      'capability_type' => 'post',
      'hierarchical' => false,
      'menu_icon' => 'dashicons-admin-site',
      'menu_position' => 5, // admin menu appears after Posts but before Media
      'supports' => array('title','editor','thumbnail')
    );

    register_post_type( 'lib_databases' , $args );
  }

  /**
   * Adds custom CSS to public pages.
   *
   * @wp-hook wp_head
   */
  function public_css() {
    ?>
    <style>
      .lib_databases_database_unavailable { color:#888; }
      .lib_databases_database_unavailable span { font-size:small; }
      .lib_databases_availability_text { font-style:italic; color:#555; }
      .lib_databases_availability_text a { font-weight:bold; }
      .lib_databases_category_image {
        vertical-align: middle;
        display: inline-block;
      }
      .lib_databases header .title_area {
        display: inline-block;
        vertical-align: middle;
      }
      .lib_database_feature_icon { vertical-align: middle; }
      .lib_databases .permalink,
      .lib_databases .database-link,
      .lib_databases .learn-more-link {
        margin-right: 0.5em;
      }
      .lib_databases .database-link,
      .lib_databases .learn-more-link {
        border-left: solid black 2px;
        padding-left: 0.5em;
      }
      .lib_databases .quick_links {

      }
    </style>
    <?php
  }

  /**
   * Use a special template for showing a single lib_database on a page.
   *
   * @wp-hook single_template
   */
  function single_template($template){
    global $post;

    if ($post->post_type == 'lib_databases') {
       $template = dirname( __FILE__ ) . '/templates/single-lib-databases.php';
    }
    return $template;
  }

  /**
   * Use a special template for showing the lib_database archives.
   *
   * @wp-hook archive_template
   */
  function archive_template($template){
    global $post;

    if ($post->post_type == 'lib_databases') {
       $template = dirname( __FILE__ ) . '/templates/archive-lib-databases.php';
    }
    return $template;
  }

  /**
   * Show all databases in alphabetical order on archive page.
   *
   * @wp-hook pre_get_posts
   */
  function archive_sort_order($query){
    if (! is_admin() && $query->is_main_query() && is_post_type_archive( 'lib_databases' )) {
      //Set the order ASC or DESC
      $query->set( 'order', 'ASC' );
      //Set the orderby
      $query->set( 'orderby', 'title' );
      //Show all Databases
      $query->set( 'nopaging', true );
    }
  }
}
new Library_Databases_Plugin();
