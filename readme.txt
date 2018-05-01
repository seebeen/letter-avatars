=== Letter Avatars ===
Contributors: seebeen
Donate link: https://sgi.io/donate
Tags: letter, avatars, custom-avatar, gravatar, comment, comments, buddypress-avatar, buddypress, letter-avatar
Requires at least: 4.0
Tested up to: 4.9.4
Requires PHP: 5.3.3
Stable tag: 2.6.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sets custom avatars for users without gravatar. Avatars will be replaced by first letter of usename (or e-mail) on a colorful background

== Description ==

Letter Avatars **enables custom avatars for users without gravatar**. Avatar will be replaced with the first letter of username or e-mail. 

Letter Avatars does not use any images, scripts, or font-icons. All letters will be rendered by your theme font (or Optionally via a Google font).

**Features**

* Works anywhere - Plugin hooks into pre_get_avatar function, so the avatar size is preserved
* Highly customizable - You can change colors, letter font, as well as the font size
* Stylish - You can change the background and letter colors or you can randomize them for all the avatars
* Lightweight - Plugin does not use any external stylesheet, image, or js files. It only adds a small inline css in your header
* Highly compatible - You don't have to edit your theme / plugin files, it works automatically and plays nice with other plugins
* **Works with BuddyPress** - Users and groups without gravatar / local avatar will use Letter Avatars

== Installation ==

1. Upload letter-avatars.zip to plugins via WordPress admin panel, or upload unzipped folder to your plugins folder
2. Activate the plugin through the "Plugins" menu in WordPress
3. Go to Settings->Letter Avatars to manage the options

== Frequently Asked Questions ==

= Can I disable gravatars and use Letter Avatars for all comments? =

Yes you can. By default, Letter Avatars are used only for users without gravatar, but you can change that in user settings.

= Can I change the font for my Avatars? =

By default, Letter Avatars will be displayed in your theme font, but you can change that in plugin settings.

= Does this plugin work with BuddyPress? =

Yes it does, since version 2.5

= Does this plugin work with bbPress / wpDiscuz =

At the moment, no. This feature is planned for versions 2.7 and 2.8. coming out in April 2018

= What does the user lock-in option do? =

If you enable user lock-in (when random colors are enabled), each user will have his own unique color which will be used for all the comments.

= How does the Font Auto Size option work?

If you enable the auto size functionality, letter size will be 75% of the avatar size.
I.E. If the avatar size is 100px, font size will be 75px.

== Screenshots ==

1. Gravatars displayed alongside letter avatars

2. Plugin settings page

3. Random colored avatars with user lock-in

== Changelog ==

= 2.6.2 =

* Bugfix: Removed all PHP Notices and Warnings
* Improvement: Fine-tuned gravatar detection code

= 2.6.1 =
* Bugfix: Fixed PHP Warning in certain scenarios

= 2.6 =
* Improvement: Added caching mechanism for gravatar checks
* Improvement: Fine-tuned gravatar checking mechanism

= 2.5 =
* New Feature: **BuddyPress support**
* New Feature: Automatic font size - Option to calculate font size automatically by avatar size
* Improvement: Optimized gravatar detection and CSS processing
* Improvement: Optimized performance on posts with many comments

= 2.4 =
* Internal beta version

= 2.3 =
* Internal alpha version

= 2.2 =
* New Feature: Load Google Font CSS without loading google font
* Improvement: Moved to select2 library for Google Font selection
* Improvement: Google Fonts selection is now cached, improving wp-admin speed
* Bugfix: Fixed the display of Letter Avatars in admin-bar
* Bugfix: Fixed Avatars not displaying properly in some scenarios
* Bugfix: Fixed Font list not showing up randomly in admin settings

= 2.1.2 =
* Bugfix: Fixed WP-Admin issues in WordPress versions 4.9.x

= 2.1.1 =
* Bugfix: Fixed Synthax Error on servers running PHP 5.3.x

= 2.1 =
* New Feature: Colors are now calculated from e-mail hash
* Improvement: Improved color randomization functions

= 2.0 =
* **Fully reworked codebase**
* New Feature: Same color for repeated comments by same author
* Improvement: Moved settings to separate page
* Improvement: Revamped settings system
* Improvement: Reworked avatar hookin process - better performance
* Improvement: Reworked gravatar detection - works in all scenarios
* Improvement: Better color randomization - each avatar has a unique color
* Bugfix: Improved option handling
* Bugfix: Better google font handling
* Many more performance and stability fixes

= 1.1 =
* Improvement: Added hook for style loading
* Improvement: Added hook for plugin options
* Improvement: Better Gravatar detection
* Improvement: Better Theme / Plugin compatibility
* Improvement: Much better performance
* Improvement: Added PHPDoc to all classes / functions

= 1.0 =
* Initial release

== Upgrade Notice ==

You will need to resave your settings after updating plugin