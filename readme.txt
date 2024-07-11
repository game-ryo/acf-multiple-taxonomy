=== ACF: Multiple Taxonomy ===
Contributors: game-ryo
Tags: acf, multiple, taxonomy
Requires at least: 4.7
Tested up to: 6.5.5
Stable tag: 1.0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple plugin to add the Multiple Taxonomy field. It allows you to select term(s) from multiple taxonomies.

== Description ==

- This plugin adds the Multiple Taxonomy field as one of the Advanced Custom Fields (ACF).
- The **Taxonomy** field in the ACF allows you to select term(s) from **only one taxonomy**.
- The **Multiple Taxonomy** field allows you to select term(s) from **multiple taxonomies**.

= Features =

As with ACF's Taxonomy field:
- You can select a term by radio buttons or a drop-down list.
- You can select terms by checkboxes or a drop-down list.
- 'Save Terms' option is supported.
- 'Load Terms' option is supported.
- 'Return Value' option is supported (Term Object or Term ID).

Note: 'Create Terms' option is not supported.

= Compatibility =

This ACF field type is compatible with:
* ACF 5

= Link =

* [Github](https://github.com/game-ryo/acf-multiple-taxonomy)

== Installation ==

1. Copy the `acf-multiple-taxonomy` folder into your `wp-content/plugins` folder
2. Activate the Multiple Taxonomy plugin via the plugins admin page
3. Create a new field via ACF and select the Multiple Taxonomy type
4. Read the description above for usage instructions

== Changelog ==

= 1.0.7 =
* Fixes the AJAX handling compatibility issue introduced in ACF 6.3.2. Thanks to [justinmaurerdotdev](https://github.com/justinmaurerdotdev) for [his contribution](https://github.com/game-ryo/acf-multiple-taxonomy/pull/5).

= 1.0.6 =
* Removed the Stylized UI toggle button on the field settings screen. This option is now always on.

= 1.0.5 =
* Fixed serious errors that had occurred with the last update. I sincerely apologize to the users.

= 1.0.4 =
* Fixed performance issues with the grid view in the Media Library

= 1.0.3 =
* Fix warnings when invalid taxonomy is loaded

= 1.0.2 =
* Fix warnings when using radio/checkbox in php 8.0
* Delete unnecessary css and js files

= 1.0.1 =
* Remove undefined variable which caused warning in php 8.0

= 1.0.0 =
* Initial Release.