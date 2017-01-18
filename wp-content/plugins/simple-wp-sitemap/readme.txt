=== Simple Wp Sitemap ===
Contributors: Webbjocke
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=UH6ANJA7M8DNS
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: sitemap, site map, sitemap.xml, xml sitemap, html sitemap, simple sitemap, seo sitemap, google sitemap, sitemap.html, sitemap plugin, wordpress sitemap
Requires at least: 4.0
Tested up to: 4.7
Stable tag: 1.1.7

An easy sitemap plugin that adds both an xml and an html sitemap to your site, which updates and maintains themselves so you don't have to!

== Description ==

= A Wordpress sitemap plugin =

Simple Wp Sitemap is a sitemap plugin that generates both an xml and an html sitemap to your site dynamically. These two are updated automatically everytime someone's visiting them. Which means they're always up to date and that you only have to install and activate the plugin once, and it will just work for you without you ever having to worry.

Reason the sitemaps are generated dynamically instead of being created as actual files as they were in the beginning, is because it's much faster and more solid. No problems with having to access, create and delete files, instead they're just presented when needed to. It's now suddenly as lightweight, simple and fast as one can expect!

Also supports the option to add pages to the sitemaps that aren't part of your original wordpress site. For instance if you create a little html file and upload to your server and want it to be included in them, it's easily done. You can also block pages that you don't want to be included, and much more.

= What it does =

So what the plugin actually does is making one xml sitemap and one html sitemap available directly on your site. These aren't actually real files that can be found in a folder or anything, instead they get generated when visited and can be found at like yourpage.com/sitemap.xml and yourpage.com/sitemap.html.

And yes, of course the sitemaps are mobile friendly. They also work well with caching plugins :)

= Multisite and multilanguage compatible =

The sitemaps are multisite and multilanguage compatible. Every site gets their own sitemap, and all multilanguage urls are included in them!

== Installation ==

1. 1. Go to the plugins page in your wordpress admin area and hit "add new".
   2. Either search for "simple wp sitemap" and click install, or hit "upload plugin" and upload the zip file.
   3. Another way is by just uploading the "simple-wp-sitemap" folder via ftp to the /wp-content/plugins/ directory.

2. Activate the plugin and that's it, done. Your xml and html sitemaps will be generated when visited, and can be found at like yourpage.com/sitemap.xml and yourpage.com/sitemap.html.

3. Customize the plugin and change titles or add/block pages by hitting the "Simple Wp Sitemap" option in the settings menu.

== Frequently Asked Questions ==

= I have installed the plugin, where can I find the sitemaps? =

You can find them at like yourpage.com/sitemap.xml and yourpage.com/sitemap.html. There's also links to them from the plugins customization page in your admin area.

= Are the sitemaps created "on the fly" dynamically or as static files? =

Dynamically! From version 1.0.8 they now get generated when someone's visiting them, rather than being created as actual files.

= Where can I find the customization or admin page for the plugin? =

Click the link called "Simple Wp Sitemap" in your admin areas settings menu and it will take you there. Theres also a link from the plugins page, where you activate and deactivate them etc.

= Does it provide both an xml and an html sitemap? =

Yes sir, it does.

= Is it possible to add the sitemaps anywhere else on my site? Like on a page with a shortcode or something? =

Sorry no, not at the moment it isn't but maybe in the future.

= Which one of the sitemaps should I submit to google and to other search engines? =

The sitemap.xml one.

= Do I actually have to go ahead and submit the sitemaps anywhere? =

Not really, search engines usually finds them automatically when they visit your site. However if you have webmaster tools at google or bing etc, that could be a good place to do it to get statistics over indexed pages and stuff.

= How do I remove the sitemaps if I stop using the plugin? =

When you deactivate the plugin they get removed automatically.

== Screenshots ==

1. Settings page
2. Html sitemap
3. Xml sitemap

== Changelog ==

= 1.1.7 (Dec 3, 2016) =
* Now escapes urls

= 1.1.6 (Sep 27, 2016) =
* 404 fix for disabled html sitemap
* Couple bugfixes and some code cleanup

= 1.1.5 (Sep 21, 2016) =
* Added option to disable html sitemap
* Plugin description changed

= 1.1.4 (May 16, 2016) =
* Bugfix for old php versions

= 1.1.3 (May 15, 2016) =
* Added option to change titles in html sitemap
* Fixed bug when upgrading to premium
* Fixed bug that made admin submit button invisible
* Moved admin settings page to it's own file
* Made all previous private functions in "builder" public

= 1.1.2 (Jan 20, 2016) =
* Premium version of the plugin has been developed
* Integrated option to upgrade
* Couple changes in php
* Made html sitemap header a link back to homepage
* Removed title tags from links
* Added license document

= 1.1.1 (Nov 11, 2015) =
* Messed up the update, new try

= 1.1.0 (Nov 11, 2015) =
* Changed date format in html sitemap to (Y-m-d H:i:s)
* Added lang attribute and to be automatically set
* Added some tags and updated the plugin page
* Removed unnecessary fixes for old versions of the plugin
* Changes done "under the hood" in php for performance etc
* Also tested with wordpress 4.4

= 1.0.9 (Jun 23, 2015) =
* Added donation link
* Made the sitemaps compatible down to ie7
* Minor changes in php, css and js

= 1.0.8 (April 29, 2015) =
* Now generates dynamic sitemaps instead of static files
* Tested with caching plugins (the popular ones) and works
* Compressed html sitemap to reduce filesize/load time
* Changed mouse hover effect in html sitemap
* Fixed couple bugs/collisions with other plugins
* Updated descriptions

= 1.0.7 (April 18, 2015) =
* Added option to specify own display order
* Excluded drafts, private and password protected pages
* Added some javascript and css to the admin area
* Added a noscript tag in admin area
* Increased amount of FAQ's
* Made sure global post is resetted after loop
* Fixed bug that gave error if no timezone was set
* Couple other changes done in php and css

= 1.0.6 (April 7, 2015) =
* Made the plugin more user friendly
* Added links to the sitemaps from the admin area
* Added FAQ's
* Formatted the code a bit better

= 1.0.5 (March 26, 2015) =
* Fixed timezone bug
* Made the sitemaps a bit lighter, they were so dark n dull
* Corrected some css problems in older browsers
* Updated screenshots

= 1.0.4 (March 18, 2015) =
* Added options to include category, tag and author pages
* Changed layout a bit and made it more responsive
* Changed font to a more readable one

= 1.0.3 (March 16, 2015) =
* New layout for both html and xml sitemaps
* Created a logo for the plugin
* Added a banner
* Added screenshots
* Fixed bug with custom post types

= 1.0.2 (March 14, 2015) =
* Messed up the upload, try again

= 1.0.1 (March 14, 2015) =
* Added link to settings from the plugins page
* Now also escapes output for user added urls
* Added max-length for user added urls

= 1.0.0 (March 14, 2015) =
* Initial public release
