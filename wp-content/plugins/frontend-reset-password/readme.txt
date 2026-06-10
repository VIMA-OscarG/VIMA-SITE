=== Frontend Reset Password ===
Contributors: wpenhanced, rwebster85
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VAYF6G99MCMHU
Author URI: https://wpenhanced.com
Requires at Least: 4.4
Tested up to: 6.9
Stable tag: trunk
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: password, reset password, lost password, login

Let your users reset their forgotten passwords from the frontend of your website.

== Documentation ==

Full documentation and setup guide:
https://docs.wpenhanced.com/frontend-reset-password/

Find answers, usage examples, and troubleshooting tips on our official documentation site.

== Description ==

**Frontend Reset Password** lets your site users reset their lost or forgotten passwords in the frontend of your site. No more default WordPress reset form! Users fill in their username or email address and a reset password link is emailed to them. When they click this link they'll be redirected to your site and asked for a new password. Everything is handled using default WordPress methods including security, so you don't have to worry.

**Frontend Reset Password** is perfect for sites that have disabled access to the WordPress dashboard, or if you want to include a lost/reset password form on one of your custom site pages. It also works great with **Easy Digital Downloads**!

Any error messages display right on the form, including whether the username or email address is invalid.

The plugin works by hooking into the ``lostpassword_url`` WordPress filter, meaning compatibility with other plugins can be better maintained.

**Frontend Reset Password** is also translation ready.

**New:**
- Modern settings framework for easy configuration & searching our documentation
- Password requirements and eye icon toggle
- Customizable reset link text and email templates
- Full documentation at https://docs.wpenhanced.com/frontend-reset-password/

== Setup Guide ==

Quick Start:
1. Add the shortcode `[reset_password]` to any page.
2. Visit **Settings > Frontend Reset Password** in your WordPress admin to select your reset page and configure options.
3. (Optional) Customize form text, password requirements, and email templates.

