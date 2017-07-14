=== Flickr Comments ===
Contributors: jdleung
Donate link: 
Tags: flickr, comment, comments, photoblog, pixelpost
Requires at least: 3.3
Tested up to: 3.8.1
Stable tag: flickr, comment, comments, photoblog, pixelpost
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Retrieves comments from your Flickr account to your Wordpress photoblog in a specified Time Frame and Time Interval.  

== Description ==

= When the specified time is up, any visitor will start the auto-update mode. =


1. Automactically updates comments from Flickr in a specified time, NOT every time.
2. Can only Retrieves the recent comments, NOT all the comments(this save time).
3. Retrieves all the recent comments of all photos at the same time, NOT one by one.
4. Can Manually Update comments.
5. Time Frame and Time Interval can be set.

== Installation ==

1. Upload folder `flickr-comments` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Login Flickr, edit the API key Callback URL to your site URL. If you have no API key, Get it here: http://www.flickr.com/services/api/keys/. See the instruction in screenshots.
4. Input Flickr API key and secret in the configuration page, then click the 'Get Token', this will send a request to Flickr. If the API key and secret matches, Flickr will send back a Token. You will see red "First step done!" when token is saved successfully.
5. Add a custom field "flickr_photo_id" in the post and input the Flickr photo id.( see how to add custom field: http://codex.wordpress.org/Custom_Fields )
6. Do a Manual-update for all the photos at the first run.
7. Make settings for your need.

== Frequently asked questions ==

= Why I write this plugin? =
I'd been using Pixelpost as my photoblog for years, and developed this plugin for it. Pixelpost is a simple and great photoblog software, but it has stopped to update for 4 years, so I decided to move to Wordpress, and with this plugin.

= How to get Flickr API key and secret? =
If you have not create one, you can 'Get Another Key' from Flickr: http://www.flickr.com/services/api/keys/. See the instruction in screenshots. 

= How to get a Flickr photo id? =
Flickr Photo ID is usually the number in the URL of a photo page eg: http://www.flickr.com/photos/jdleung/435246848/ 

= Why auto-update does't work? =
Check your theme if there is a 'wp_head();' between '<head>' and '</head>' in the header.php. It is one of the most essential theme hooks, so it is widely supported.

= Why it take so long in manual-update? =
You may have many photos and comments.

= Why some comments can not be retrieved in Auto-update? =
They might be created more than 200 days before. Do a Manual-update to read all the comments.


== Screenshots ==

1. Configuration page. 

2. If you have no API key, Get another key from: http://www.flickr.com/services/api/keys/

3. Select NON-COMMERCIAL KEY.

4. Input the information.

5. When done, Flikcr will show you the API key and secret. Then click "Edit auth flow for this app".

6. Set the "Callback URL" to you site URL.

== Changelog ==

= 1.23 =
* Bug fix: Convert the old date time format to new format automatically. Withou this conversion, last update time and next update time will show going back to “1970/01/01”, but a manual update or auto update can also fix it in version 1.22.

= 1.22 =
* save last update time in timestamp format.
* Use wordpress datetime format to show las update time and next update time.
* Fix timezone bug.
* Better instruction on installation.

= 1.21 = 
* Reply-user-name replace with icon when image allowed.
* Bug fix: Reply-user-name doesn't show in Auto-update mode with image disabled.
* Bug fix: reply-user-icon link error.
* Bug fix: Timeframe can begin with 0.

= 1.20 =
* You can retrieve comments of a specified photo in Manual-update.
* Add 'Allow HTML' and 'Allow Image' setting.
* Reply icon replace with screen username.
* Redundant　codes cleaned.

= 1.12 =
* Bug fix: delete duplicate loops in codes. Faster.
* Limit timeframe no more than 200d or 4800h. 

= 1.11 =
* Replace add_filter with add_action, auto-update may not work in some case.
* Add config link at plugins page.
* All forms generated with WP functions.
* Allow html in comment content.

= 1.1 =
* Bug fix: Cannot get-token on server(Weird thing: works at local, fails on server)
* Use the newest phpFlickr.
* Retrieving comments of more pages.
* Bug fix: Using two Timezone, it may cause retrieving duplicate comments of one photo.

= 1.0 = 
* First version.

== Upgrade notice ==



== Arbitrary section 1 ==
