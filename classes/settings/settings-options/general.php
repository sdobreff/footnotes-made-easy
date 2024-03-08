<?php
/**
 * General settings of the plugin
 *
 * @package fme
 * @license Unauthorized copying of this file, via any medium is strictly prohibited Proprietary and confidential
 *
 * @since 2.0.0
 */

use FME\Helpers\Settings;
use FME\Controllers\Footnotes_Formatter;

	Settings::build_option(
		array(
			'title' => esc_html__( 'General Settings', 'footnotes-made-easy' ),
			'id'    => 'general-settings-tab',
			'type'  => 'tab-title',
		)
	);

	// Markup used.
	Settings::build_option(
		array(
			'title' => esc_html__( 'Markup', 'footnotes-made-easy' ),
			'id'    => 'markup-format-settings',
			'type'  => 'header',
		)
	);

	Settings::build_option(
		array(
			'title' => esc_html__( 'Markup', 'footnotes-made-easy' ),
			'id'    => 'markup-format-settings',
			'type'  => 'hint',
			'hint'  => esc_html__( 'How the markup should be represented in the documents', 'footnotes-made-easy' ) . '<div>' . esc_html__( 'Changing the following settings will change functionality in a way which may stop footnotes from displaying correctly. For footnotes to work as expected after updating these settings, you will need to manually update all existing posts with footnotes.', 'footnotes-made-easy' ) . '</div>',
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Open footnote tag', 'footnotes-made-easy' ),
			'id'      => 'footnotes_open',
			'type'    => 'text',
			'default' => Settings::get_current_options()['footnotes_open'],
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Close footnote tag', 'footnotes-made-easy' ),
			'id'      => 'footnotes_close',
			'type'    => 'text',
			'default' => Settings::get_current_options()['footnotes_close'],
		)
	);

	Settings::build_option(
		array(
			'type' => 'hint',
			'hint' => '<b><i>' . esc_html__( 'Example:', 'footnotes-made-easy' ) . '</i></b><div>' . esc_html__( '"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 'footnotes-made-easy' ) . Settings::get_current_options()['footnotes_open'] . '<b>' . esc_html__( 'Text of your footnote goes between these tags.', 'footnotes-made-easy' ) . Settings::get_current_options()['footnotes_close']
			. '</b>"</div>',
		)
	);

	// Identifier settings begin.
	Settings::build_option(
		array(
			'title' => esc_html__( 'Identifier', 'footnotes-made-easy' ),
			'id'    => 'identifier-format-settings',
			'type'  => 'header',
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Type', 'footnotes-made-easy' ),
			'id'      => 'list_style_type',
			'type'    => 'radio',
			'hint'    => esc_html__( 'How the footnotes will be represented', 'footnotes-made-easy' ),
			'toggle'  => array(
				''       => '',
				'symbol' => '#list_style_symbol-item',
			),
			'options' => Footnotes_Formatter::get_styles(),
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Symbol', 'footnotes-made-easy' ),
			'id'      => 'list_style_symbol',
			'class'   => 'list_style_type',
			'type'    => 'text',
			'default' => Settings::get_default_options()['list_style_symbol'],
			'hint'    => esc_html__( 'Preview: ', 'footnotes-made-easy' ) .
			'<b>' . html_entity_decode( Settings::get_current_options()['list_style_symbol'] ) . '</b>',
		)
	);

	Footnotes_Formatter::insert_styles();

	?>
	<style>
		.symbol-example ol.footnotes > li::marker {
			font-weight: bold;
		}
		.backlink-example ol.footnotes > li > span.footnote-back-link-wrapper {
			font-weight: bold;
		}
		.pre-show .pre-demo {
			font-weight: bold;
		}
		.post-show .post-demo {
			font-weight: bold;
		}
		.foot-header-example .pre-foot-example {
			font-weight: bold;;
		}
		.foot-footer-example .post-foot-example {
			font-weight: bold;;
		}
	</style>
	<?php

	ob_start();
	?>
	<ol class="footnotes">
		<li id="footnote_0_1" class="footnote">
			<span class="symbol"><?php echo html_entity_decode( Settings::get_current_options()['list_style_symbol'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>

			<?php
			$back_link_title = Settings::get_current_options()['back_link_title'];

			if ( false !== \mb_strpos( $back_link_title, '###' ) ) {

				$text_pos = \strpos( $back_link_title, '###' );
				if ( false !== $text_pos ) {
					$back_link_title = \substr_replace( $back_link_title, (string) 1, $text_pos, \mb_strlen( '###' ) );
				}
			}
			?>

			<?php esc_html_e( 'First footnote', 'footnotes-made-easy' ); ?> <span class="footnote-back-link-wrapper"><?php echo Settings::get_current_options()['pre_backlink']; ?><a href="#" class="footnote-link footnote-back-link" title="<?php echo $back_link_title; ?>" aria-label="<?php echo $back_link_title; ?>" onclick="return false"><?php echo Settings::get_current_options()['backlink']; ?></a><?php echo Settings::get_current_options()['post_backlink']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</li>
		<li id="footnote_1_1" class="footnote">
			<span class="symbol"><?php echo str_repeat( html_entity_decode( Settings::get_current_options()['list_style_symbol'] ), 2 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>


			<?php
			$back_link_title = Settings::get_current_options()['back_link_title'];

			if ( false !== \mb_strpos( $back_link_title, '###' ) ) {

				$text_pos = \strpos( $back_link_title, '###' );
				if ( false !== $text_pos ) {
					$back_link_title = \substr_replace( $back_link_title, (string) 2, $text_pos, \mb_strlen( '###' ) );
				}
			}
			?>

			<?php esc_html_e( 'Second footnote', 'footnotes-made-easy' ); ?> <span class="footnote-back-link-wrapper"><?php echo Settings::get_current_options()['pre_backlink']; ?><a href="#" class="footnote-link footnote-back-link" title="<?php echo $back_link_title; ?>" aria-label="<?php echo $back_link_title; ?>" onclick="return false"><?php echo Settings::get_current_options()['backlink']; ?></a><?php echo Settings::get_current_options()['post_backlink']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</li>
		<li id="footnote_2_1" class="footnote">
			<span class="symbol"><?php echo str_repeat( html_entity_decode( Settings::get_current_options()['list_style_symbol'] ), 3 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>


			<?php
			$back_link_title = Settings::get_current_options()['back_link_title'];

			if ( false !== \mb_strpos( $back_link_title, '###' ) ) {

				$text_pos = \strpos( $back_link_title, '###' );
				if ( false !== $text_pos ) {
					$back_link_title = \substr_replace( $back_link_title, (string) 3, $text_pos, \mb_strlen( '###' ) );
				}
			}
			?>

			<?php esc_html_e( 'Third footnote', 'footnotes-made-easy' ); ?> <span class="footnote-back-link-wrapper"><?php echo Settings::get_current_options()['pre_backlink']; ?><a href="#" class="footnote-link footnote-back-link" title="<?php echo $back_link_title; ?>" aria-label="<?php echo $back_link_title; ?>" onclick="return false"><?php echo Settings::get_current_options()['backlink']; ?></a><?php echo Settings::get_current_options()['post_backlink']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</li>
	</ol>
<?php
	$footnote_example = ob_get_clean();
	Settings::build_option(
		array(
			'type' => 'hint',
			'hint' => '<b><i>' . esc_html__( 'Example:', 'footnotes-made-easy' ) . '</i></b><div class="symbol-example">' .
			$footnote_example
			. '</div>',
		)
	);

	Settings::build_option(
		array(
			'name' => esc_html__( 'Show identifier as superscript', 'footnotes-made-easy' ),
			'id'   => 'superscript',
			'type' => 'checkbox',
		)
	);

	$id_replace = '<a href="#" class="footnote-link footnote-identifier-link" title="Lorem ipsum dolor sit" onclick="return false">1</a>';
	if ( Settings::get_current_options()['superscript'] ) {
		$id_replace = '<sup>' . $id_replace . '</sup>';
	}

	Settings::build_option(
		array(
			'type' => 'hint',
			'hint' => '<b><i>' . esc_html__( 'Example:', 'footnotes-made-easy' ) . '</i></b><div>' . esc_html__( '"Lorem ipsum dolor sit amet', 'footnotes-made-easy' ) . '<b>' . $id_replace . '</b>' . esc_html__( ', consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'footnotes-made-easy' ) . '"</div>',
		)
	);

	// Before identifiers.

	$id_replace = '<span class="pre-demo">' . Settings::get_current_options()['pre_identifier'] . '</span><a href="#" class="footnote-link footnote-identifier-link" title="Lorem ipsum dolor sit" onclick="return false"><span class="pre-demo">' . Settings::get_current_options()['inner_pre_identifier'] . '</span>1<span class="post-demo">' . Settings::get_current_options()['inner_post_identifier'] . '</span></a><span class="post-demo">' . Settings::get_current_options()['post_identifier'] . '</span>';
	if ( Settings::get_current_options()['superscript'] ) {
		$id_replace = '<sup>' . $id_replace . '</sup>';
	}

	Settings::build_option(
		array(
			'title' => esc_html__( 'Before identifiers', 'footnotes-made-easy' ),
			'id'    => 'before-identifier-format-settings',
			'type'  => 'header',
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Pre identifier', 'footnotes-made-easy' ),
			'id'      => 'pre_identifier',
			'type'    => 'text',
			'default' => Settings::get_current_options()['pre_identifier'],
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Inner pre identifier', 'footnotes-made-easy' ),
			'id'      => 'inner_pre_identifier',
			'type'    => 'text',
			'default' => Settings::get_current_options()['inner_pre_identifier'],
		)
	);

	Settings::build_option(
		array(
			'type' => 'hint',
			'hint' => '<b><i>' . esc_html__( 'Example:', 'footnotes-made-easy' ) . '</i></b><div class="pre-show">"' . esc_html__( 'Lorem ipsum dolor sit amet', 'footnotes-made-easy' ) . '<b>' . $id_replace . '</b>' . esc_html__( ', consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'footnotes-made-easy' ) . '"</div>',
		)
	);

	// After identifiers.
	Settings::build_option(
		array(
			'title' => esc_html__( 'Post identifiers', 'footnotes-made-easy' ),
			'id'    => 'post-identifier-format-settings',
			'type'  => 'header',
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Post identifier', 'footnotes-made-easy' ),
			'id'      => 'post_identifier',
			'type'    => 'text',
			'default' => Settings::get_current_options()['post_identifier'],
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Inner post identifier', 'footnotes-made-easy' ),
			'id'      => 'inner_post_identifier',
			'type'    => 'text',
			'default' => Settings::get_current_options()['inner_post_identifier'],
		)
	);

	Settings::build_option(
		array(
			'type' => 'hint',
			'hint' => '<b><i>' . esc_html__( 'Example:', 'footnotes-made-easy' ) . '</i></b><div class="post-show">"' . esc_html__( 'Lorem ipsum dolor sit amet', 'footnotes-made-easy' ) . '<b>' . $id_replace . '</b>' . esc_html__( ', consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'footnotes-made-easy' ) . '"</div>',
		)
	);

	// Back link.
	Settings::build_option(
		array(
			'title' => esc_html__( 'Back link', 'footnotes-made-easy' ),
			'id'    => 'before-identifier-format-settings',
			'type'  => 'header',
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Open back link tag', 'footnotes-made-easy' ),
			'id'      => 'pre_backlink',
			'type'    => 'text',
			'default' => Settings::get_current_options()['pre_backlink'],
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Close back link tag', 'footnotes-made-easy' ),
			'id'      => 'post_backlink',
			'type'    => 'text',
			'default' => Settings::get_current_options()['post_backlink'],
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Back link symbol', 'footnotes-made-easy' ),
			'id'      => 'backlink',
			'type'    => 'text',
			'default' => Settings::get_current_options()['backlink'],
		)
	);

	Settings::build_option(
		array(
			'type' => 'hint',
			'hint' => '<b><i>' . esc_html__( 'Example:', 'footnotes-made-easy' ) . '</i></b><div class="backlink-example">' .
			$footnote_example
			. '</div>',
		)
	);

	// Back link title.
	Settings::build_option(
		array(
			'title' => esc_html__( 'Back link title', 'footnotes-made-easy' ),
			'id'    => 'backlink-general-settings',
			'type'  => 'header',
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Title to show on the backlinks', 'footnotes-made-easy' ),
			'id'      => 'back_link_title',
			'type'    => 'text',
			'default' => Settings::get_current_options()['back_link_title'],
			'hint'    => '<b><i>' . esc_html__( 'Options:', 'footnotes-made-easy' ) . '</i></b><div class="post-show">' . esc_html__( 'Add "###" (without the quotes) to include the footnote number.', 'footnotes-made-easy' ) . '</div>',
		)
	);
