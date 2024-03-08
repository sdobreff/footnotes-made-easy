<?php
/**
 * Formatting settings of the plugin
 *
 * @package fme
 * @license Unauthorized copying of this file, via any medium is strictly prohibited Proprietary and confidential
 *
 * @since 2.0.0
 */

use FME\Helpers\Settings;

	Settings::build_option(
		array(
			'title' => esc_html__( 'Formatting Settings', 'footnotes-made-easy' ),
			'id'    => 'formatting-settings-tab',
			'type'  => 'tab-title',
		)
	);

	if ( class_exists( 'ACF' ) ) {

		// ACF support.
		Settings::build_option(
			array(
				'title' => esc_html__( 'Advanced Custom Fields support', 'footnotes-made-easy' ),
				'id'    => 'acf-format-settings',
				'type'  => 'header',
			)
		);

		Settings::build_option(
			array(
				'name'    => esc_html__( 'Show footnotes in ACF', 'footnotes-made-easy' ),
				'id'      => 'acf_show_footnotes',
				'type'    => 'checkbox',
				'default' => Settings::get_current_options()['acf_show_footnotes'],
			)
		);
	}

	// Pretty tooltips formatting.
	Settings::build_option(
		array(
			'title' => esc_html__( 'jQuery pretty tooltip', 'footnotes-made-easy' ),
			'id'    => 'jquery-pretty-tooltips-format-settings',
			'type'  => 'header',
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Use jQuery pretty tooltips for showing the footnotes', 'footnotes-made-easy' ),
			'id'      => 'pretty_tooltips',
			'type'    => 'checkbox',
			'default' => Settings::get_current_options()['pretty_tooltips'],
		)
	);

	// Global header and footer settings.
	Settings::build_option(
		array(
			'title' => esc_html__( ' Global header and footer settings', 'footnotes-made-easy' ),
			'id'    => 'global-header-footer-format-settings',
			'type'  => 'header',
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Do not use editor for footer and header', 'footnotes-made-easy' ),
			'id'      => 'no_editor_header_footer',
			'type'    => 'checkbox',
			'default' => Settings::get_current_options()['no_editor_header_footer'],
			'hint'    => esc_html__( 'Enable this if you don\'t want to use editors for header and footer.', 'footnotes-made-easy' ),
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Do not use tags for footer and header', 'footnotes-made-easy' ),
			'id'      => 'no_tags_header_footer',
			'type'    => 'checkbox',
			'default' => Settings::get_current_options()['no_tags_header_footer'],
			'hint'    => esc_html__( 'Enable this if you don\'t want header and footer to be in separate tags and auto close tags, as well as replacing tags like new lines. That could be potential security issue! Using this option is highly discouraged.', 'footnotes-made-easy' ),
		)
	);

	// Header section used.
	Settings::build_option(
		array(
			'title' => esc_html__( 'Footnote header', 'footnotes-made-easy' ),
			'id'    => 'markup-format-settings',
			'type'  => 'header',
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Before footnotes', 'footnotes-made-easy' ),
			'id'      => 'pre_footnotes',
			'type'    => Settings::get_current_options()['no_editor_header_footer'] ? 'textarea' : 'editor',
			'hint'    => esc_html__( 'Anything to be displayed before the footnotes at the bottom of the post can go here.', 'footnotes-made-easy' ),
			'default' => Settings::get_current_options()['pre_footnotes'],
		)
	);

	Settings::build_option(
		array(
			'type' => 'hint',
			'hint' => '<b><i>' . esc_html__( 'Example:', 'footnotes-made-easy' ) . '</i></b><div class="foot-header-example">' .
			'<span class="pre-foot-example">' . Settings::get_current_options()['pre_footnotes'] . '</span>' .
			$footnote_example
			. Settings::get_current_options()['post_footnotes']
			. '</div>',
		)
	);

	// Header section used.
	Settings::build_option(
		array(
			'title' => esc_html__( 'Footnote footer', 'footnotes-made-easy' ),
			'id'    => 'markup-format-settings',
			'type'  => 'header',
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'After footnotes', 'footnotes-made-easy' ),
			'id'      => 'post_footnotes',
			'type'    => Settings::get_current_options()['no_editor_header_footer'] ? 'textarea' : 'editor',
			'hint'    => esc_html__( 'Anything to be displayed after the footnotes at the bottom of the post can go here.', 'footnotes-made-easy' ),
			'default' => Settings::get_current_options()['post_footnotes'],
		)
	);

	Settings::build_option(
		array(
			'type' => 'hint',
			'hint' => '<b><i>' . esc_html__( 'Example:', 'footnotes-made-easy' ) . '</i></b><div class="foot-footer-example">' .
			Settings::get_current_options()['pre_footnotes'] .
			$footnote_example
			. '<span class="post-foot-example">' . Settings::get_current_options()['post_footnotes'] . '</span>'
			. '</div>',
		)
	);
