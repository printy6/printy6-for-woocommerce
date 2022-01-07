<?php

if ( ! class_exists( 'Printy6_Base' ) ) {

	/**
	 * Main / front controller class
	 *
	 * WordPress_Plugin_Skeleton is an object-oriented/MVC base for building WordPress plugins
	 */
	class PT6_Base extends PT6WC_Module {
		protected static $readable_properties  = array();    // These should really be constants, but PHP doesn't allow class constants to be arrays
		protected static $writeable_properties = array();
		protected $modules;

		const VERSION    								= '1.2.2';
		const PREFIX     								= 'pt6wc_';
		const DEBUG_MODE 								= true;
		
		const MENU_TITLE_TOP 						= 'Printy6';
		const MENU_SLUG_CONNECT 				= 'printy6-for-woocommerce-connect';
		const MENU_SLUG_DASHBOARD 			= 'printy6-for-woocommerce-dashboard';
		const MENU_SLUG_ORDER 					= 'printy6-for-woocommerce-order';
		const MENU_SLUG_PRODUCT 				= 'printy6-for-woocommerce-product';
		const MENU_SLUG_SETTING 				= 'printy6-for-woocommerce-settings';
		const CAPABILITY 								= 'manage_options';

		const REQUIRED_CAPABILITY = 'administrator';

		/*
		 * Magic methods
		 */

		/**
		 * Constructor
		 *
		 * @mvc Controller
		 */
		protected function __construct() {
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

			$this->register_hook_callbacks();

			// $this->modules = [];

			$this->modules = array(
			);

		}


		/*
		 * Static methods
		 */

		/**
		 * Enqueues CSS, JavaScript, etc
		 *
		 * @mvc Controller
		 */
		public static function load_resources() {
			wp_register_style(
				self::PREFIX . 'global-styles',
				plugins_url( 'assets/css/admin.css', dirname( __FILE__ ) ),
				array(),
				self::VERSION,
				'all'
			);

			wp_register_style(
				self::PREFIX . 'admin-styles',
				PT6WC_TEST ?
				plugins_url( 'assets/css/bundle.css', dirname( __FILE__ ) ) :
				plugins_url( 'assets/css/pt6wc-bundle.' . str_replace(".", "-", PT6WC_VERSION) . '.css', dirname( __FILE__ ) ),
				array(),
				self::VERSION,
				'all'
			);

			wp_register_script(
				self::PREFIX . 'connect-script',
				PT6WC_TEST ?
				plugins_url( 'assets/js/bundle.connect.js', dirname( __FILE__ ) ) :
				plugins_url( 'assets/js/pt6wc-bundle.' . str_replace(".", "-", PT6WC_VERSION) . '.connect.js', dirname( __FILE__ ) ),
				array(),
				self::VERSION,
				'all'
			);

			wp_register_script(
				self::PREFIX . 'dashboard-script',
				PT6WC_TEST ?
				plugins_url( 'assets/js/bundle.dashboard.js', dirname( __FILE__ ) ) :
				plugins_url( 'assets/js/pt6wc-bundle.' . str_replace(".", "-", PT6WC_VERSION) . '.dashboard.js', dirname( __FILE__ ) ),
				array(),
				self::VERSION,
				'all'
			);

			// 注册 全局css文件
			wp_enqueue_style( self::PREFIX . 'global-styles' );

			if ( is_admin() ) {
				if( $_GET && isset($_GET['page']) && $_GET['page'] === self::MENU_SLUG_CONNECT ){
					wp_enqueue_style( self::PREFIX . 'admin-styles' );
					wp_enqueue_script( self::PREFIX . 'connect-script' );
				}
	
				if( $_GET && isset($_GET['page']) && $_GET['page'] === self::MENU_SLUG_DASHBOARD ){
					wp_enqueue_style( self::PREFIX . 'admin-styles' );
					wp_enqueue_script( self::PREFIX . 'dashboard-script' );
				}
			}
		}

		public function hide_notice() { ?> <style> .notice { display: none;} </style> <?php }

		/**
		 * Clears caches of content generated by caching plugins like WP Super Cache
		 *
		 * @mvc Model
		 */
		protected static function clear_caching_plugins() {
			// WP Super Cache
			if ( function_exists( 'wp_cache_clear_cache' ) ) {
				wp_cache_clear_cache();
			}

			// W3 Total Cache
			if ( class_exists( 'W3_Plugin_TotalCacheAdmin' ) ) {
				$w3_total_cache = w3_instance( 'W3_Plugin_TotalCacheAdmin' );

				if ( method_exists( $w3_total_cache, 'flush_all' ) ) {
					$w3_total_cache->flush_all();
				}
			}
		}


		/*
		 * Instance methods
		 */

		/**
		 * Prepares sites to use the plugin during single or network-wide activation
		 *
		 * @mvc Controller
		 *
		 * @param bool $network_wide
		 */
		public function activate( $network_wide ) {

			if ( $network_wide && is_multisite() ) {
				$sites = wp_get_sites( array( 'limit' => false ) );

				foreach ( $sites as $site ) {
					switch_to_blog( $site['blog_id'] );
					$this->single_activate( $network_wide );
					restore_current_blog();
				}
			} else {
				$this->single_activate( $network_wide );
			}
		}

		/**
		 * Runs activation code on a new WPMS site when it's created
		 *
		 * @mvc Controller
		 *
		 * @param int $blog_id
		 */
		public function activate_new_site( $blog_id ) {
			switch_to_blog( $blog_id );
			$this->single_activate( true );
			restore_current_blog();
		}

		/**
		 * Prepares a single blog to use the plugin
		 *
		 * @mvc Controller
		 *
		 * @param bool $network_wide
		 */
		protected function single_activate( $network_wide ) {
			foreach ( $this->modules as $module ) {
				$module->activate( $network_wide );
			}

			flush_rewrite_rules();
		}

		/**
		 * Rolls back activation procedures when de-activating the plugin
		 *
		 * @mvc Controller
		 */
		public function deactivate() {
			PT6_Base::update_settings([], true);

			foreach ( $this->modules as $module ) {
				$module->deactivate();
			}

			flush_rewrite_rules();
		}

		/**
		 * Register callbacks for actions and filters
		 *
		 * @mvc Controller
		 */
		public function register_hook_callbacks() {
			add_action( 'wp_enqueue_scripts',    __CLASS__ . '::load_resources' );
			add_action( 'admin_enqueue_scripts', __CLASS__ . '::load_resources' );

			// 添加 侧边栏 入口
			add_action( 'admin_menu', 							array( $this, 'register_admin_menu_page' ) );

			add_action( 'wpmu_new_blog',         		array( $this, 'activate_new_site' ) );
			add_action( 'init',                  		array( $this, 'init' ) );

			add_action( 'in_admin_header',					array( $this, 'embed_page_header' ) );
		}

    /**
     * Register admin menu pages
     */
		public function register_admin_menu_page() {
			// PT6_Base::update_settings([], true);
			$woocommercePrinty6Settings = PT6_Base::get_settings();
			$isConnect = isset($woocommercePrinty6Settings['access_token']);
			
			add_menu_page(
				"Printy6 Dashboard",
				"Printy6",
				self::CAPABILITY,
				self::MENU_SLUG_DASHBOARD,
				false,
				PT6_Base::get_asset_url() . 'images/printy6-menu-icon.png',
				58
			);

			if(!$isConnect) {
				add_submenu_page( 
					self::MENU_SLUG_DASHBOARD,
					// 'woocommerce', 
					"Printy6 Connect",
					"Connect",
					self::CAPABILITY,
					self::MENU_SLUG_DASHBOARD,
					__CLASS__ . '::connectPage',
				);
			}

			if($isConnect) {
				add_submenu_page( 
					self::MENU_SLUG_DASHBOARD,
					"Printy6 Dashboard",
					"Dashboard",
					self::CAPABILITY,
					self::MENU_SLUG_DASHBOARD,
					__CLASS__ . '::dashboardPage',
				);
	
				// add_submenu_page( 
				// 	self::MENU_SLUG_DASHBOARD,
				// 	"Printy6 Order",
				// 	"Orders",
				// 	self::CAPABILITY,
				// 	self::MENU_SLUG_ORDER,
				// 	__CLASS__ . '::dashboardPage',
				// );
	
				// add_submenu_page( 
				// 	self::MENU_SLUG_DASHBOARD,
				// 	"Printy6 Product",
				// 	"Products",
				// 	self::CAPABILITY,
				// 	self::MENU_SLUG_PRODUCT,
				// 	__CLASS__ . '::dashboardPage',
				// );
	
				// add_submenu_page( 
				// 	self::MENU_SLUG_DASHBOARD,
				// 	__( 'Printy6', 'Settings' ),
				// 	"Settings",
				// 	self::CAPABILITY,
				// 	self::MENU_SLUG_SETTING,
				// 	__CLASS__ . '::dashboardPage',
				// );
			}
		}
		
		/**
		 * Creates the markup for the Settings page
		 *
		 * @mvc Controller
		 */
		public static function connectPage() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				echo self::render_template( 'pt6wc-connect/page-connect.php' );
			} else {
				wp_die( 'Access denied.' );
			}
		}
		
		/**
		 * Creates the markup for the Settings page
		 *
		 * @mvc Controller
		 */
		public static function dashboardPage() {
			if ( current_user_can( self::REQUIRED_CAPABILITY ) ) {
				echo self::render_template( 'pt6wc-dashboard/page-dashboard.php' );
			} else {
				wp_die( 'Access denied.' );
			}
		}

		/**
		 * Initializes variables
		 *
		 * @mvc Controller
		 */
		public function init() {
			try {
				$woocommercePrinty6Settings = PT6_Base::get_settings();
				$isConnect = isset($woocommercePrinty6Settings['access_token']);
				// if(!$isConnect) {
					add_action( 'admin_notices', __CLASS__ . '::admin_notice_connect' );
				// }

				// hiden notice
				if ( is_admin() ) {
					if( $_GET && isset($_GET['page']) && $_GET['page'] === self::MENU_SLUG_CONNECT ){
						add_action('admin_head', array( $this, 'hide_notice' ));
					}
		
					if( $_GET && isset($_GET['page']) && $_GET['page'] === self::MENU_SLUG_DASHBOARD ){
						add_action('admin_head', array( $this, 'hide_notice' ));
					}
				}
			} catch ( Exception $exception ) {
				add_notice( __METHOD__ . ' error: ' . $exception->getMessage(), 'error' );
			}
		}

		/**
		 * Checks if the plugin was recently updated and upgrades if necessary
		 *
		 * @mvc Controller
		 *
		 * @param string $db_version
		 */
		public function upgrade( $db_version = 0 ) {
			self::clear_caching_plugins();
		}

		/**
		 * Checks that the object is in a correct state
		 *
		 * @mvc Model
		 *
		 * @param string $property An individual property to check, or 'all' to check all of them
		 * @return bool
		 */
		protected function is_valid( $property = 'all' ) {
			return true;
		}

		/**
		 * @return string
		 */
		public static function get_asset_url() {
			return trailingslashit(plugin_dir_url(__FILE__)) . '../assets/';
		}

		/**
		 * @return string
		 */
		public static function get_settings() {
			$settings = get_option( 'woocommerce_printy6_settings', [] );

			return $settings;
		}

		/**
		 * @return string
		 */
		public static function update_settings($newSettings, $force = false) {
			$settings = get_option( 'woocommerce_printy6_settings', [] );

			$settings = array_merge($settings, $newSettings);
			update_option( 'woocommerce_printy6_settings',  $force ? $newSettings : $settings );
		}

		/**
		 * send require connect notic
		 * @return string
		 */
		public static function admin_notice_connect() {
			$href = get_home_url() . '/wp-admin/admin.php?page=' . self::MENU_SLUG_DASHBOARD;
			?>
				<div class="notice notice-warning is-dismissible">
					<p>
						<b>Printy6 for Woocommerce</b>
						is not yet connected to a Printy6 account. To complete the connection, visit the
						<a href="<?php echo esc_url($href); ?>" style="color: #2271b1; text-decoration: underline;">plugin settings page</a>.
					</p>
				</div>
			<?php
		}

		public function plugins_loaded()
		{
			if (!class_exists('WC_Integration')) {
				return;
			}

			// WP REST API.
			$this->rest_api_init();
		}

    private function rest_api_init()
    {
        // REST API was included starting WordPress 4.4.
        if ( ! class_exists( 'WP_REST_Server' ) ) {
            return;
        }
        // Init REST API routes.
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 20);
				
    }

    public function register_rest_routes()
    {
        require_once 'pt6wc-rest-api-controller.php';
        $printy6RestAPIController = new Printy6_REST_API_Controller();
        $printy6RestAPIController->register_routes();
    }

		public function embed_page_header()
		{
			if ( is_admin() ) {
				if( $_GET && isset($_GET['page']) && $_GET['page'] === self::MENU_SLUG_DASHBOARD ) {
					$sections = wc_admin_get_breadcrumbs();
					$sections = is_array( $sections ) ? $sections : array( $sections );
					?>
						<div>
							<div class="pt6-class-header">
								<div class="pt6-class-header-content">
									<div class="pt6-text-xl">
									<img src="<?php echo plugins_url( 'assets/images/full-logo.svg', dirname( __FILE__ ) ); ?>" class="pt6-class-haeder-logo" alt="printy6-connect-woocommerce"></div>
									<a href="<?php echo esc_ur('https://www.printy6.com/dashboard/'); ?>" class="pt6-class-href pt6-class-ml-auto" target="_blank" rel="noreferrer">
										<button class="pt6-outline-0 pt6-inline-flex pt6-items-center pt6-rounded-md pt6-font-sans pt6-font-medium focus:pt6-outline-none focus:pt6-ring-2 focus:pt6-ring-offset-2 pt6-text-sm pt6-px-3 pt6-py-2 pt6-text-emerald-600 pt6-border pt6-border-emerald-600 pt6-bg-white hover:pt6-bg-emerald-50 hover:pt6-border-emerald-600 focus:pt6-ring-emerald-600 focus:pt6-bg-emerald-50">Open Printy6</button>
									</a>
								</div>
							</div>
						</div>
					<?php
				}
			}
		}
	} // end WordPress_Plugin_Skeleton
}
