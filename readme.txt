=== Rewind WP ===
Contributors: NoteToServices
Author website: https://notetoservices.com
Tags: demo, staging, testing, presentation, automatic, backup, rewind, display
Requires at least: 5.4
Tested up to: 5.4
Stable tag: 1.0.3
Requires PHP: 7.2
Plugin URI: http://wordpress.org/extend/plugins/rewind-wp/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://paypal.me/andbenice
Test site: https://demo.rewindwp.com

Automatically rewind your WordPress website.

== Description ==
Test plugins and themes or allow visitors to interact with your website for demo or testing purposes without saving any changes in the database.

== Details ==
The Rewind WP website interacts with the Rewind WP plugin to create a snapshot of your website and restore it after a certain duration has passed.

This plugin requires registering for API key at <a href="https://rewindwp.com" target="_blank">https://rewindwp.com</a>.

== Installation ==

1. Install and activate the Rewind WP plugin.
2. Navigate to rewindwp.com and sign up for a free account.
3. Add your website and grab the API key.
4. Enter in your API key and configure how your website will rewind.

== Requirements ==

You will need to have WP-CLI and the ZipArchive PHP modules installed on your server to use this plugin.

== Frequently Asked Questions ==

Q: What does Rewind WP do?
A: Rewind WP will make a snapshot of your website at a specified time and then automatically restore itself to that snapshot once the duration has been set. Basically, you are setting a demo stage for your website.

Q: What can I do with Rewind WP?
A: If you are a plugin developer or a website designer, you might want your clients or potential clients to interact with your website. After they are done, you have to go in and restore everything to the way it was before. With Rewind WP, it will automatically restore itself to the way it was before.

Q: Is Rewind WP free?  
A: Rewind WP does require an account at rewindwp.com and an API key, but using it is completely free with limitations.

Q: What makes the free version different from the pro version?
A: With the free version of Rewind WP, you can set your duration in half-hour intervals up to one hour, and only a snapshot of the database 
is made. Files within the WordPress folder are not protected. 

With the pro version, you unlock this feature and can set your duration in 5-minute intervals up to 24 hours and 
you also have the option to protect your wp-config file and the files within your wp-content folder. 

Q: Can I use this as a backup service?
A: Rewind WP was not intended to be used as a backup service and will only store a single copy of your website, but can technically be 
used as a manual backup & restore service that can be used when the Force Rewind button is pressed.

Q: Does Rewind WP protect my website from malware and viruses?
A: Rewind WP may help in restoring files, but may not eliminate new ones and should not be used as an alternative to your website malware, 
virus scanner, or automated backup service.

Q: Can I just use the Rewind WP plugin?
A: The Rewind WP plugin requires registration at rewindwp.com in order to obtain an API key.

Q: Can I just use the rewindwp.com website?
A: The rewindwp.com website requires instructions from the WordPress plugin and cannot work as a standalone without the plugin.

Q: Can I add multiple websites?
A: You may add multiple websites on rewindwp.com.

Q: Can I create multiple snapshots? 
A: Only one snapshot per website at a time is possible.

Q: Do I need to stop the rewind before making a new snapshot or changing the duration?
A: You can change the plugin configuration settings without stopping the cron job timer.

Q: The plugin only restores the database but does not delete anything in the themes, plugins, or uploads folders.
A: The free version of the plugin only rewinds the database, while the pro version offers support for files and your wp-config file.

Q: The plugin broke my website.
A: Unfortunately, there are a few things that could have gone wrong during the backup or restore point which we cannot indepdently always verify. While not an unknown issue, we urge you before setting the service as active the plugin, you should create an official copy of a backup for your website just in case.

Q: Can I restore my website faster than 5 minutes or longer than 24 hours?
A: No, as rewinding a website to less than every 5 minutes may cause issues or abuse and it is unlikely that any website needs a snapshot any longer 
than 24 hours later.

== Screenshots ==

* screenshot-1.jpg

== Changelog ==

= 1.0.0 =
Basic functionality for Rewind WP.

= 1.0.1 =
Bug fix on check of $status in Settings

= 1.0.2 = 
Bug fix for Next Rewind Cron resetting itself at midnight. 

= 1.0.3 = 
Bug fix: dbcheck type
Bug fix: added in existing but missing function of rwp_wpconfig_restore()
Bug fix: added in a double file check before attempted deletion
Removed: wp_rewinds_count(), rwp_rewinds_count()

== Upgrade Notice ==

= 1.0.0 =
Initial release.

= 1.0.1 =
Bug fix.

= 1.0.2 = 
Bug fix.

= 1.0.3 =
Bug fixes and updates.

== Known Issues ==

* Doing a site backup zipped to more than 2 GB may fail

* Backing up a database of more than 2 GB may fail

* The Settings page may not always appear with the correct information -- the plugin is pulling in the database snapshot which may not always 
reflect the accurate settings, which are actually being pulled from the Rewind WP server

* Occasionally, you may see error messages of database tables missing ~ don't worry about it ~ the plugin may have been 
attempting to load in the database and if you are loading mid-page, you may have loaded a database without all of the data 
fully implemented

* "Publishing failed. Invalid post ID." This message occurs when you are attempting to do something, such as write a post, page, or upload a 
media file  and the database is reset. The postid is no longer a valid ID since it is no longer in the database. 
You will need to create a new post completely and ensure you allow enough time for posts to be created.

== Additional Info ==

* Any inactive plugins and themes will be deleted upon rewind

* The free version of the plugin does not backup and cannot restore physical assets including media files, themes, or plugins
	- In other words: if a theme or plugin is deleted, once the data is restored, it will think it still has the files and 
		attempt to load them, but give error messages

* Configuration information is sent to Rewind WP for official authenticity, instead of relying on the WordPress database


== Copyright Info ==

Copyright (C) 2015-2020 [NoteToServices](https://www.notetoservices.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA

== Terms ==

By using this plugin and enabling Rewind WP, you agree that your website will need to make external calls to rewindwp.com.

If you do not agree to these terms, please uninstall and delete this plugin.

If you find this plugin useful, please give a review.

Rewind WP's <a href="https://rewindwp.com/terms" target="_blank">Terms of Service</a>

You may not redistribute this plugin or alter it in any way without permission from NoteToServices.
