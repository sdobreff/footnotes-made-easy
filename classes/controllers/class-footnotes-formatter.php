<?php
/**
 * Formats the post content and creates the footnotes HTML markup
 *
 * @package footnotes-made-easy
 * @license Unauthorized copying of this file, via any medium is strictly prohibited Proprietary and confidential
 *
 * @since 2.0.0
 */

declare(strict_types=1);

namespace FME\Controllers;

use FME\Helpers\Settings;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\FME\Controllers\Footnotes_Formatter' ) ) {
	/**
	 * Responsible for proper context determination.
	 *
	 * @since 2.0.0
	 */
	class Footnotes_Formatter {

		public const TEXT_MARKER_PREFIX = '#########';

		public const SHORT_CODE_POSITION_HOLDER = '########FME########';

		public const SHORTCODE_NAME = 'fme_show_footnotes';

		/**
		 * Array with the styles
		 *
		 * @var array
		 *
		 * @since 2.0.0
		 */
		private static $styles = array(
			'decimal'              => '1,2...10',
			'decimal-leading-zero' => '01, 02...10',
			'lower-alpha'          => 'a,b...j',
			'upper-alpha'          => 'A,B...J',
			'lower-roman'          => 'i,ii...x',
			'upper-roman'          => 'I,II...X',
			'symbol'               => 'Symbol',
		);

		/**
		 * Stores the processed identifiers for the given post ID and caches them.
		 *
		 * @var array
		 *
		 * @since 2.4.0
		 */
		private static $identifiers = array();

		/**
		 * Inits the formatter class and sets the hooks
		 *
		 * @return void
		 *
		 * @since 2.0.0
		 */
		public static function init() {

			\add_filter( 'fme_process_content_hooks', array( __CLASS__, 'acf_footnotes' ) );

			/**
			 * Apply the content filters - parses the content and adds the extracted footnotes
			 *
			 * @param array - The array with the hooks to be applied for the processing.
			 *
			 * @since 2.4.1
			 */
			$process_hooks = \apply_filters( 'fme_process_content_hooks', array( 'the_content' ) );
			array_map(
				function ( $hook ) {
					\add_action( $hook, array( __CLASS__, 'process' ), Settings::get_current_options()['priority'] );
				},
				$process_hooks
			);

			\add_action( 'wp_head', array( __CLASS__, 'insert_styles' ) );

			\add_shortcode( self::SHORTCODE_NAME, array( __CLASS__, 'show_footnotes' ) );

			if ( Settings::get_current_options()['pretty_tooltips'] ) {
				\add_action( 'init', array( __CLASS__, 'register_script' ) );
				\add_action( 'wp_footer', array( __CLASS__, 'print_script' ) );
			}
		}

		/**
		 * Shortcode / show footnotes method
		 *
		 * @param array $args - Array of arguments passed to the method. Currently only one is supported.
		 *
		 * `post_id` - the ID of the post for which we should extract footnotes.
		 *
		 * @return string|void
		 *
		 * @since 2.4.0
		 */
		public static function show_footnotes( $args = null ) {
			global $post, $wp_current_filter;

			$filters = (array) $wp_current_filter;
			// Lets check if the call comes from the WP the content filter.
			foreach ( $filters as $filter ) {
				if ( 'the_content' === $filter ) {
					// It does - the process method will be called later on as it has lower priority, so mark the positions and bounce - the method will take care of them later on.

					return self::SHORT_CODE_POSITION_HOLDER;
				}
			}

			$atts = shortcode_atts(
				array(
					'post_id' => 0,
				),
				$args
			);

			if ( ! $atts['post_id'] && ! $post ) {
				return '';
			} elseif ( ! $atts['post_id'] ) {

				echo self::get_footnotes_markup( $post ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo self::get_footnotes_markup( \get_post( (int) $atts['post_id'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		/**
		 * Should we show footnotes in the ACF fields?
		 *
		 * @param array $hooks - The array of hooks.
		 *
		 * @return array
		 *
		 * @since 2.4.0
		 */
		public static function acf_footnotes( array $hooks ): array {
			if ( class_exists( 'ACF' ) && Settings::get_current_options()['acf_show_footnotes'] ) {
				$hooks[] = 'acf_the_content';
			}

			return $hooks;
		}

		/**
		 * Insert additional CSS
		 *
		 * Add additional CSS to the page for the footnotes styling
		 *
		 * @since 2.0.0
		 */
		public static function insert_styles() {
			?>
			<style>
			<?php if ( 'symbol' !== Settings::get_current_options()['list_style_type'] ) { ?>
				ol.footnotes>li {list-style-type:<?php echo \esc_attr( Settings::get_current_options()['list_style_type'] ); ?>;}
				ol.footnotes>li>span.symbol {display: none;}
			<?php } else { ?>
				ol.footnotes>li {list-style-type:none};
			<?php } echo Settings::get_current_options()['css_footnotes']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</style>
			<?php
		}

		/**
		 * Tooltip Scripts
		 *
		 * Add scripts and CSS for pretty tooltips
		 *
		 * @since 2.0.0
		 */
		public static function register_script() {

			wp_register_script(
				'wp-footnotes-tooltips',
				FME_PLUGIN_ROOT_URL . 'js/tooltips.min.js',
				array(
					'jquery',
					'jquery-ui-widget',
					'jquery-ui-tooltip',
					'jquery-ui-core',
					'jquery-ui-position',
				),
				FME_VERSION,
				true
			);

			wp_register_style(
				'wp-footnotes-tt-style',
				FME_PLUGIN_ROOT_URL . 'css/tooltips.min.css',
				array(),
				FME_VERSION
			);
		}

		/**
		 * Adds the scripts and CSS in the footer section of the WP
		 *
		 * @return void
		 *
		 * @since 2.0.0
		 */
		public static function print_script() {
			\wp_print_scripts( 'wp-footnotes-tooltips' );
			\wp_print_styles( 'wp-footnotes-tt-style' );
		}

		/**
		 * Searches the text and extracts footnotes
		 *
		 * Adds the identifier links and creates footnotes list
		 *
		 * @param string $data - The content of the post.
		 *
		 * @return string  The new content with footnotes generated
		 *
		 * @since 2.0.0
		 */
		public static function process( $data ) {

			global $post, $wp_current_filter;

			$shortcode_replace = false;

			// check against post existing before processing.
			if ( ! $post ) {
				return $data;
			}

			// Check whether we are displaying them or not.
			$display = true;
			if ( Settings::get_current_options()['no_display_home'] && is_home() ) {
				$display = false;
			}
			if ( Settings::get_current_options()['no_display_archive'] && is_archive() ) {
				$display = false;
			}
			if ( Settings::get_current_options()['no_display_date'] && is_date() ) {
				$display = false;
			}
			if ( Settings::get_current_options()['no_display_category'] && is_category() ) {
				$display = false;
			}
			if ( Settings::get_current_options()['no_display_search'] && is_search() ) {
				$display = false;
			}
			if ( Settings::get_current_options()['no_display_feed'] && is_feed() ) {
				$display = false;
			}
			if ( Settings::get_current_options()['no_display_preview'] && is_preview() ) {
				$display = false;
			}

			// Check if we have a work to do here.
			if ( false === \mb_strpos( $data, Settings::get_current_options()['footnotes_open'] ) ) {
				// Nope - bounce.
				return $data;
			}

			$identifiers = self::extract_current_notes( $data, $post->ID );

			$filters = (array) $wp_current_filter;

			$excerpt_call = false;
			// Lets check filters, and if that call comes from excerpt function, we have to remove ourselves.
			foreach ( $filters as $filter ) {
				if ( false !== \strpos( $filter, 'excerpt' ) ) {
					$display      = false;
					$excerpt_call = true;

					break;
				}
			}

			if ( ! $display ) {
				// We don't have to display anything, but that doesn't mean there are no footnotes assigned. So we should clear them out, as well as the shortcodes marked positions.
				if ( count( $identifiers ) ) {
					foreach ( $identifiers as $identifier ) {

						$pos = strpos( $data, self::TEXT_MARKER_PREFIX . $identifier['original_position'] );
						if ( false !== $pos ) {
							$data = substr_replace( $data, '', $pos, strlen( self::TEXT_MARKER_PREFIX . $identifier['original_position'] ) );
						}
					}
				}

				$data = \str_replace( self::SHORT_CODE_POSITION_HOLDER, '', $data );

				if ( $excerpt_call ) {
					if ( isset( self::$identifiers[ $post->ID ] ) ) {
						unset( self::$identifiers[ $post->ID ] );
					}
				}

				return $data;
			}

			// Check for and setup the starting number.
			$start_number = ( 1 === preg_match( '|<!\-\-startnum=(\d+)\-\->|', $data, $start_number_array ) ) ? $start_number_array[1] : 1;

			$processed_data = self::get_footnotes( $data, $post->ID );

			$footnotes   = $processed_data['footnotes'];
			$identifiers = $processed_data['identifiers'];

			$style = self::get_style( $post );

			// Footnotes and identifiers are stored in the array.

			$use_full_link = false;
			if ( is_feed() ) {
				$use_full_link = true;
			}

			if ( is_preview() ) {
				$use_full_link = false;
			}

			// Display identifiers.

			foreach ( $identifiers as $key => $identifier ) {

				$id_id      = 'identifier_' . $key . '_' . $post->ID;
				$id_num     = ( 'decimal' === $style ) ? $identifier['use_footnote'] + $start_number : self::convert_num( $identifier['use_footnote'] + $start_number, $style, count( $footnotes ) );
				$id_href    = ( ( $use_full_link ) ? get_permalink( $post->ID ) : '' ) . '#footnote_' . $identifier['use_footnote'] . '_' . $post->ID;
				$id_title   = str_replace( '"', '&quot;', htmlentities( html_entity_decode( wp_strip_all_tags( $identifier['text'] ), ENT_QUOTES, 'UTF-8' ), ENT_QUOTES, 'UTF-8' ) );
				$id_replace = Settings::get_current_options()['pre_identifier'] . '<a href="' . $id_href . '" id="' . $id_id . '" class="footnote-link footnote-identifier-link" title="' . $id_title . '">' . Settings::get_current_options()['inner_pre_identifier'] . $id_num . Settings::get_current_options()['inner_post_identifier'] . '</a>' . Settings::get_current_options()['post_identifier'];
				if ( Settings::get_current_options()['superscript'] ) {
					$id_replace = '<sup>' . $id_replace . '</sup>';
				}

				$pos = strpos( $data, self::TEXT_MARKER_PREFIX . $identifier['original_position'] );
				if ( false !== $pos ) {
					$data = substr_replace( $data, $id_replace, $pos, strlen( self::TEXT_MARKER_PREFIX . $identifier['original_position'] ) );
				}
			}

			// Is there a shortcode present in the content?
			if ( false !== \mb_strpos( $data, self::SHORT_CODE_POSITION_HOLDER ) ) {
				// Yes.
				$display           = false;
				$shortcode_replace = true;
			}

			if ( Settings::get_current_options()['no_display_post'] ) {
				$display = false;
			}

			// Display footnotes.
			if ( $display ) {

				$data .= self::get_footnotes_markup();
			} elseif ( $shortcode_replace ) {
				$data = \str_replace( self::SHORT_CODE_POSITION_HOLDER, self::get_footnotes_markup(), $data );
			}

			return $data;
		}

		/**
		 * Builds the footnotes array
		 *
		 * @param string  $data - The raw text.
		 * @param integer $post_id - The ID of the post.
		 *
		 * @return array
		 *
		 * @since 2.4.2
		 */
		private static function get_footnotes( string $data, int $post_id ): array {

			$footnotes = array();

			$identifiers = self::extract_current_notes( $data, $post_id );

			// Create 'em.
			$identifiers_count = count( $identifiers );
			for ( $i = 0; $i < $identifiers_count; $i++ ) {

				// Look for ref: and replace in identifiers array.
				if ( 'ref:' === substr( $identifiers[ $i ]['raw_text'], 0, 4 ) ) {
					$ref = (int) substr( $identifiers[ $i ]['raw_text'], 4 );

					if ( isset( $identifiers[ $ref - 1 ] ) && isset( $identifiers[ $ref - 1 ]['text'] ) ) {
						$identifiers[ $i ]['text'] = $identifiers[ $ref - 1 ]['text'];
					} else {
						// In that case referred is not yet populated, lets mark it and assign it later.
						$identifiers[ $i ]['refers_to'] = $ref - 1;
						$identifiers[ $i ]['text']      = '';
					}
				} else {
					$identifiers[ $i ]['text'] = $identifiers[ $i ]['raw_text'];
				}
			}

			// All footnotes are collected, lets fix missing references.
			foreach ( $identifiers as &$identifier ) {
				if ( isset( $identifier['refers_to'] ) ) {
					if ( isset( $identifiers[ $identifier['refers_to'] ] ) && isset( $identifiers[ $identifier['refers_to'] ]['text'] ) ) {
						$identifier['text'] = $identifiers[ $identifier['refers_to'] ]['text'];
						unset( $identifier['refers_to'] );
					}
				}
			}
			unset( $identifier );

			for ( $i = 0; $i < $identifiers_count; $i++ ) {
				// if we're combining identical notes check if we've already got one like this & record keys.

				if ( Settings::get_current_options()['combine_identical_notes'] ) {
					$footnotes_count = count( $footnotes );
					for ( $j = 0; $j < $footnotes_count; $j++ ) {
						if ( $footnotes[ $j ]['text'] === $identifiers[ $i ]['text'] ) {
							$identifiers[ $i ]['use_footnote'] = $j;
							$footnotes[ $j ]['identifiers'][]  = $i;
							break;
						}
					}
				}

				if ( ! isset( $identifiers[ $i ]['use_footnote'] ) ) {

					// Add footnote and record the key.

					$identifiers[ $i ]['use_footnote']                                = count( $footnotes );
					$footnotes[ $identifiers[ $i ]['use_footnote'] ]['text']          = $identifiers[ $i ]['text'];
					$footnotes[ $identifiers[ $i ]['use_footnote'] ]['symbol']        = isset( $identifiers[ $i ]['symbol'] ) ? $identifiers[ $i ]['symbol'] : '';
					$footnotes[ $identifiers[ $i ]['use_footnote'] ]['identifiers'][] = $i;
				}
			}

			return array(
				'footnotes'   => $footnotes,
				'identifiers' => $identifiers,
			);
		}

		/**
		 * Creates the entire HTML for the footnotes for the given post
		 *
		 * @param \WP_Post $post - The post object to extract the footnotes from / for. If empty the global will be used. If there is no global also - empty string will be returned.
		 *
		 * @return string
		 *
		 * @since 2.4.0
		 */
		private static function get_footnotes_markup( \WP_Post $post = null ): string {
			// check against post existing before processing.
			if ( ! $post ) {
				global $post;

				if ( ! $post ) {
					return '';
				}
			}

			$style = self::get_style( $post );

			$use_full_link = true;

			$data = \get_the_content( null, false, $post );

			$start_number = ( 1 === preg_match( '|<!\-\-startnum=(\d+)\-\->|', $data, $start_number_array ) ) ? $start_number_array[1] : 1;

			$footnotes = self::get_footnotes( $data, $post->ID )['footnotes'];

			$footnotes_markup = '';
			$start            = ( 1 !== $start_number ) ? 'start="' . $start_number . '" ' : '';

			if ( ! empty( $footnotes_header = Settings::get_current_options()['pre_footnotes'] ) ) { // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
				if ( ! Settings::get_current_options()['no_tags_header_footer'] ) {
					$footnotes_header =
					'<div class="footnotes-made-easy-header">' .
					$footnotes_header .
					'</div>';
				}
			}

			/**
			 * Gives the ability to change the footnotes header to something else
			 *
			 * @param string - The parsed footnotes header.
			 *
			 * @since 2.4.0
			 */
			$footnotes_markup .= \apply_filters( 'fme_footnotes_header', $footnotes_header );

			$footnotes_markup = $footnotes_markup . '<ol ' . $start . 'class="footnotes">';
			foreach ( $footnotes as $key => $value ) {
				$footnotes_markup = $footnotes_markup . '<li id="footnote_' . $key . '_' . $post->ID . '" class="footnote"';
				if ( Settings::get_current_options()['list_style_type'] !== $style ) {
					$footnotes_markup = $footnotes_markup . ' style="list-style-type:' . $style . ';"';
				}
				$footnotes_markup = $footnotes_markup . '>';
				if ( 'symbol' === $style ) {
					$footnotes_markup = $footnotes_markup . '<span class="symbol">' . self::convert_num( $key + $start_number, $style, count( $footnotes ) ) . '</span> ';
				}
				$footnotes_markup = $footnotes_markup . $value['text'];

				$back_link_title = Settings::get_current_options()['back_link_title'];

				if ( ! is_feed() ) {
					$footnotes_markup .= '<span class="footnote-back-link-wrapper">';
					foreach ( $value['identifiers'] as $identifier ) {

						$back_link_title_footnote = $back_link_title;

						if ( false !== \mb_strpos( $back_link_title, '###' ) ) {

							$text_pos = \strpos( $back_link_title, '###' );
							if ( false !== $text_pos ) {
								$back_link_title_footnote = \substr_replace( $back_link_title, (string) ( $identifier + $start_number ), $text_pos, \mb_strlen( '###' ) );
							}
						}

						$footnotes_markup = $footnotes_markup . '<span class="fme-pre-backlink">' . Settings::get_current_options()['pre_backlink'] . '</span><a href="' . ( ( $use_full_link ) ? get_permalink( $post->ID ) : '' ) . '#identifier_' . $identifier . '_' . $post->ID . '" class="footnote-link footnote-back-link" title="' . $back_link_title_footnote . '" aria-label="' . $back_link_title . '">' . Settings::get_current_options()['backlink'] . '</a><span class="fme-pre-backlink">' . Settings::get_current_options()['post_backlink'] . '</span>';
					}
					$footnotes_markup .= '</span>';
				}
				$footnotes_markup .= '</li>';
			}
			$footnotes_markup .= '</ol>';

			if ( ! empty( $footnotes_footer = Settings::get_current_options()['post_footnotes'] ) ) { // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure, Generic.CodeAnalysis.AssignmentInCondition.Found
				if ( ! Settings::get_current_options()['no_tags_header_footer'] ) {
					$footnotes_footer =
					'<div class="footnotes-made-easy-footer">' .
					$footnotes_footer .
					'</div>';
				}
			}

			/**
			 * Gives the ability to change the footnotes header to something else
			 *
			 * @param string - The parsed footnotes header.
			 *
			 * @since 2.4.0
			 */
			$footnotes_markup .= \apply_filters( 'fme_footnotes_footer', $footnotes_footer );

			return $footnotes_markup;
		}

		/**
		 * Extracts the currently used style for the footnotes markup.
		 *
		 * @param \WP_Post $post - The post to extract data from, if not specified, global option will be used.
		 *
		 * @return string
		 *
		 * @since 2.0.0
		 */
		public static function get_style( \WP_Post $post = null ) {
			// Check if this post is using a different list style to the settings.
			if ( get_post_meta( $post->ID, 'footnote_style', true ) && array_key_exists( get_post_meta( $post->ID, 'footnote_style', true ), self::$styles ) ) {
				$style = get_post_meta( $post->ID, 'footnote_style', true );
			} else {
				$style = Settings::get_current_options()['list_style_type'];
			}

			return $style;
		}

		/**
		 * Parses the text, extracts the footnotes, marks their positions and removes them from the content.
		 * Returns array with all the collected footnotes.
		 * Contains:
		 * - raw_text - the extracted text without the opening / closing chars
		 * - original_position - the position at which given footnote is collected
		 * - original_text - the text with the closing / opening chars
		 *
		 * @param string $data - The post text to be parsed.
		 * @param int    $post_id - The post to be processed.
		 *
		 * @return array
		 *
		 * @since 2.0.0
		 * @since 2.4.0 - added $post_id parameter
		 */
		private static function extract_current_notes( string &$data, int $post_id ): array {

			if ( isset( self::$identifiers[ $post_id ] ) ) {
				foreach ( self::$identifiers[ $post_id ] as $identifier ) {
					$data = self::replace_identifiers( $data, $identifier['original_text'], $identifier['original_position'] );
				}

				return self::$identifiers[ $post_id ];
			} else {
				self::$identifiers[ $post_id ] = array();
				$raw_notes                     = \explode( Settings::get_current_options()['footnotes_open'], $data );

				$notes = array();
				$pos   = 0;

				if ( ! empty( $raw_notes ) && \is_array( $raw_notes ) ) {
					foreach ( $raw_notes as $note ) {
						if ( $position = \mb_strpos( $note, Settings::get_current_options()['footnotes_close'] ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.Found, Squiz.PHP.DisallowMultipleAssignments.FoundInControlStructure
							$notes[ $pos ]['raw_text']          = \mb_substr( $note, 0, $position );
							$notes[ $pos ]['original_position'] = $pos;
							$notes[ $pos ]['original_text']     = Settings::get_current_options()['footnotes_open'] . $notes[ $pos ]['raw_text'] . Settings::get_current_options()['footnotes_close'];

							$data = self::replace_identifiers( $data, $notes[ $pos ]['original_text'], $pos );

							++$pos;
						}
					}
				}
				self::$identifiers[ $post_id ] = $notes;
			}

			return self::$identifiers[ $post_id ];
		}

		/**
		 * Replaces the identifiers in text
		 *
		 * @param string  $data - The raw text.
		 * @param string  $text - The text to search for and replacing it.
		 * @param integer $pos - The number of the position to put after the footnote mark.
		 *
		 * @return string
		 *
		 * @since 2.4.2
		 */
		private static function replace_identifiers( string &$data, string $text, int $pos ): string {

			$text_pos = \mb_strpos( $data, $text );
			if ( false !== $text_pos ) {
				$data = self::mb_substr_replace( $data, self::TEXT_MARKER_PREFIX . $pos, $text_pos, \mb_strlen( $text ) );
			}

			return $data;
		}

		/**
		 * Multibyte string replace function
		 *
		 * @param string $original — The input string.
		 * @param string $replacement — The replacement string.
		 * @param int    $position - The offset.
		 * @param int    $length - The length.
		 *
		 * @return string
		 *
		 * @since 2.3.0
		 */
		public static function mb_substr_replace( $original, $replacement, $position, $length ): string {
			$start_string = mb_substr( $original, 0, $position, 'UTF-8' );
			$end_string   = mb_substr( $original, $position + $length, mb_strlen( $original ), 'UTF-8' );

			$out = $start_string . $replacement . $end_string;

			return $out;
		}

		/**
		 * Convert number
		 *
		 * Convert number to a specific style
		 *
		 * @param string $num      The number to be converted.
		 * @param string $style    The style of output required.
		 * @param string $total    The total length.
		 *
		 * @return string  The converted number
		 *
		 * @since 2.0.0
		 */
		public static function convert_num( $num, $style, $total ) {

			switch ( $style ) {
				case 'decimal-leading-zero':
					$width = max( 2, strlen( $total ) );
					return sprintf( "%0{$width}d", $num );
				case 'lower-roman':
					return self::roman( $num, 'lower' );
				case 'upper-roman':
					return self::roman( $num );
				case 'lower-alpha':
					return self::alpha( $num, 'lower' );
				case 'upper-alpha':
					return self::alpha( $num );
				case 'symbol':
					$sym = '';
					for ( $i = 0; $i < $num; $i++ ) {
						$sym .= Settings::get_current_options()['list_style_symbol'];
					}
					return $sym;
				default:
					return $num;
			}
		}

		/**
		 * Convert to a roman numeral
		 *
		 * Convert a provided number into a roman numeral
		 *
		 * @param int    $num    The number to convert.
		 * @param string $letter_case   Upper or lower case.
		 *
		 * @return string          The roman numeral
		 *
		 * @since 2.0.0
		 */
		public static function roman( $num, $letter_case = 'upper' ) {

			$num        = (int) $num;
			$conversion = array(
				'M'  => 1000,
				'CM' => 900,
				'D'  => 500,
				'CD' => 400,
				'C'  => 100,
				'XC' => 90,
				'L'  => 50,
				'XL' => 40,
				'X'  => 10,
				'IX' => 9,
				'V'  => 5,
				'IV' => 4,
				'I'  => 1,
			);
			$roman      = '';

			foreach ( $conversion as $r => $d ) {
				$roman .= str_repeat( $r, (int) ( $num / $d ) );
				$num   %= $d;
			}

			return ( 'lower' === $letter_case ) ? strtolower( $roman ) : $roman;
		}

		/**
		 * Alpha numeric conversion
		 *
		 * @param integer $num - The number.
		 * @param string  $target_case - The case.
		 *
		 * @return string
		 *
		 * @since 2.0.0
		 */
		public static function alpha( $num, $target_case = 'upper' ) {
			$j = 1;
			for ( $i = 'A'; $i <= 'ZZ'; $i++ ) {
				if ( $j === $num ) {
					if ( 'lower' === $target_case ) {
						return strtolower( (string) $i );
					} else {
						return $i;
					}
				}
				++$j;
			}
		}

		/**
		 * Returns the implemented styles
		 *
		 * @return array
		 *
		 * @since 2.0.0
		 */
		public static function get_styles(): array {
			return self::$styles;
		}
	}
}
