<?php
/**
 * Migration Tool for the Library Databases Plugin
 */
class Library_Databases_Update_Tool {
  /**
   * Migrate data from earlier versions
   */
  function update() {
    if (!get_option('lib_databases_version')) {
      $this->update_to_1_0_0();
    }
    if (version_compare(get_option('lib_databases_version'), "1.0.3", "<")) {
      $this->update_to_1_0_3();
    }
  }

  /**
   * Migrate to v1.0.1
   *
   * Bug fix update.
   */
  function update_to_1_0_3() {
    update_option( 'lib_databases_version', '1.0.3' );
  }

  /**
   * Migrate to v1.0.0
   *
   * Earlier versions had hard coded access rules and didn't use the
   * lib_database_categories taxonomy
   */
  function update_to_1_0_0() {
    $this->rename_custom_posts_for_1_0_0();
    $this->rename_taxonomies_for_1_0_0();
    $this->update_availability_for_1_0_0();
    $this->update_settings_for_1_0_0();
    $this->update_shortcodes_for_1_0_0();
    update_option( 'lib_databases_version', '1.0.0' );
  }

  function update_availability_for_1_0_0() {
    global $post;
    $query = new WP_Query(
      array(
        'post_type' => 'lib_databases',
        'nopaging' => true
      )
    );
    if ( $query->have_posts() ) {
      while ( $query->have_posts() ) {
        $query->the_post();
        $custom = get_post_custom($post->ID);
        $availability = $custom["database_availability"][0];
        if (!term_exists($availability, 'lib_databases_categories')) {
          switch ($availability) {
            case 'state-wide':
              $name = "Free State Wide";
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
            'description' => Library_Databases_Helpers::get_availability_text($post),
            'slug' => $availability
          );
          $result = wp_insert_term($name, 'lib_databases_categories', $args);
          if (is_array($result)) {
            $term_id = $result['term_id'];
            if ($availability == 'bpl-ecard') {
              $term_meta = get_option( "taxonomy_{$term_id}" );
              $term_meta['postfix'] = '(with BPL eCard)';
              update_option( "taxonomy_{$term_id}", $term_meta );
            }
          }
        }
        wp_set_object_terms($post->ID, $availability, 'lib_databases_categories');
      }
    }
    wp_reset_query();
  }

  function rename_custom_posts_for_1_0_0() {
    register_post_type('forbes_databases'); // we must register the old post type
    $items = get_posts('numberposts=-1&post_status=any&post_type=forbes_databases');
    foreach ($items as $item) {
      $update['ID'] = $item->ID;
      $update['post_type'] = 'lib_databases';
      wp_update_post( $update );
    }
  }

  function rename_taxonomies_for_1_0_0() {
    // the custom taxonomy lib_databases_research_areas used to be called research-area
    global $wpdb;
    register_taxonomy('research-area', 'lib_databases'); // we must register the taxonomy
    $terms = get_terms('research-area');
    foreach ($terms as $term) {
      $wpdb->query(
        "
        UPDATE $wpdb->term_taxonomy
        SET taxonomy = 'lib_databases_research_areas'
        WHERE term_id = $term->term_id
        "
      );
    }
  }

  function update_settings_for_1_0_0() {
    global $wpdb;
    $wpdb->update(
      $wpdb->options,
      array('option_name' => 'lib_databases_settings_ip_addresses'),
      array('option_name' => 'forbes_databases_settings_ip_addresses')
    );
  }

  function update_shortcodes_for_1_0_0() {
    global $post;
    $shortcode_migrations = array(
      'forbes_database_list' => 'lib_database_list',
      'forbes_database_select' => 'lib_database_select',
    );
    foreach ($shortcode_migrations as $old => $new) {
      $query = new WP_Query(
        array(
          's' => $old,
          'nopaging' => true
        )
      );
      if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
          $query->the_post();
          $update['ID'] = $post->ID;
          $update['post_content'] = str_replace($old, $new, $post->post_content);
          wp_update_post( $update );
        }
      }
      wp_reset_query();
    }
  }
}
