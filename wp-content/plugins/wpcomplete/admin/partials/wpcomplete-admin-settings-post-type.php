  
  <select name="<?php echo $this->plugin_name . '_post_type'; ?>" id="<?php echo $this->plugin_name . '_post_type'; ?>"<?php if ($disabled) echo " disabled" ?>>
    <option value="post"<?php if ($selected_type == 'post') echo ' selected="selected"'; ?>>Post Types</option>
    <option value="page"<?php if ($selected_type == 'page') echo ' selected="selected"'; ?>>Page Types</option>
    <?php foreach ( get_post_types( array( '_builtin' => false ) ) as $post_type ) { ?>
    <option value="<?php echo $post_type; ?>"<?php if ($selected_type == $post_type) echo ' selected="selected"'; ?>><?php echo ucwords(str_replace("_", " ", $post_type)); ?> Types</option>
    <?php } ?>
    <option value="page_post"<?php if ($selected_type == 'page_post') echo ' selected="selected"'; ?>>Page &amp; Post Types Only</option>
    <option value="all"<?php if ($selected_type == 'all') echo ' selected="selected"'; ?>>All Content Types</option>
  </select>
  <?php if ($disabled) echo '<span class="profeature"></span>' ?>
