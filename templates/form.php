<p>
  <label for="<?php echo esc_attr($widget->get_field_id('title')); ?>">
    <?php esc_html_e("Title:", "crumble"); ?>
  </label>
  <input class="widefat" id="<?php echo esc_attr($widget->get_field_id('title')); ?>" name="<?php echo esc_attr( $widget->get_field_name('title')); ?>" type="text" value="<?php echo esc_html($instance['title']); ?>" placeholder="optional" />
</p>

<p>
  <label for="<?php echo esc_attr($widget->get_field_id('text')); ?>">
    <?php esc_html_e('Text:','crumble'); ?>
  </label>
  <textarea class="widefat" rows="16" cols="20" id="<?php echo esc_attr($widget->get_field_id('text')); ?>" name="<?php echo esc_attr($widget->get_field_name('text')); ?>"><?php echo esc_textarea( $instance['text'] ); ?></textarea>
</p>

<p class="description"><?php esc_html_e( 'Basic HTML tags are allowed.', 'crumble' ); ?></p>

<p>
  <label for="<?php echo esc_attr($widget->get_field_id('endpoint')); ?>">
    <?php esc_html_e("API Endpoint:", "crumble"); ?>
  </label>
  <input class="widefat" id="<?php echo esc_attr($widget->get_field_id('endpoint')); ?>" name="<?php echo esc_attr( $widget->get_field_name('endpoint')); ?>" type="text" value="<?php echo esc_html($instance['endpoint']); ?>" placeholder="optional" />
</p>
