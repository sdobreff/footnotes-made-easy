<?php
/**
 * Responsible for plugin updates.
 *
 * @package    fme
 * @subpackage migration
 * @copyright  %%YEAR%%
 * @license Unauthorized copying of this file, via any medium is strictly prohibited Proprietary and confidential
 */

declare(strict_types=1);

namespace FME\Migration;

use FME\Migration\Abstract_Migration;


defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Migration class
 */
if ( ! class_exists( '\FME\Migration\Migration' ) ) {

	/**
	 * Put all you migration methods here
	 *
	 * @since 2.0.0
	 */
	class Migration extends Abstract_Migration {

		/**
		 * Migrates the plugin up-to version 2.0.0
		 *
		 * @return void
		 *
		 * @since 2.0.0
		 */
		public static function migrate_up_to_200() {

			// Check if there is previous version installed.
			if ( \get_option( 'swas_footnote_options', false ) ) {
				\update_option( FME_SETTINGS_NAME, \get_option( 'swas_footnote_options', array() ), false );
				\delete_option( 'swas_footnote_options' );
			}
		}
	}
}
