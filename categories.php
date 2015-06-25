<?php
/**
 * Custom taxonomy lib_databases_categories
 *
 * All necessary hooks are added when a new instance is created
 */
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
   * Returns the description for the lib_databases_categories term associated
   * with a post.
   *
   * Uses the current post if none is specified.
   */
  static function get_description($post = 0) {
    $post = get_post($post);
    $term_id = self::get_availability($post)->term_id;
    return term_description( $term_id, self::$tax_name);
  }

  /**
   * Returns true if the lib_databases_categories term associated
   * with a post is restricted by ip.
   *
   * Uses the current post if none is specified.
   */
  static function is_restricted_by_ip($post = 0) {
    $post = get_post($post);
    $term = self::get_availability($post);
    if (!$term) {
      return;
    }

    $term_id = $term->term_id;
    $term_meta = get_option( "taxonomy_{$term_id}" );
    if (isset($term_meta['library_use_only'])) {
      return $term_meta['library_use_only'];
    }
    return false;
  }

  /**
   * Returns the lib_databases_categories term for a post.
   *
   * Uses the current post if none is specified.
   */
  static function get_availability($post = 0) {
    $post = get_post($post);

    $postterms = get_the_terms($post->ID, self::$tax_name);
    return (is_array($postterms) ? array_pop($postterms) : false);
  }
}
