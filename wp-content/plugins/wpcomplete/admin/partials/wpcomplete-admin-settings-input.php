
<input type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo _e( $text, $this->plugin_name ); ?>"<?php echo (!empty($class)) ? ' class="' . $class . '"' : ''; ?><?php if ($disabled) echo " disabled" ?>><?php if ($disabled) echo ' <span class="profeature"></span>' ?>

