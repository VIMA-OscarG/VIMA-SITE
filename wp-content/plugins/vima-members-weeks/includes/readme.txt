=== Members Weeks ===
Contributors: og.lopar711
Tags: rental-submissions, api, integration
Requires at least: 6.0
Tested up to: 6.6
Stable tag: 1.0.0
License: GPLv2 or later

Secure REST bridge for Laravel Members Area to create, update and fetch rental-submissions posts in WordPress.

Usage:
1. Install and activate plugin.
2. Add MEMBERS_WEEKS_SHARED_SECRET in wp-config.php.
3. Laravel sends requests to /wp-json/members-weeks/v1/... with header:
   X-Members-Weeks-Secret: your-shared-secret
4. Endpoints are intended for server-to-server use only.
