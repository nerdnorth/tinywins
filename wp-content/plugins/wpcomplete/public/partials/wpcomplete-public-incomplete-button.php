<a href="<?php echo admin_url( 'admin-ajax.php?action=mark_completed&post_id=' . $post_id ); ?>" class="wpc-button wpc-button-complete wpc-complete" data-id="<?php echo $post_id; ?>"><span class="wpc-inactive"><?php echo get_option($this->plugin_name . '_incomplete_text', 'Mark as complete'); ?></span><span class="wpc-active"><?php echo get_option($this->plugin_name . '_incomplete_active_text', 'Saving...'); ?></span></a>
