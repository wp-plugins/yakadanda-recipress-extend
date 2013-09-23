<?php
function recipressextend_print_link() {
  $recipressextend_options = get_option('recipressextend_options');
  
  $print_stub = ( isset($recipressextend_options['print_text']) && !empty($recipressextend_options['print_text']) ) ? $recipressextend_options['print_text'] : 'Print Recipe';
  
  wp_enqueue_script('recipressextend-print');
  
  $link = add_query_arg('yrecipressextend_print', '1', get_permalink());
  $content = '<p style="margin: 10px 0px;" id="recipressextend-print-link"><a href="' . $link . '">' . $print_stub . '</a></p>';
  
  return $content;
}

add_action('template_redirect', 'recipressextend_print', 5);
function recipressextend_print() {
  if (isset($_GET['yrecipressextend_print']) && $_GET['yrecipressextend_print'] == 1) {
    include( plugin_dir_path(__FILE__) . 'print-preview.php' );
    exit();
  }
}

add_action('admin_menu', 'recipressextend_option_menu');
function recipressextend_option_menu() {
  add_options_page('ReciPress Extend Options', 'ReciPress Extend', 'manage_options', 'recipressextend_options', 'recipressextend_options_page');
  add_filter('plugin_action_links', 'recipressextend_options_links', 10, 2);

  function recipressextend_options_links($links, $file) {
    if ($file != plugin_basename(__FILE__))
      return $links;
    $settings_link = '<a href="options-general.php?page=recipressextend_print">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
  }

}

add_action('admin_init', 'recipressextend_print_settings');
function recipressextend_print_settings() {
  register_setting('recipressextend_print', 'recipressextend_options');
}

function recipressextend_options_page() {
  $recipressextend_options = get_option('recipressextend_options');
  
  $recipressextend_options['print_text'] = isset($recipressextend_options['print_text']) ? $recipressextend_options['print_text'] : null;
  $recipressextend_options['ingredient_links'] = isset($recipressextend_options['ingredient_links']) ? $recipressextend_options['ingredient_links'] : null;
  $recipressextend_options['taxonomy_links'] = isset($recipressextend_options['taxonomy_links']) ? $recipressextend_options['taxonomy_links'] : null;
  ?>
    <div class="wrap">
      <div class="icon32" id="icon-options-general"></div>
      <h2>ReciPress Extend Options</h2>
      <form method="post" action="options.php">
        <?php settings_fields('recipressextend_print'); ?>
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row"><label for="recipressextend_options[print_text]">Custom text instead of 'Print Recipe'</label></th>
              <td>
                <input type="text" class="regular-text code" value="<?php echo $recipressextend_options['print_text']; ?>" id="recipressextend_options[print_text]" name="recipressextend_options[print_text]">
                <p class="description">Type text</p>
              </td>
            </tr>
            <tr valign="top" <?php echo (recipressextend_recipress_version() == '1.9.5') ? 'style="display: none;"' : null; ?>>
              <th scope="row"><label>Ingredient Links</label></th>
              <td>
                <fieldset>
                  <legend class="screen-reader-text">
                    <span>Ingredient Links</span>
                  </legend>
                  <p>
                    <label>
                      <input type="radio" value="1" name="recipressextend_options[ingredient_links]" <?php echo ( ($recipressextend_options['ingredient_links']=='1') || !isset($recipressextend_options['ingredient_links']) ) ? 'checked="checked"' : null;?>>
                      On
                    </label>
                    <br>
                    <label>
                      <input type="radio" value="0" name="recipressextend_options[ingredient_links]" <?php echo ( $recipressextend_options['ingredient_links']=='0' ) ? 'checked="checked"' : null;?>>
                      Off
                    </label>
                  </p>
                </fieldset>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row"><label>Taxonomy Links</label></th>
              <td>
                <fieldset>
                  <legend class="screen-reader-text">
                    <span>Taxonomy Links</span>
                  </legend>
                  <p>
                    <label>
                      <input type="radio" value="1" name="recipressextend_options[taxonomy_links]" <?php echo ( ($recipressextend_options['taxonomy_links']=='1') || !isset($recipressextend_options['taxonomy_links']) ) ? 'checked="checked"' : null;?>>
                      On
                    </label>
                    <br>
                    <label>
                      <input type="radio" value="0" name="recipressextend_options[taxonomy_links]" <?php echo ( $recipressextend_options['taxonomy_links']=='0' ) ? 'checked="checked"' : null;?>>
                      Off
                    </label>
                  </p>
                </fieldset>
              </td>
            </tr>
          </tbody>
        </table>
        <?php submit_button('Save Changes', 'primary', 'submit', 'true'); ?>
      </form>
    </div>
  <?php
}
