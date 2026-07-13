=== Carmo Cover Block ===
Contributors: carmopereira
Donate link: https://ko-fi.com/carmopereira
Tags: cover, block, background, acf
Requires at least: 6.9
Tested up to: 7.0.1
Stable tag: 0.2.8
Requires PHP: 8.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Extends the core Cover block with an ACF background image that replaces the block's background on desktop screens.

== Description ==

This plugin extends the core Gutenberg Cover block, adding an Advanced Custom Fields (ACF) image field control in the editor. When a field is selected, its image is injected as the Cover block's background on desktop screens (min-width: 768px) via a scoped media query, leaving the block's own background untouched on smaller screens.

Requires Advanced Custom Fields to be installed and active.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/carmo-cover-block` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Make sure Advanced Custom Fields is installed and active.
4. Edit a Cover block and select the ACF image field to use as its desktop background.

== Frequently Asked Questions ==

= Does this affect all blocks? =

No. It only affects blocks of type core/cover.

= Do I need Advanced Custom Fields installed? =

Yes. The plugin relies on ACF's `get_field()` to retrieve the background image, and does nothing if ACF is not active.

== Changelog ==

= 0.2.8 =
* Set plugin author

= 0.2.7 =
* Update Tested up to and Stable tag

= 0.2.6 =
* Extend core/cover with ACF background support instead of a custom block
* Inject desktop ACF background via media query, mobile support removed

= 0.1.0 =
* Release
