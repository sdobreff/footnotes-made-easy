<?php
/**
 * Footnotes Made Easy
 *
 * Easily add footnotes to a post
 *
 * @package   footnotes
 * @author    sdobreff
 * @copyright Copyright (C) Sdobreff - All Rights Reserved
 * @license Unauthorized copying of this file, via any medium is strictly prohibited Proprietary and confidential
 * Written by sdobreff, November 2023
 * @link      https://github.com/sdobreff/footnotes-made-easy
 *
 * Plugin Name:     Footnotes Made Easy
 * Plugin URI:      https://github.com/sdobreff/footnotes-made-easy
 * Description:     Allows post authors to easily add and manage footnotes in posts.
 * Version:         2.5.0
 * Author:          Footnotes
 * Author URI:      https://github.com/sdobreff
 * Text Domain:     footnotes-made-easy
 * Requires PHP:    7.1
 */

use FME\Helpers\Context_Helper;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'We\'re sorry, but you can not directly access this file.' );
}

define( 'FME_VERSION', '2.5.0' );
define( 'FME_TEXTDOMAIN', 'footnotes-made-easy' );
define( 'FME_NAME', 'Footnotes Made Easy' );
define( 'FME_PLUGIN_ROOT', plugin_dir_path( __FILE__ ) );
define( 'FME_PLUGIN_ROOT_URL', plugin_dir_url( __FILE__ ) );
define( 'FME_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'FME_PLUGIN_ABSOLUTE', __FILE__ );
define( 'FME_MIN_PHP_VERSION', '7.1' );
define( 'FME_WP_VERSION', '5.0' );
define( 'FME_SETTINGS_NAME', 'fme_footnote_options' );


if ( version_compare( PHP_VERSION, FME_MIN_PHP_VERSION, '<=' ) ) {
	add_action(
		'admin_init',
		static function () {
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
	);
	add_action(
		'admin_notices',
		static function () {
			echo wp_kses_post(
				sprintf(
					'<div class="notice notice-error"><p>%s</p></div>',
					sprintf(
						// translators: the minimum version of the PHP required by the plugin.
						__(
							'"%1$s" requires PHP %2$s or newer. Plugin is automatically deactivated.',
							'footnotes-made-easy'
						),
						FME_NAME,
						FME_MIN_PHP_VERSION
					)
				)
			);
		}
	);

	// Return early to prevent loading the plugin.
	return;
}

if ( ! extension_loaded( 'mbstring' ) ) {
	add_action(
		'admin_init',
		static function () {
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
	);
	add_action(
		'admin_notices',
		static function () {
			echo wp_kses_post(
				sprintf(
					'<div class="notice notice-error"><p>%s</p></div>',
					sprintf(
						// translators: the mbstring extensions is required by the plugin.
						__(
							'"%1$s" requires multi byte string extension loaded. Plugin is automatically deactivated.',
							'footnotes-made-easy'
						)
					)
				)
			);
		}
	);

	// Return early to prevent loading the plugin.
	return;
}

$plugin_name_libraries = require FME_PLUGIN_ROOT . 'vendor/autoload.php';

if ( ! Context_Helper::is_installing() ) {
	\register_activation_hook( FME_PLUGIN_ABSOLUTE, array( '\FME\Footnotes_Made_Easy', 'plugin_activate' ) );
	\add_action( 'plugins_loaded', array( '\FME\Footnotes_Made_Easy', 'init' ) );
}
