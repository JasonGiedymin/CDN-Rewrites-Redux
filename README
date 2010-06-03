=== CDN Rewrites Redux ===
Contributors: Redux by Jason Giedymin, Amuxbit.com, Original by Phoenixheart
Author URLS: Redux by http://jasongiedymin.com, http://amuxbit.com, Original Author: http://www.pheonixheart.net/
Donate link: http://www.phoenixheart.net/wp-plugins/cdn-rewrites/
Tags: buddypress, cdn, content delivery network, bandwidth, rewrites
Requires at least: 2.2
Tested up to: 3.0
Stable tag: 1.0.1

This plugin is a redux with BuddyPress compatability based off the original CDN Rewrites Plugin by http://www.pheonixheart.net

This plugin rewrites the host(s) of your static files (JavaScripts, CSS, images etc.) (called Origin) into a CDN (Content Delivery Network) host. Works with almost all commerical and free CDNs like Akamai, Limelight Networks, EdgeCast, Coral etc. REQUIRES PHP >= 5. Now with BuddyPress compatability.

== Description ==
After releasing a WordPress plugin called [Free CDN](http://www.phoenixheart.net/wp-plugins/free-cdn/), I got a request to create another that supports rewriting hosts for commerical CDNs - those big names like Limelight, Akamai, Velocix, EdgeCast etc. 

Basically, this plugin allows a WordPress user to specify two important variables: an orgin host (says http://www.yoursite.com) and a "destination host" (like http://www.static.yoursite.com). It will then find all the static contents from that orgin host and rewrite them into the destination so that they will be delivered from there. 

== Features ==
* Multiple profiles are supported, allowing different contents to be treated differently
* You can exclusively select the content types for each profile - currently supporting JS, CSS, images, inline background images, and &lt;object&gt;'s
* Additional URL's can be added as excludes
* Debug mode gives you a good preview to make sure nothing goes wrong
* Works normally with other plugins. Especially useful if run along with WP Super Cache
* AJAX'ed admin section makes it quick and easy to adjust the settings

== Installation ==
As usual:

1. Download the plugin
1. Extract into a folder
1. Upload the entire to `wp-content/plugins/` directory of your WordPress installation
1. Enable it via Plugins panel
1. Head to Settings-&gt;CDN Rewrites and create the first profile. 
1. You're ready to go!

== Frequently Asked Questions ==
= My JavaScripts got broken! My cookies just don't work! [Insert error here] =
If your javascript doesn't work anymore, best bet is to uncheck "External JavaScript includes" from "Using CDN on these contents" option section. You can also enable Debug mode to see which files get involved, and try excluding them one by one to find out the problematic one and deal with it for good. 

= Why doesn't the plugin parse the external stylesheet and rewrite the background images there too? =
There should be no need. If the stylesheet got rewritten and is served through a destination host, all relative (most commonly) background images defined inside it will be pulled from that host too. 

== Screenshots ==
1. The profile list
2. "Add new profile" form
3. Options form

== History ==
* 1.0.1
1. Added uninstall handler to remove all settings and data upon being installed
1. Added link to Configuration page on plugins page
1. 
1. Added PayPal Donate button
1. Minor bug fixes and improvements
* 1.0.0 Initial version
