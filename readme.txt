=== Simple Diary for Wordpress ===
Contributors: jojaba
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=5PXUPNR78J2YW&lc=FR&item_name=Jojaba&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: diary, reminders, custom post type
Requires at least: 3.8
Tested up to: 3.9.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A very simple diary listing user created reminders. Ready to use in WordPress default themes.

== Description ==

Simple Diary is meant to be simple enough to be used out of the box. Simple, but also powerfull and customizable. All skill user should find something to do with it.

Here's the list of the settings (see screenshots for further infos):

* Custom post type "reminder" available. The reminder infos : Title, start date (required), end date (optional), start time(optional), end time (optional), location (required), url (optionnal), article (optional). The date and time infos are set using [pickadate.js](http://wordpress.org/ "Go to the pickadate.js homepage") jQuery plugin. All the system (compose reminder page and datepicker) is responsive.
* The admin reminders edit page is sorted by start date and contain title, start date, end date, location and creation/modification date. All columns are sortable except location column.
* Option page will let you modify some settings : Title of the diary page, slug modification, reminder count listed in upcoming reminders.
* All default WordPress themes (twentyten, twentyeleven, twentytwelve, twentythirteen, twentyfourteen) can easily be updated to take in account the reminders. You just have to get archive-reminder.php, content-reminder.php or loop-reminder.php, single-reminder.php from `/simple-diary/themes-support/your_theme/` and put it into your hteme folder (`/wp-content/themes/your-theme/`). You can take these files also as examples to customize Diary and reminders for your theme.
* A "Upcoming reminders" widget is available in the admin widget section.

Simple Diary has been developed by keeping in mind following rules:

* Easy to install, use and customize
* Working on every theme (including responsive themes)
* Adding microdata used to markup HTML code for semantic (so that most popular search providers can handle the infos).
* Make it translatable (availabe languages : english and french).

== Installation ==

= 1-The plugin installation =

1. Upload the `simple-diary` directory to `/wp-content/plugins/` folder of your Wordpress installation
2. Activate the plugin through the 'Plugins' menu in WordPress

= 2-The theme update =

1. Find what theme you use in your administration Appearance » Theme section
2. Upload the 3 files (archive-reminder.php, content-reminder.php or loop-reminder.php, single-reminder.php) matching your theme into the `/wp-content/themes/your_theme/` folder. You can find these files in `/simple-diary/themes-support/your_theme/` folder. For example, if you use twentyfourteen theme, you have to get the 3 files in `/simple-diary/themes-support/twentyfourteen/` folder and upload them into the `/wp-content/themes/twentyfourteen/` folder.

== Frequently Asked Questions ==

= Where could I find the template functions of Simple Diary? =

Edit the `/simple-diary/simdiaw-template-functions.php` file, you will find all available template functions.

= I don't want to use the widget to display the upcoming reminders, is it possible? =

Yes, you can list the upcoming reminders everywhere you want, you just have to use the `the_simdiaw_upcoming_reminders()` function to get them in list format.

This code:
    <ul>
    <?php the_simdiaw_upcoming_reminders(2) ?>
    </ul>
Will generate a html code like this:
    <ul>
        <li>Eiffel tower visiting<br>Date: 30/06/2014<br>Location: Paris</li>
        <li>Storks observation<br>Date: 06/06/2014<br>Location: Obersoultzbach</li>
    </ul>

== Screenshots ==

1. The Reminder compose window
2. The diary edit page
3. The Smple Diary options
4. The Simple Diary widget in the admin page
5. The widget in the Twenty Fourteen theme sidebar (frontend)
6. The diary page in the Twenty Fourteen theme (frontend)
7. A single reminder in the Twenty Fourteen theme (frontend)


== Changelog ==

= 1.0 =
* First release. Thanks for your feedback!
