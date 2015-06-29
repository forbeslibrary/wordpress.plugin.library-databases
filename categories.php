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
   * Returns the description for the lib_databases_categories term with the
   * given id.
   */
  static function get_description($term_id) {
    return term_description( $term_id, self::$tax_name);
  }

  /**
   * Returns an image tag for the media for the lib_databases_categories term
   * with the given id.
   */
  static function get_image($term_id) {
    $term_meta = get_option( "taxonomy_{$term_id}" );
    if (isset($term_meta['image'])) {
      return wp_get_attachment_image($term_meta['image'], array(32, 32));
    }
    return '';
  }

  /**
   * Returns true if the lib_databases_categories term associated
   * with the given id is restricted by IP address.
   */
  static function is_restricted_by_ip($term_id) {
    $term_meta = get_option( "taxonomy_{$term_id}" );
    if (isset($term_meta['library_use_only'])) {
      return $term_meta['library_use_only'];
    }
    return false;
  }
}
