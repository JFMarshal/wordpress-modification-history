=== WP Modification History ===
Contributors: jphase, robido
Donate link: http://robido.com
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: modification, history, data, meta, postmeta
Requires at least: 3.2
Tested up to: 4.2
Stable tag: 1.0

WordPress Modification History adds a metabox that shows modification history on a particular set of post types.

== Description ==

WP Modification History enables a metabox that shows modficiation history by date/time, user, and the values that were modified. The modified values are shown in a diff format so that you can easily see what lines and characters were modified, deleted, added, etc.

Within the settings page, you are able to toggle off/on meta values in the event you don't want certain things to be tracked when hitting the update button on that post type.

Possible future features:

* Additional settings and display options (AJAX pagination, limit of results per page/metabox, etc.)
* Comments and remarks on a particular revision
* Email notifications when specific changesets are made
* Stats

== Installation ==

1. Drop unzipped folder into wp-content/plugins/wp-mod-history and activate the plugin via the plugins admin page.

1. Alternatively, just install the plugin from the plugins admin page by searching for "WP Modification History"

== Frequently Asked Questions ==

= Where do I see the modification history? =

By default, modification history will be tracked on posts of post_type "post" whenever a change has been made. This means you'll need to change something (the title, the content, or some custom post meta) and click Update. Once a change has been made, you should see a metabox appear towards the bottom of the edit post screen that will outline the changes that were made.

= Why was this written? =

This was written initially as a work project to show editorial staff who changed what and when they changed it. This was later modularized and developed into a stand alone plugin for your convenience.

== Changelog ==

= trunk =
* Added core functionality and metabox to display modification history.

== Upgrade Notice ==

= 1.0 =
* Added default settings page to allow admin users to configure which post types and meta values to track modification history for.

== Screenshots ==

1. The metabox that displays modification history in a diff format