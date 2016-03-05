<?php
/**
 * Custom taxonomy lib_databases_research_areas
 *
 * All necessary hooks are added when a new instance is created
 */
class Library_Databases_Research_Areas {
  static $tax_name = 'lib_databases_research_areas';

  function __construct() {
    add_action('init', array($this, 'init'));
    /*if (is_admin()) {
      require_once( dirname( __FILE__ ) . '/research-areas-admin.php' );
      new Library_Databases_Research_Areas_Admin();
    }*/
  }

  /**
   * Register the taxonomy
   */
  function init() {
    register_taxonomy(
      self::$tax_name,
      'lib_databases',
      array(
        'label' => 'Research Areas',
        'singular_label' => 'Research Area',
        'hierarchical' => True,
        'show_ui' => True
      )
    );
  }
}
