=== Admin Live Search ===
Contributors: humbertosilva
Donate link: https://www.paypal.me/humbertosilvacom
Tags: admin, search, live search, ajax search, admin ajax search, dashboard ajax search, search title, search content, admin search, admin live search
Requires at least: 4.0
Tested up to: 5.1.1
Requires PHP: 5.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Live search pages and posts in the dashboard / admin area via AJAX.

== Description ==

Live search pages and posts in the dashboard / admin area as you type using the internal wordpress search via AJAX.

Added filter to search by title only, content only or all (default).

Date and Category filters also work without refreshing the page.


In the Page and Post list you can select to search in title only, text only or everything.

As you type in the search box the results will update accordingly withou the need to press the enter key or click the search button.


== Installation ==

Upload Admin Live Search plugin to your website and activate it.

No configuration necessary.

Access your post or page list in the admin area and as you type in the search box the post/page list bellow updates with the results.

You can search title only or content only, apart for the default all content in posts and pages.

Note: Custom Post Types are not fully supported, but you can enable/disable Live Search on Custom Post Types and see if it's OK for your use case.

== Changelog ==


= 3.2.1 =
* bugfix : sorting not working properly
* validated compability against Wordpress 5.1.1

= 3.2.0 =
* bugfix : bulk actions not working properly in some cases
* changed filter to the left of search input
* validated compability against Wordpress 5

= 3.1.1 =
* bugfix : new js file released

= 3.1.0 =
* bugfix : active on CPT when CPT option was off
* bugfix : Special chars and punctuation not searchable
* bugfix : showed all results when no results available
* changed query for title and text (before was only WP query for that search)

= 3.0.0 =
* added filter to search by title only and by content only
* internal code change independent of wordpress
* bugfixes

= 2.1.0 =
* bugfixes

= 2.0.0 =
* added support for sortable columns
* added support for pagination within the results
* added option to allow enable live search for Custom Post Types
* added help/setting page
* added dismissable warning info to Custom Post Types when live search is disabled
* Various bug fixes

= 1.0.0 =
* Initial Release
