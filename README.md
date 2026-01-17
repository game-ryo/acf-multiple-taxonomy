![banner-1544x500](https://user-images.githubusercontent.com/47914904/125542624-2fb488c8-fee9-43f2-a1bc-626cb6b0b4d6.png)

# ACF: Multiple Taxonomy

A simple plugin to add the Multiple Taxonomy field. It allows you to select term(s) from multiple taxonomies.

## Description

- This plugin adds the Multiple Taxonomy field as one of the Advanced Custom Fields (ACF).
- The **Taxonomy** field in the ACF allows you to select term(s) from **only one taxonomy**.
- The **Multiple Taxonomy** field allows you to select term(s) from **multiple taxonomies**.

## Features

As with ACF's Taxonomy field:
- You can select a term by radio buttons or a drop-down list.
- You can select terms by checkboxes or a drop-down list.
- 'Save Terms' option is supported.
- 'Load Terms' option is supported.
- 'Return Value' option is supported (Term Object or Term ID).

Note:
- 'Create Terms' option is not supported.

## Link

[Plugin page (Wordpress.org)](https://wordpress.org/plugins/acf-multiple-taxonomy/)

## Changelog
### 1.0.11
- Fixed double escaping issue in term titles for Select2. Thanks to [braican](https://github.com/braican) for [his contribution](https://github.com/game-ryo/acf-multiple-taxonomy/pull/8).
### 1.0.10
- Remove legacy constructor parameter to fix PHP 8.2 compatibility.
### 1.0.9
- Fixed AJAX issues that occurred when selecting terms. See [Github](https://github.com/game-ryo/acf-multiple-taxonomy/issues/3).
### 1.0.8
- Fixed AJAX verification error in ACF6.3.10. Thanks again to [justinmaurerdotdev](https://github.com/justinmaurerdotdev) for [his contribution](https://github.com/game-ryo/acf-multiple-taxonomy/pull/6).
### 1.0.7
- Fixes the AJAX handling compatibility issue introduced in ACF 6.3.2. Thanks to [justinmaurerdotdev](https://github.com/justinmaurerdotdev) for [his contribution](https://github.com/game-ryo/acf-multiple-taxonomy/pull/5).
### 1.0.6
- Removed the Stylized UI toggle button on the field settings screen. This option is now always on.
### 1.0.5
- Fixed serious errors that had occurred with the last update. I sincerely apologize to the users.
### 1.0.4
- Fixed performance issues with the grid view in the Media Library
### 1.0.3
- Fix warnings when invalid taxonomy is loaded
### 1.0.2
- Fix warnings when using radio/checkbox in php 8.0
- Delete unnecessary css and js files
### 1.0.1
- Remove undefined variable which caused warning in php 8.0
### 1.0.0
- Initial Release.
