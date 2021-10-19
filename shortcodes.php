<?php
/**
 * Shortcodes for the Library Databases plugin.
 */
class Library_Databases_Shortcodes {

  /**
   * A shortcode for listing lib_databases.
   *
   * @wp-hook add_shortcode lib_database_list
   */
  static function lib_database_list( $atts, $content = null ) {
    if (is_search()) { return ''; }
    $the_query = self::query($atts);

    ob_start();
    if ( $the_query->have_posts() ) {
      while ( $the_query->have_posts() ) {
        $the_query->the_post();
        echo Library_Databases_Helpers::display(get_post());
      }
    } else {
      echo 'no databases found';
    }
    wp_reset_postdata();

    return ob_get_clean();
  }

  /**
   * This shortcode creates a select menu of database titles.
   *
   * @wp-hook add_shortcode lib_database_select
   */
  static function lib_database_select( $atts, $content = null ) {
    extract( shortcode_atts( array(
      'title' => 'Database Quick Access',
      'select_message' => 'Select a Database',
    ), $atts ) );

    static $count = 0;
    $count++;

    if (is_search()) { return ''; }
    $the_query = self::query($atts);

    $menu_data = array();

    if ( $the_query->have_posts() ) {
      while ( $the_query->have_posts() ) {
        $the_query->the_post();
        $menu_option = array(
          'title' => get_the_title(),
          'url' => Library_Databases_Helpers::get_database_url(get_post()),
        );
        $postfix = Library_Databases_Helpers::get_postfix_for_post(get_post());
  			if ($postfix) {
  			  $menu_option['title'] = $menu_option['title'] . ' ' . $postfix;
  			}
        if (Library_Databases_Helpers::is_inaccessible(get_post())) {
          $menu_option['title'] = $menu_option['title'] . ' (available in library)';
          $menu_option['disabled'] = TRUE;
        }
        array_push($menu_data, $menu_option);
      }
    }
    wp_reset_postdata();

    ob_start();?>
    <div id="<?php echo "lib_databases_nav_$count"; ?>"></div>
    <script>
    var nav_id = "lib_databases_nav_<?php echo $count; ?>";
    var select_id = "lib_databases_select_<?php echo $count; ?>";
    jQuery('#' + nav_id).append('<label for="' + select_id + '"><?php echo $title; ?></label>');
    jQuery('#' + nav_id).append(' ');
    jQuery('#' + nav_id).append('<select id="' + select_id + '"><option><?php echo $select_message; ?></option></select>');
    options = jQuery.map(JSON.parse('<?php echo json_encode($menu_data); ?>'), function( value, index ) {
       option = jQuery('<option></option>');
       option.html(value.title);
       option.attr('value',value.url);
       if (value.disabled) { option.attr('disabled','disabled'); }
       return option;
    });
    jQuery('#' + select_id).append(options);
    jQuery('#' + select_id).change(function() {
      window.location = jQuery('#' + select_id + ' option:selected').val();
    });
    </script>
    <?php

    return ob_get_clean();
  }

  /**
   * Returns a wp_query object for the passed shortcode attributes.
   */
  static function query($atts) {
    extract( shortcode_atts( array(
      'research_area' => null,
      'exclude_category' => null,
    ), $atts ) );

    $query_args = array(
      'post_type' => 'lib_databases',
      'orderby' => 'title',
      'order' => 'ASC',
      'posts_per_page'=>-1,
      );

    if ($research_area) {
      $query_args['tax_query'] = array( array(
        'taxonomy' => 'lib_databases_research_areas',
        'field'=>'slug',
        'include_children'=>FALSE,
        'terms' => $research_area) );
    }

    if ($exclude_category) {
      $query_args['tax_query'] = array( array(
        'taxonomy' => 'lib_databases_categories',
        'field'=>'slug',
        'include_children'=>FALSE,
        'terms' => $exclude_category,
        'operator' => 'NOT IN') );
    }

    $the_query = new WP_Query( $query_args );

    return $the_query;
  }

}
