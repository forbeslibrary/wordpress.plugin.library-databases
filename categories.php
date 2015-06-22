<?php
class Library_Databases_Categories {
  static $tax_name = 'lib_databases_categories';

  function __construct() {
    add_action('init', array($this, 'init'));
    if (is_admin()) {
      require_once( dirname( __FILE__ ) . '/categories-admin.php' );
      new Library_Databases_Categories_Admin();
    }
  }

  /**
   * Register the taxonomy
   */
  function init() {
    register_taxonomy(
      self::$tax_name,
      'lib_databases',
      array(
        'label' => 'Access Categories',
        'labels' => array(
          'singular_label' => 'Access Category',
          'add_new_item' => 'Add Access Category',
          'edit_item' => 'Edit Access Category',
          'search_items' => 'Search Access Categories',
          'popular_items' => NULL
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
   * Returns the availability for the current post.
   */
  static function get_availability() {
    global $post;
    $taxonomy = get_taxonomy(self::$tax_name);

    $postterms = get_the_terms($post->ID, self::$tax_name);
    return ($postterms ? array_pop($postterms) : false);
  }
}
