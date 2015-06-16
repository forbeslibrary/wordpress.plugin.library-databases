<?php
class Library_Databases_Categories_Admin {
  function __construct() {
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
        <label for="lib_categories_file">
          <?php _e('Image'); ?>
        </label>
      </th>
      <td>
        <input type="file" id="lib_categories_file" name="term_meta[image]"/>
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
}
