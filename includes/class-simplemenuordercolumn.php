<?php
/**
 * Simple Menu Order Column
 *
 * @package SimpleMenuOrderColumn
 *
 * Copyright: (c) 2003 Chillcode
 */

namespace SMOC;

use WP_Error;

/**
 * SMOCWC class.
 */
final class SimpleMenuOrderColumn {


	/**
	 * The single instance of the class.
	 *
	 * @var SimpleMenuOrderColumn|null
	 */
	private static $smoc_instace;

	/**
	 * Allowed types.
	 *
	 * We allow all WP_Post since has menu_order column and are sortable.
	 *
	 * @var array{0: 'post', 1: 'page', 2: 'product', 3: 'attachment'}
	 */
	private static $smoc_allowed_types = array( 'post', 'page', 'product', 'attachment' );

	public const SMOC_OPTION_ALLOWED_TYPES  = 'smoc_ui_allowed_types';
	public const SMOC_OPTION_UI_CONFIRM     = 'smoc_ui_confirmation';
	public const SMOC_OPTION_UI_TAB_TO_NEXT = 'smoc_ui_tab_to_next';

	/**
	 * Construtor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
	}

	/**
	 * After plugins loaded.
	 *
	 * @return void
	 */
	public function plugins_loaded() {
		/** If we are not in admin pages nothing to do. */
		if ( ! is_admin() ) {
			return;
		}

		/**
		 *  If it's an ajax call add the reorder action and ignore the rest.
		 *
		 *  Same as usig $GLOBAL['pagenow'] === 'admin-ajax.php
		 */
		if ( wp_doing_ajax() ) {
			add_action( 'wp_ajax_smoc_reorder', array( __CLASS__, 'ajax_set_post_menu_order' ) );

			return;
		}

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'current_screen', array( $this, 'current_screen' ) );
	}

	/**
	 * Initialize plugin.
	 *
	 * @return void
	 */
	public function init() {
		if ( function_exists( 'load_plugin_textdomain' ) ) {
			load_plugin_textdomain( 'simple-menu-order-column', false, dirname( plugin_basename( SMOC_PLUGIN_FILE ) ) . '/i18n/languages/' );
		}

		add_action( 'admin_init', array( $this, 'add_setting' ) );
	}

	/**
	 * Add setting to wriring dashboard to disable UI confirmation.
	 *
	 * @return void
	 */
	public function add_setting() {

		add_settings_section(
			'smoc_section',
			'Simple Menu Order Column',
			array( __CLASS__, 'output_section_description' ),
			'writing'
		);

		register_setting(
			'writing',
			self::SMOC_OPTION_ALLOWED_TYPES,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( __CLASS__, 'input_sanitize_textbox' ),
				'default'           => self::get_default_allowed_types(),
			)
		);

		add_settings_field(
			self::SMOC_OPTION_ALLOWED_TYPES,
			__( 'WP_Post types allowed', 'simple-menu-order-column' ),
			array( __CLASS__, 'output_admin_textbox' ),
			'writing',
			'smoc_section',
			array(
				'option_name' => self::SMOC_OPTION_ALLOWED_TYPES,
			)
		);

		register_setting(
			'writing',
			self::SMOC_OPTION_UI_CONFIRM,
			array(
				'type'              => 'boolean',
				'sanitize_callback' => array( __CLASS__, 'input_sanitize_checkbox' ),
				'default'           => true,
			)
		);

		add_settings_field(
			self::SMOC_OPTION_UI_CONFIRM,
			__( 'Show confirmation prompt', 'simple-menu-order-column' ),
			array( __CLASS__, 'output_admin_checkbox' ),
			'writing',
			'smoc_section',
			array(
				'option_name' => self::SMOC_OPTION_UI_CONFIRM,
				'option_desc' => esc_attr__( 'If disabled, the value will be updated automatically without prompting.', 'simple-menu-order-column' ),
			)
		);

		register_setting(
			'writing',
			self::SMOC_OPTION_UI_TAB_TO_NEXT,
			array(
				'type'              => 'boolean',
				'sanitize_callback' => array( __CLASS__, 'input_sanitize_checkbox' ),
				'default'           => true,
			)
		);

		add_settings_field(
			self::SMOC_OPTION_UI_TAB_TO_NEXT,
			__( 'Go to next field on update', 'simple-menu-order-column' ),
			array( __CLASS__, 'output_admin_checkbox' ),
			'writing',
			'smoc_section',
			array(
				'option_name' => self::SMOC_OPTION_UI_TAB_TO_NEXT,
				'option_desc' => esc_attr__( 'If disabled, the cursor will remain in the input field after menu order is updated.', 'simple-menu-order-column' ),
			)
		);
	}

	/**
	 * Sanitize checkbox value.
	 *
	 * @param mixed $value Value to sanitize.
	 *
	 * @return int
	 */
	public static function input_sanitize_checkbox( $value ) {

		return $value ? 1 : 0;
	}

	/**
	 * Sanitize textbox value.
	 *
	 * @param mixed $allowed_types Value to sanitize.
	 *
	 * @return int
	 */
	public static function input_sanitize_textbox( $allowed_types ) {
		if ( ! empty( $allowed_types ) ) {
			$allowed_types     = sanitize_text_field( $allowed_types );
			$allowed_types_ary = array();

			foreach ( explode( ',', $allowed_types ) as $allowed_type ) {
				$allowed_type = trim( $allowed_type );

				if ( empty( $allowed_type ) ) {
					continue;
				}

				if ( null === get_post_type_object( $allowed_type ) ) {
					/* translators: %s: The invalid post type slug entered by the user */
					add_settings_error( self::SMOC_OPTION_ALLOWED_TYPES, 'invalid_textbox_value', sprintf( __( '"%s" is not valid post type for this plugin.', 'simple-menu-order-column' ), esc_html( $allowed_type ) ), 'error' );

					continue;
				}

				$allowed_types_ary[] = $allowed_type;
			}

			if ( ! empty( $allowed_types_ary ) ) {
				return implode( ',', $allowed_types_ary );
			}
		}

		return implode( ',', self::get_default_allowed_types() );
	}

	/**
	 * Generate html checkbox to disable UI confirmation section
	 *
	 * @return void
	 */
	public static function output_section_description() {
		print '<p>' . esc_html__(
			'Controls how the plugin handles UI confirmations.',
			'simple-menu-order-column'
		) . '</p>';
	}

	/**
	 * Generate html checkbox to disable UI confirmation.
	 *
	 * @param array $options Option name.
	 *
	 * @return void
	 */
	public static function output_admin_checkbox( array $options ) {
		$checked = filter_var( get_option( $options['option_name'], true ), FILTER_VALIDATE_BOOLEAN, array( 'default' => true ) );

		print '<label>';
		print '<input name="' . esc_attr( $options['option_name'] ) . '" type="checkbox" ' . checked( $checked, true, false ) . ' class="smoc-input" value="1" />';
		print esc_attr( $options['option_desc'] );
		print '</label>';
	}

	/**
	 * Generate html textbox to disable UI confirmation.
	 *
	 * @param array $options Option name.
	 *
	 * @return void
	 */
	public static function output_admin_textbox( array $options ) {
		$allowed_types = implode( ',', self::get_allowed_types() );

		print '<label>';
		print '<input name="' . esc_attr( $options['option_name'] ) . '" type="text" class="regular-text ltr" value="' . esc_attr( $allowed_types ) . '" />';
		print '</label>';
	}

	/**
	 * Manage columns when we are on the screen we want.
	 *
	 * @return void
	 */
	public function current_screen() {
		/** Add only on listings pages and compatible post types. */
		$current_screen = get_current_screen();

		if ( null === $current_screen ) {
			return;
		}

		if (
			! in_array( $current_screen->base, array( 'edit', 'upload' ), true ) ||
			! in_array( $current_screen->post_type, self::get_allowed_types(), true )
		) {
			return;
		}

		add_filter( 'manage_' . $current_screen->id . '_columns', array( __CLASS__, 'manage_edit_columns' ) );
		add_filter( 'manage_' . $current_screen->id . '_posts_columns', array( __CLASS__, 'manage_edit_columns' ) );
		add_filter( 'manage_' . $current_screen->id . '_sortable_columns', array( __CLASS__, 'manage_edit_sortable_columns' ) );

		if ( 'upload' === $current_screen->base ) {
			/** This action is called directly. */
			add_action( 'manage_media_custom_column', array( __CLASS__, 'manage_posts_custom_column' ), 10, 2 );
		} else {
			add_action( 'manage_' . $current_screen->post_type . '_posts_custom_column', array( __CLASS__, 'manage_posts_custom_column' ), 10, 2 );
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		$wp_scripts_get_suffix = wp_scripts_get_suffix();

		wp_enqueue_script(
			'simple-menu-order-column',
			plugins_url( 'assets/js/simple-menu-order-column' . $wp_scripts_get_suffix . '.js', SMOC_PLUGIN_FILE ),
			array( 'wp-i18n' ),
			SMOC_PLUGIN_VERSION,
			true
		);

		wp_localize_script(
			'simple-menu-order-column',
			'smoc_ui',
			array(
				'enable_confirm' => filter_var(
					get_option(
						self::SMOC_OPTION_UI_CONFIRM,
						true
					),
					FILTER_VALIDATE_BOOLEAN,
					array( 'default' => true )
				),
				'tab_to_next'    => filter_var(
					get_option(
						self::SMOC_OPTION_UI_TAB_TO_NEXT,
						true
					),
					FILTER_VALIDATE_BOOLEAN,
					array( 'default' => true )
				),
			)
		);

		wp_set_script_translations(
			'simple-menu-order-column',
			'simple-menu-order-column',
			plugin_dir_path( SMOC_PLUGIN_FILE ) . '/i18n/languages/'
		);

		wp_enqueue_style(
			'simple-menu-order-column',
			plugins_url( 'assets/css/simple-menu-order-column' . $wp_scripts_get_suffix . '.css', SMOC_PLUGIN_FILE ),
			array(),
			SMOC_PLUGIN_VERSION
		);
	}

	/**
	 * Allowed post_types.
	 *
	 * @return array
	 */
	public static function get_allowed_types() {

		$get_allowed_types = wp_cache_get( 'smoc_get_allowed_types' );

		if ( false !== $get_allowed_types ) {
			return $get_allowed_types;
		}

		$get_allowed_types = array();

		$allowed_types = get_option( self::SMOC_OPTION_ALLOWED_TYPES );

		if ( empty( $allowed_types ) ) {
			$allowed_types = self::get_default_allowed_types();
		}

		$allowed_types = is_array( $allowed_types ) ? array_filter( $allowed_types ) : array_filter( explode( ',', $allowed_types ) );

		foreach ( $allowed_types as $allowed_type ) {
			$allowed_type = trim( $allowed_type );

			if ( empty( $allowed_type ) || null === get_post_type_object( $allowed_type ) ) {
				continue;
			}

			$get_allowed_types[] = $allowed_type;
		}

		wp_cache_set( 'smoc_get_allowed_types', $get_allowed_types );

		return $get_allowed_types;
	}

	/**
	 * Allowed default post_types.
	 *
	 * @return array{0: 'post', 1: 'page', 2: 'product', 3: 'attachment'}
	 */
	public static function get_default_allowed_types() {
		return self::$smoc_allowed_types;
	}

	/**
	 * Ajax call to reorder.
	 *
	 * @return void
	 */
	public static function ajax_set_post_menu_order() {
		if ( false === check_ajax_referer( 'set-post-menu-order', '_wpnonce', false ) ) {
			wp_send_json_error();
		}

		/**
		 * Check post_type.
		 */
		$post_type = filter_input( INPUT_POST, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( ! $post_type || ! in_array( $post_type, self::get_allowed_types(), true ) ) {
			wp_send_json_error();
		}

		/**
		 * Get post_id & post_menu_order.
		 */
		$post_id         = filter_input(
			INPUT_POST,
			'post_id',
			FILTER_VALIDATE_INT,
			array( 'options' => array( 'min_range' => 1 ) )
		);
		$post_menu_order = filter_input(
			INPUT_POST,
			'post_menu_order',
			FILTER_VALIDATE_INT
		);

		if (
			! is_integer( $post_id ) ||
			! is_integer( $post_menu_order ) ||
			! current_user_can( 'edit_post', $post_id ) ||
			self::set_post_menu_order( $post_id, $post_menu_order ) instanceof WP_Error
		) {
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	/**
	 * Set post menu order.
	 *
	 * @param int $post_id Post id.
	 * @param int $post_menu_order Post order.
	 * @return WP_Error|int The post ID on success or WP_Error on failure.
	 */
	private static function set_post_menu_order( int $post_id, int $post_menu_order ) {
		return wp_update_post(
			array(
				'ID'         => $post_id,
				'menu_order' => $post_menu_order,
			),
			true
		);
	}

	/**
	 * Generate html column order input field.
	 *
	 * @param int $post_id Post id.
	 * @param int $post_menu_order Post order.
	 * @return void
	 */
	private static function output_menu_order_column( int $post_id, int $post_menu_order ) {
		/** Plugins should not change the nonce so make it static for all boxes */
		static $wp_nonce = esc_attr( wp_create_nonce( 'set-post-menu-order' ) );
		/** Even all output is XSS secure to prevent bot complaining we cast variables again. */
		print '<div class="smoc-container">';
		print '<input id="smoc-' . (int) $post_id . '" type="text" class="smoc-input" value="' . (int) $post_menu_order . '" title="' . (int) $post_menu_order . '" data-wpnonce="' . $wp_nonce . '" data-post-id="' . (int) $post_id . '" />';
		print '</div>';
	}

	/**
	 * Append menu order column to listings pages.
	 *
	 * @param string $column Column name.
	 * @param int    $postid Post order.
	 * @return void
	 */
	public static function manage_posts_custom_column( $column, $postid ) {
		if ( 'menu_order' === $column ) {
			$post = get_post( $postid );

			if ( null === $post ) {
				return;
			}

			self::output_menu_order_column( $postid, $post->menu_order );
		}
	}

	/**
	 * Add menu order column.
	 *
	 * @param string[] $columns Post list columns.
	 * @return string[]
	 */
	public static function manage_edit_columns( $columns ) {
		$columns['menu_order'] = esc_html__( 'Order', 'simple-menu-order-column' );
		return $columns;
	}

	/**
	 * Add menu order column to sortable columns.
	 *
	 * @param string[] $sortable_columns Post list columns.
	 * @return string[]
	 */
	public static function manage_edit_sortable_columns( $sortable_columns ) {
		$sortable_columns['menu_order'] = 'menu_order';
		return $sortable_columns;
	}

	/**
	 * Get this as singleton.
	 *
	 * @return SimpleMenuOrderColumn
	 */
	public static function instance() {
		if ( ! self::$smoc_instace instanceof SimpleMenuOrderColumn ) {
			self::$smoc_instace = new self();
		}

		return self::$smoc_instace;
	}

	/**
	 * Delete options.
	 *
	 * @return int|bool
	 */
	public static function delete_options() {
		global $wpdb;
		/**
		 * WP_Query
		 *
		 * @var \wpdb $wpdb
		 */
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		return $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'smoc_%'" );
	}

	/**
	 * Activate plugin, keep for meta update.
	 *
	 * @return void
	 */
	public static function activate() {
	}

	/**
	 * Deactivate plugin.
	 *
	 * @return void
	 */
	public static function deactivate() {
	}

	/**
	 * Uninstall plugin.
	 *
	 * @return void
	 */
	public static function uninstall() {
		self::delete_options();
	}
}

register_activation_hook(
	SMOC_PLUGIN_FILE,
	array( SimpleMenuOrderColumn::class, 'activate' )
);

register_deactivation_hook(
	SMOC_PLUGIN_FILE,
	array( SimpleMenuOrderColumn::class, 'deactivate' )
);

register_uninstall_hook(
	SMOC_PLUGIN_FILE,
	array( SimpleMenuOrderColumn::class, 'uninstall' )
);
