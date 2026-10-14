=== S3 Stream Wrapper ===
Contributors: onecooltaco
Tags: amazon s3, aws, media, uploads, stream wrapper
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Stores WordPress media files in Amazon S3 through the AWS SDK for PHP stream wrapper.

== Description ==

S3 Stream Wrapper registers the AWS SDK's `s3://` protocol and rewrites WordPress upload filesystem paths to a configured bucket. It includes filename and object-key validation plus compatibility handling for attachment size and image metadata.

This is an infrastructure component, not a complete media-offload product. It does not create a bucket, provision IAM, configure a CDN, migrate existing media, or provide an administration screen.

The deployed plugin must include Composer's `vendor` directory and AWS credentials must be available through the SDK's default credential provider chain.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/s3-stream-wrapper`.
2. Run `composer install --no-dev --optimize-autoloader` in the plugin directory.
3. Add `define( 'S3_BUCKET', 'example-media-bucket' );` to `wp-config.php`.
4. Add `define( 'S3_USE_STREAM_WRAPPER', true );` to `wp-config.php`.
5. Make AWS credentials and least-privilege S3 permissions available to WordPress.
6. Activate the plugin and test representative media workflows.

== Frequently Asked Questions ==

= Does this migrate existing uploads? =

No. Existing local media must be migrated separately.

= Does this configure public media URLs or a CDN? =

No. Bucket access, URL delivery, CDN behavior, and cache policy are separate infrastructure concerns.

= Why does the plugin not start? =

It runs only when `S3_USE_STREAM_WRAPPER` is strictly `true`, `S3_BUCKET` is non-empty, Composer dependencies are present, and the AWS SDK can resolve credentials.

== Changelog ==

= 1.0.0 =

* Initial release.
