=== WPComplete ===
Contributors: zackgilbert, pauljarvis
Tags: courses, teaching, read, mark, complete, lms, membership, pages, page, posts, post, widget, plugin, admin, shortcode
Requires at least: 4.5.3
Tested up to: 4.7
Stable tag: 1.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin that helps your students keep track of their progress through your course.


== Description ==

WPComplete is a WordPress plugin that helps your students keep track of their progress through your course or membership site. 

All you have to do is pick which pages or posts can be marked as “Completed”.

There’s no programming required, it works with every WordPress theme, WordPress course plugin, and is ready to use instantly. Help your students complete the course you’ve put so much information, knowledge and heart into creating.


**Free version**

* Mark lessons as complete - students can complete lessons so they know how far they’ve progressed in your course.
* Quick toggle - set which pages or posts are completable via Quick Edit or by editing the page/post.
* Any theme, any plugin - use WPComplete with any WordPress theme or membership plugin.


**PRO version**

WPComplete is available as a pro version with lots of extra features to help you customize and visually show students their progress.

* Supports multiple courses within a single WordPress site.
* Course progression - when a student clicks complete, they’re taken to the next lesson automagically.
* Dead-easy shortcodes - without any programming, add shortcodes for buttons, graphs, and completion text.
* View progress - see the number of students who’ve completed each lesson or percentage complete by each student.
* Fancy graphs - use a bar or circle graph to display progress through your course via simple shortcodes.
* Completion indicators - visually show logged in students which lessons they have already completed.
* Customize everything - choose different wording for the completion buttons and/or pick colours for the buttons and graphs.
* Email support - we are available to quickly answer questions, fix bugs and take feature requests.

[https://wpcomplete.co](https://wpcomplete.co)

Although WPC is course platform agnostic, we've thoroughly tested it with: [Restrict Content Pro](https://restrictcontentpro.com/), Memberful and WOO.


**Please vote & enjoy**

If you like WPComplete, please [leave us a ★★★★★ rating](https://wordpress.org/support/view/plugin-reviews/wpcomplete). Your votes really make a difference! Thanks.


== Installation ==

1. Upload ‘wpcomplete-*.zip’ to your /wp-content/plugins/‘ directory or use the WordPress plugin uploader.
1. Activate the plugin through the ‘Plugins’ menu in WordPress.
1. Go to ‘Settings’ then ‘WPComplete’ to customize the text and colours.
1. Edit any page or post and check the box beside ‘Enable Complete button’ to set a page or post as completable.


== Frequently Asked Questions ==

= How do I enable a post or page so that it is completable? =
To enable a page so that it's completable:
1. When you have a page that you want to be completable, find the page from your Wordpress admin page directory: /wp-admin/edit.php?post_type=page and click in to edit.
2. Find the WPComplete metabox.
3. Check the "Enable Complete button" checkbox 
4. Update or Publish the page to save the changes.

= What shortcodes are available in the pro version? =
`[complete_button]` or `[wpc_complete_button]` will add your complete button anywhere on the page or post.
`[progress_percentage]` or `[wpc_progress_percentage]` will display the current student's progress as a percentage (ex: 49%).
`[progress_ratio]` or `[wpc_progress_ratio]` will display the current student's progress as a ratio (ex: 10/35).
`[progress_graph]` or `[wpc_progress_graph]` will display a radial (donut) graph showing the current student's progress with percentage.
`[progress_bar]` or `[wpc_progress_bar]` will display a bar graph showing the current student's progress with percentage.

= Can WPComplete handle multiple courses within the same WordPress installation? =
Yes! Once you enable completion for a page or post, in the pro version, you will be given the option to assign it to a specific course. If you use any progress shortcodes, by default it will display the progress for the course of that post, but progress shortcodes also accept a course attribute if you want to force showing progress for a specific course. Ex:
`[wpc_progress_bar course="All"]`
`[wpc_progress_bar course="My Awesome Course"]`

= Can I use this with custom post types? =
Yes! By default, posts and pages are enabled. In the pro version, you have the ability to enable it for individual post types, including your custom types.

= Can shortcodes be used in sidebar widgets? =
Yes! Just use a text widget and include the shortcode.

= Can shortcodes be used in my template files? =
Yes! You just need to use a special php tag to trigger the shortcode, for example:
`<?php echo do_shortcode( '[wpc_progress_bar]' ); ?>`

= How do I style the completion buttons beyond just the available colour options on the settings page? =
If you'd like to style the buttons further, you can target them using the css style class: 
`.wpc-button` for both buttons and `.wpc-button-complete` `.wpc-button-completed` for each individually.

= Can I style links to posts and pages that are completable? =
Yes! Upon page load, we append the css class `.wpc-lesson` to ALL links that have been marked completable. Links that have not been completed by the logged in student will also have the class `.wpc-lesson-complete` added (no 'd'). And links that have been completed by the logged in student will also have the class `.wpc-lesson-completed` added, along with some really basic styles that are easy to override manually from the settings page.

= I use OptimizePress. Can I use WPComplete? =
Yes! OptimizePress is a little tricky to get working, but it does work! We automatically disable automatic insertion of the completion button, but you can easily add it where you want the button to show up.

To add the button to your page:
1. Edit the page you want to add completion to.
2. You should already be on the OptimizePress tab (not the WordPress tab).
3. Click the Live Editor "Launch Now" button.
4. Click the "Add Element" button where you want to add your completion button.
5. Select a "Custom HTML / Shortcode" element.
6. In the "Content" field, insert the shortcode: [complete_button]
7. Scroll down and click the "Insert" button.
8. The new preview will say something like: 
`!!! CUSTOM HTML CODE ELEMENT !!! 
[Complete_button]`
9. Click the Save & Close (or Save & Continue) button.


== Changelog ==

= 1.4 =
* Support for multiple courses within a single WordPress site.
* Basic post page displaying all available students and their current status. 
* Basic user page displaying all available posts a user can complete and their current status.
* Minor bug fixes.

= 1.3 =
* Added additional progress shortcodes that include a wpc_ prefix.
* Added a setting to turn off auto append.
* Disabled auto append for OptimizePress on plugin activation due to conflicts.
* Started storing a student's completion date and times.
* Added live update of completed links when completed.
* Added a first (probably horrible) attempt at Spanish translations.

= 1.2 =
* Upon page load, all links to pages or posts that are completable, will be tagged with a `.wpc-lesson` class along with either `.wpc-lesson-complete` or `.wpc-lesson-completed` based on the current logged in student's completion status.
* Added advanced custom styles textarea in settings page, allowing for easier styling.

= 1.1 =
* Added support for custom post types. Default is still posts and pages, but you can now select individual custom post types or all post types. [Thanks, Scott Winterroth](https://www.producthunt.com/tech/wpcomplete#comment-342255)
* Fixed a bug where the completion button would sometimes display twice if other plugins would render content before WPComplete. [Thanks, Philip Morgan]
* Fixed a bug relating to license activation and validity checking.

= 1.0 =
* Initial working version ready for public consumption.


== Upgrade Notice ==

= 1.4 =
Finally: Multi-course support!

= 1.3 =
More customization and new features!

= 1.2 =
New features!

= 1.1 =
Adds support for custom content types. Also fixed a couple non-critical bugs.

= 1.0 =
First release. No need to upgrade yet.
