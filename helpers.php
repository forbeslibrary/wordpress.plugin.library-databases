<?php
/**
 * Helper functions for the Library Databases plugin.
 */
class Library_Databases_Helpers {
  /**
   * Returns image tag for the availability icon.
   */
  static function get_availability_icon($post) {
    return self::get_image_for_post();
  }

  /**
   * Returns a statement about the availability and funding source of the database.
   *
   * deprecated. Should not be called by new code, but still used by update.php
   */
  static function get_availability_text($post) {
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
  static function is_inaccessible($post) {

    if (self::is_post_restricted_by_ip() && !self::user_in_library()) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Is the user in the library?
   */
  static function user_in_library() {
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
  static function get_database_url($post) {

    $custom = get_post_custom($post->ID);
    $database_main_url = $custom["database_main_url"][0];
    $database_home_use_url = $custom["database_home_use_url"][0];
    $database_availability = $custom["database_availability"][0];
    if ($database_home_use_url && !self::user_in_library()) {
       return $database_home_use_url;
    }
    return $database_main_url;
  }

  /**
   * Returns a simple HTML rendering of the database.
   */
  static function display($post) {
    ob_start();?>
    <?php load_template( dirname( __FILE__ ) . '/templates/partials/content-library-database.php', False ); ?>
    <?php
    return ob_get_clean();
  }

  /**
   * Returns the description for the lib_databases_categories term associated
   * with a post.
   *
   * Uses the current post if none is specified.
   */
  static function get_description_for_post($post = 0) {
    $post = get_post($post);
    $term_id = self::get_term_for_post($post)->term_id;
    return Library_Databases_Categories::get_description($term_id);
  }

  /**
   * Returns an image tag for the media for the lib_databases_categories term
   * associated with a post.
   *
   * Uses the current post if none is specified.
   */
  static function get_image_for_post($post = 0) {
    $post = get_post($post);
    $term_id = self::get_term_for_post($post)->term_id;
    return Library_Databases_Categories::get_image($term_id);
  }

  /**
   * Returns the title postfix for select menus for the lib_databases_categories
   * term associated with a post.
   *
   * Uses the current post if none is specified.
   */
  static function get_postfix_for_post($post = 0) {
    $post = get_post($post);
    $term_id = self::get_term_for_post($post)->term_id;
    return Library_Databases_Categories::get_postfix($term_id);
  }

  /**
   * Returns true if the lib_databases_categories term associated
   * with a post is restricted by ip.
   *
   * Uses the current post if none is specified.
   */
  static function is_post_restricted_by_ip($post = 0) {
    $post = get_post($post);
    $term_id = self::get_term_for_post($post)->term_id;
    return Library_Databases_Categories::is_restricted_by_ip($term_id);
  }

  /**
   * Returns the lib_databases_categories term for a post.
   *
   * Uses the current post if none is specified.
   */
  static function get_term_for_post($post = 0) {
    $post = get_post($post);

    $postterms = get_the_terms($post->ID, Library_Databases_Categories::$tax_name);
    return (is_array($postterms) ? array_pop($postterms) : false);
  }
}
