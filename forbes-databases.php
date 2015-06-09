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
  private $admin;

  function __construct() {
    $this->load_dependencies();
    register_activation_hook(__FILE__, array($this, 'flush_rewrites'));
    $this->add_actions();
    $this->add_filters();
    $this->add_shortcodes();
  }

  function load_dependencies() {
    require_once( dirname( __FILE__ ) . '/helpers.php' );
    require_once( dirname( __FILE__ ) . '/shortcodes.php' );
    if ( is_admin() ) {
      require_once(dirname( __FILE__ ) . '/admin.php');
      $this->admin = new Library_Databases_Plugin_Admin();
    }
  }

  function add_actions() {
    $admin = $this->admin;
    add_action('wp_head', array($this, 'public_css'));
    add_action('dashboard_glance_items', array($this, 'add_glance_items'));
    add_action('forbes_database_categories_add_form_fields', array($this, 'categories_custom_fields'));
    add_action('init', array($this, 'init'));
    add_action('admin_init', array($admin, 'init'));
    add_action('admin_menu', array($admin, 'menu'));
    add_action('add_meta_boxes', array($admin, 'add_meta_boxes'));
    add_action('save_post', array($admin, 'save_details'));
    add_action('manage_forbes_databases_posts_custom_column', array($admin, 'custom_columns'));
    add_action('admin_head', array($admin, 'admin_css'));
  }

  function add_filters() {
    $admin = $this->admin;
    add_filter('single_template', array($this, 'single_template'));
    add_filter('manage_forbes_databases_posts_columns', array($admin, 'manage_columns'));
  }

  function add_shortcodes() {
    add_shortcode( 'forbes_database_list', 'forbes_database_list_shortcode_handler' );
    add_shortcode( 'forbes_database_feature', 'forbes_database_feature_shortcode_handler' );
    add_shortcode( 'forbes_database_select', 'forbes_database_select_shortcode_handler' );
  }

  /**
   * Flush rewrite rules on plugin activation
   *
   * This is registered with register_activation_hook for this file
   */
  function flush_rewrites() {
    forbes_databases_init();
    flush_rewrite_rules();
  }

  /**
   * Initializes the plugin.
   *
   * @wp-hook init
   */
  function init() {
    $this->register_custom_post_types();
    $this->register_custom_taxonomies();
    forbes_databases_migrate();
  }

  /**
   * Registers the custom post type forbes_databases
   */
  function register_custom_post_types() {
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
  }

  /**
   * Registers the custom taxonomies
   */
  function register_custom_taxonomies() {
    register_taxonomy(
      'research-area',
      array('forbes_databases'),
      array(
        'label' => 'Research Areas',
        'singular_label' => 'Research Area',
        'hierarchical' => True,
        'show_ui' => True
      )
    );

    register_taxonomy(
      'forbes_database_categories',
      'forbes_databases',
      array(
        'label' => 'Access Categories',
        'labels' => array(
          'singular_label' => 'Access Category',
          'add_new_item' => 'Add Access Category',
          'edit_item' => 'Edit Access Category',
        ),
        'hierarchical' => False,
        'show_ui' => True,
        'capabilities' => array(
          'manage_terms' => 'manage_options', // by default only admin
          'edit_terms' => 'manage_options',
          'delete_terms' => 'manage_options',
          'assign_terms' => 'edit_posts'  // means administrator', 'editor', 'author', 'contributor'
        )
      )
    );
  }

  /**
   * Adds custom CSS to public pages.
   *
   * @wp-hook wp_head
   */
  function public_css() {
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
  function single_template($template){
    global $post;

    if ($post->post_type == 'forbes_databases') {
       $template = dirname( __FILE__ ) . '/single-forbes-database.php';
    }
    return $template;
  }
}
new Library_Databases_Plugin();

/**
 * Migrate data from earlier versions
 */
function forbes_databases_migrate() {
  global $post;
  $version = get_option('forbes_databases_version');

  // Migrate to version 1.0.0. Earlier versions had hardcoded access rules
  if (!$version) {
    $query = new WP_Query(
      array(
        'post_type' => 'forbes_databases',
        'nopaging' => true
      )
    );
    if ( $query->have_posts() ) {
      while ( $query->have_posts() ) {
        $query->the_post();
        $custom = get_post_custom($post->ID);
        $availability = $custom["database_availability"][0];
        if (!term_exists($availability, 'forbes_database_categories')) {
          switch ($availability) {
            case 'state-wide':
              $name = "Free State Wide.";
              break;
            case 'cwmars':
              $name = "Free With C/W Mars Card";
              break;
            case 'forbes-card':
              $name = "Free With Forbes Card";;
              break;
            case 'bpl-ecard':
              $name = 'Free With BPL ECard';
              break;
            case 'in-library':
              $name = 'Free In Library';
              break;
            case 'anywhere':
              $name = 'Free Anywhere';
          }
          $args = array(
            'description' => forbes_databases_get_availability_text($post),
            'slug' => $availability
          );
          wp_insert_term($name, 'forbes_database_categories', $args);
        }
        wp_set_object_terms($post->ID, $availability, 'forbes_database_categories');
      }
    }
    wp_reset_query();
  }
}
