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
  function lib_database_list( $atts, $content = null ) {
    if (is_search()) { return ''; }
    $the_query = Library_Databases_Helpers::query($atts);

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
  function lib_database_select( $atts, $content = null ) {
    if (is_search()) { return ''; }
    $the_query = Library_Databases_Helpers::query($atts);

    $menu_data = array();

    if ( $the_query->have_posts() ) {
      while ( $the_query->have_posts() ) {
        $the_query->the_post();
        $menu_option = array(
          'title' => get_the_title(),
          'url' => Library_Databases_Helpers::get_database_url(get_post()),
        );
  			if (Library_Databases_Helpers::requires_bpl_card(get_post())) {
  			  $menu_option['title'] = $menu_option['title'] . ' (with BPL eCard)';
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
    <div id="lib_databases_nav"></div>
    <script>
    jQuery("#lib_databases_nav").append('<label for="lib_databases_select">Database Quick Access</label>');
    jQuery("#lib_databases_nav").append('<select id="lib_databases_select"><option>—Select a Database—</option></select>');
    options = jQuery.map(JSON.parse('<?php echo json_encode($menu_data); ?>'), function( value, index ) {
       option = jQuery('<option></option>');
       option.html(value.title);
       option.attr('value',value.url);
       if (value.disabled) { option.attr('disabled','disabled'); }
       return option;
    });
    jQuery("#lib_databases_select").append(options);
    jQuery("#lib_databases_select").change(function() {
      window.location = jQuery("#lib_databases_select option:selected").val();
    });
    </script>
    <?php

    return ob_get_clean();
  }
}
