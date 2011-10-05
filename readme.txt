=== LB Video for Wordpress ===
Contributors: tunnhn, lessbugs
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=tunnhn%40gmail%2ecom&item_name=LB%20Mixed%20Slideshow&currency_code=USD&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: youtube, video, rss, youtube rss, youtube widget
Requires at least: 2.8
Tested up to: 3.1
Stable tag: 3.0

Show and play video from Youtube RSS in a Lightbox or Post/Page

== Description ==

This plugin is used to create a slideshow for Wordpress Blog with multiple of transition effects.
You can use this plugin by:

1. Use as a shortcode for Post/Page.
1. Use as a Widget.
1. Use PHP code any where.

== Installation ==

1. Upload `lb-tube-video` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to "Tube Video" in left pane

= Usage =
You can add slideshow on your web page by 3 ways:
1. Add a shortcode like this [mixed-slideshow gid='GALLERY_ID_YOU_WANT'] in to your post/page.
2. Use widget named "mixedSlideshowWidget" in widget page.
3. Insert `<?php if(function_exists('ms_do_mixed_slideshow')) { ms_do_mixed_slideshow(array('gid'=>GALLERY_ID_YOU_WANT)); } ?>` into anywhere on your template.

== Screenshots ==

1. Admin - Manage Images
2. Admin - Manage Transitions
3. Admin - Global Configuration
4. Admin - Reset/Uninstall
5. Admin - Widget
6. Slideshow

== Frequently Asked Questions ==

= What parameters support in shortcode? =
1. `gid` - the ID of gallery you want to use to create slideshow
2. `width/height` - the width and height of slideshow
3. `border_width` - the width of border
4. `border_color` - the color of border
5. `border_image` - use an image as border
6. `limit_start` - only display images from `start` index
7. `limit` - only display `n images` (limit = n images) from `start`
8. `transitions` - only use these transitions, e.g: transitions='crossfade, blindup, etc'
9. `time_delay` - delay time to next the slide (1000 = 1s)
10. `show_description` - show description 0 = never, 1 = mouse hover, 2 = always
11. `show_thumbnails` - show thumbnails 0 = never, 1 = mouse hover, 2 = always
12. `show_top_nav` - show navigator on the top 0 = never, 1 = mouse hover, 2 = always
13. `show_nav` - show prev/next button 0 = never, 1 = mouse hover, 2 = always

== Changelog ==

= 1.0 =
* Release First Version

== Other Nodes ==

Please visit this link for more details [http://lessbugs.com](http://lessbugs.com/index.php?option=com_simpleshopping&Itemid=62&ctrl=product&id=3&task=showDetails "Lessbugs")

