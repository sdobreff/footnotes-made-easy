<?php
/**
 * Import/Export settings of the plugin
 *
 * @package fme
 * @license Unauthorized copying of this file, via any medium is strictly prohibited Proprietary and confidential
 *
 * @since 2.0.0
 */

use FME\Helpers\Settings;

	Settings::build_option(
		array(
			'title' => esc_html__( 'Export/Import Plugin Options', 'footnotes-made-easy' ),
			'id'    => 'export-settings-tab',
			'type'  => 'tab-title',
		)
	);

	if ( isset( $_REQUEST['import'] ) ) {

		Settings::build_option(
			array(
				'text' => esc_html__( 'The plugin options have been imported successfully.', 'footnotes-made-easy' ),
				'type' => 'message',
			)
		);
	}

	Settings::build_option(
		array(
			'title' => esc_html__( 'Export', 'footnotes-made-easy' ),
			'id'    => 'export-settings',
			'type'  => 'header',
		)
	);

	?>

<div class="option-item">

	<p><?php esc_html_e( 'When you click the button below the plugin will create a .dat file for you to save to your computer.', 'footnotes-made-easy' ); ?>
	</p>
	<p><?php esc_html_e( 'Once you’ve saved the download file, you can use the Import function in another WordPress installation to import the plugin options from this site.', 'footnotes-made-easy' ); ?>
	</p>

	<p><a class="fme-primary-button button button-primary button-hero"
			href="
			<?php
			print \esc_url(
				\wp_nonce_url(
					admin_url( 'admin.php?page=' . Settings::MENU_SLUG . '&export-settings' ),
					'export-plugin-settings',
					'export_nonce'
				)
			);
			?>
				"><?php esc_html_e( 'Download Export File', 'footnotes-made-easy' ); ?></a>
	</p>
</div>

<?php

	Settings::build_option(
		array(
			'title' => esc_html__( 'Import', 'footnotes-made-easy' ),
			'id'    => 'import-settings',
			'type'  => 'header',
		)
	);

	?>

<div class="option-item">

	<p><?php esc_html_e( 'Upload your .dat plugin options file and we will import the options into this site.', 'footnotes-made-easy' ); ?>
	</p>
	<p><?php esc_html_e( 'Choose a (.dat) file to upload, then click Upload file and import.', 'footnotes-made-easy' ); ?></p>

	<p>
		<label for="upload"><?php esc_html_e( 'Choose a file from your computer:', 'footnotes-made-easy' ); ?></label>
		<input type="file" name="<?php echo \esc_attr( Settings::SETTINGS_FILE_FIELD ); ?>" id="fme-import-file" />
	</p>

	<p>
		<input type="submit" name="<?php echo \esc_attr( Settings::SETTINGS_FILE_UPLOAD_FIELD ); ?>" id="fme-import-upload" class="button-primary"
			value="<?php esc_html_e( 'Upload file and import', 'footnotes-made-easy' ); ?>" />
	</p>
</div>
