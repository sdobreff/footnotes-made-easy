<?php
/**
 * Option settings of the plugin
 *
 * @package fme
 * @license Unauthorized copying of this file, via any medium is strictly prohibited Proprietary and confidential
 *
 * @since 2.0.0
 */

use FME\Helpers\Settings;

	Settings::build_option(
		array(
			'title' => esc_html__( 'Options', 'footnotes-made-easy' ),
			'id'    => 'options-settings-tab',
			'type'  => 'tab-title',
		)
	);

	// Markup used.
	Settings::build_option(
		array(
			'title' => esc_html__( 'Suppress footnotes', 'footnotes-made-easy' ),
			'id'    => 'markup-format-settings',
			'type'  => 'header',
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Do not autodisplay in posts', 'footnotes-made-easy' ),
			'id'      => 'no_display_post',
			'type'    => 'checkbox',
			'default' => Settings::get_current_options()['no_display_post'],
			'hint' => esc_html__( 'Use this option if you want to display footnotes on separate place other than below the post (default). To achieve that you have to either use a shortcode ([fme_show_footnotes]), or direct PHP call (Footnotes_Formatter::show_footnotes();).', 'footnotes-made-easy' ),
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'On the home page', 'footnotes-made-easy' ),
			'id'      => 'no_display_home',
			'type'    => 'checkbox',
			'default' => Settings::get_current_options()['no_display_home'],
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'When displaying a preview', 'footnotes-made-easy' ),
			'id'      => 'no_display_preview',
			'type'    => 'checkbox',
			'default' => Settings::get_current_options()['no_display_preview'],
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'In search results', 'footnotes-made-easy' ),
			'id'      => 'no_display_search',
			'type'    => 'checkbox',
			'default' => Settings::get_current_options()['no_display_search'],
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'In the feed (RSS, Atom, etc.)', 'footnotes-made-easy' ),
			'id'      => 'no_display_feed',
			'type'    => 'checkbox',
			'default' => Settings::get_current_options()['no_display_feed'],
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'In any kind of archive', 'footnotes-made-easy' ),
			'id'      => 'no_display_archive',
			'type'    => 'checkbox',
			'default' => Settings::get_current_options()['no_display_archive'],
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'In category archives', 'footnotes-made-easy' ),
			'id'      => 'no_display_category',
			'type'    => 'checkbox',
			'default' => Settings::get_current_options()['no_display_category'],
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'in date-based archives', 'footnotes-made-easy' ),
			'id'      => 'no_display_date',
			'type'    => 'checkbox',
			'default' => Settings::get_current_options()['no_display_date'],
		)
	);

	// Priority.
	Settings::build_option(
		array(
			'title' => esc_html__( 'Priority', 'footnotes-made-easy' ),
			'id'    => 'priority-format-settings',
			'type'  => 'header',
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Plugin priority', 'footnotes-made-easy' ),
			'id'      => 'priority',
			'type'    => 'number',
			'default' => Settings::get_current_options()['priority'],
		)
	);

	// Combine footnotes.
	Settings::build_option(
		array(
			'title' => esc_html__( 'Combine footnotes', 'footnotes-made-easy' ),
			'id'    => 'priority-format-settings',
			'type'  => 'header',
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Combine identical footnotes', 'footnotes-made-easy' ),
			'id'      => 'combine_identical_notes',
			'type'    => 'checkbox',
			'default' => Settings::get_current_options()['combine_identical_notes'],
		)
	);

	// Custom CSS.
	Settings::build_option(
		array(
			'title' => esc_html__( 'Styling (CSS)', 'footnotes-made-easy' ),
			'id'    => 'markup-format-settings',
			'type'  => 'header',
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'CSS footnotes', 'footnotes-made-easy' ),
			'id'      => 'css_footnotes',
			'type'    => 'textarea',
			'hint'    => esc_html__( 'You can change the footnotes styling from here or leave it empty if you are using your own.', 'footnotes-made-easy' ),
			'default' => Settings::get_current_options()['css_footnotes'],
		)
	);
