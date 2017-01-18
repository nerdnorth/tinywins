  
  <?php if ( !is_production() ) { ?>
  Development mode! Enjoy. :)
  <?php } else { ?>
  <?php if ( $license_status && WPCOMPLETE_IS_ACTIVATED ) { ?>
  <a href="#" onclick="jQuery('#license-key-container').show(); jQuery(this).hide(); return false;">Need to change your license key?</a>

  <div id="license-key-container" style="display: none;">
  <?php } else { ?>
  <div id="license-key-container">
  <?php } ?>
    <h2>Plugin License</h2>
    <table class="form-table">
      <tbody>
        <tr>
          <th scope="row">
            <label for="wpcomplete_license_key">License Key</label>
          </th>
          <td>
            <input type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo $text; ?>"<?php echo (!empty($class)) ? ' class="' . $class . '"' : ''; ?>> <input type="submit" class="button-secondary" id="<?php echo $button_name; ?>" name="<?php echo $button_name; ?>" value="<?php _e('Activate License'); ?>">
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  <?php } ?>
