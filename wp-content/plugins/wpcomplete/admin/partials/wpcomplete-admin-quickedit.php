    
  <fieldset class="inline-edit-col-right inline-edit-completable">
    <div class="inline-edit-col column-<?php echo $column_name; ?>">
      <label class="inline-edit-group">
      <?php 
       switch ( $column_name ) {
       case 'completable':
          wp_nonce_field( $this->plugin_name, 'completable_nonce' );
          ?><input type="hidden" name="wpcomplete[completable]" value="false">
          <label><input type="checkbox" name="wpcomplete[completable]" value="true"><?php echo __( 'Enable Complete button', $this->plugin_name ); ?></label><?php
          break;
       }
      ?>
      </label>
    </div>
  </fieldset>
