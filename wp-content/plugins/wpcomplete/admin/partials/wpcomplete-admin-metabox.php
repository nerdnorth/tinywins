
  <p>
    <input type="hidden" name="wpcomplete[completable]" value="false">
    <label><input type="checkbox" id="completable" name="wpcomplete[completable]" value="true"<?php if ($completable) echo " checked"; ?> onclick="jQuery('#completable-enabled').toggle();"><?php echo __( 'Enable Complete button', $this->plugin_name ); ?></label>
  </p>

  <p>
    <!-- FREE: -->
    <?php //echo __( 'Upgrade to the <a href="https://wpcomplete.co">PRO version</a> for support and to unlock all available features.', $this->plugin_name ); ?>
    <!-- PREMIUM: -->
    <?php if ( !WPCOMPLETE_IS_ACTIVATED ) { ?>
    <a href="options-general.php?page=<?php echo $this->plugin_name; ?>"><?php echo __( 'Activate your plugin to enable support and to unlock all available features.', $this->plugin_name ); ?></a>
    <?php } ?>
  </p>

  <!-- PREMIUM: -->
  <div id="completable-enabled"<?php if (!$completable) echo " style='display:none;'"; ?>>
    <p>
      <label for="course-assigned"><?php echo __( 'This is a part of:', $this->plugin_name ); ?></label>
      <select name="wpcomplete[course]" id="course-assigned" class="course-toggle" onchange="if (this.value == '--new--') { jQuery('.course-toggle').toggle(); jQuery('.course-toggle select').attr('disabled', 'disabled'); jQuery('.course-toggle input').attr('disabled', false); jQuery('.course-toggle input').focus(); this.selectedIndex = 0; }">
        <option value="true"><?php echo __( 'No specific course', $this->plugin_name ); ?></option>
        <?php foreach ( $this->get_course_names() as $course_name ) : ?>
        <option value="<?php echo $course_name; ?>"<?php if ($course_name == $completable) echo ' selected'; ?>><?php echo $course_name; ?></option>
        <?php endforeach; ?>
        <option value="--new--">-- <?php echo __( 'Add a specific course', $this->plugin_name ); ?> --</option>
      </select>
      <span class="course-toggle" style="display: none;">
        <input type="text" name="wpcomplete[course-custom]" disabled> or <a href="javascript:void();" onclick="jQuery('.course-toggle').toggle(); jQuery('.course-toggle select').attr('disabled', false); jQuery('.course-toggle input').attr('disabled', 'disabled'); ">cancel</a>
      </span>
    </p>

    <p>
      <label for="completion_redirect_url"><?php echo __( 'Where would you like to redirect your students upon marking this as completed?', $this->plugin_name ); ?></label><br>
      <input type="text" id="completion_redirect_to" name="wpcomplete[completion-redirect-to]" value="<?php echo $redirect['title']; ?>" placeholder="">
      <input type="hidden" id="completion_redirect_url" name="wpcomplete[completion-redirect-url]" value="<?php echo $redirect['url']; ?>">
      <span class="howto"><?php echo __( 'Leave empty to not redirect.', $this->plugin_name ); ?></span>
    </p>
  </div>
