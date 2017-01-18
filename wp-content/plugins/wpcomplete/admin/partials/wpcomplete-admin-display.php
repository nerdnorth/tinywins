<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://wpcomplete.co
 * @since      1.0.0
 *
 * @package    WPComplete
 * @subpackage wpcomplete/admin/partials
 */
?>

<div class="wpcomplete-settings wrap">
  
  <h2><img src="https://wpcomplete.co/wp-content/themes/wpc/assets/wpcomplete-dark.png" width="300"></h2>
  
  <?php if ( isset( $_GET['sl_activation'] ) && ( $_GET['sl_activation'] == 'false' ) && ( $message = urldecode( $_GET['message'] ) ) ) { ?>
  <div class="error">
    <p><?php echo $message; ?></p>
  </div>
  <?php } ?>

  <div class="content">
    <form action="options.php" method="post">
      <?php
        $name = $this->plugin_name . '_license_key';
        $text = get_option( $name );
        $class = '';
        $license_status = get_option( $this->plugin_name . '_license_status' );
        $button_name = $this->plugin_name . '_license_activate';

        include 'wpcomplete-admin-settings-license-status.php';

        settings_fields( $this->plugin_name );
        do_settings_sections( $this->plugin_name );
        submit_button();
      ?>
    </form>
  </div>

  <div class="sidebar">

    <!-- FREE: -->
    <!--<div class="postbox">
      <h2><span>Update to WPComplete PRO</span></h2>
      <div class="inside">
        <p>This plugin has a PRO version with tons more features, like:</p>
        <ul>
          <li>redirecting upon completion</li>
          <li>progress graphs (bar and circle)</li>
          <li>textual progress indicators</li>
          <li>full email support</li>
          <li>and more...</li>
        </ul>
        <p><a href="https://wpcomplete.co">Check out all the benefits</a></p>
      </div>
    </div>-->

    <!-- FREE: -->
    <!--<div class="postbox">
      <h2><span>Want 10% off the PRO version?</span></h2>
      <div class="inside">
        <p>Get a great deal on the PRO version and all our best course tips.</p>

        <form action="//wpcomplete.us13.list-manage.com/subscribe/post?u=aa3f8a628a4c1c32b221a6399&amp;id=a3f8cf0350" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
        <div id="mce-responses" class="clear">
          <div class="response" id="mce-error-response" style="display:none"></div>
          <div class="response" id="mce-success-response" style="display:none"></div>
        </div>
        <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_aa3f8a628a4c1c32b221a6399_a3f8cf0350" tabindex="-1" value=""></div>
        <input type="email" value="" name="EMAIL" placeholder="Email Address" class="required email" id="mce-EMAIL">
        <input type="hidden" name="GROUPINGS[7265]" value="Free">
        <input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button">
        </form>
      </div>
    </div>-->

    <div class="postbox">
      <h2><span>Need help?</span></h2>
      <div class="inside">
        <!-- FREE: -->
        <!--<p>Only our <a href="https://wpcomplete.co">PRO version</a> comes with email support, but feel free to ping us on the <a href="https://wordpress.org/support/plugin/wpcomplete">WordPress support forum</a>.</p>-->
        <!-- PREMIUM: -->
        <p>Need help? Found a bug? We are here to help! Email us: <a href="mailto:nerds@wpcomplete.co">nerds@wpcomplete.co</a></p>
      </div>
    </div>

  </div>

  <hr>
  <p>If you like WPComplete, please <a href="https://wordpress.org/support/view/plugin-reviews/wpcomplete">leave us a ★★★★★ rating</a>. Your votes really make a difference! Thanks.</p>

</div>
