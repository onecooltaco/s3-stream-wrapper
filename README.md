# S3 Stream Wrapper for WordPress

A WordPress plugin that stores media-library files in Amazon S3 through the AWS SDK for PHP stream wrapper.

When enabled, the plugin registers the `s3://` stream wrapper and rewrites WordPress upload filesystem paths to the configured bucket. It also validates S3 object-key length, normalizes filenames, preserves attachment file-size metadata, and works around image metadata functions that require local files.

> **Special-purpose plugin:** this is an infrastructure component, not a complete media-offload product. It does not provision a bucket, create IAM policies, configure a CDN, migrate existing media, or provide a settings screen.

## Requirements

- WordPress and PHP versions compatible with the installed AWS SDK for PHP 3.x
- Composer for installing runtime dependencies
- An S3 bucket
- AWS credentials available through the SDK's default credential provider chain
- IAM permissions for the object operations required by WordPress

## Installation

1. Clone or copy the repository to `wp-content/plugins/s3-stream-wrapper`.
2. Install production dependencies inside the plugin directory:

   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. Define the bucket and enable the wrapper in `wp-config.php`:

   ```php
   define( 'S3_BUCKET', 'example-media-bucket' );
   define( 'S3_USE_STREAM_WRAPPER', true );
   ```

4. Make AWS credentials available to the PHP/WordPress process—for example with an instance or task role in AWS.
5. Activate **S3 Stream Wrapper**.
6. Upload and process representative images before enabling it on a production site.

The plugin only starts when `S3_USE_STREAM_WRAPPER` is strictly `true` and `S3_BUCKET` is non-empty.

## AWS permissions

Grant the smallest policy that supports your site's workflows. Typical media operations need bucket listing plus object read, write, and delete permissions scoped to the relevant bucket prefix. Exact permissions depend on WordPress, image-processing plugins, and any cleanup or regeneration tools in use.

## Operational considerations

- Existing local uploads are not migrated automatically.
- S3 consistency and filesystem semantics differ from local disk behavior.
- Object URLs, public access, CDN delivery, and bucket policies must be designed separately.
- Keep `vendor/` in deployed plugin artifacts; the AWS SDK is loaded from Composer's autoloader.
- Test thumbnails, EXIF/IPTC reads, media replacement, deletion, backups, and metadata regeneration.

## Development

Install all dependencies with `composer install`. The repository includes WordPress coding-standard and PHPUnit development dependencies but currently has no documented automated test suite.

## License

GPL-2.0-or-later. See [`LICENSE`](LICENSE).
