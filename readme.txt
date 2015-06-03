=== WP GIF Player ===
Contributors:  _changa_, cudaja, psmedia-hamburg
Tags: gif, player, easy, performance, image, preview
Requires at least: 3.0.1
Tested up to: 4.2.2
Stable tag: 0.8
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

WP GIF Player is an easy to use GIF Player for WordPress. It prevents GIF-files from loading on page load, boosting your page load time.

== Description ==

WP GIF Player is an easy to use GIF Player for WordPress. It prevents GIF-files from loading on page load, boosting your page load time.

* Adds the GIF button on your animated media
* Does play
* Does stop
* Loads GIF-files on-demand for faster page load times
* Plays only one GIF at a time
* Creates preview images automatically
* GIFs can be added easily with the Add GIF Button
+ Supports parallel uploads
* Simple installation
* Easily limit maximum screen size of your GIF

> <strong>Demo Page</strong><br>
> Check the demo page at [wp-gif-player.p-s-media.de](http://wp-gif-player.p-s-media.de)
<br>

> <strong>WP GIF Player on GitHub</strong><br>
> Please contribute to the project on [GitHub](https://github.com/psmedia-hamburg/wp-gif-player).

== Installation ==

The installation process.

Upload the plugin to your blog. Click "activate plugin". That's it.


- Start using WP-GIF-Player by adding a GIF via the "Add GIF" button in your editor screen
- Limit maximum width by changing width="..." parameter in the shortcode (150 - 600 in steps of 50)

In the settings you can choose whether you like WP GIF Player to create a preview pic for your article.

= Attention =

* You will have to use the WP standard editor to add gifs to your post
* If you deactivate or uninstall WP GIF Player, instead of the media, the shortcode [WPGP gif_id=".." width="600"] will appear in your posts. To replace shortcodes with your media you will have to edit these posts manually (An automated solution is planed but we can't guaranteed that it will work on posts created with this version of WP GIf Player).
* WP GIF Player will not work automatically on your existing gif-posts (you can review them manually)
* LazyLoad will not work on WP GIF Player preview pictures

** We are still in beta, use at your own risk. **


= Settings =

This plugin comes with a few settings regarding the automatic setting of thumbnails, which you can find in your WP Admin screen
at "Settings -> WP GIF Player".

You can choose between the following options for your GIF posts:

* Set no thumbnail at all (e.g. if you don't display thumbnails in your theme)
* Set the first frame of the first GIF in your post as the thumbnail (e.g. if you want a still image and not a whole GIF to display as the thumbnail)
* Set the first GIF in your post as the thumbnail


== Frequently Asked Questions ==

= You can be the first one! =


== Screenshots ==

1. Gifs added via WP GIF Player to your post will have an the "GIF" button which starts the animation. WP GIF Player will always play just one GIF at a time
2. WP GIF Player will add the "Add GIF" button to your editor. "Width" parameter will set a maximum width (50px steps from 150-600 are possible).
3. You can add multiple GIFs at once


== Changelog ==

= 0.8 =
Maximum width feature added

= 0.7 =
First public beta
