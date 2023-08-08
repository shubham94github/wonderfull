<?php

/**
 * Intellectual Property rights, and copyright, reserved by Todd Lahman, LLC as allowed by law include,
 * but are not limited to, the working concept, function, and behavior of this software,
 * the logical code structure and expression as written.
 *
 * @package     WooCommerce API Manager plugin and theme library
 * @author      Todd Lahman LLC https://www.toddlahman.com/
 * @copyright   Copyright (c) Todd Lahman LLC (support@toddlahman.com)
 * @since       1.0
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$report_for_email = '';

if ( ! class_exists( 'ddd_AM_License_Menu' ) ) {
	class ddd_AM_License_Menu {

		/**
		 * Class args.
		 *
		 * @var string
		 */
		public $file             = '';
		public $software_title   = '';
		public $software_version = '';
		public $plugin_or_theme  = '';
		public $api_url          = '';
		public $data_prefix      = '';
		public $slug             = '';
		public $plugin_name      = '';
		public $text_domain      = '';
		public $extra            = '';

		/**
		 * Class properties.
		 *
		 * @var string
		 */
		public $ame_software_product_id;
		public $ame_data_key;
		public $ame_api_key;
		public $ame_activation_email;
		public $ame_product_id_key;
		public $ame_instance_key;
		public $ame_deactivate_checkbox_key;
		public $ame_activated_key;
		public $ame_activation_tab_key;
		public $ame_settings_menu_title;
		public $ame_settings_title;
		public $ame_menu_tab_activation_title;
		public $ame_menu_tab_deactivation_title;
		public $ame_options;
		public $ame_plugin_name;
		public $ame_product_id;
		public $ame_renew_license_url;
		public $ame_instance_id;
		public $ame_domain;
		public $ame_software_version;

		/**
		 * @var null
		 */
		protected static $_instance = null;

		/**
		 * @param string $file             Must be $this->file from the root plugin file, or theme functions file.
		 * @param string $software_title   Must be exactly the same as the Software Title in the product.
		 * @param string $software_version This products current software version.
		 * @param string $plugin_or_theme  'plugin' or 'theme'
		 * @param string $api_url          The URL to the site that is running the API Manager. Example: https://www.toddlahman.com/ Must have a trailing slash.
		 * @param string $text_domain      The text domain for translation. Hardcoding this string is preferred rather than using this argument.
		 * @param string $extra            Extra data. Whatever you want.
		 *
		 * @return \AM_License_Menu|null
		 */
		public static function instance( $file, $software_title, $software_version, $plugin_or_theme, $api_url, $text_domain = '', $extra = '' ) {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self( $file, $software_title, $software_version, $plugin_or_theme, $api_url, $text_domain, $extra );
			}

			return self::$_instance;
		}

		public function __construct( $file, $software_title, $software_version, $plugin_or_theme, $api_url, $text_domain, $extra ) {
			$this->file            = $file;
			$this->software_title  = $software_title;
			$this->version         = $software_version;
			$this->plugin_or_theme = $plugin_or_theme;
			$this->api_url         = $api_url;
			$this->text_domain     = $text_domain;
			$this->extra           = $extra;
			$this->data_prefix     = str_ireplace( array( ' ', '_', '&', '?' ), '_', strtolower( $this->software_title ) );

			if ( is_admin() ) {
				if ( ! empty( $this->plugin_or_theme ) && $this->plugin_or_theme == 'theme' ) {
					add_action( 'admin_init', array( $this, 'activation' ) );
				}

				if ( ! empty( $this->plugin_or_theme ) && $this->plugin_or_theme == 'plugin' ) {
					register_activation_hook( $this->file, array( $this, 'activation' ) );
				}

				add_action( 'admin_menu', array( $this, 'register_menu' ) );

				// Check for external connection blocking
				//add_action( 'admin_notices', array( $this, 'check_external_blocking' ) );

				/**
				 * Software Product ID is the product title string
				 * This value must be unique, and it must match the API tab for the product in WooCommerce
				 */
				$this->ame_software_product_id = $this->software_title;

				/**
				 * Set all data defaults here
				 */
				$this->ame_data_key                = $this->data_prefix . '_data';
				$this->ame_api_key                 = 'api_key';
				$this->ame_activation_email        = 'activation_email';
				$this->ame_product_id_key          = $this->data_prefix . '_product_id';
				$this->ame_instance_key            = $this->data_prefix . '_instance';
				$this->ame_deactivate_checkbox_key = $this->data_prefix . '_deactivate_checkbox';
				$this->ame_activated_key           = $this->data_prefix . '_activated';

				/**
				 * Set all admin menu data
				 */
				$this->ame_deactivate_checkbox         = $this->data_prefix . '_deactivate_checkbox';
				$this->ame_activation_tab_key          = $this->data_prefix . '_dashboard';
				$this->ame_deactivation_tab_key        = $this->data_prefix . '_deactivation';
				$this->ame_settings_menu_title         = $this->software_title;
				$this->ame_settings_title              = $this->software_title . __( 'License Key', $this->text_domain );
				$this->ame_menu_tab_activation_title   = __( 'License Key', $this->text_domain );
				$this->ame_menu_tab_deactivation_title = __( 'License Key Deactivation', $this->text_domain );

				/**
				 * Set all software update data here
				 */
				$this->ame_options           = get_option( $this->ame_data_key );
				$this->ame_plugin_name       = $this->plugin_or_theme == 'plugin' ? untrailingslashit( plugin_basename( $this->file ) ) : get_stylesheet(); // same as plugin slug. if a theme use a theme name like 'twentyeleven'
				$this->ame_product_id        = get_option( $this->ame_product_id_key ); // Software Title
				$this->ame_renew_license_url = $this->api_url . 'my-account'; // URL to renew an API Key. Trailing slash in the upgrade_url is required.
				$this->ame_instance_id       = get_option( $this->ame_instance_key ); // Instance ID (unique to each blog activation)
				/**
				 * Some web hosts have security policies that block the : (colon) and // (slashes) in http://,
				 * so only the host portion of the URL can be sent. For example the host portion might be
				 * www.example.com or example.com. http://www.example.com includes the scheme http,
				 * and the host www.example.com.
				 * Sending only the host also eliminates issues when a client site changes from http to https,
				 * but their activation still uses the original scheme.
				 * To send only the host, use a line like the one below:
				 *
				 * $this->ame_domain = str_ireplace( array( 'http://', 'https://' ), '', home_url() ); // blog domain name
				 */
				$this->ame_domain           = str_ireplace( array( 'http://', 'https://' ), '', home_url() ); // blog domain name
				$this->ame_software_version = $this->version; // The software version
				$options                    = get_option( $this->ame_data_key );

				/**
				 * Check for software updates
				 */

			}
		}

		/**
		 * Register submenu specific to this product.
		 */
		public function register_menu() {
           // add_options_page( __( $this->ame_settings_menu_title, $this->text_domain ), __( $this->ame_settings_menu_title, $this->text_domain ), 'manage_options', $this->ame_activation_tab_key, array(
           //      $this,
           //      'config_page'
           //  ));
			add_menu_page( __( $this->ame_settings_menu_title, $this->text_domain ), __( $this->ame_settings_menu_title, $this->text_domain ), 'manage_options', $this->ame_activation_tab_key, array(
				$this,
				'config_page'
			), plugins_url( 'divi-den-on-demand/include/ddd-icon.png' ) );

		}

    /**
    * Generate report
    */

    /**
     * helper function for number conversions
     *
     * @access public
     * @param mixed $v
     * @return void
     */
    public function num_convt( $v ) {
        $l   = substr( $v, -1 );
        $ret = substr( $v, 0, -1 );

        switch ( strtoupper( $l ) ) {
            case 'P': // fall-through
            case 'T': // fall-through
            case 'G': // fall-through
            case 'M': // fall-through
            case 'K': // fall-through
                $ret *= 1024;
                break;
            default:
                break;
        }

        return $ret;
    }

    public function report_data($warning_flag) {

        // call WP database
        global $wpdb;

        // data checks for later

        $mu_plugins = get_mu_plugins();
        $plugins    = get_plugins();
        $active     = get_option( 'active_plugins', array() );

        $theme_data = wp_get_theme();
        $theme      = $theme_data->Name . ' ' . $theme_data->Version;
        $style_parent_theme = wp_get_theme(get_template());
        $parent_theme = $style_parent_theme->get( 'Name' )." ".$style_parent_theme->get( 'Version' );
        //print_r($theme_data);

        // multisite details
        $nt_plugins = is_multisite() ? wp_get_active_network_plugins() : array();
        $nt_active  = is_multisite() ? get_site_option( 'active_sitewide_plugins', array() ) : array();
        $ms_sites   = is_multisite() ? get_blog_list() : null;

        // yes / no specifics
        $ismulti    = is_multisite() ? __( 'Yes', 'ddd-report' ) : __( 'No', 'ddd-report' );
        $safemode   = ini_get( 'safe_mode' ) ? __( 'Yes', 'ddd-report' ) : __( 'No', 'ddd-report' );
        $wpdebug    = defined( 'WP_DEBUG' ) ? WP_DEBUG ? __( 'Enabled', 'ddd-report' ) : __( 'Disabled', 'ddd-report' ) : __( 'Not Set', 'ddd-report' );
        $errdisp    = ini_get( 'display_errors' ) != false ? __( 'On', 'ddd-report' ) : __( 'Off', 'ddd-report' );

        $jquchk     = wp_script_is( 'jquery', 'registered' ) ? $GLOBALS['wp_scripts']->registered['jquery']->ver : __( 'n/a', 'ddd-report' );

        $sessenb    = isset( $_SESSION ) ? __( 'Enabled', 'ddd-report' ) : __( 'Disabled', 'ddd-report' );
        $usecck     = ini_get( 'session.use_cookies' ) ? __( 'On', 'ddd-report' ) : __( 'Off', 'ddd-report' );
        $hascurl    = function_exists( 'curl_init' ) ? __( 'Supports cURL.', 'ddd-report' ) : __( 'Does not support cURL.', 'ddd-report' );
        $openssl    = extension_loaded('openssl') ? __( 'OpenSSL installed.', 'ddd-report' ) : __( 'OpenSSL not installed.', 'ddd-report' );

        // language

        $site_lang = get_bloginfo('language');
        if (is_rtl()) $site_text_dir = 'rtl';
            else $site_text_dir = 'ltr';

        // start generating report

        $report = '<div id="ddd-report">';
        $report .= '<h2>Understanding The System Status Report</h2>';
        $report .= '<p><h4>Self Help</h4> The system report contains useful technical information to predict problems. If you see an orange "Warning" notice, it can indicate a potential issue. To ensure optimal performance of the plugin, please work with your hosting company support. Update server settings to match the "Recommended Values" tab. You can take a screenshot or copy/paste the recommended and actual values and ask your hosting company to update the setting/s for you. </p>';
        $report .= '<input data-clipboard-action="copy" data-clipboard-target="#ddd-report-textarea" id="ddd-copy-report" type="button" value="Copy Report to Clipboard" class="et-core-modal-action">';
        $report .= '<p id="ddd-success-report" class="notice notice-success" style="max-width: 150px; margin-top: 10px; margin-bottom: 0;">Done: Copied to clipboard</p>';
        $report .= '<textarea readonly="readonly" id="ddd-report-textarea" name="ddd-report-textarea" style="width:0; height: 0; margin 0; padding: 0 !important; margin-top: -15px; position: absolute; z-index: -1; ">';
        $report .= '====== BEGIN REPORT ======'."\n";

        $report .= "\n".'--- WORDPRESS DATA ---'."\n";
        $report .= 'Multisite:'." ".$ismulti."\n";
        $report .= 'SITE_URL:'." ".site_url()."\n";
        $report .= 'HOME_URL:'." ".home_url()."\n";
        $report .= 'WP Version:'." ".get_bloginfo( 'version' )."\n";
        $report .= 'Permalink:'." ".get_option( 'permalink_structure' )."\n";
        $report .= 'Current Theme:'." ".$theme."\n";
        $report .= 'Parent Theme:'." ".$parent_theme."\n";

        $report .= "\n".'--- WORDPRESS CONFIG ---'."\n";
        $report .= 'WP_DEBUG:'." ".$wpdebug."\n";
        $report .= 'WP Memory Limit:'." ".$this->num_convt( WP_MEMORY_LIMIT )/( 1024 ).'MB'."\n";
        $report .= 'jQuery Version:'." ".$jquchk."\n";
        $report .= 'Site Language:'." ".$site_lang."\n";
        $report .= 'Site Text Direction:'." ".$site_text_dir."\n";

        if ( is_multisite() ) :
            $report .= "\n".'--- MULTISITE INFORMATION ---'."\n";
            $report .= 'Total Sites:'." ".get_blog_count()."\n";
            $report .= 'Base Site:'." ".$ms_sites[0]['domain']."\n";
            $report .= 'All Sites:'."\n";
            foreach ( $ms_sites as $site ) :
                if ( $site['path'] != '/' )
                    $report .= " ".'- '. $site['domain'].$site['path']."\n";

            endforeach;
            $report .= "\n";
        endif;

        $report .= "\n".'--- SERVER DATA ---'."\n";
        $report .= 'PHP Version:'." ".PHP_VERSION."\n";
        $report .= 'Server Software:'." ".$_SERVER['SERVER_SOFTWARE']."\n";

        $report .= "\n".'--- PHP CONFIGURATION ---'."\n";
        $report .= 'Safe Mode:'." ".$safemode."\n";
        $report .= 'memory_limit:'." ".ini_get( 'memory_limit' )."\n";
        $report .= 'upload_max_filesize:'." ".ini_get( 'upload_max_filesize' )."\n";
        $report .= 'post_max_size:'." ".ini_get( 'post_max_size' )."\n";
        $report .= 'max_execution_time:'." ".ini_get( 'max_execution_time' )."\n";
        $report .= 'max_input_vars:'." ".ini_get( 'max_input_vars' )."\n";
        $report .= 'max_input_time:'." ".ini_get( 'max_input_time' )."\n";
        $report .= 'Display Errors:'." ".$errdisp."\n";
        $report .= 'Cookie Path:'." ".esc_html( ini_get( 'session.cookie_path' ) )."\n";
        $report .= 'Save Path:'." ".esc_html( ini_get( 'session.save_path' ) )."\n";
        $report .= 'Use Cookies:'." ".$usecck."\n";
        $report .= 'cURL:'." ".$hascurl."\n";
        $report .= 'OpenSSL:'." ".$openssl."\n";

        $report .= "\n".'--- PLUGIN INFORMATION ---'."\n";
        if ( $plugins && $mu_plugins ) :
            $report .= 'Total Plugins:'." ".( count( $plugins ) + count( $mu_plugins ) + count( $nt_plugins ) )."\n";
        endif;

        // output must-use plugins
        if ( $mu_plugins ) :
            $report .= 'Must-Use Plugins: ('.count( $mu_plugins ).')'. "\n";
            foreach ( $mu_plugins as $mu_path => $mu_plugin ) :
                $report .= "\t".'- '.$mu_plugin['Name'] . ' ' . $mu_plugin['Version'] ."\n";
            endforeach;
            $report .= "\n";
        endif;

        // if multisite, grab active network as well
        if ( is_multisite() ) :
            // active network
            $report .= 'Network Active Plugins: ('.count( $nt_plugins ).')'. "\n";

            foreach ( $nt_plugins as $plugin_path ) :
                if ( array_key_exists( $plugin_base, $nt_plugins ) )
                    continue;

                $plugin = get_plugin_data( $plugin_path );

                $report .= "\t".'- '.$plugin['Name'] . ' ' . $plugin['Version'] ."\n";
            endforeach;
            $report .= "\n";

        endif;

        // output active plugins
        if ( $plugins ) :
            $report .= 'Active Plugins: ('.count( $active ).')'. "\n";
            foreach ( $plugins as $plugin_path => $plugin ) :
                if ( ! in_array( $plugin_path, $active ) )
                    continue;
                $report .= "\t".'- '.$plugin['Name'] . ' ' . $plugin['Version'] ."\n";
            endforeach;
            $report .= "\n";
        endif;

        // output inactive plugins
        if ( $plugins ) :
            $report .= 'Inactive Plugins: ('.( count( $plugins ) - count( $active ) ).')'. "\n";
            foreach ( $plugins as $plugin_path => $plugin ) :
                if ( in_array( $plugin_path, $active ) )
                    continue;
                $report .= "\t".'- '.$plugin['Name'] . ' ' . $plugin['Version'] ."\n";
            endforeach;
            $report .= "\n";
        endif;

        // end it all
        $report .= "\n".'====== END REPORT ======';

        $GLOBALS['$report_for_email'] = strstr(str_replace("\n", "      ", $report), '====== BEGIN REPORT');
        $report .= '</textarea></div>';

        $ddd_warning_status = '<td class="ddd_warning"><span>Warning</span></td>';
        $ddd_ok_status = '<td class="ddd_ok"><span>OK</span></td>';

        $report_table = '';

        $report_table .= '<table class="ddd-report-table"><tr><th colspan="4">Server Environment</th><tr class="ddd-header-row"><td>Config Option</td><td>Recommended Value</td><td>Actual Value</td><td>Status</td></tr>';

        $report_table .= '<tr><td>PHP Version</td><td>7.2+</td><td>'.PHP_VERSION.'</td>';
        if ((int) substr(str_replace(".", "", PHP_VERSION), 0, 2) >= 72) $ddd_php_status = $ddd_ok_status;
            else $ddd_php_status = $ddd_warning_status;
            $report_table .= $ddd_php_status.'</tr>';

        $report_table .= '<tr><td>Server Software</td><td>-</td><td>'.$_SERVER['SERVER_SOFTWARE'].'</td>'.$ddd_ok_status.'</tr>';

        $report_table .= '<tr><td>Safe Mode</td><td>No</td><td>'.$safemode.'</td>';
        if($safemode == 'No') $ddd_safe_mode_status = $ddd_ok_status;
            else $ddd_safe_mode_status = $ddd_warning_status;
            $report_table .= $ddd_safe_mode_status.'</tr>';

        $report_table .= '<tr><td>memory_limit</td><td>256+ MB</td><td>'.ini_get( 'memory_limit' ).'B</td>';
        if((int)str_replace("M", "",ini_get( 'memory_limit' )) >= 256) $ddd_memory_limit_status = $ddd_ok_status;
            else $ddd_memory_limit_status = $ddd_warning_status;
            $report_table .= $ddd_memory_limit_status.'</tr>';

        $report_table .= '<tr><td>post_max_size</td><td>128+ MB</td><td>'.ini_get( 'post_max_size' ).'B</td>';
        if((int)str_replace("M", "",ini_get( 'post_max_size' )) >= 128) $ddd_post_max_size_status = $ddd_ok_status;
            else $ddd_post_max_size_status = $ddd_warning_status;
            $report_table .= $ddd_post_max_size_status.'</tr>';

        $report_table .= '<tr><td>max_execution_time</td><td>180+</td><td>'.ini_get( 'max_execution_time' ).'</td>';
        if((int)ini_get( 'max_execution_time') >= 180) $ddd_max_execution_time_status = $ddd_ok_status;
            else $ddd_max_execution_time_status = $ddd_warning_status;
            $report_table .= $ddd_max_execution_time_status.'</tr>';

         $report_table .= '<tr><td>upload_max_filesize</td><td>64+ MB</td><td>'.ini_get( 'upload_max_filesize' ).'B</td>';
        if((int)str_replace("M", "",ini_get( 'upload_max_filesize' )) >= 64) $ddd_upload_max_status = $ddd_ok_status;
            else $ddd_upload_max_status = $ddd_warning_status;
            $report_table .= $ddd_upload_max_status.'</tr>';

        $report_table .= '<tr><td>max_input_time</td><td>180+</td><td>'.ini_get( 'max_input_time' ).'</td>';
        if((int)ini_get( 'max_input_time') >= 180) $ddd_max_input_time_status = $ddd_ok_status;
            else $ddd_max_input_time_status = $ddd_warning_status;
            $report_table .= $ddd_max_input_time_status.'</tr>';

        $report_table .= '<tr><td>max_input_vars</td><td>3000+</td><td>'.ini_get( 'max_input_vars' ).'</td>';
        if((int)ini_get( 'max_input_vars') >= 3000) $ddd_max_input_vars_status = $ddd_ok_status;
            else $ddd_max_input_vars_status = $ddd_warning_status;
            $report_table .= $ddd_max_input_vars_status.'</tr>';

        $report_table .= '<tr><td>Display Errors</td><td>Off</td><td>'.$errdisp.'</td>';
        if($errdisp == 'Off') $ddd_display_errors_status = $ddd_ok_status;
            else $ddd_display_errors_status = $ddd_warning_status;
            $report_table .= $ddd_display_errors_status.'</tr>';

        $report_table .= '<tr><td>Cookie Path</td><td>-</td>'.'<td>'.esc_html( ini_get( 'session.cookie_path' ) ).'</td>'.$ddd_ok_status.'</tr>';

        $report_table .= '<tr><td>Save Path</td><td>-</td>'.'<td>'.esc_html( ini_get( 'session.save_path' ) ).'</td>'.$ddd_ok_status.'</tr>';

        $report_table .= '<tr><td>Use Cookies</td><td>On</td><td>'.$usecck.'</td>';
        if($usecck == 'On') $ddd_use_cookies_status = $ddd_ok_status;
            else $ddd_use_cookies_status = $ddd_warning_status;
            $report_table .= $ddd_use_cookies_status.'</tr>';

        $report_table .= '<tr><td>cURL</td><td>Supports cURL.</td><td>'.$hascurl.'</td>';
        if($hascurl == 'Supports cURL.') $ddd_curl_status = $ddd_ok_status;
            else $ddd_curl_status = $ddd_warning_status;
            $report_table .= $ddd_curl_status.'</tr>';

        $report_table .= '<th colspan="4">WordPress Environment</th></tr><tr class="ddd-header-row"><td>Config Option</td><td>Recommended Value</td><td>Actual Value</td><td>Status</td></tr>';
        $report_table .= '<tr><td>Multisite</td><td>-</td>';
        $report_table .= '<td>'.$ismulti.'</td>'.$ddd_ok_status.'</tr>';
        $report_table .= '<td>Site url</td><td>-</td>'.'<td>'.site_url().'</td>'.$ddd_ok_status.'</tr>';
        $report_table .= '<td>Home url</td><td>-</td>'.'<td>'.home_url().'</td>'.$ddd_ok_status.'</tr>';
        $report_table .= '<td>WP Version</td><td>4.2+</td><td>'.get_bloginfo( 'version' ).'</td>';
        if ( (int)str_replace(".", "", get_bloginfo( 'version' )) < 42) {$wp_version_status = $ddd_warning_status;}
            else {$wp_version_status = $ddd_ok_status;}
        $report_table .= $wp_version_status.'</tr>';

        $report_table .= '<td>Permalink</td><td>-</td>'.'<td>'.get_option( 'permalink_structure' ).'</td>'.$ddd_ok_status.'</tr><tr>';
        $report_table .= '<td>Current Theme</td><td>-</td>'.'<td>'.$theme.'</td>'.$ddd_ok_status.'</tr><tr>';

        $report_table .= '<td>Parent Theme</td><td>Divi 3.0.0+</td>'.'<td>'.$parent_theme.'</td>';
        $parent_theme_version = (int)str_replace(".", "", $style_parent_theme->get( 'Version' ));
         if ( $style_parent_theme->get( 'Name' ) != 'Divi' || ($parent_theme_version < 300 && $parent_theme_version > 50)) {$wp_parent_theme_status = $ddd_warning_status;}
            else {$wp_parent_theme_status = $ddd_ok_status;}
        $report_table .= $wp_parent_theme_status.'</tr>';

        $report_table .= '<tr><td>WP debug</td><td>Disabled</td><td>'.$wpdebug.'</td>';
        if($wpdebug == 'Disabled') $ddd_wpdebug_status = $ddd_ok_status;
            else $ddd_wpdebug_status = $ddd_warning_status;
            $report_table .= $ddd_wpdebug_status.'</tr>';

        $report_table .= '<tr><td>WP Memory Limit</td><td>30+ MB</td><td>'.$this->num_convt( WP_MEMORY_LIMIT )/( 1024 ).' MB</td>';
             if($this->num_convt( WP_MEMORY_LIMIT )/( 1024 ) > 30) $ddd_wp_memory_status = $ddd_ok_status;
            else $ddd_wp_memory_status = $ddd_warning_status;
        $report_table .= $ddd_wp_memory_status.'</tr>';

        $report_table .= '<tr><td>jQuery Version</td><td>1.1.0+</td><td>'.$jquchk.'</td>';
        if((int)str_replace(".", "",$jquchk) >= 110) $ddd_jquery_status = $ddd_ok_status;
            else $ddd_jquery_status = $ddd_warning_status;
            $report_table .= $ddd_jquery_status.'</tr>';

        $report_table .= '<td>Site Language</td><td>-</td>'.'<td>'.$site_lang.'</td>'.$ddd_ok_status.'</tr><tr>';

        $report_table .= '<td>Site Text Direction</td><td>ltr (left-to-right)</td>'.'<td>'.$site_text_dir.'</td>';

        if($site_text_dir == 'ltr') $ddd_mtd_status = $ddd_ok_status;
            else $ddd_mtd_status = $ddd_warning_status;
            $report_table .= $ddd_mtd_status.'</tr>';


            // multisite info
		if ( is_multisite() ) :
			$report_table .= '<th colspan="4">Multisite</th></tr><tr class="ddd-header-row"><td>Config Option</td><td>Recommended Value</td><td>Actual Value</td><td>Status</td></tr>';
        $report_table .= '<tr><td>Total Sites</td><td>-</td>';
        $report_table .= '<td>'.get_blog_count().'</td>'.$ddd_ok_status.'</tr>';
        $report_table .= '<td>Base Site</td><td>-</td>'.'<td>'.$ms_sites[0]['domain'].'</td>'.$ddd_ok_status.'</tr>';
        $report_table .= '<td colspan="2">All Sites</td>'.'<td colspan="2">';
        	foreach ( $ms_sites as $site ) :
                if ( $site['path'] != '/' ) {
                    $report_table .= $site['domain'];
                	$report_table .= $site['path'];
                	$report_table .= "<br/>";
                }
          	endforeach;
        $report_table .='</td></tr>';

        endif;      // is_multisite()

         // output active plugins
        if ($plugins ):

        	$report_table .= '<th colspan="4">Active plugins</th>';

        	$report_table .= '<tr><td colspan="2">';
        	$report_table .= count( $active );
        	$report_table .= ' active plugins</td><td colspan="2">';
         	foreach ( $plugins as $plugin_path => $plugin ) :
                if ( ! in_array( $plugin_path, $active ) )
                    continue;
                $report_table .= $plugin['Name'];
                $report_table .= ' ';
                $report_table .= $plugin['Version'];
                $report_table .= "<br>";
         	endforeach;
         	$report_table .= '</td></tr>';

        endif;

         // output inctive plugins
        if ($plugins ):

        	$report_table .= '<th colspan="4">Inactive Plugins</th>';

        	$report_table .= '<tr><td colspan="2">';
        	$report_table .=  count( $plugins ) - count( $active );
        	$report_table .= ' inactive plugins</td><td colspan="2">';
         	foreach ( $plugins as $plugin_path => $plugin ) :
                if ( in_array( $plugin_path, $active ) )
                    continue;
                $report_table .= $plugin['Name'];
                $report_table .= ' ';
                $report_table .= $plugin['Version'];
                $report_table .= "<br>";
         	endforeach;
         	$report_table .= '</td></tr></table>';

        endif;

        if (strpos($report_table, $ddd_warning_status) !== false) {
        	$class = 'notice notice-error is-dismissible';
			$message = 'Action Required: Please review Divi Den on Demand system status report. Setting update may be required for best results. <a href=?page=' . $this->ame_activation_tab_key . '&tab=ddd_assistant_system_status>Go to system status tab</a>.';

	if($warning_flag == 1 && PAnD::is_admin_notice_active( 'disable-ddd-status-report-notice-forever' )) printf( '<div data-dismissible="disable-ddd-status-report-notice-forever" class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
        }

        return $report.$report_table;
    }

    public function ddd_getting_started() {
        add_option( 'ddd_enable', 'enabled' );
    	$ddd_starting = '';
    	$ddd_starting .= '<div class="et-epanel-box divi-button"><div class="et-box-title"><h3>Enable/Disable Divi Den on Demand Service</h3><p class="et-box-subtitle">(removes DDD tab from "Load Layouts" window)</p></div><div class="et-box-content"><input type="checkbox" class="et-checkbox yes_no_button" name="ddd_enable" id="ddd_enable" style="display: none;"><div class="et_pb_yes_no_button ';
        $ddd_option_template = get_option('ddd_enable');
        if ($ddd_option_template == 'enabled')
                $ddd_starting .= 'et_pb_on_state';
            else $ddd_starting .= 'et_pb_off_state';
    	$ddd_starting .='"><!-- .et_pb_on_state || .et_pb_off_state -->
				<span class="et_pb_value_text et_pb_on_value">Enabled</span>
				<span class="et_pb_button_slider"></span>
				<span class="et_pb_value_text et_pb_off_value">Disabled</span>
			</div></div></div><hr style="clear: both;">';
            if ($ddd_option_template == 'enabled') { $ddd_starting .= '<iframe id="ondemanIframe" name="ondemandIframe" class="settingsIframe" src="https://ondemand.divi-den.com/search-free-items-only/"></iframe><div class="saving_message"><h3 class="sectionSaved"><div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div><span style="display: block !important;">Taking too long? Try downloading the layout instead, and upload it using Divi Library.</span></h3><span class="close">&#x2715;</span></div><div class="loaded_message"><h3 class="sectionSaved">Success! Saved to Divi Library<br>The layout or section has been saved to your Divi Library.<br>Use the "Add From Library" tab in Divi Builder to load it onto a new page.</h3><span class="close">&#x2715;</span></div>'; }
            return $ddd_starting;
    }

    public function ddd_pro() {
        add_option( 'ddd_enable', 'enabled' );
        $ddd_pro = '<h3>Pro Layouts are offered exclusively by <a href="https://wp-den.com/" target="_blank" title="The Home Of Divi Den Pro">WP Den</a>. Upgrade to Divi Den Pro with a <a href="https://seku.re/ddd-14day-free" target="_blank" title="Get Divi Den Pro">14 Day Free Trial</a></h3>';
        $ddd_pro .= '<iframe id="ondemanIframe" name="ondemandIframe" class="settingsIframe" src="https://ondemand.divi-den.com/search-everything/"></iframe><div class="saving_message"><h3 class="sectionSaved"><div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div><span style="display: block !important;">Taking too long? Try downloading the layout instead, and upload it using Divi Library.</span></h3><span class="close">&#x2715;</span></div><div class="loaded_message"><h3 class="sectionSaved">Success! Saved to Divi Library<br>The layout or section has been saved to your Divi Library.<br>Use the "Add From Library" tab in Divi Builder to load it onto a new page.</h3><span class="close">&#x2715;</span></div>';
            return $ddd_pro;
    }

    public function ddd_assistant_help_faq() {
    	$ddd_help_faq = '<a class="et-core-modal-action" target="_blank" href="https://seku.re/ondemand-kb">View Help Center Files</a>
            <br/>
            <h3>Send a support request or give feedback</h3>
            <p><strong>Hello! We are happy to help. The support team is active Monday to Friday 9am - 4pm, central European time.</strong></p>
            <p><strong>No reply yet? Look in your email spam folder for a Divi Den Support reply.</strong></p>
            <iframe id="supportIframe" src="https://divi-den.com/divi-den-support-for-plugins-iframe?systemreport='.$GLOBALS['$report_for_email'].'"/></iframe>';
             // echo '$report_for_email: '.$GLOBALS['$report_for_email'];
    	return $ddd_help_faq;
    }

    public function ddd_assistant_tutorials_function() {
        $ddd_assistant_tutorials_content = '<h1>Get the best results from your efforts by watching these short video tutorials</h1>';

        $ddd_assistant_tutorials_content .= '<div class="ddd-accordion closed"><div class="ddd-accordion-header"><h3>Watch this before you begin <span>+</span></h3><p>Important things to do before you start building pages</p></div>';
        $ddd_assistant_tutorials_content .= '<div class="ddd-accordion-content"><p>Learn how to deactivate Divi local caching and JS minification, plugin caching and server caching.</p><iframe width="560" height="315" src="https://www.youtube.com/embed/RPnroDcjaZU?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen class="ddp-youtube"></iframe></div></div>';

         $ddd_assistant_tutorials_content .= '<div class="ddd-accordion closed"><div class="ddd-accordion-header"><h3>Finding & loading layouts <span>+</span></h3><p>Tips for finding relevant layouts fast</p></div>';
         $ddd_assistant_tutorials_content .= '<div class="ddd-accordion-content"><p>How to search by keyword. Using filter options, collections, bundles, page types and topics. Saving to the library or loading direct to a page.</p><iframe width="560" height="315" src="https://www.youtube.com/embed/d2byxdyxrDw?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen class="ddp-youtube"></iframe></div></div>';

         $ddd_assistant_tutorials_content .= '<div class="ddd-accordion closed"><div class="ddd-accordion-header"><h3>Making content & style updates <span>+</span></h3><p>Modify content and design to suit your needs</p></div>';
         $ddd_assistant_tutorials_content .= '<div class="ddd-accordion-content"><p>How to make updates using Divi module settings. Content, design and custom tab options.</p><iframe width="560" height="315" src="https://www.youtube.com/embed/ZiX28JWsTYA?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen class="ddp-youtube"></iframe></div></div>';

         $ddd_assistant_tutorials_content .= '<div class="ddd-accordion closed"><div class="ddd-accordion-header"><h3>Using developer tools<span>+</span></h3><p>How to customize ANYTHING easily using developer tools.</p></div>';
         $ddd_assistant_tutorials_content .= '<div class="ddd-accordion-content"><iframe width="560" height="315" src="https://www.youtube.com/embed/eRSae6dy1Ps?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen class="ddp-youtube"></iframe></div></div>';

         $ddd_assistant_tutorials_content .= '<div class="ddd-accordion closed"><div class="ddd-accordion-header"><h3>Common problems and troubleshooting tips <span>+</span></h3><p>Missing options or not able to save/load layouts?</p></div>';
         $ddd_assistant_tutorials_content .= '<div class="ddd-accordion-content"><p>Some tips for troubleshooting...</p><iframe width="560" height="315" src="https://www.youtube.com/embed/YS-C6n-mFII?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen class="ddp-youtube"></iframe></div></div>';

         $ddd_assistant_tutorials_content .= '<div class="ddd-accordion closed"><div class="ddd-accordion-header"><h3>Setting up blog layouts & blog modules <span>+</span></h3><p>Assign posts, categories, featured images and more</p></div>';
         $ddd_assistant_tutorials_content .= '<div class="ddd-accordion-content"><p>Tips for setting up posts, image sizes, image optimization, tick which categories to display on page & dummy posts if needed.</p><iframe width="560" height="315" src="https://www.youtube.com/embed/7COeB5hejrY?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen class="ddp-youtube"></iframe></div></div>';

        return $ddd_assistant_tutorials_content;
    }

    public function activation()
    {
        if (get_option($this->ame_data_key) === false || get_option($this->ame_instance_key) === false) {
            $global_options = array(
                $this->ame_api_key => ''
            );

            update_option($this->ame_data_key, $global_options);

            $single_options = array(
                $this->ame_product_id_key => $this->ame_software_product_id,
                $this->ame_instance_key => wp_generate_password(12, false),
                $this->ame_deactivate_checkbox_key => 'on',
                $this->ame_activated_key => 'Deactivated'
            );

            foreach ($single_options as $key => $value) {
                update_option($key, $value);
            }
        }
    }


		// Draw option page
	public function config_page() {
		$this->report_data($warning_flag = 1);
			$settings_tabs = array(
				'ddd_assistant_getting_started' => 'Free Layouts',
                'ddd_assistant_pro' => 'Divi Den Pro Layouts',
                'ddd_assistant_tutorials' => 'Tutorials',
				'ddd_assistant_system_status' => 'System Status',
				'ddd_assistant_help_faq' => 'Support & Feedback'
			);

				if(isset( $_GET[ 'tab' ] )) $current_tab = $tab = $_GET[ 'tab' ];
					else $current_tab = $tab = 'ddd_assistant_getting_started';
			?>
            <div class='wrap ddd-assistant'>
            	<h1>Divi Den on Demand Dashboard</h1>
                <h2 class="nav-tab-wrapper">
					<?php
					foreach ( $settings_tabs as $tab_page => $tab_name ) {
						$active_tab = $current_tab == $tab_page ? 'nav-tab-active' : '';
						echo '<a class="nav-tab '. $tab_page . ' ' .$active_tab . '" href="?page=' . $this->ame_activation_tab_key . '&tab=' . $tab_page . '">' . $tab_name . '</a>';
					}
					?>
                </h2>
                <form action='options.php' method='post'>
                    <div class="main">
						<?php
						if ( $tab == 'ddd_assistant_system_status') {
							echo $this->report_data($warning_flag = 0);
						}
						else if ( $tab == 'ddd_assistant_getting_started') {
							echo $this->ddd_getting_started();
						}
                        else if ( $tab == 'ddd_assistant_pro') {
                            echo $this->ddd_pro();
                        }
						else if ( $tab == 'ddd_assistant_help_faq') {
							echo $this->ddd_assistant_help_faq();
						}
                        else if ($tab == 'ddd_assistant_tutorials') {
                            echo $this->ddd_assistant_tutorials_function();
                        }
						?>
                    </div>
                </form>
            </div>
			<?php
		}

	}
}