<?php
/**
 * Admin code for the custom taxonomy lib_databases_categories
 *
 * All necessary hooks are added when a new instance is created
 */
class Library_Databases_Categories_Admin {
  static $tax_name = 'lib_databases_categories';

  function __construct() {
    $tax_name = self::$tax_name;
    $actions = array(
      "admin_head" => 'embedUploaderCode',
      "admin_menu" => 'admin_menu',
      "add_meta_boxes" => "add_meta_boxes",
      "{$tax_name}_add_form_fields" => 'add_form_fields',
      "{$tax_name}_edit_form_fields" => 'edit_form_fields',
      "create_{$tax_name}" => 'create',
      "edit_{$tax_name}" => 'edit'
    );
    foreach ($actions as $action => $method_name) {
      add_action($action, array($this, $method_name));
    }

    add_filter(
      "manage_edit-{$tax_name}_columns",
      array($this, 'columns')
    );

    add_filter(
      "manage_{$tax_name}_custom_column",
      array($this, 'column_content'),
      10, 3
    );
  }

  /**
   * Adds a custom metabox to select a single lib_databases_categories term on
   * the lib_databases edit page.
   */
  function add_meta_boxes() {
    add_meta_box(
      "database-availability-meta",
      __("Database Availability"),
      array($this, 'output_metabox'),
      "lib_databases",
      "side",
      "high"
    );
  }

  /**
   * Returns the html for the database availability box on the lib_databases edit page.
   */
  function output_metabox(){
    global $post;
    $tax_name = self::$tax_name;
    $tax = get_taxonomy($tax_name);

    //The name of the form
    $name = "tax_input[$tax_name]";

    //Get all the terms for this taxonomy
    $terms = get_terms($tax_name, array('hide_empty' => 0));

    $current = Library_Databases_Helpers::get_term_for_post()->term_id;
    ?>
    <ul id="<?php echo $tax_name; ?>checklist" class="list:<?php echo $tax_name; ?> categorychecklist form-no-clear">
      <?php foreach($terms as $term) :?>
          <?php $id = $tax_name.'-'.$term->term_id; ?>
          <li id="<?php echo $id; ?>">
            <label class='selectit'>
              <input type='radio'
                id="<?php echo "in-$id"?>"
                name="<?php echo $name; ?>"
                <?php echo checked($current, $term->term_id, false); ?>
                value="<?php echo $term->name; ?>" />
            <?php echo $term->name; ?>
          </label>
        </li>
      <?php endforeach; ?>
    </ul>
    <?php
  }

  /**
   * Removes the default lib_databases_categories metabox
   */
  function admin_menu() {
    $tax_name = self::$tax_name;
    remove_meta_box("tagsdiv-{$tax_name}", 'lib_databases', 'side');
  }

  /**
   * Modify the columns in the admin interface_exists
   */
  function columns($columns) {
    $columns = array(
      'cb' => '<input type="checkbox" />',
      'name' => __('Name'),
      'image' => __('Image'),
      'library_use_only' => __('Library Use Only'),
      'description' => __('Description'),
      'slug' => __('Slug'),
      'posts' => __('Count')
    );
    return $columns;
  }

  /**
   * Return content for custom columns
   */
  function column_content($value, $column_name = null, $term_id) {
    $term_meta = get_option( "taxonomy_{$term_id}" );
    switch ($column_name) {
      case 'image':
        if (isset($term_meta['image'])) {
          $value = wp_get_attachment_image($term_meta['image'], array(32, 32));
        }
        break;
      case 'library_use_only':
        if (isset($term_meta['library_use_only'])) {
          $value = ($term_meta['library_use_only'] ? 'yes' : 'no');
        } else {
          $value = 'no';
        }
    }
    return $value;
  }

  /**
   * Save taxonomy custom fields
   */
  function save($term_id, $data = null) {
      $tax_name = self::$tax_name;
      if (!$data and isset($_POST['term_meta'])) {
        $data = $_POST['term_meta'];
      }

      if (!$data) {
        error_log("No {$tax_name} data to save");
        return;
      }

      $term_meta = get_option( "taxonomy_{$term_id}" );

      if (isset($_POST['term_meta']['library_use_only'])) {
        $term_meta['library_use_only'] = true;
      } else {
        $term_meta['library_use_only'] = false;
      }

      if (is_numeric($_POST['term_meta']['image'])) {
        $term_meta['image'] = intval($_POST['term_meta']['image']);
      } else {
        $term_meta['image'] = null;
      }

      // Save the option array.
      update_option( "taxonomy_{$term_id}", $term_meta );

  }

