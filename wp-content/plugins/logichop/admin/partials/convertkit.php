				
	<div class="logichop_settings_container">
		
		<p>	
			Getting started with your ConvertKit integration? <a href="https://logichop.com/docs/using-logic-hop-with-convertkit/" target="_blank">View the instructions</a>
		</p>
		
		<h2>ConvertKit Links</h2>
		<p>
			Append the ConvertKit Link Query String to your Broadcast & email links to enable ConvertKit data for users on your site.
		</p>
		
		<p>
			<em>Example:</em> <?php echo site_url(); ?>/landing-page/?convertkit=true&email={{subscriber.email_address}}&logichop={{subscriber.logichop}}
		<p>
		
		<p>
			<label>ConvertKit Link Query String</label>
			<input type="text" onfocus="this.select();" readonly="readonly" value="?convertkit=true&email={{subscriber.email_address}}&logichop={{subscriber.logichop}}">
		</p>
		
		<p>
			Logic Hop uses cookies to access users' ConvertKit data on future visits. To ensure consistency across devices, append the ConvertKit Link Query String to all ConvertKit links directed to your site.
		</p>
		
		<p>
			Additional query string parameters can be added as necessary. Append with an ampersand: <em>&utm_campaign=spring+campaign</em>
		</p>
		
		<hr>
		
		<h3>ConvertKit Forms</h3>
		<p>
			Use the following settings for your ConvertKit forms to enable data for new users.
		</p>
		
		<ul class="logichop-ul">
			<li>Check <em>Send incentive / double opt-in email to confirm new subscribers</em></li>
			<li>Select <em>Thank you page: URL</em></li>
			<li>Check <em>Special Options: Send subscriber data to Thank you page</em></li>
			<li>Append the ConvertKit Form Query String to the Thank you page URL</em>
				<ul class="logichop-ul">
					<li><em>Example:</em> <?php echo site_url(); ?>/thank-you/?convertkit=true</li>
				</ul>
			</li>
		</ul>
		
		<p>
			<label>ConvertKit Form Query String</label>
			<input type="text" onfocus="this.select();" readonly="readonly" value="?convertkit=true">
		</p>
		
		<p>
			ConvertKit user data is available once users confirm their subscription.
		</p>
		
	</div>		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		