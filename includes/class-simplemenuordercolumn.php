<?php
/**
 * Simple Menu Order Column
 *
 * @package SimpleMenuOrderColumn
 *
 * Copyright: (c) 2003-2022 Chillcode
 */

namespace SMOC;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * SMOCWC class.
 */
final class SimpleMenuOrderColumn {

	/**
	 * The single instance of the class.
	 *
	 * @var SimpleMenuOrderColumn
	 */
	private static $smoc_instace;

	/**
	 * Allowed types.
	 *
	 * We allow all WP_Post since has menu_order column and are sortable.
	 *
	 * @var array
	 */
	private static $smoc_allowed_types = array( 'post', 'page', 'product', 'attachment' );

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
	}

	/**
	 * Manage columns when we are on the screen we want.
	 *
	 * @return void
	 */
	public function current_screen() {
		/** Add only on listings pages and compatible post types. */
		$current_screen = get_current_screen();

		if (
			! in_array( $current_screen->base, array( 'edit', 'upload' ), true ) ||
			! in_array( $current_screen->post_type, self::$smoc_allowed_types, true )
		) {
			return;
		}

		add_filter( 'manage_' . $current_screen->id . '_columns', array( __CLASS__, 'manage_edit_columns' ) );
		add_filter( 'manage_' . $current_screen->id . '_sortable_columns', array( __CLASS__, 'manage_edit_sortable_columns' ) );

		if ( 'upload' === $current_screen->base ) {
			/** This filter is called directly. */
			add_filter( 'manage_media_custom_column', array( __CLASS__, 'manage_posts_custom_column' ), 10, 2 );
		} else {
			add_action( 'manage_' . $current_screen->post_type . '_posts_custom_column', array( __CLASS__, 'manage_posts_custom_column' ), 10, 2 );
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts.
	 */
	public function admin_enqueue_scripts() {
		$wp_scripts_get_suffix = wp_scripts_get_suffix();

		wp_enqueue_script( 'simple-menu-order-column', plugins_url( 'assets/js/simple-menu-order-column' . $wp_scripts_get_suffix . '.js', SMOC_PLUGIN_FILE ), array( 'jquery' ), SMOC_PLUGIN_VERSION, true );
		wp_enqueue_style( 'simple-menu-order-column', plugins_url( 'assets/css/simple-menu-order-column' . $wp_scripts_get_suffix . '.css', SMOC_PLUGIN_FILE ), array(), SMOC_PLUGIN_VERSION );
	}

	/**
	 * Allowed post_types.
	 *
	 * @return array
	 */
	public static function get_allowed_types() {
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

		if ( ! $post_type || ! in_array( $post_type, self::$smoc_allowed_types, true ) ) {
			wp_send_json_error();
		}

		/**
		 * Get post_id & post_menu_order.
		 */
		$post_id         = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
		$post_menu_order = filter_input( INPUT_POST, 'post_menu_order', FILTER_VALIDATE_INT );

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
	 */
	private static function set_post_menu_order( int $post_id, int $post_menu_order ) {

		$post = get_post( $post_id );

		if ( ! $post ) {
			return new WP_Error();
		}

		$post->menu_order = $post_menu_order;

		return wp_update_post( $post, true );
	}

	/**
	 * Generate html column order input field.
	 *
	 * @param int $post_id Post id.
	 * @param int $post_menu_order Post order.
	 */
	private static function output_menu_order_column( int $post_id, int $post_menu_order ) {
		/**
		 * NOTE: Is better to use woocommerce_wp_text_input method but to keep the plugin Woo free we create it here.
		 */
		// Even all output is XSS secure to prevent bot complaining we cast variables again.
		print '<div class="smoc-container">';
		print '<input id="smoc-' . (int) $post_id . '" type="text" class="smoc-input" value="' . (int) $post_menu_order . '" title="' . (int) $post_menu_order . '" data-wpnonce="' . esc_attr( wp_create_nonce( 'set-post-menu-order' ) ) . '" data-post-id="' . (int) $post_id . '" />';
		print '</div>';
	}

	/**
	 * Append menu order column to listings pages.
	 *
	 * @param string $column Column name.
	 * @param int    $postid Post order.
	 */
	public static function manage_posts_custom_column( $column, $postid ) {
		if ( 'menu_order' === $column ) {
			$post = get_post( $postid );

			self::output_menu_order_column( $postid, $post->menu_order );
		}
	}

	/**
	 * Add menu order column.
	 *
	 * @param array $columns Post list columns.
	 * @return array
	 */
	public static function manage_edit_columns( $columns ) {
		$columns['menu_order'] = esc_html__( 'Order', 'simple-menu-order-column' );

		return $columns;
	}

	/**
	 * Add menu order column to sortable columns.
	 *
	 * @param array $sortable_columns Post list columns.
	 * @return array
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
		if ( is_null( self::$smoc_instace ) ) {
			self::$smoc_instace = new self();
		}

		return self::$smoc_instace;
	}
}
