<?php
/**
 * Advanced settings of the plugin
 *
 * @package fme
 * @license Unauthorized copying of this file, via any medium is strictly prohibited Proprietary and confidential
 *
 * @since 2.0.0
 */

use FME\Helpers\Settings;

Settings::build_option(
	array(
		'title' => esc_html__( 'Advanced Settings', 'footnotes-made-easy' ),
		'id'    => 'advanced-settings-tab',
		'type'  => 'tab-title',
	)
);

Settings::build_option(
	array(
		'type'  => 'header',
		'id'    => 'advanced-settings',
		'title' => esc_html__( 'Advanced Settings', 'footnotes-made-easy' ),
	)
);

	// Pretty tooltips formatting.
	Settings::build_option(
		array(
			'title' => esc_html__( 'Where to put this settings page?', 'footnotes-made-easy' ),
			'id'    => 'jquery-pretty-tooltips-format-settings',
			'type'  => 'header',
		)
	);

	Settings::build_option(
		array(
			'name'    => esc_html__( 'Stand alone WP menu on the left', 'footnotes-made-easy' ),
			'id'      => 'stand_alone_menu',
			'type'    => 'checkbox',
			'default' => Settings::get_current_options()['stand_alone_menu'],
			'hint'    => esc_html__( 'Set plugin as a stand alone menu (checked), or in the WP->Settings menu (unchecked)', 'footnotes-made-easy' ) . '<br><i>' . esc_html__( 'Note: ', 'footnotes-made-easy' ) . '</i>' . esc_html__( 'After changing this and saving the plugin settings you will end up on "Sorry, you are not allowed to access this page." page. Just navigate back to the admin section (your changes will are saved successfully).', 'footnotes-made-easy' ),
		)
	);

	Settings::build_option(
		array(
			'type'  => 'header',
			'id'    => 'reset-all-settings',
			'title' => esc_html__( 'Reset All Settings', 'footnotes-made-easy' ),
		)
	);

	Settings::build_option(
		array(
			'title' => esc_html__( 'Markup', 'footnotes-made-easy' ),
			'id'    => 'reset-settings-hint',
			'type'  => 'hint',
			'hint'  => esc_html__( 'This is destructive operation, which can not be undone! You may want to export your current settings first.', 'footnotes-made-easy' ),
		)
	);

	?>

	<div class="option-item">
		<a id="fme-reset-settings" class="fme-primary-button button button-primary button-hero fme-button-red" href="<?php print \esc_url( \wp_nonce_url( \admin_url( 'admin.php?page=' . self::MENU_SLUG . '&reset-settings' ), 'reset-plugin-settings', 'reset_nonce' ) ); ?>" data-message="<?php esc_html_e( 'This action can not be undone. Clicking "OK" will reset your plugin options to the default installation. Click "Cancel" to stop this operation.', 'footnotes-made-easy' ); ?>"><?php esc_html_e( 'Reset All Settings', 'footnotes-made-easy' ); ?></a>
	</div>

