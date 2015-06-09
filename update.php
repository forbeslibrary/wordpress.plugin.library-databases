<?php
/**
 * Migration Tool for the Library Databases Plugin
 */
class Library_Databases_Update_Tool {
  /**
   * Migrate data from earlier versions
   */
  function update() {
    $version = get_option('forbes_databases_version');

    if (!$version) {
      $this->update_to_1_0_0();
    }
  }

  /**
   * Migrate to v1.0.0
   *
   * Earlier versions had hard coded access rules and didn't use the
   * forbes_database_categories taxonomy
   */
  function update_to_1_0_0() {
    global $post;
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
            default:
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
