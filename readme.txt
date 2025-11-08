=== Bullhorn api client for wordpress ===
Contributors: yaroslawww
Tags: bullhorn
Requires at least: 5.3
Tested up to: 5.3
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

== Description ==

Bullhorn Staffing synchronisation plugin

== Frequently Asked Questions ==

= How add bullhorn credentials =
You should define constants in wp-config.php
```php
define('BH_CLIENT_ID', 'dc585694-g09...');
define('BH_CLIENT_SECRET', 'Ico50E....');
define('BH_API_USERNAME', 'your_username');
define('BH_API_PASSWORD', 'your_password');
```

= How get current user candidate =
```php
$candidate = \WPBullhornStaffing::candidate();
if($candidate) {
    // candidate found
}
```

= How get specific user candidate =
```php
$candidate = \WPBullhornStaffing::candidate($user);
if($candidate) {
    // candidate found
}
```