See the [online documentation](https://docs.wpenhanced.com/frontend-reset-password/) for screenshots and advanced usage.

== Customisation ==

**Customisation Features:**
- Change all form text and labels
- Set password requirements (length, character types)
- Show/hide eye icon for password fields
- Customize email subject, sender, and template
- Display login link after password reset

Very little CSS styling is used, so the forms should style with your website theme beautifully.

If you use a frontend login page you can set that in the plugin also. Users are told they can login and are shown the url when they successfully change their password.

You can also set the minimum number of characters required for a password. Default is 0.

== Support & Resources ==

- [Full Documentation](https://docs.wpenhanced.com/frontend-reset-password/)
- Quick start guide in plugin settings
- [WordPress.org Support Forum](https://wordpress.org/support/plugin/frontend-reset-password/)

== Installation ==

**Manually in WordPress**

1. Download the plugin ZIP file from WordPress.org
2. From the WordPress admin dashboard go to Plugins, Add New
3. Click Upload Plugin, locate the file, upload
4. In the WordPress dashboard go to Plugins, Installed Plugins, and activate **Frontend Reset Password**
5. Make sure to read the quick start guide! (it's really short)

**Manually using FTP**

1. Download the plugin ZIP file, extract it
2. FTP to your server and go to your root WordPress directory
3. Navigate to wp-content/plugins
4. Upload the parent directory *som-frontend-reset-password* - the folder that contains the file som-frontend-reset-password.php - to that location
5. In the WordPress dashboard go to Plugins, Installed Plugins, and activate **Frontend Reset Password**
6. Make sure to read the quick start guide! (it's really short)

For detailed installation steps, troubleshooting, and advanced configuration, visit:
https://docs.wpenhanced.com/frontend-reset-password/

== Frequently Asked Questions ==

**The e-mail could not be sent:** This happens when the wp_mail() function call fails. If you're testing the plugin on a localhost and don't use a local email server, this error will show.

**Settings page dropdown shows 403 or "Failed to load resource" (Solid Security):** If you use Solid Security (formerly iThemes Security) with "Restrict REST API" enabled, the page selector in settings can be blocked. This plugin automatically allows logged-in administrators to load the pages list for that dropdown only. Make sure you are logged in as an admin when opening the settings page. If the dropdown still fails, temporarily set Solid Security's REST API access to "Default" under Security > Settings > Advanced, or check that your user has the "manage_options" capability.

See the [FAQ section in our documentation](https://docs.wpenhanced.com/frontend-reset-password/#faq) for more common questions and solutions.

== Screenshots ==

1. Reset Password Form (Twenty Seventeen Theme)
2. Enter New Password Form (Twenty Seventeen Theme)

== Changelog ==

= 1.3.3 = 30th January 2026 =
* [MOD] Strange, endless update loop (saying it was still 1.3.1)

= 1.3.2 = 30th January 2026 =
* [MOD] Page dropdown in settings now uses AJAX only (one request shared across all page fields); works when REST API is restricted

= 1.3.1 = 29th January 2026 =
* [FIX] Password reset form no longer shows raw special characters or regex in the page when special character requirement is enabled
* [FIX] Settings page "page dropdown" now works when Solid Security (or similar) blocks the public REST API

= 1.3.0 - 28th January 2026 =
* [NEW] New settings framework for our common brand. Search documentation and settings in WordPress admin
* [NEW] Full documentation site: https://docs.wpenhanced.com/frontend-reset-password/
* [NEW] Customizable special characters for password requirements. Now uses the full OWASP recommended character set by default, and allows admins to customize the allowed characters.
* [NEW] Added Settings link on the Plugins page for quick access to plugin settings
* [MOD] Removal of google font being loaded in CSS
* [MOD] Legacy Settings menu (Settings > Frontend Reset Password) now redirects to the new WP Enhanced settings page

= 1.2.5 - 23rd January 2026 =
* [MOD] Confirmed compatibility with WordPress 6.9

= 1.2.4 - 20th August 2025 =
* [NEW] New setting "Reset Link Text" - this will change the reset link text in the email from the URL to be what you add in the setting.
* [NEW] New Setting "Show Eye Icon on Password Fields" - if enabled it will allow the users to toggle the password visibility on the reset password.*
* [NEW] Added optional password format requirements. You can now choose to require at least one lowercase letter, one uppercase letter, one number, and/or one special character. Each requirement can be enabled individually.*
* [MOD] Modified the password reset form to display the requirements (character length and format requirements) in a list form and if they do not meet them, it will be red. When they meet the requirement it will go green.
* [THANKS] Special thanks to Colin Stearman (@britcoder) for his contributions and suggestions toward these enhancements.

= 1.2.3 - 16th April 2025 =
* Fixed bug when using WP 2FA
* Added {email} tag as option for custom email template
* Fix fatal error when there are issues sending email
* Fixed issue where translations were not loading from frontend-reset-password-LANG.mo

= 1.2.2 - 1st August 2023 =
* MOD: Lost Password Form - Accessibility

= 1.2.1 - 8th November 2022 =
* MOD: Updated branding to match WP Enhanced
* MOD: Updated "tested up to" so its not out of date anymore

= 1.2 - 13th July 2020 =
* [New Feature] Setting to change the email subject.
* [Change] Additional `esc_html()` calls added to frontend facing text.
* [Change] All translatable strings using `__` have been converted to `esc_html__()`.
* [Change] Updated POT file included.

= 1.1.91 =
* New Feature: Custom templates. Template files can now be included in your child theme folder. Create a new folder inside your child theme directory called ``somfrp-templates``, and follow the same template folder/file structure as found in the plugin's ``templates`` folder.
* Change: Now uses ``add_query_arg()`` when creating a reset password link, to improve compatibility
* Change: Username no longer included in reset password link, switched to using user ID
* Change: Changes made to ``lost_password_form.php`` template file
* Change: Tested up to WordPress 5.4

= 1.1.9 =
* Fix: Changed the way error messages are displayed to improve security. As such the plugin template files have changed, meaning any custom ones you have made will need to be updated to reflect the new changes.
* Change: Removed redundant ``<i>`` element from ``lost_password_form.php``.

= 1.1.8 =
* New Feature: Custom templates. You can now override the form templates in your theme. Create a new folder inside your theme directory called ``somfrp-templates``, and follow the same template folder/file structure as found in the plugin's ``templates`` folder.

= 1.1.7 =
* Change: More HTML tags are now available to use in email messages, since the saved message is now included in the email raw, but still uses the ``wpautop()`` function to automatically add ``<p>`` tags

= 1.1.6 =
* Change: Replaced filter 'retrieve_password_title' with 'somfrp_retrieve_password_title' to prevent other plugins unintentionally overriding the email title
* Change: Replaced filter 'retrieve_password_message' with 'somfrp_retrieve_password_message' to prevent other plugins unintentionally overriding the email message
* Change: Set priority for lostpassword_url filter to 999
* Change: Moved plugin settings page from the Plugins section to the Settings section of the admin menu

= 1.1.5 =
* Change: Removed login check to allow resetting password when logged in
* Fix: Error output corrected for the invalid_key index

= 1.1.41 =
* Fix: Corrected bug with action displaying form

= 1.1.4 =
* Change: Changed to using "somfrp_action" parameter rather than "action" to avoid conflicts

= 1.1.3 =
* Change: Functions to override default lost password actions and filters have a higher priority number

= 1.1.2 =
* Change: Email sent confirmation text no longer shows the email address

= 1.1.1 =
* Fix: Custom text for the reset form now outputs HTML tags correctly

= 1.1 =
* New feature: Customise the name and email address that the reset password emails send from, rather than the default wordpress@yoursite.com
* New feature: Plugin now sends HTML formatted emails which can be fully customised in the settings
* New feature: Select custom pages to redirect to for the email sent successfully and password changed pages, rather than the reset password page handling everything

= 1.0.5 =
* Cleaned undefined index errors
* Change wp_mail() headers to better support some plugins/themes

= 1.0.4 =
* Fixed missing WP_Error object on password validation

= 1.0.3 =
* Plugin now translation ready

= 1.0.2 =
* Textdomain set for language file

= 1.0.1 =
* Textdomain fix

= 1.0 =
* Initial release