=== Paid Memberships Pro - Set Expiration Dates Add On ===
Contributors: strangerstudios
Tags: paid memberships pro, pmpro, memberships, ecommerce, expiration
Requires at least: 3.5
Tested up to: 6.5
Stable tag: 0.7

Set a specific expiration date (e.g. 2013-12-31) for a PMPro membership level or discount code in YYYY-MM-DD format.
Enter "Y" for current year, "Y2" for next year. "M", "M2" for current/next month.
If the expiration date has already passed and "Y" is used, "Y" will start at the next year.

This expiration date will override any expiration period set on the level.

== Description ==
This plugin requires Paid Memberships Pro to function.

Set a specific expiration date (e.g. 2013-12-31) for a PMPro membership level or discount code in YYYY-MM-DD format.
Enter "Y" for current year, "Y2" for next year. "M", "M2" for current/next month.
If the expiration date has already passed and "Y" is used, "Y" will start at the next year.

This expiration date will override any expiration period set on the level.

== Installation ==

1. Upload the `pmpro-set-expiration-date` directory to the `/wp-content/plugins/` directory of your site.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Change the expiration date on the edit levels and edit discount code pages.

== Changelog ==
= 0.7 - 2024-04-24 =
* ENHANCEMENT: Support Paystack and PayFast payment gateway.
* ENHANCEMENT: Applied WordPress Coding Standards to code (improves readability).
* ENHANCEMENT: Improved support to show the expiration date when a discount code is applied.
* ENHANCEMENT: Moved Set Expiration Date settings to Expiration Date tab in the Membership Level settings.
* BUG FIX: Fixed an issue in some cases where the expiration date was losing a day.

= 0.6.1 - 2020-01-19 =
* BUG FIX: Fixed issue where expiration dates might not apply accurately when using PayPal Standard.

= 0.6 =
* SECURITY: Escaped text on output.
* BUG FIX: Fixed a warning if the level's expiration date was not set at checkout.
* BUG FIX/ENHANCEMENT: Added a wrapper function for the pmpro_ipnhandler_level hook to ensure correct arguments are passed into this hook. Thanks @aquiferweb.
* ENHANCEMENT: Stopped autoloading set expiration date option. This should improve performance for sites that have a lot of membership levels with a set expiration date.
* ENHANCEMENT: Added in "Set Expiration Date" to level's table in the admin area to easily see which levels have these options set.
* ENHANCEMENT: Show a warning inside the admin area, if a level has a past date set.
* ENHANCEMENT: Allow localization/translations. Thanks to @skotperez for the French translation files.
* ENHANCEMENT: Rebuilt logic around calculating date, supports multi-digit placeholders.
* ENHANCEMENT: Added new filters to allow developers to dynamically adjust dates: 'pmprosed_expiration_date_raw' and 'pmprosed_expiration_date'.

= .5.1 =
* BUG FIX: Fixed issue where expiration dates like 2019-07-29 would be converted to 2019-07-31 when used.

= .4.3 =
* BUG FIX: Added back the code to make sure dates like 2019-02-31 are converted to 2019-02-28

= .4.2 =
* BUG FIX: Fixed bug where some dates were not correctly pushed to next month.
* ENHANCEMENT: Added ability to pass a date to pmprosed_fixDate() to set expiration dates from instead of using current date.

= .4.1 =
* BUG FIX: Better handling of Y1 and M1. A date like Y1-12-31 no longer is pushed out to next year.
* ENHANCEMENT: Created a test file here (https://gist.github.com/ideadude/f94f4440ab109fe894a4a45bd9b64734) to make sure future updates don't break functionality for date formats.

= .4 =
* BUG FIX/ENHANCEMENT: Accepting Y1 and M1 in addition to Y and M in dates.

= .3.1 =
* BUG: Fixed warning if the $level passed into the pmpro_after_checkout hook was empty.

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
