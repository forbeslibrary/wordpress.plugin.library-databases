<?php
/**
 * Plugin Name: Library Databases
 * Plugin URI: https://github.com/forbeslibrary/wordpress.plugin.library-databases
 * Author: Benjamin Kalish
 * Author URI: https://github.com/bkalish
 * Description: Provides easy access to and organization of a library databases and other electronic resources on the web.
 * Version: 1.0.0-dev
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
  }

  function add_filters() {
    add_filter('single_template', array($this, 'single_template'));
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
      #content .lib_databases_database_unavailable { color:#888; }
      #content .lib_databases_database_unavailable span { font-size:small; }
      #content .lib_databases_availability_text { font-style:italic; color:#555; }
      #content .lib_databases_availability_text a { font-weight:bold; }
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
   * Use a special template for showing a single lib_database on a page.
   *
   * @wp-hook single_template
   */
  function single_template($template){
    global $post;

    if ($post->post_type == 'lib_databases') {
       $template = dirname( __FILE__ ) . '/single-library-database.php';
    }
    return $template;
  }
}
new Library_Databases_Plugin();
