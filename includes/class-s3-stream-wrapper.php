<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * Includes filters shamelessly taken from:
 * https://github.com/Automattic/vip-go-mu-plugins/blob/develop/files/class-vip-filesystem.php
 *
 * @link       https://local.local
 * @since      1.0.0
 *
 * @package    S3_Stream_Wrapper
 * @subpackage S3_Stream_Wrapper/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    S3_Stream_Wrapper
 * @subpackage S3_Stream_Wrapper/includes
 * @author     Jeremy Leggat <jleggat@asu.edu>
 */
class S3_Stream_Wrapper {

	/**
	 * The protocol defined for the S3 Filesystem
	 */
	const PROTOCOL = 's3';

	/**
	 * Max length allowed for file paths in the S3 Service.
	 */
	const MAX_FILE_PATH_LENGTH = 1024;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      S3_Stream_Wrapper_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The S3 bucket
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     string	$bucket	The S3 bucket to access.
	 */
	protected $s3_bucket;

	/**
	 * The S3 Client
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     S3Client
	 */
	protected $s3_client;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $bucket ) {
		if ( defined( 'S3_STREAM_WRAPPER_VERSION' ) ) {
			$this->version = S3_STREAM_WRAPPER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 's3-stream-wrapper';
		$this->s3_bucket = $bucket;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_hooks();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - S3_Stream_Wrapper_Loader. Orchestrates the hooks of the plugin.
	 * - S3_Stream_Wrapper_i18n. Defines internationalization functionality.
	 * - S3_Stream_Wrapper_Admin. Defines all hooks for the admin area.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * This will set up autoloading for all classes in your Composer dependencies.
		 */
		if ( is_readable( plugin_dir_path( dirname( __FILE__ ) ) . '/vendor/autoload.php' ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . '/vendor/autoload.php';
		}

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-s3-stream-wrapper-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-s3-stream-wrapper-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-s3-stream-wrapper-admin.php';

		$this->loader = new S3_Stream_Wrapper_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the S3_Stream_Wrapper_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new S3_Stream_Wrapper_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_hooks() {

		$this->loader->add_filter( 'upload_dir', $this, 'filter_upload_dir', 10, 1 );
		$this->loader->add_filter( 'wp_handle_upload_prefilter', $this, 'filter_validate_file' );
		$this->loader->add_filter( 'wp_handle_sideload_prefilter', $this, 'filter_validate_file' );
		$this->loader->add_filter( 'get_attached_file', $this, 'filter_get_attached_file', 20 );
		$this->loader->add_filter( 'wp_generate_attachment_metadata', $this, 'filter_wp_generate_attachment_metadata', 10, 2 );
		$this->loader->add_filter( 'wp_read_image_metadata', $this, 'filter_wp_read_image_metadata', 10, 2 );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new S3_Stream_Wrapper_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();

		// Create and register stream
		$this->s3_client = new Aws\S3\S3Client( ['version' => 'latest'] );
		$this->s3_client->registerStreamWrapper();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    S3_Stream_Wrapper_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Return uploads path
	 *
	 * Different from wp_uploads_dir() as that will return the `s3://` protocol too
	 *
	 * @since   1.0.0
	 * @access  private
	 *
	 * @return  string The uploads path
	 */
	private function get_upload_path() {
		$upload_dir_path = wp_get_upload_dir()['path'];
		return substr( $upload_dir_path, strlen( self::PROTOCOL . '://' . $this->s3_bucket ) );
	}

	/**
	 * Filter the result of `wp_upload_dir` function
	 *
	 * @since 1.0.0
	 *
	 * @param array $params Array of upload directory paths and URLs
	 *
	 * @return array Modified output of `wp_upload_dir`
	 */
	public function filter_upload_dir( $params ) {
		$pos               = stripos( $params['path'], constant( 'WP_CONTENT_DIR' ) );
		$params['path']    = substr_replace( $params['path'],
			self::PROTOCOL . '://'  . trailingslashit($this->s3_bucket) . 'wp-content',
			$pos,
		strlen( constant( 'WP_CONTENT_DIR' ) ) );
		$params['basedir'] = substr_replace( $params['basedir'],
			self::PROTOCOL . '://' . trailingslashit( $this->s3_bucket ) . 'wp-content',
			$pos,
		strlen( constant( 'WP_CONTENT_DIR' ) ) );

		return $params;
	}

	/**
	 * Validate the file before we allow uploading it.
	 *
	 * @param  string[]  An array of data for a single file.
	 */
	public function filter_validate_file( $file ) {
		$file_name   = rawurlencode( $file['name'] );
		$upload_path = trailingslashit( $this->get_upload_path() );
		$file_path   = $upload_path . $file_name;

		$check_file_name = $this->validate_file_name( $file_path );
		if ( is_wp_error( $check_file_name ) ) {
			$file['error'] = $check_file_name->get_error_message();

			return $file;
		} elseif ( $check_file_name !== $file_name ) {
				$file['name'] = $check_file_name;
				$file_path    = $upload_path . $check_file_name;
		}

		$check_length = $this->validate_file_path_length( $file_path );
		if ( is_wp_error( $check_length ) ) {
			$file['error'] = $check_length->get_error_message();

			return $file;
		}

		return $file;
	}

	/**
	 * Check if file path is within the allowed length.
	 *
	 * @param  string  Path starting with /wp-content/uploads
	 */
	protected function validate_file_path_length( $file_path ) {
		if ( mb_strlen( $file_path ) > self::MAX_FILE_PATH_LENGTH ) {
			return new WP_Error( 'path-too-long', sprintf( 'The file name and path cannot exceed %d characters. Please rename the file to something shorter and try again.', self::MAX_FILE_PATH_LENGTH ) );
		}

		return true;
	}

	/**
	 * Use iconv filename to ASCII if needed.
	 *
	 * Convert filename to ASCII text.
	 *
	 * @since   1.0.0
	 * @access  protected
	 *
	 * @param   string      $file_path   Path starting with /wp-content/uploads
	 *
	 * @return  WP_Error|string        Filename string if file name is supported. Else false.
	 */
	protected function validate_file_name( $file_path ) {
		$text_encoding = mb_detect_encoding( $file_path );
		if ( 'ASCII' !== $text_encoding ) {
			// convert $text to ASCII
			$step1 = iconv( $text_encoding, 'ASCII//TRANSLIT', $file_path );
			// replace spaces and unknown characters with hyphens
			$step2 = sanitize_file_name( $step1 );
			$file_path = $step2;
		}

		return $file_path;
	}

	/**
	 * Filters the generated attachment metadata and adds "filesize".
	 *
	 * WP 6.0+ will be handling this automatically.
	 * @see https://core.trac.wordpress.org/ticket/49412
	 *
	 * @return array
	 */
	public function filter_wp_generate_attachment_metadata( $metadata, $attachment_id ) {
		// Append the filesize if not already set to avoid continued dynamic API calls.
		// The filesize doesn't change so it's okay to store it in meta.
		if ( ! isset( $metadata['filesize'] ) ) {
			$filesize = $this->get_filesize_from_file( $attachment_id );
			if ( false !== $filesize ) {
				$metadata['filesize'] = $filesize;
			}
		}

		return $metadata;
	}

	private function get_filesize_from_file( $attachment_id ) {
		$file = get_attached_file( $attachment_id );

		if ( ! file_exists( $file ) ) {
			return false;
		}

		return filesize( $file );
	}

	/**
	 * Filter `get_attached_file` output
	 *
	 * Fixes incorrect attachment post meta data where `_wp_attached_file` is a
	 * URL instead of a file path relative to the uploads directory
	 *
	 * @since   1.0.0
	 * @access  public
	 *
	 * @param   string  $file Path to file
	 *
	 * @return  string  Path to file
	 */
	public function filter_get_attached_file( $file ) {
		$uploads = wp_get_upload_dir();

		if ( $file && false !== strpos( $file, $uploads['baseurl'] ) ) {
			$file = str_replace( $uploads['baseurl'] . '/', '', $file );
		}

		return $file;
	}

	/**
	 * Exif compat for Streams.
	 *
	 * The iptc and exif functions don't always work with streams.
	 *
	 * So, download a local copy of the file and use that to read the exif data instead.
	 *
	 * Props S3-Uploads and humanmade for the fix
	 *
	 * https://github.com/humanmade/S3-Uploads
	 */
	public function filter_wp_read_image_metadata( $meta, $file ) {
		if ( ! wp_is_stream( $file ) ) {
			return $meta;
		}

		remove_filter( 'wp_read_image_metadata', [ $this, 'filter_wp_read_image_metadata' ], 10 );

		// Save a local copy and read metadata from that
		$temp_file = wp_tempnam();
		file_put_contents( $temp_file, file_get_contents( $file ) );
		$meta = wp_read_image_metadata( $temp_file );

		add_filter( 'wp_read_image_metadata', [ $this, 'filter_wp_read_image_metadata' ], 10, 2 );

		unlink( $temp_file );

		return $meta;
	}

}
