<?php

	if (!defined('ABSPATH')) { header('location: /'); die; }
	
	$tab = 'settings';
	if (isset($_GET['tab'])) {
		$tab = $_GET['tab'];
	}
	
	$status = $this->logic->api_post('status');
	
	$upgrade_available = '';
	if (isset($status['Client']['Version'])) {
		if ($status['Client']['Version'] > $this->version) {
			$upgrade_available = sprintf('<p><strong>%s</strong><br>%s</p>',
											__('There is a newer version of Logic Hop available.', 'logichop'),
											__('Please <a href="https://logichop.com/my-account/" target="_blank">Upgrade now</a>.', 'logichop')
									);
		}
	}
	
	print('<div class="wrap">');
	printf('<h2>%s</h2>',
			__('Logic Hop Settings', 'logichop')
		);
	
	printf('<div class="notice notice-%s">
				<p>
					Logic Hop API %s: 
					<strong>API %s</strong><br>
					<small>%s: %s</small>
					%s
				</p>
				<ul>
					<li><strong>%s:</strong> %s</li>
					<li><strong>%s %s:</strong> %s</li>
					<li><strong>%s:</strong> %s</li>
				</ul>
				<p>
					%s
				</p>
			</div>',
			(isset($status['Client']['Active']) && $status['Client']['Active']) ? 'success' : 'error',
			__('Status', 'logichop'),
			(isset($status['Client']['Active']) && $status['Client']['Active']) ? __('Enabled', 'logichop') : __('Disabled', 'logichop'),
			__('Version', 'logichop'),
			$this->version,
			$upgrade_available,
			__('Account Type', 'logichop'),
			(isset($status['Client']['Account']) && $status['Client']['Account']) ? $status['Client']['Tier'] : sprintf('<a href="http://logichop.com" target="_blank">%s</a>', __('Create an Account', 'logichop')),
			__('Account', 'logichop'),
			(isset($status['Client']['Active']) && $status['Client']['Active']) ? __('Expires', 'logichop') : __('Expired', 'logichop'),
			(isset($status['Client']['Expires']) && $status['Client']['Expires']) ? date('M. jS, Y \a\t g:ia', strtotime($status['Client']['Expires'])) : sprintf('<a href="http://logichop.com" target="_blank">%s</a>', __('Create an Account', 'logichop')),
			__('Domain Name', 'logichop'),
			(isset($status['Client']['DomainStatus'])) ? $status['Client']['DomainStatus'] : '',
			(isset($status['Client']['Message'])) ? $status['Client']['Message'] : ''
		);
    
	settings_errors();
	
	$convertkit = (!$this->logic->convertkit->active()) ? '' : sprintf('<a href="?page=logichop-settings&tab=convertkit" class="nav-tab %s">ConvertKit</a>', ($tab == 'convertkit') ? 'nav-tab-active' : '');
	$drip = (!$this->logic->drip->active()) ? '' : sprintf('<a href="?page=logichop-settings&tab=drip" class="nav-tab %s">Drip</a>', ($tab == 'drip') ? 'nav-tab-active' : '');
	
	
	printf('<h2 class="nav-tab-wrapper">
            	<a href="%s" class="nav-tab %s">%s</a>
            	%s
            	%s
            	<a href="%s" class="nav-tab %s">%s</a>
            	<a href="%s" class="nav-tab %s">%s</a>
        	</h2>',
        	'?page=logichop-settings',
        	($tab == 'settings') ? 'nav-tab-active' : '',
        	__('Settings', 'logichop'),
        	$convertkit,
        	$drip,
        	'?page=logichop-settings&tab=instructions',
        	($tab == 'instructions') ? 'nav-tab-active' : '',
        	__('Instructions', 'logichop'),
        	'?page=logichop-settings&tab=configuration',
        	($tab == 'configuration') ? 'nav-tab-active' : '',
        	__('Configuration', 'logichop')
        );
	
	if ($tab == 'settings') {
		if (!isset($_SESSION['logichop']) || !$_SESSION['logichop']) {
			printf('<h2>%s</h2>
					<p>%s</p>
					<p>%s</p>
					<hr>',
					__('Welcome to Logic Hop!', 'logichop'),
					__('Enter your API Key and information below to get started.', 'logichop'),
					__('If you need an API Key, visit <a href="http://logichop.com" target="_blank">LogicHop.com</a>.', 'logichop')
				);
		}
		
		print('<form method="post" action="options.php">');
			settings_fields( 'logichop-settings' );
			do_settings_sections( 'logichop-settings' );      
			submit_button();
		print('</form>');
	} else if ($tab == 'instructions') {
		include_once('instructions.php');
	} else if ($tab == 'configuration') {
		include_once('configuration.php');
	} else if ($tab == 'convertkit') {
		include_once('convertkit.php');
	} else if ($tab == 'drip') {
		include_once('drip.php');
	}
	
	print('</div>');
	
	