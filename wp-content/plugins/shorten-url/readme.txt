=== Short URL ===

Author: SedLex
Contributors: SedLex
Author URI: http://www.sedlex.fr/
Plugin URI: http://wordpress.org/plugins/shorten-url/
Tags: shorttag, shortag, bitly, url, short
Requires at least: 3.0
Tested up to: 3.9.1
Stable tag: trunk

Your article (including custom type) may have a short url hosted by your own domain.

== Description ==

Your article (including custom type) may have a short url hosted by your own domain.

Replace the internal function of wordpress get_short_link() by a bit.ly like url. 

Instead of having a short link like http://www.yourdomain.com/?p=3564, your short link will be http://www.yourdomain.com/NgH5z (for instance). 

You can configure: 

* the length of the short link, 
* if the link is prefixed with a static word, 
* the characters used for the short link.

Moreover, you can manage external links with this plugin. The links in your posts will be automatically replace by the short one if available.

This plugin is under GPL licence. 

= Multisite - Wordpress MU =

This plugin is compatible with multisite installation. Each blog may manage their own list of links.

= Localization =

* Arabic (U.A.E.) translation provided by ZILZAL, alaaasly
* Arabic (Saudi Arabia) translation provided by ZILZAL
* German (Austria) translation provided by AndreasK.
* German (Germany) translation provided by reitermarkus, navelbrush, HenningKlocke
* English (United States), default language
* Spanish (Spain) translation provided by SebasContre, JosLuisCruz
* Farsi (Iran) translation provided by EhsanKing, sehrama.ir, HamidEslami, Yhayakhaledi
* French (France) translation provided by SedLex, jlmcreation
* Indonesian (Indonesia) translation provided by Adhityawicaksana
* Portuguese (Brazil) translation provided by Blinky, TonyFranco, ViniciusSantos, Bruno
* Russian (Russia) translation provided by Pacifik, AndreyFedotov
* Chinese (People's Republic of China) translation provided by OWenT

= Features of the framework =

This plugin uses the SL framework. This framework eases the creation of new plugins by providing tools and frames (see dev-toolbox plugin for more info).

You may easily translate the text of the plugin and submit it to the developer, send a feedback, or choose the location of the plugin in the admin panel.

Have fun !

== Installation ==

1. Upload this folder shorten-url to your plugin directory (for instance '/wp-content/plugins/')
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to the 'SL plugins' box
4. All plugins developed with the SL core will be listed in this box
5. Enjoy !

== Screenshots ==

1. The synthesis of the short link generated for your post and page
2. The configuration page of the plugin 

== Changelog ==

= 1.6.1 =
* BUG: The import function were not working ... now solved

= 1.6.0 =
* NEW: redirection through an internal page if wanted
* NEW: HOW TO

= 1.5.0 -&gt; 1.5.4 =
* BUG: convert url_externe into text to remove the 255 characters limitations
* BUG: do not test correctly diplicate entries for external link (now, only one shortlink may be available per external link)
* BUG: Remove the key of the MySQL table ; 
* NEW: new look
* NEW: comment for external link
* NEW: export / import function
* BUG: avoid problem with &
* BUG: avoid problem when too many articles 
* NEW: the shortlink may point to another domain
* NEW: support of custom page
* Various enhancements

= 1.4.0 -&gt; 1.4.5 =
* Now compatible with full HTTPS site
* A new keyword %short_url_without_link% is available to display short link without any html link
* The short URL may be displayed in the excerpt
* Some issue when the number of articles were too big
* Some page may be excluded
* The short URL may be displayed in the page

= 1.3.0 -&gt; 1.3.11 =
* Re-add the image button
* Warning popup
* Big issue with excerpt
* Update the core
* Avoid an infinite loop when 404 error
* Root URL for short link may be modified
* Correct a problem in quick move
* Search URL feature
* Shorten all links in posts
* www may be removed from short URL
* Even non published posts have short url
* Add http:// if missing
* Add a counter of the number of clicks on links
* The links may be ordered
* Update of Russian translation
* Major release of the framework

= 1.2.0 -&gt; 1.2.3 =
* Russian translation (by Pacifik)
* Improve English text thanks to Rene
* Correct a bug since home_url is different from site_url (thanks to Julian)
* Update of the core and translations
* SVN support

= 1.1.0 -&gt; 1.1.3 =
* Updating the core plugin
* Replacing the word "force" into "edit" (trevor's suggestion)
* When forcing an url, you may use a-zA-Z0-9.-_ characters (trevor's suggestion)
* ZipArchive class has been suppressed and pclzip is used instead
* Ensure that folders and files permissions are correct for an adequate behavior
* You can add any shortlink you want (i.e. with external links)
* Add translation for French

= 1.0.0 -&gt; 1.0.2 =
* Upgrade of the framework (version 3.0)
* First release in the wild web (enjoy)

== Frequently Asked Questions ==

* Where can I read more?

Visit http://www.sedlex.fr/cote_geek/

 
InfoVersion:2507148150c1b0751ecf9156a61fac717f115248