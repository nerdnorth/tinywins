<?php
	
	$options 	= get_option('logichop-settings');
	$theme 		= wp_get_theme();
	
	printf('<div class="logichop_settings_container">
			<h2>%s</h2>
			<ul class="logichop-ul-blank">
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
				<li><strong style="color: rgb(255,0,0);">%s</strong></li>
				<li><strong>%s</strong> %s</li>
				<li><strong>%s</strong> %s</li>
			</ul>
			</div>',
			__('Configuration', 'logichop'),
			__('Wordpress Domain:', 'logichop'),
			$_SERVER['SERVER_NAME'],
			__('Domain Name:', 'logichop'),
			(isset($options['domain']) && $options['domain']) ? $options['domain'] : __('Not Set', 'logichop'),
			__('Wordpress Version:', 'logichop'),
			$wp_version,
			__('PHP Version:', 'logichop'),
			PHP_VERSION,
			__('Logic Hop Version:', 'logichop'),
			$this->version,
			__('Logic Hop API Key:', 'logichop'),
			(isset($options['api_key']) && $options['api_key']) ? $options['api_key'] : __('Not Set', 'logichop'),
			__('Cookie TTL:', 'logichop'),
			isset($options['cookie_ttl']) ? $options['cookie_ttl'] : __('Not Set', 'logichop'),
			__('Javscript Referrer:', 'logichop'),
			isset($options['ajax_referrer']) ? $options['ajax_referrer'] : __('Not Set', 'logichop'),
			__('Cache Enabled:', 'logichop'),
			(defined('WP_CACHE') && WP_CACHE) ? __('Enabled', 'logichop') : __('Disabled', 'logichop'),
			__('Javscript Tracking:', 'logichop'),
			($this->logic->js_tracking()) ? __('Enabled', 'logichop') : __('Disabled', 'logichop'),
			__('Google Analytics:', 'logichop'),
			($this->logic->google->active()) ? __('Enabled', 'logichop') : __('Disabled', 'logichop'),
			__('ConvertKit:', 'logichop'),
			($this->logic->convertkit->active()) ? __('Enabled', 'logichop') : __('Disabled', 'logichop'),
			__('Drip:', 'logichop'),
			($this->logic->drip->active()) ? __('Enabled', 'logichop') : __('Disabled', 'logichop'),
			(defined('WP_CACHE') && WP_CACHE && !$this->logic->js_tracking()) ? __('Cache Enabled: Javascript Tracking is recommended.', 'logichop') : '',
			__('Theme:', 'logichop'),
			sprintf('%s, %s', $theme->Name, $theme->Version),
			__('Plugins:', 'logichop'),
			$this->get_active_plugins(true)
		);