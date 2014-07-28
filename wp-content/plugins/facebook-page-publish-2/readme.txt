=== Facebook Page Publish 2 ===
Contributors: deano1987, mtschirs
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MPJDRVYM87ZR4
Version: 0.4.1
Tags: Facebook, page, profile, publish, wall, share, post
Requires at least: 3.0
Tested up to: 3.5.0
Stable tag: 0.4.1

"Facebook Page Publish" publishes your blog posts to your Facebook profile or page. New fork updated for latest wordpress and facebook.

== Description ==

"Facebook Page Publish" publishes your blog posts to the wall of your Facebook profile, page or application. Posts appear on the wall of your choice as if you would share a link (there is NO "published via Application" notification). The authors [gravatar](http://gravatar.com), a self-choosen or random post image, the title, author, categories and a short excerpt of your post can be shown.

Decide yourself when and what posts to publish. Supports local and remote publishing based e.g. on the post category.

Makes use of the modern Facebook graph-API and integrates easily into your WordPress Blog.

All you need to do is (see *Installation*):

* Create a [Facebook application](https://www.facebook.com/developers/createapp.php)

Technical features:

* 100% userfriendly, easy to install & remove
* Lightweight, clean code

This plugin is a fork from mtschirs plugin, with updates to work in the latest wordpress aswel as facebook.

== Installation ==

Bob from http://wpvideo.tv made a nice [Installation Video How-To](http://wpvideo.tv/wordpress-blog-posts-auto-feed-facebook-fan-page/wordpress/682/)!

1. Install the plugin from your wordpress admin panel.

OR

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

Done? Then go to the plugin's settings page and follow the detailed setup instructions.

== Frequently Asked Questions ==

= How can I publish to multiple walls, pages or profiles? =
Not possible. See [Facebook Platform Policies](https://developers.facebook.com/policy), IV:

    IV. Application Integration Points
    5. You must not provide users with the option to publish more than one Stream story at a time.

This also makes a great excuse for not having had the time to implement such functionality.

= How can I publish to a group? =
Also not possible, at least not the expected way without additional "published via Application XY" notice.

= I can't activate the plugin because of fatal errors! =
This plugin requires php5. Sometimes you have to add 
`AddType x-mapp-php5 .php
AddHandler x-mapp-php5 .php`
to your .htaccess file.

= I got an error: Your authorization code was invalid or expired =
This happens from time to time. Give Facebook some time, they will fix it shortly.

= I have an other question / idea for improvement / observed a bug! =
Please post your question in the [forum](http://wordpress.org/tags/facebook-page-publish), I will try to reply shortly.

== Screenshots ==

1. Check to publish your post to Facebook.
2. An example post on Facebook.
3. The settings page.

== Changelog ==

= 0.4.1 =
* a space in the main file was messing up wordpress...

= 0.4.0 =
* NEW! back in development as Facebook Page Publish 2
* Update: plugin now works again with Facebook and in the latest wordpress.

= 0.3.9 =
* Update: Compatibility issues with other plugins that use filters resolved (thanks to *John DoeXAXA*!)
* Bugfix: Posts modified, but not republished on FB are no longer deleted from FB (thanks to *OurRVJourney*!)

= 0.3.8 =
* Update: Deletes previous version from Facebook when republishing a post (old Facebook comments will get lost)
* Update: Use post content as message if post extract contains no plain text (but is not empty)
* Update: Facebook requires now a type and url graph meta tag, both are now included
* Bugfix: /me replaced by object_id (thanks to *Niraj Shah*!)
* Bugfix: Compatibility issues with some other plugins resolved (including Flash Media Player)

= 0.3.7 =
* Update: Support for custom post types added
* Bugfix: Facebook resolved /link publishing bug http://bugs.developers.facebook.net/show_bug.cgi?id=19324

= 0.3.5 =
* Bugfix: Application secret validation no longer supported by facebook in its current form

= 0.3.3 =
* Bugfix: No more double postings when attachements included
* Bugfix: Default publishing policy for already on WP published posts set to "publish nothing"
* Bugfix: Design fixed for empty author or categories
* Bugfix: Included URLs where not always seperated by whitespace
* Update: Post to application walls
* Update: Internationalization - German and 63% French available

= 0.3.2 =
* Critical bugfix: fpp_get_post_image crashed when theme support for post thumbnails was not supported!

= 0.3.1 =
* Bugfix: Password protected posts: incorrect title and image was shown
* Bugfix: Shortcodes are now processed and no longer (incompletely) stripped (thanks to *cntrlwiz*!)
* Bugfix: diagnosis script URL now correct
* Bugfix: Author name now taken from first / last name, if those are not empty (thanks to *cntrlwiz*!)
* Bugfix: Timeout for http requests now 20s, 5s was too short on some servers (thanks to *misterjoecity*!)
* Bugfix: Fixed error in fpp_acquire_profile_access_token (thanks to *misterjoecity*!!)
* Update: Diagnostic script detects SSL availability and https connections (thanks to *mioto*!)
* Update: New settings introduced: disallow publishing of post excerpt, include links
* Update: Thumbnail from post: use featured thumbnail, if available (thanks to *Luis Marcos Loaiza*!)
* Update: Profile and page ID's are now automatically detected, major GUI redesign

= 0.3.0 =
* Update: Publishes to a page or profile
* Update: More userfriendly error reporting
* Update: New settings introduced: publishing policy (thanks to *Li-An*!) and appearance customization.
* Major bugfixes: Scheduled and remote posts (thanks to *ksoszka*!), posting as password-protected, private or draft (thanks to *tbjers*!)

= 0.2.2 =
* Bugfix: <!--more--> tags now recognized (thanks to *tbjers*!).
* Bugfix: Apostrophes (') no longer slashed (thanks to *dmeglio*!).
* Update: SSL_VERIFY and ALWAYS_POST_TO_FACEBOOK constants for manual configuration.

= 0.2.1 =
* Bugfix: Not all images in a post where found.
* Bugfix: Default transparent image prevents FB from choosing a poor random image for posts containing no images.
* Bugfix: Graph meta tags are now only rendered when displaying a single post.
* Update: Detailed setup instructions now available from the options page.

= 0.2.0 =
* Security: Only authors can publish to Facebook.
* Bugfix: Only posts can be published (no pages etc.).
* Bugfix: Character encoding for categories and title fixed.
* Bugfix: Facebook link description length is 420 chars max.

= 0.1.0 =
* First internal alpha release.

== Upgrade Notice ==


= 0.3.9 =
Updates, bugfixes, upgrade recommended.

= 0.3.8 =
Updates, bugfixes, upgrade recommended.

= 0.3.8 =
Updates, bugfixes, upgrade recommended.

= 0.3.7 =
Updates, bugfixes, upgrade recommended.

= 0.3.3 =
Updates, bugfixes, upgrade recommended.

= 0.3.2 =
Critical bugfixes, upgrade strongly recommended.

= 0.3.1 =
Updates, bugfixes, upgrade recommended.

= 0.3.0 =
Major update and bugfixes, upgrade strongly recommended.

= 0.2.2 =
Bugfixes, upgrade recommended.

= 0.2.1 =
Bugfixes, upgrade recommended.