  /**
   * Hook for term creation
   */
  function create($term_id) {
    $this->save($term_id);
  }

  /**
   * Hook for term edit
   */
  function edit($term_id) {
    $this->save($term_id);
  }

  /**
   * Returns the html for the custom fields in the new database access category box
   */
  function add_form_fields(){
    ?>
    <div class="form-field">
      <label for="lib_databases_media_button">
        <div class="label">
          <?php _e('Image'); ?>
        </div>
      </label>
      <?php echo $this->image_form_fields(); ?>
    </div>
    <div class="form-field">
      <div class="label">
        <?php _e('Access Restrictions'); ?>
      </div>
      <label>
        <input type="checkbox" name="term_meta[library_use_only]"/>
        <?php _e('In Library Only'); ?>
        <p>
          <?php _e('(set library IP addresses under Settings > Library Databases)'); ?>
        </p>
      </label>
    </div>
    <?php
  }

  /**
   * Returns the html for the custom fields in the edit database access category box
   */
  function edit_form_fields($term) {
    $term_meta = get_option( "taxonomy_{$term->term_id}" );
    ?>
    <tr class="form-field">
      <th scope="row">
        <label for="image_choose_button">
          <?php _e('Image'); ?>
        </label>
      </th>
      <td>
        <?php echo $this->image_form_fields($term); ?>
      </td>
    </tr>
    <tr class="form-field">
      <th scope="row">
        <?php _e('Access Restrictions'); ?>
      </th>
      <td>
        <label>
          <input type="checkbox" name="term_meta[library_use_only]" <?php checked($term_meta['library_use_only']); ?>/>
          <?php _e('In Library Only'); ?>
          <p>
            <?php _e('(set library IP addresses under Settings > Library Databases)'); ?>
          </p>
        </label>
      </td>
    </tr>
    <?php
  }

  function image_form_fields($term = null) {
    if ($term) {
      $term_meta = get_option( "taxonomy_{$term->term_id}" );
      $has_image = isset($term_meta['image']);
    } else {
      $term_meta = array();
      $has_image = false;
    }
    ob_start();
    ?>
    <input type="hidden"
      class="metaValueField"
      id="term_meta[image]"
      name="term_meta[image]"
      value="<?php if ($has_image) { echo $term_meta['image']; } ?>"
    />
    <div id="lib_databases_thumbnail">
      <?php if ($has_image): ?>
        <?php echo wp_get_attachment_image( $term_meta['image']); ?>
      <?php endif; ?>
    </div>
    <input id="lib_databases_media_button" class="image_choose_button"  type="button" value="Choose File" />
    <input class="removeImageBtn" type="button" value="Remove File" style="display:none;" />
    <?php
    return ob_get_clean();
  }

  /**
   * Add JavaScript to get URL from media uploader.
   */
  function embedUploaderCode() {
    $screen = get_current_screen();
    if ($screen->base != 'edit-tags'
        or $screen->taxonomy != self::$tax_name) {
      return;
    }
    wp_enqueue_media();
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function() {

      var hidden_field = jQuery('#term_meta\\[image\\]');
      var remove_button = jQuery('.removeImageBtn');
      var choose_button = jQuery('.image_choose_button');
      var image_wrapper = jQuery('#lib_databases_thumbnail');

      if (hidden_field.val()) {
        remove_button.show();
      }

      remove_button.click(function() {
        image_wrapper.html('');
        hidden_field.val('');
        remove_button.hide();
        return false;
      });

      choose_button.click(function() {
        custom_uploader = wp.media.frames.file_frame = wp.media({
          title: 'Choose Image',
          library: { type: 'image' },
          button: { text: 'Choose Image' },
          multiple: false
        });

        // set selected image on open
        custom_uploader.on('open', function() {
          var selection = custom_uploader.state().get('selection');
          var id = hidden_field.val();
          var attachment = wp.media.attachment(id);
          attachment.fetch();
          selection.add( attachment ? [ attachment ] : [] );
        });

        // get selected image on select
        custom_uploader.on('select', function() {
          var attachment = custom_uploader.state().get('selection').first().toJSON();
          hidden_field.val(attachment.id);
          var thumb = jQuery('<img>').attr('src', attachment.url);
          image_wrapper.html(thumb);
          remove_button.show();
        });

        custom_uploader.open();
      });
    });
    </script>
    <?php
  }
}
