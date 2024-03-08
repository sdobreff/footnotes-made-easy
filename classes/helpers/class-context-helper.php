<?php
/**
 * Class: Determine the context in which the plugin is executed.
 *
 * Helper class to determine the proper status of the request.
 *
 * @package footnotes-made-easy
 * @license Unauthorized copying of this file, via any medium is strictly prohibited Proprietary and confidential
 *
 * @since 2.0.0
 */

declare(strict_types=1);

namespace FME\Helpers;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\FME\Helpers\Context_Helper' ) ) {
	/**
	 * Responsible for proper context determination.
	 *
	 * @since 1.0.0
	 */
	class Context_Helper {

		public const AJAX        = 'ajax';
		public const BACKOFFICE  = 'backoffice';
		public const CLI         = 'wpcli';
		public const CORE        = 'core';
		public const CRON        = 'cron';
		public const FRONTOFFICE = 'frontoffice';
		public const INSTALLING  = 'installing';
		public const LOGIN       = 'login';
		public const REST        = 'rest';
		public const XML_RPC     = 'xml-rpc';
		public const WP_ACTIVATE = 'wp-activate';

		/**
		 * Holds all the information about the current statuses.
		 *
		 * @var array
		 *
		 * @since 2.0.0
		 */
		private static $all = array(
			self::AJAX        => null,
			self::BACKOFFICE  => null,
			self::CLI         => null,
			self::CORE        => null,
			self::CRON        => null,
			self::FRONTOFFICE => null,
			self::INSTALLING  => null,
			self::LOGIN       => null,
			self::REST        => null,
			self::XML_RPC     => null,
			self::WP_ACTIVATE => null,
		);

		/**
		 * Keeps the actions associated with this class.
		 *
		 * @var array<string, callable>
		 */
		private static $action_callbacks = array();

		/**
		 * Keeps the installing status of the current request.
		 *
		 * @var bool
		 *
		 * @since 1.0.0
		 */
		private static $not_installing = null;

		/**
		 * Keeps the value of undetermined status of the current request.
		 *
		 * @var bool
		 *
		 * @since 1.0.0
		 */
		private static $undetermined = null;

		/**
		 * Determines the current context
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		final public static function determine() {
			$is_installing     = defined( 'WP_INSTALLING' ) && WP_INSTALLING;
			$is_xml_rpc        = defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST;
			$is_core           = defined( 'ABSPATH' );
			$is_cli            = defined( 'WP_CLI' );
			$is_not_installing = $is_core && ! $is_installing;
			$is_ajax           = $is_not_installing && wp_doing_ajax();
			$is_admin          = $is_not_installing && is_admin() && ! $is_ajax;
			$is_cron           = $is_not_installing && wp_doing_cron();
			$is_wp_activate    = $is_installing && is_multisite() && self::is_wp_activate_request();

			$undetermined = $is_not_installing && ! $is_admin && ! $is_cron && ! $is_cli && ! $is_xml_rpc && ! $is_ajax;

			$is_rest  = $undetermined && self::is_rest_request();
			$is_login = $undetermined && ! $is_rest && self::is_login_request();

			// When nothing else matches, we assume it is a front-office request.
			$is_front = $undetermined && ! $is_rest && ! $is_login;

			/*
			 * Note that when core is installing **only** `INSTALLING` will be true, not even `CORE`.
			 * This is done to do as less as possible during installation, when most of WP does not act
			 * as expected.
			 */

			self::$all = array(
				self::AJAX        => $is_ajax,
				self::BACKOFFICE  => $is_admin,
				self::CLI         => $is_cli,
				self::CORE        => ( $is_core || $is_xml_rpc ) && ( ! $is_installing || $is_wp_activate ),
				self::CRON        => $is_cron,
				self::FRONTOFFICE => $is_front,
				self::INSTALLING  => $is_installing && ! $is_wp_activate,
				self::LOGIN       => $is_login,
				self::REST        => $is_rest,
				self::XML_RPC     => $is_xml_rpc && ! $is_installing,
				self::WP_ACTIVATE => $is_wp_activate,
			);

			self::add_action_hooks();
		}

		/**
		 * Is that a rest request
		 *
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		private static function is_rest_request(): bool {
			if (
			( defined( 'REST_REQUEST' ) && REST_REQUEST )
            || ! empty( $_GET['rest_route'] ) // phpcs:ignore
			) {
				return true;
			}

			if ( ! get_option( 'permalink_structure' ) ) {
				return false;
			}

			/*
			 * This is needed because, if called early, global $wp_rewrite is not defined but required
			 * by get_rest_url(). WP will reuse what we set here, or in worst case will replace, but no
			 * consequences for us in any case.
			 */
			if ( empty( $GLOBALS['wp_rewrite'] ) ) {
				$GLOBALS['wp_rewrite'] = new \WP_Rewrite(); // phpcs:ignore -- WordPress.WP.GlobalVariablesOverride.Prohibited
			}

			$current_path = trim( (string) parse_url( (string) add_query_arg( array() ), PHP_URL_PATH ), '/' ) . '/'; // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
			$rest_path    = trim( (string) parse_url( (string) get_rest_url(), PHP_URL_PATH ), '/' ) . '/'; // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url

			return strpos( $current_path, $rest_path ) === 0;
		}

		/**
		 * Is that a login request
		 *
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		private static function is_login_request(): bool {
			/**
			 * New core function with WordPress 6.1
			 *
			 * @link https://make.wordpress.org/core/2022/09/11/new-is_login-function-for-determining-if-a-page-is-the-login-screen/
			 */
			if ( function_exists( 'is_login' ) ) {
				return is_login() !== false;
			}

			if (!empty($_REQUEST['interim-login'])) { // phpcs:ignore
				return true;
			}

			/**
			 * Fallback and 1:1 copy from is_login() in case, the function is
			 * not available for WP < 6.1.
			 * phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			 * phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			 * phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			 */
			$script_name = $_SERVER['SCRIPT_NAME'] ?? '';

			return false !== stripos( wp_login_url(), $script_name );
		}

		/**
		 * Is that an activate request?
		 *
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		private static function is_wp_activate_request(): bool {
			return static::is_page_now( 'wp-activate.php', \network_site_url( 'wp-activate.php' ) );
		}

		/**
		 * Is that a request for specific page?
		 *
		 * @param string $page - The page to check for.
		 * @param string $url - The URL to check.
		 *
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		public static function is_page_now( string $page, string $url = '' ): bool {
			$page_now = (string) ( $GLOBALS['pagenow'] ?? '' );
			if ( $page_now && ( basename( $page_now ) === $page ) ) {
				return true;
			}

			if ( '' === $url ) {
				$url = \network_site_url( $page );
			}

			$current_path = (string) parse_url( add_query_arg( array() ), PHP_URL_PATH ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
			$target_path  = (string) parse_url( $url, PHP_URL_PATH ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url

			return trim( $current_path, '/' ) === trim( $target_path, '/' );
		}

		/**
		 * Force give context
		 *
		 * @param string $context - The context to be forced.
		 *
		 * @throws \LogicException - if that context is not valid.
		 *
		 * @since 1.0.0
		 */
		final public static function force( string $context ) {
			if ( ! \array_key_exists( $context, self::$all ) ) {
				throw new \LogicException( "'{$context}' is not a valid context." ); // phpcs:ignore -- WordPress.Security.EscapeOutput.ExceptionNotEscaped
			}

			self::remove_action_hooks();

			self::$all             = array_fill_keys( array_keys( self::$all ), null );
			self::$all[ $context ] = true;
			if ( ! in_array( $context, array( self::INSTALLING, self::CLI, self::CORE ), true ) ) {
				self::$all[ self::CORE ] = true;
			}
		}

		/**
		 * Sets the context wp-cli to true.
		 *
		 * @since 1.0.0
		 */
		final public static function with_cli() {
			self::$all[ self::CLI ] = true;
		}

		/**
		 * Checks given context status
		 *
		 * @param string $context - The name of the context to get status for.
		 *
		 * @return bool
		 *
		 * @throws \LogicException - if that context is not valid.
		 *
		 * @since 1.0.0
		 */
		final public static function is( string $context ): bool {
			if ( ! \array_key_exists( $context, self::$all ) ) {
				throw new \LogicException( "'{$context}' is not a valid context." ); // phpcs:ignore -- WordPress.Security.EscapeOutput.ExceptionNotEscaped
			}

			if ( is_null( self::$all[ $context ] ) ) {

				$proper_function_name = \str_replace( '-', '_', $context );

				if ( \method_exists( __CLASS__, 'is_' . $proper_function_name ) ) {
					call_user_func_array( array( __CLASS__, 'is_' . $proper_function_name ), array() );
				}
			}

			if ( ( self::$all[ $context ] ?? null ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Checks if that is a core request.
		 *
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		public static function is_core(): bool {
			if ( is_null( self::$all[ self::CORE ] ) ) {
				$is_core        = defined( 'ABSPATH' );
				$is_xml_rpc     = self::is_xml_rpc();
				$is_installing  = self::is_installing();
				$is_wp_activate = self::is_wp_activate();

				self::$all[ self::CORE ] = ( $is_core || $is_xml_rpc ) && ( ! $is_installing || $is_wp_activate );
			}
			return self::$all[ self::CORE ];
		}

		/**
		 * Is that a frontend request?
		 *
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		public static function is_frontoffice(): bool {
			if ( is_null( self::$all[ self::FRONTOFFICE ] ) ) {
				$undetermined = self::is_undetermined();

				self::$all[ self::FRONTOFFICE ] = $undetermined && ! self::is_rest() && ! self::is_login();
			}

			return self::$all[ self::FRONTOFFICE ];
		}

		/**
		 * Alias function for the is_frontoffice() function.
		 *
		 * @return boolean
		 *
		 * @since 1.0.0
		 */
		public static function is_front(): bool {
			return self::is_frontoffice();
		}

		/**
		 * Checks if that is admin / backoffice request.
		 *
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		public static function is_backoffice(): bool {
			if ( is_null( self::$all[ self::BACKOFFICE ] ) ) {
				$is_not_installing             = self::is_not_installing();
				self::$all[ self::BACKOFFICE ] = $is_not_installing && is_admin() && ! self::is_ajax();
			}

			return self::$all[ self::BACKOFFICE ];
		}

		/**
		 * Alias function for the is_backoffice
		 *
		 * @return boolean
		 *
		 * @since 1.0.0
		 */
		public static function is_admin(): bool {
			return self::is_backoffice();
		}

		/**
		 * Is that an AJAX request?
		 *
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		public static function is_ajax(): bool {
			if ( is_null( self::$all[ self::AJAX ] ) ) {
				$is_not_installing       = self::is_not_installing();
				self::$all[ self::AJAX ] = $is_not_installing && wp_doing_ajax();
			}

			return self::$all[ self::AJAX ];
		}

		/**
		 * Is that a login request?
		 *
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		public static function is_login(): bool {
			if ( is_null( self::$all[ self::LOGIN ] ) ) {
				$undetermined = self::is_undetermined();

				self::$all[ self::LOGIN ] = $undetermined && ! self::is_rest() && self::is_login_request();
			}

			return self::$all[ self::LOGIN ];
		}

		/**
		 * Is that a rest request?
		 *
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		public static function is_rest(): bool {
			if ( is_null( self::$all[ self::REST ] ) ) {
				$undetermined = self::is_undetermined();

				self::$all[ self::REST ] = $undetermined && self::is_rest_request();
			}

			return self::$all[ self::REST ];
		}

		/**
		 * Checks if that is a cron request.
		 *
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		public static function is_cron(): bool {
			if ( is_null( self::$all[ self::CRON ] ) ) {
				$is_not_installing       = self::is_not_installing();
				self::$all[ self::CRON ] = $is_not_installing && wp_doing_cron();
			}

			return self::$all[ self::CRON ];
		}

		/**
		 * Is that a WP-CLI request?
		 *
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		public static function is_wp_cli(): bool {
			if ( is_null( self::$all[ self::CLI ] ) ) {
				self::$all[ self::CLI ] = defined( 'WP_CLI' );
			}

			return self::$all[ self::CLI ];
		}

		/**
		 * Check if that is a xml_rpc request.
		 *
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		public static function is_xml_rpc(): bool {
			if ( is_null( self::$all[ self::XML_RPC ] ) ) {
				$is_xml_rpc    = defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST;
				$is_installing = self::is_installing();

				self::$all[ self::XML_RPC ] = $is_xml_rpc && ! $is_installing;
			}

			return self::$all[ self::XML_RPC ];
		}

		/**
		 * Checks is it is installing request
		 *
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		public static function is_installing(): bool {
			if ( is_null( self::$all[ self::INSTALLING ] ) ) {
				$is_installing                 = defined( 'WP_INSTALLING' ) && WP_INSTALLING;
				$is_wp_activate                = self::is_wp_activate();
				self::$all[ self::INSTALLING ] = $is_installing && ! $is_wp_activate;
			}

			return self::$all[ self::INSTALLING ];
		}

		/**
		 * Checks if it is an activating request.
		 *
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		public static function is_wp_activate(): bool {
			if ( is_null( self::$all[ self::WP_ACTIVATE ] ) ) {
				$is_installing = defined( 'WP_INSTALLING' ) && WP_INSTALLING;

				self::$all[ self::WP_ACTIVATE ] = $is_installing && is_multisite() && self::is_wp_activate_request();
			}

			return self::$all[ self::WP_ACTIVATE ];
		}

		/**
		 * When context is determined very early we do our best to understand some context like
		 * login, rest and front-office even if WordPress normally would require a later hook.
		 * When that later hook happen, we change what we have determined, leveraging the more
		 * "core-compliant" approach.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		private static function add_action_hooks(): void {
			if ( empty( self::$action_callbacks ) ) {
				self::$action_callbacks = array(
					'login_init'        => function (): void {
						$this->reset_and_force( self::LOGIN );
					},
					'rest_api_init'     => function (): void {
						$this->reset_and_force( self::REST );
					},
					'activate_header'   => function (): void {
						$this->reset_and_force( self::WP_ACTIVATE );
					},
					'template_redirect' => function (): void {
						$this->reset_and_force( self::FRONTOFFICE );
					},
					'current_screen'    => function ( \WP_Screen $screen ): void {
						$screen->in_admin() && $this->reset_and_force( self::BACKOFFICE );
					},
				);
			}
			foreach ( self::$action_callbacks as $action => $callback ) {
				add_action( $action, $callback, PHP_INT_MIN );
			}
		}

		/**
		 * When "force" is called on an instance created via `determine()` we need to remove added hooks
		 * or what we are forcing might be overridden.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		private static function remove_action_hooks(): void {
			foreach ( self::$action_callbacks as $action => $callback ) {
				remove_action( $action, $callback, PHP_INT_MIN );
			}
			self::$action_callbacks = array();
		}

		/**
		 * Makes sure that this is not installing request.
		 *
		 * @return boolean
		 *
		 * @since 1.0.0
		 */
		private static function is_not_installing(): bool {
			if ( is_null( self::$not_installing ) ) {
				self::$not_installing = self::is_core() && ! self::is_installing();
			}
			return self::$not_installing;
		}

		/**
		 * Checks if that request is undetermined.
		 *
		 * @return boolean
		 *
		 * @since 1.0.0
		 */
		private static function is_undetermined(): bool {
			if ( is_null( self::$undetermined ) ) {
				$is_not_installing = self::is_not_installing();
				$is_ajax           = self::is_ajax();
				$is_admin          = $is_not_installing && is_admin() && ! $is_ajax;
				$is_cron           = self::is_cron();
				$is_cli            = self::is_wp_cli();
				$is_xml_rpc        = self::is_xml_rpc();

				self::$undetermined = $is_not_installing && ! $is_admin && ! $is_cron && ! $is_cli && ! $is_xml_rpc && ! $is_ajax;
			}

			return self::$undetermined;
		}

		/**
		 * Resets all the context gathered information and sets the given context as true.
		 *
		 * @param string $context - The name of the context to be forced as true.
		 *
		 * @return void
		 *
		 * @since 1.0.0
		 */
		private static function reset_and_force( string $context ): void {
			self::force( $context );
		}
	}
}
