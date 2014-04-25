<?php

/**
* Favicon_Selector class
*
* @since 1.0.0
*/
class Favicon_Selector {

	/**
	* Plugin version, used for cache-busting of style and script file references.
	*
	* @since 1.0.0
	*
	* @var string
	*/
	const VERSION = '1.0.0';

	/**
	* Unique identifier for your plugin.
	*
	* @since 1.0.0
	*
	* @var string
	*/
	protected $plugin_slug = 'favicon-selector';

	/**
	* Instance of this class.
	*
	* @since 1.0.0
	*
	* @var object
	*/
	protected static $instance = null;

	/**
	* Initialize the plugin by setting localization and loading public scripts
	* and styles.
	*
	* @since 1.0.0
	*/
	private function __construct() {
		
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		//add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		
		// Load options page
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		
		// Load callback for saving settings
		add_action( 'wp_ajax_fs_save_settings', array( $this, 'save_settings_callback' ) );
		
		// Display the favicon in the frontend
		add_action( 'wp_head', array( $this, 'display_favicon_in_frontend' ) );
		add_action( 'admin_head', array( $this, 'display_favicon_in_backend' ) );

	}

	/**
	* Return the plugin slug.
	*
	* @since 1.0.0
	*
	* @return Plugin slug variable.
	*/
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	* Return an instance of this class.
	*
	* @since 1.0.0
	*
	* @return object A single instance of this class.
	*/
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	* Fired when the plugin is activated.
	*
	* @since 1.0.0
	*
	* @param boolean $network_wide True if WPMU superadmin uses
	* "Network Activate" action, false if
	* WPMU is disabled or plugin is
	* activated on an individual blog.
	*/
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	* Fired when the plugin is deactivated.
	*
	* @since 1.0.0
	*
	* @param boolean $network_wide True if WPMU superadmin uses
	* "Network Deactivate" action, false if
	* WPMU is disabled or plugin is
	* deactivated on an individual blog.
	*/
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	* Fired when a new site is activated with a WPMU environment.
	*
	* @since 1.0.0
	*
	* @param int $blog_id ID of the new blog.
	*/
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	* Get all blog ids of blogs in the current network that are:
	* - not archived
	* - not spam
	* - not deleted
	*
	* @since 1.0.0
	*
	* @return array|false The blog ids, false if no matches.
	*/
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
		WHERE archived = '0' AND spam = '0'
		AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	* Fired for each blog when the plugin is activated.
	*
	* @since 1.0.0
	*/
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	* Fired for each blog when the plugin is deactivated.
	*
	* @since 1.0.0
	*/
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	* Load the plugin text domain for translation.
	*
	* @since 1.0.0
	*/
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	* Register and enqueue admin style sheet.
	*
	* @since 1.0.0
	*/
	public function enqueue_styles() {
		//wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	* Register and enqueues admin JavaScript files.
	*
	* @since 1.0.0
	*/
	public function admin_enqueue_scripts( $hook ) {
		if ( $hook == 'settings_page_favicon-selector' ) {
			wp_enqueue_script( $this->plugin_slug . '-script', FAVICON_SELECTOR_BACKEND_URL . '/res/js/favicon_selector.js', array( 'jquery' ), self::VERSION );
		}
	}
	
	/**
	* Creates the Options page
	*
	* @since 1.0.0
	*/
	public function admin_menu() {
        /**
		* fs_filter_save_settings_capability
		*
		* Filter the capability that can access the settings page
		*
		* @since 1.0.0
		*/
		$capability = apply_filters( 'fs_filter_save_settings_capability', 'manage_options' );
		add_options_page( __('Favicon selector', 'favicon-selector'), 'Favicon selector', $capability, 'favicon-selector', array( $this, 'favicon_selector_options_page' ) );
    }
	
	/**
	* Generates the Options page
	*
	* @since 1.0.0
	*/
	public function favicon_selector_options_page() {
		$existing_favicons = array(
			'anchor' => __( 'Anchor', 'favicon-selector' ),
			'asterisk' => __( 'Asterisk', 'favicon-selector' ),
			'building-classic' => __( 'Building - classic', 'favicon-selector' ),
			'cherries-red' => __( 'Cherries - red', 'favicon-selector' ),
			'circle-dots' => __( 'Circle - dots', 'favicon-selector' ),
			'code' => __( 'Code', 'favicon-selector' ),
			'coffee' => __( 'Cofee', 'favicon-selector' ),
			'lightning' => __( 'Lightning', 'favicon-selector' ),
			'moon' => __( 'Moon', 'favicon-selector' ),
			'music-note' => __( 'Music - note', 'favicon-selector' ),
			'pin' => __( 'Pin', 'favicon-selector' ),
			'pyramid' => __( 'Pyramid', 'favicon-selector' ),
			'shield' => __( 'Shield', 'favicon-selector' ),
			'squares' => __( 'Squares', 'favicon-selector' ),
			'star' => __( 'Star', 'favicon-selector' ),
			'tv-color' => __( 'TV - color', 'favicon-selector' )
		);
		$favicons_folder = FAVICON_SELECTOR_BACKEND_URL . '/res/img/favicons/';
		$favicon_options = get_option( 'fs_options', array() );
		$favicon_selected = isset( $favicon_options[ 'favicon_selected' ] ) ? $favicon_options[ 'favicon_selected' ] : '';
		$favicon_on_dashboard = isset( $favicon_options[ 'favicon_dashboard' ] ) ? $favicon_options[ 'favicon_dashboard' ] : 'disable';
		?>
		<div class="wrap">

			<div id="icon-favicon-selector" class="icon32"></div>
			<h2><?php _e('Favicon selector', 'favicon-selector') ?></h2>
			
			<table class="form-table js-fs-options">
				<tbody>
					<?php
					/**
					* fs_action_options_table_before_rows
					*
					* Action before the options table rows
					*
					* @since 1.0.0
					*/
					do_action( 'fs_action_options_table_before_rows' );
					?>
					<tr>
						<th scope="row"><?php _e( 'Select a favicon', 'favicon-selector' ); ?></th>
						<td>
							<ul style="overflow:hidden">
							<?php
							foreach ( $existing_favicons as $favicon_slug => $favicon_name ) {
								?>
								<li style="width:20%;float:left;">
									<input id='<?php echo $favicon_slug; ?>' type='radio' class='js-fs-favicon-item' name='favicon-selected' value='<?php echo $favicon_slug; ?>' <?php checked( $favicon_selected, $favicon_slug ); ?> />
									<label title='<?php echo $favicon_slug; ?>' for='<?php echo $favicon_slug; ?>'><img src='<?php echo $favicons_folder . $favicon_slug . '.ico'; ?>' title='<?php echo $favicon_slug; ?>' alt='<?php echo $favicon_slug; ?>' /></label>
								</li>
								<?php
							}
							?>
							<?php
							/**
							* fs_action_options_table_extend_existing_favicons
							*
							* Action to extend the built-in list of favicons with custom ones
							*
							* @since 1.0.0
							*/
							do_action( 'fs_action_options_table_extend_existing_favicons' );
							?>
							</ul>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Dashboard favicon', 'favicon-selector' ); ?></th>
						<td>
							<input id='fs-favicon-dashboard' type='checkbox' class='js-fs-favicon-dashboard' name='favicon-dashboard' value='enable' <?php checked( $favicon_on_dashboard, 'enable' ); ?> />
							<label for='fs-favicon-dashboard'><?php _e( 'Apply this same favicon to the dashboard', 'favicon-selector' ); ?></label>
						</td>
					</tr>
					<?php
					/**
					* fs_action_options_table_after_rows
					*
					* Action after the options table rows
					*
					* @since 1.0.0
					*/
					do_action( 'fs_action_options_table_after_rows' );
					?>
				</tbody>
			</table>
			<?php 
			$other_attributes = array( 
				'disabled' => 'disabled'
			);
			submit_button( __( 'Save favicon settings', 'favicon-selector' ), 'js-fs-save-settings', '', false, $other_attributes );
			wp_nonce_field( 'fs_save_settings_nonce', 'fs_save_settings_nonce');
			?>
		</div>
		<?php
	}
	
	/**
	* Saves the options
	*
	* @since 1.0.0
	*/
	public function save_settings_callback() {
		if ( !wp_verify_nonce( $_POST['wp_nonce'], 'fs_save_settings_nonce' ) ) die( "Security check" );
		if ( !isset( $_POST['favicon_data'] ) ) die( 'notset' );
		$favicon_data = wp_parse_args( $_POST['favicon_data'] );
		if ( !isset( $favicon_data['favicon-selected'] ) ) die( 'notselected' );
		$sanitized_favicon_to_save = sanitize_title( $favicon_data['favicon-selected'] );
		$favicon_in_dashboard = isset( $favicon_data['favicon-dashboard'] ) ? sanitize_title( $favicon_data['favicon-dashboard'] ) : 'disable';
		$options = array(
			'favicon_selected' => $sanitized_favicon_to_save,
			'favicon_dashboard' => $favicon_in_dashboard
		);
		/**
		* fs_filter_saving_options
		*
		* Filter before saving options
		*
		* @since 1.0.0
		*/
		$options = apply_filters( 'fs_filter_saving_options', $options );
		update_option( 'fs_options', $options );
		die( 'ok' );
	}
	
	/**
	* Displays the favicon in the frontend
	*
	* @since 1.0.0
	*/
	public function display_favicon_in_frontend() {
		$favicon_options = get_option( 'fs_options', array() );
		$favicon_selected = isset( $favicon_options[ 'favicon_selected' ] ) ? $favicon_options[ 'favicon_selected' ] : '';
		if ( !empty( $favicon_selected ) ) {
			$favicons_folder = FAVICON_SELECTOR_FRONTEND_URL . '/res/img/favicons/';
			/**
			* fs_filter_frontend_favicons_folder
			*
			* Filter the built-in favicon files folder
			*
			* Use this if you are trying to load a favicon added on the fs_action_options_table_extend_existing_favicons action
			* as it will likely not be placed on the built-in folder
			*
			* @param $favicon_folder
			* @param @favicon_selected
			*
			* @return $favicon_folder
			*
			* @since 1.0.0
			*/
			$favicons_folder = apply_filters( 'fs_filter_frontend_favicons_folder', $favicons_folder, $favicon_selected );
			?>
			<link rel="shortcut icon" href="<?php echo $favicons_folder . $favicon_selected; ?>.ico?ver=<?php echo $favicon_selected; ?>"> 
			<?php
		}
	}
	
	/**
	* Displays the favicon in the backend
	*
	* @since 1.0.0
	*/
	public function display_favicon_in_backend() {
		$favicon_options = get_option( 'fs_options', array() );
		$favicon_selected = isset( $favicon_options[ 'favicon_selected' ] ) ? $favicon_options[ 'favicon_selected' ] : '';
		$favicon_on_dashboard = isset( $favicon_options[ 'favicon_dashboard' ] ) ? $favicon_options[ 'favicon_dashboard' ] : 'disable';
		if ( !empty( $favicon_selected ) && $favicon_on_dashboard == 'enable' ) {
			$favicons_folder = FAVICON_SELECTOR_BACKEND_URL . '/res/img/favicons/';
			/**
			* fs_filter_backend_favicons_folder
			*
			* Filter the built-in favicon files folder
			*
			* Use this if you are trying to load a favicon added on the fs_action_options_table_extend_existing_favicons action
			* as it will likely not be placed on the built-in folder
			*
			* @param $favicon_folder
			* @param @favicon_selected
			*
			* @return $favicon_folder
			*
			* @since 1.0.0
			*/
			$favicons_folder = apply_filters( 'fs_filter_backend_favicons_folder', $favicons_folder, $favicon_selected );
			?>
			<link rel="shortcut icon" href="<?php echo $favicons_folder . $favicon_selected; ?>.ico?ver=<?php echo $favicon_selected; ?>"> 
			<?php
		}
	}

}