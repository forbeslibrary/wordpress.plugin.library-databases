<?php
class Library_Databases_Categories_Admin {
  function __construct() {
    add_action('admin_head', array($this, 'embedUploaderCode'));

    $actions = array(
      'add_form_fields',
      'edit_form_fields'
    );
    foreach ($actions as $action) {
      add_action(
        'lib_databases_categories_' . $action,
        array($this, $action)
      );
    }

    $actions = array(
      'create',
      'edit'
    );
    foreach ($actions as $action) {
      add_action(
        $action . '_lib_databases_categories',
        array($this, $action)
      );
    }
  }

  /**
   * Save taxonomy custom fields
   */
  function save($term_id) {
      $term_meta = get_option( "taxonomy_{$term_id}" );

      if (isset($_POST['term_meta']['library_use_only'])) {
        $term_meta['library_use_only'] = true;
      } else {
        $term_meta['library_use_only'] = false;
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
      <label>
        <div class="label">
          <?php _e('Image'); ?>
        </div>
        <input type="file" name="term_meta[image]"/>
      </label>
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
    var_dump($term_meta);
    ?>
    <tr class="form-field">
      <th scope="row">
        <label for="image_upload_button">
          <?php _e('Image'); ?>
        </label>
      </th>
      <td>
        <input type="hidden"
            class="metaValueField"
            id="term_meta[image]"
            name="term_meta[image]"
            value="<?php echo $term_meta['image']; ?>"
          />
          <div id="metaImage"></div>
          <input class="image_upload_button"  type="button" value="Choose File" />
          <input class="removeImageBtn" type="button" value="Remove File" />
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

  /**
   * Add JavaScript to get URL from media uploader.
   */
  function embedUploaderCode() {
    $screen = get_current_screen();
    if ($screen->base != 'edit-tags'
        or $screen->taxonomy != 'lib_databases_categories') {
      return;
    }
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function() {

      jQuery('.removeImageBtn').click(function() {
        jQuery('#awdMetaImage').html('');
        jQuery('#term_meta[image]').val('');
        return false;
      });

      jQuery('.image_upload_button').click(function() {
        inputField = jQuery(this).prev('.metaValueField');
        tb_show('', 'media-upload.php?TB_iframe=true');
        window.send_to_editor = function(html) {
          url = jQuery(html).attr('href');
          inputField.val(url);
          jQuery('#awdMetaImage').html('<p>URL: '+ url + '</p>');
          tb_remove();
        };
        return false;
      });
    });

    </script>
    <?php
  }
}
