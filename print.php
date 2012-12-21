<?php
$recipressextend_options = get_option( 'recipressextend_options' );

function recipressextend_print_link() {
	global $recipressextend_options;
	
  $size = empty( $recipressextend_options['size'] ) ?  10 : $recipressextend_options['size'];
  $margin_v = empty( $recipressextend_options['margin_v'] ) ? 10 : $recipressextend_options['margin_v'];
  $margin_h = empty( $recipressextend_options['margin_h'] ) ? 0 : $recipressextend_options['margin_h'];
  $print_stub = empty( $recipressextend_options['print_text'] ) ? 'Print Recipe' : $recipressextend_options['print_text'];

  wp_register_script( 'recipressextend_print', plugins_url( '/js/print.js', __FILE__ ), array( 'jquery' ), '1', true );
  wp_enqueue_script( 'recipressextend_print' );

  $link = add_query_arg( 'print', '1', get_permalink() );
  $content = '<p style="margin:' . $margin_v . 'px ' . $margin_h . 'px;" id="print-page-link"><a href="' . $link . '">' . $print_stub . '</a></p>';
  return $content;
	
}

add_action( 'template_redirect', 'recipressextend_print', 5 );
function recipressextend_print() {
	if ( isset( $_GET['print'] ) && $_GET['print'] == 1 ) {
		include( plugin_dir_path(__FILE__) . 'print-preview.php' );
		exit();
	}
}

add_action( 'admin_menu', 'recipressextend_option_menu' );
function recipressextend_option_menu() {
	add_options_page( 'ReciPress Extend Options', 'ReciPress Extend', 'manage_options', 'recipressextend_options', 'recipressextend_options_page' );
	add_filter( 'plugin_action_links', 'recipressextend_options_links', 10, 2 );
	function recipressextend_options_links( $links, $file ) {
		if ( $file != plugin_basename( __FILE__ )) return $links;
		$settings_link = '<a href="options-general.php?page=recipressextend_print">Settings</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
}

add_action( 'admin_init', 'recipressextend_print_settings' );
function recipressextend_print_settings() {
	register_setting( 'recipressextend_print', 'recipressextend_options' );
}

function recipressextend_options_page() {

	global $recipressextend_options; ?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"></div>
		<h2>ReciPress Extend Options</h2>
		<form method="post" action="options.php">
			<?php settings_fields( 'recipressextend_print' ); ?>
			<table class="form-table">
				<tbody>
					<!-- <tr valign="top">
						<th scope="row"><label for="recipressextend_options[size]">Width/height of icon</label></th>
						<td>
              <input type="text" class="regular-text code" value="<?php //echo $recipressextend_options['size']; ?>" id="recipressextend_options[size]" name="recipressextend_options[size]">
              <p class="description">Insert width/height of icon (<=64)</p>
            </td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="recipressextend_options[margin_v]">Margin-top/margin-bottom</label></th>
						<td>
              <input type="text" class="regular-text code" value="<?php //echo $recipressextend_options['margin_v']; ?>" id="recipressextend_options[margin_v]" name="recipressextend_options[margin_v]">
              <p class="description">Insert margin-top/margin-bottom of icon</p>
            </td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="recipressextend_options[margin_h]">Margin-left/margin-right</label></th>
						<td>
              <input type="text" class="regular-text code" value="<?php //echo $recipressextend_options['margin_h']; ?>" id="recipressextend_options[margin_h]" name="recipressextend_options[margin_h]">
              <p class="description">Insert margin-left/margin-right of icon</p>
            </td>
					</tr> -->
					<tr valign="top">
						<!-- <th scope="row"><label for="recipressextend_options[print_text]">Text instead of icon</label></th> -->
            <th scope="row"><label for="recipressextend_options[print_text]">Custom text instead of 'Print Recipe'</label></th>
						<td>
              <input type="text" class="regular-text code" value="<?php echo $recipressextend_options['print_text']; ?>" id="recipressextend_options[print_text]" name="recipressextend_options[print_text]">
              <p class="description">Type text</p>
            </td>
					</tr>
					<!-- <tr valign="top">
						<th scope="row"><label for="recipressextend_options[auto]">Turn off put icon/text automatically</label></th>
						<td><input type="checkbox" name="recipressextend_options[auto]" id="recipressextend_options[auto]" <?php //isset( $recipressextend_options['auto'] ) ? checked( $recipressextend_options['auto'], 'on', true ) : ''; ?>></td>
					</tr> -->
          <tr valign="top">
            <th scope="row"><label for="recipressextend_options[ingredient_tax_links]">Ingredient links and Recipe Tax links</label></th>
            <td>
              <fieldset>
                <legend class="screen-reader-text">
                  <span>Ingredient links and Recipe tax links</span>
                </legend>
                <p>
                  <label>
                    <input type="radio" <?php echo (($recipressextend_options['ingredient_tax_links'] == 'on') || (!isset($recipressextend_options['ingredient_tax_links']))) ? 'checked="checked"' : null; ?> value="on" name="recipressextend_options[ingredient_tax_links]">
                    On
                  </label>
                  <br>
                  <label>
                    <input type="radio" <?php echo ($recipressextend_options['ingredient_tax_links'] == 'off') ? 'checked="checked"' : null; ?> value="off" name="recipressextend_options[ingredient_tax_links]">
                    Off
                  </label>
                </p>
              </fieldset>
            </td>
          </tr>
				</tbody>
			</table>
			<?php submit_button( 'Save Changes', 'primary', 'submit', 'true' ); ?>
		</form>
	</div>
<?php
}
