# Bullhorn Staffing Plugin Wordpress

=== Bullhorn Staffing API Client for WordPress ===
# Contributors: Marko Tiosavljevic
# Tags: bullhorn, staffing, api
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

== Description ==
Bullhorn Staffing synchronisation plugin, updated for latest WP and PHP.

== Installation ==
1. Upload plugin and activate.
2. Configure API credentials via the settings page (future) or via wp-config.php as fallback:
```php
define('BH_CLIENT_ID', 'your-client-id');
define('BH_CLIENT_SECRET', 'your-client-secret');
define('BH_API_USERNAME', 'your-username');
define('BH_API_PASSWORD', 'your-password');
```

== Frequently Asked Questions ==

= How do I add Bullhorn credentials? =
Via WP admin: Bullhorn Staffing → Settings. (Or use constants above.)

= Health Checks =
Errors and connection failures will show up as admin notices. Check Site Health screen for plugin status.

= Getting Current User Candidate =
```php
$candidate = \WPBullhornStaffing::candidate();
if ($candidate) {
    // candidate found
}
```

== Changelog ==
= 0.1.0 =
* Updated compatibility with WP 6.4 and PHP 8.0+
* Admin error notices, improved REST handling
* Planned: Settings page for API credentials
