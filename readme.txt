=== Unoapp Protect WP Admin ===
Contributors: unoapp
Donate link: http://www.unoapp.com/support
Tags: unoapp protect wp admin,ip based login,protect wordpress admin,change wp admin slug,secure wordpress admin,rename admin url,protect wp admin by ip,change wp user register url,change wp login url
Requires at least: 4.0
Tested up to: 5.9.3
Stable tag: 1.1
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

unoapp protect wp admin allows access for you only by URL change and access on IP based.

== Description ==

Many time sites hacked by admin access because it's common URL for all wp-admin, this unoapp protect wp admin gives advanced security against hackers.

Are you seeing a lot of attacks on your WordPress admin area? Protecting the admin area from unauthorized access allows you to block many common security threats
unoapp protect wp admin helps solve this problem by allowing webmasters to customize their admin panel URL and access allows only selected ips.

After installed and configured unoapp protect wp admin plugin, administrator able to change the "sitename.com/wp-admin" link into "sitename.com/custom-admin".

The plugin also restrict admin access by multiple ips based

** NOTE: You should keed backup your database before activating this plugin.**
for some reason, you find it necessary to restore your database from these backups.

= Features =

 * Option to change custom wp-admin into both sides after logged in and before login URL(i.e http://yourdomain.com/custom-admin)
 * Automatically change "Register" page URL
 * Automatically change "Lost Password" page URL
 * Restrict applied for registered non-admin users from wp-admin
 * Allow admin access by defining comma separated multiple ips

== Installation ==
Automatically: In most cases, you can install automatically from WordPress.

Manually: follow these steps:

 * 1) Upload "unoapp protect wp admin" folder to the `/wp-content/plugins/` directory.
 * 2) Activate the plugin.
 * 3) Go to Settings "WP-Admin Advanced Security" and configure the plugin settings.

== Important ==

1) Save the slug.

2) Please put below two lines code in your wp-config.php file above of Absolute path (ABSPATH).

define('WP_ADMIN_DIR', 'office-admin');

define('ADMIN_COOKIE_PATH', SITECOOKIEPATH . WP_ADMIN_DIR);

3) 
Sometimes it's issuing while permalink settings not updated. 
Some time .htaccess not updated due to permission issue, permalink issue or some other security plugins, in that case, you can update .htaccess manually.

<code>
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteRule ^custom-admin/(.*) wp-admin/$1?%{QUERY_STRING} [L]
RewriteRule ^custom-admin/?$ wp-login.php [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
</code>

== Frequently Asked Questions ==

* 1.) Nothing happen after enable and add the new wordpress admin url? 

   Don't worry, Just update the site permalink ("Settings" >> "Permalinks") and logout.

* 2.) Not able to login into admin after enable plugin? 

   Make sure you have given proper writable permission on htaccess and check htaccess is updated plugin code. 

* 3.) Am i not able to login after installation

Sometime issues can come only in case when you will use default permalink settings. 
If your permalink will be update to any other option except default then it will be work fine. Anyway Dont' worry, manualy you can add code into your site .htaccess file.

== Screenshots ==

1. screenshot-1.png

2. screenshot-2.png

3. screenshot-3.png

4. screenshot-4.png

== Changelog == 

= 1.0 = 
 * First stable release

= 1.1 = 
 * New version release
   + fixed permission issues
   + guidline added for users
