<?php
/**
 * Plugin review class
 *
 * @package footnotes-made-easy
 * @license Unauthorized copying of this file, via any medium is strictly prohibited Proprietary and confidential
 *
 * @since 2.0.0
 */

declare(strict_types=1);

namespace FME\Helpers;

use FME\Controllers\Pointers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\FME\Helpers\Review_Plugin' ) ) {

	/**
	 * Nudges the admins to review the plugin.
	 *
	 * @since 2.0.0
	 */
	class Review_Plugin {

		public const REVIEW_OPTION_KEY = 'fme_review_plugin_option';

		public const LINK_ID = 'fme_review_plugin_link_id';

		/**
		 * Get default object settings
		 *
		 * @since 2.0.0
		 *
		 * @return array
		 */
		protected static function get_defaults() {

			$defaults = array(
				'days_after' => 10,
				'rating'     => 5,
				'message'    => sprintf(
					// translators: The plugin name.
					esc_html__( 'Hey! It&#039;s been a little while that you&#039;ve been using %s. You might not realize it, but user reviews are such a great help to us. We would be so grateful if you could take a minute to leave a review on WordPress.org. Many thanks in advance :)', 'footnotes-made-easy' ),
					'<b>Footnotes Made Easy</b>'
				),
				'link_label' => esc_html__( 'Click here to leave your review', 'footnotes-made-easy' ),
				// Parameters used in WP Dismissible Notices Handler.
				'cap'        => 'administrator',
				'scope'      => 'global',
			);

			return $defaults;
		}

		/**
		 * Initialize the library
		 *
		 * @since 2.0.0
		 *
		 * @return void
		 */
		public static function init() {

			if ( ! Pointers::is_dismissed( self::REVIEW_OPTION_KEY ) ) {

				\add_action( 'admin_footer', array( __CLASS__, 'script' ) );

				// \add_action( 'admin_notices', array( __CLASS__, 'maybe_prompt' ) );
			} else {
				\delete_option( self::REVIEW_OPTION_KEY );
			}
		}

		/**
		 * Check if it is time to ask for a review
		 *
		 * @since 2.0.0
		 *
		 * @return bool
		 */
		public static function is_time(): bool {

			$installed = (int) \get_option( self::REVIEW_OPTION_KEY, 0 );

			if ( 0 === $installed ) {
				self::setup_date();
				$installed = time();
			}

			if ( $installed + ( self::get_defaults()['days_after'] * \DAY_IN_SECONDS ) > time() ) {
				return false;
			}

			return true;
		}

		/**
		 * Save the current date as the installation date
		 *
		 * @since lates
		 *
		 * @return void
		 */
		protected static function setup_date() {
			\update_option( self::REVIEW_OPTION_KEY, time() );
		}

		/**
		 * Get the review link
		 *
		 * @since 2.0.0
		 *
		 * @return string
		 */
		protected static function get_review_link() {

			$link = 'https://wordpress.org/support/';

			$link .= 'plugin/';

			$link .= FME_TEXTDOMAIN . '/reviews';
			$link  = add_query_arg( 'rate', self::get_defaults()['rating'], $link );
			$link  = esc_url( $link . '#new-post' );

			return $link;
		}

		/**
		 * Get the complete link tag
		 *
		 * @since 2.0.0
		 *
		 * @return string
		 */
		protected static function get_review_link_tag() {

			$link = self::get_review_link();

			return '<a href="' . $link . '" target="_blank" id="' . self::LINK_ID . '">' . self::get_defaults()['link_label'] . '</a>';
		}

		/**
		 * Trigger the notice if it is time to ask for a review
		 *
		 * @since 2.0.0
		 *
		 * @return void
		 */
		public static function maybe_prompt() {

			if ( ! self::is_time() ) {
				return;
			}

			$class = array(
				'notice',
				'is-dismissible',
				'update-nag',
				'inline',
				'fme-review-notice',
			);

			printf( '<div id="%3$s" class="%1$s"><p>%2$s</p></div>', trim( implode( ' ', $class ) ), self::get_message(), self::REVIEW_OPTION_KEY ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Echo the JS script in the admin footer
		 *
		 * @since 2.0.0
		 *
		 * @return void
		 */
		public static function script() {
			?>

			<script>
				jQuery(document).on('click', '.fme-review-notice .notice-dismiss', function () {
					jQuery.ajax({
						url: ajaxurl,
						type: 'post',
						data: {
							pointer: jQuery(this).closest('.fme-review-notice').attr('id'),
							action: 'dismiss-wp-pointer',
						},
					});
				});
			</script>

			<?php
		}

		/**
		 * Get the review prompt message
		 *
		 * @since 2.0.0
		 *
		 * @return string
		 */
		protected static function get_message() {

			$message = self::get_defaults()['message'];
			$link    = self::get_review_link_tag();
			$message = $message . ' ' . $link;

			return wp_kses_post( $message );
		}
	}
}