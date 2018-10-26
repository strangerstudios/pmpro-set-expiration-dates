=== Paid Memberships Pro - Set Expiration Dates Add On ===
Contributors: strangerstudios
Tags: paid memberships pro, pmpro, memberships, ecommerce, expiration
Requires at least: 3.5
Tested up to: 4.5.3
Stable tag: .3.2

Set a specific expiration date (e.g. 2013-12-31) for a PMPro membership level or discount code in YYYY-MM-DD format.
Enter "Y" for current year, "Y2" for next year. "M", "M2" for current/next month.
If the expiration date has already passed and "Y" is used, "Y" will start at the next year.

This expiration date will override any expiration period set on the level.

If a user already has an active membership on this level, this plugin attempts to extend their existing expiry date, rather than setting expiry dates based on the current time.

== Description ==
This plugin requires Paid Memberships Pro to function.

== Installation ==

1. Upload the `pmpro-set-expiration-date` directory to the `/wp-content/plugins/` directory of your site.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Change the expiration date on the edit levels and edit discount code pages.

== Changelog ==
= .3.2 =
* Attempts to extend an existing membership level (if present) rather than always working from current time.

= .3.1 = 
* BUG: fixed bug - added new function pmprosed_pmpro_ipnhandler that filters IPNs to allow access to $user_id

= .3 =
* BUG: Fixed bug when using PayPal Standard.
* BUG: Fixed bug with dates near the end of the month.

= .2 =
* Now showing Membership expires on {date} and checkout page when a set expiration date is set. (Thanks, Tania)
* Fixed bug when applying discount codes

= .1.1 =
* Fixed a warning.
* "Y" variable now starts from the next year if the expiration date has already passed.

= .1 =
* Initial commit.
