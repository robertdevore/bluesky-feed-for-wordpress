<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://robertdevore.com
 * @since             1.0.0
 * @package           Bluesky_Feed_For_WordPress
 *
 * @wordpress-plugin
 *
 * Plugin Name: Bluesky Feed for WordPress®
 * Description: Showcase your latest Bluesky posts on your WordPress® site with customizable display options and seamless integration
 * Plugin URI:  https://github.com/robertdevore/bluesky-feed-for-wordpress/
 * Version:     1.0.0
 * Author:      Robert DeVore
 * Author URI:  https://robertdevore.com/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: bluesky-feed
 * Domain Path: /languages
 * Update URI:  https://github.com/robertdevore/bluesky-feed-for-wordpress/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

require 'vendor/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/robertdevore/bluesky-feed-for-wordpress/',
    __FILE__,
    'bluesky-feed-for-wordpress'
);

// Set the branch that contains the stable release.
$myUpdateChecker->setBranch( 'main' );

// Set the version number.
define( 'BLUESKY_FEED_VERSION', '1.0.0' );

/**
 * Main plugin class for building the Bluesky Feed.
 */
class Bluesky_Feed {

    /**
     * Constructor - Hooks into WordPress actions.
     * 
     * @since  1.0.0
     * @return void
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        add_action('wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ]);
    }

    /**
     * Registers settings for the plugin.
     * 
     * @since  1.0.0
     * @return void
     */
    public function register_settings() {
        register_setting( 'bluesky_settings_group', 'bluesky_username', [ 'sanitize_callback' => 'sanitize_text_field' ] );
        register_setting( 'bluesky_settings_group', 'bluesky_theme', [ 'default' => 'light', 'sanitize_callback' => 'sanitize_text_field' ] );
        register_setting( 'bluesky_settings_group', 'bluesky_post_count', [ 'default' => 5, 'sanitize_callback' => 'absint' ] );
        register_setting( 'bluesky_settings_group', 'bluesky_include_pins', [ 'default' => 1, 'sanitize_callback' => 'absint' ] );
        register_setting( 'bluesky_settings_group', 'bluesky_include_link', [ 'default' => 1, 'sanitize_callback' => 'absint' ] );
    }

    /**
     * Adds the settings page to the WordPress admin menu.
     * 
     * @since  1.0.0
     * @return void
     */
    public function add_settings_page() {
        add_options_page(
            esc_html__( 'Bluesky Feed for WordPress® Settings', 'bluesky-feed' ),
            esc_html__( 'Bluesky Feed', 'bluesky-feed' ),
            'manage_options',
            'bluesky-feed',
            [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Renders the settings page in the admin area.
     * 
     * @since  1.0.0
     * @return void
     */
    public function render_settings_page() {
        $username     = esc_attr( get_option( 'bluesky_username', '' ) );
        $theme        = esc_attr( get_option( 'bluesky_theme', 'light' ) );
        $post_count   = absint( get_option( 'bluesky_post_count', 5 ) );
        $include_pins = absint( get_option( 'bluesky_include_pins', 1 ) );
        $include_link = absint( get_option( 'bluesky_include_link', 1 ) );
        ?>
        <div class="wrap">
            <h1>
                <img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'assets/img/bluesky-logo.svg' ); ?>" 
                alt="<?php esc_attr_e( 'Bluesky Logo', 'bluesky-feed' ); ?>" 
                style="vertical-align: middle; height: 24px; margin-right: 0px;">
                <?php esc_html_e( 'Bluesky Feed Settings', 'bluesky-feed' ); ?>
            <?php
            echo '<a id="bluesky-support-btn" href="https://robertdevore.com/contact/" target="_blank" class="button button-alt" style="margin-left: 10px;">
                    <span class="dashicons dashicons-format-chat" style="vertical-align: middle;"></span> ' . esc_html__( 'Support', 'bluesky-feed' ) . '
                </a>
                <a id="bluesky-docs-btn" href="https://robertdevore.com/articles/bluesky-feed-for-wordpress/" target="_blank" class="button button-alt" style="margin-left: 5px;">
                    <span class="dashicons dashicons-media-document" style="vertical-align: middle;"></span> ' . esc_html__( 'Documentation', 'bluesky-feed' ) . '
                </a>';
            ?>
            </h1>
            <hr />
            <form method="post" action="options.php">
                <?php settings_fields( 'bluesky_settings_group' ); ?>
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e( 'Bluesky Username', 'bluesky-feed' ); ?></th>
                        <td>
                            <input 
                                id="bluesky_username" 
                                type="text" 
                                name="bluesky_username" 
                                value="<?php echo esc_attr( $username ); ?>" 
                                class="regular-text"
                            >
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Theme', 'bluesky-feed' ); ?></th>
                        <td>
                            <select name="bluesky_theme">
                                <option value="light" <?php selected( $theme, 'light' ); ?>><?php esc_html_e( 'Light', 'bluesky-feed' ); ?></option>
                                <option value="dim" <?php selected( $theme, 'dim' ); ?>><?php esc_html_e( 'Dim', 'bluesky-feed' ); ?></option>
                                <option value="dark" <?php selected( $theme, 'dark' ); ?>><?php esc_html_e( 'Dark', 'bluesky-feed' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Number of Posts', 'bluesky-feed' ); ?></th>
                        <td>
                            <input 
                                type="number" 
                                name="bluesky_post_count" 
                                value="<?php echo esc_attr( $post_count ); ?>" 
                                min="1" 
                                max="20"
                            >
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Include Pins', 'bluesky-feed' ); ?></th>
                        <td>
                            <input 
                                type="checkbox" 
                                name="bluesky_include_pins" 
                                value="1" 
                                <?php checked( $include_pins, 1 ); ?>
                            >
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Include Link to Original Post', 'bluesky-feed' ); ?></th>
                        <td>
                            <input 
                                type="checkbox" 
                                name="bluesky_include_link" 
                                value="1" 
                                <?php checked( $include_link, 1 ); ?>
                            >
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <h2><?php esc_html_e( 'Feed Preview', 'bluesky-feed' ); ?></h2>
            <div id="bluesky-feed-preview" class="bluesky-feed"></div>
        </div>
        <?php
    }

    /**
     * Enqueue scripts and styles for the frontend.
     * 
     * @since  1.0.0
     * @return void
     */
    public function enqueue_frontend_assets() {
        // Check if the widget is active or the shortcode is used on the current page.
        if (
            is_active_widget( false, false, 'bluesky_widget', true ) || 
            ( is_singular() && has_shortcode( get_post()->post_content, 'bluesky_feed' ) )
        ) {
            wp_enqueue_script(
                'axios',
                plugin_dir_url(__FILE__) . 'assets/js/axios.min.js',
                [],
                BLUESKY_FEED_VERSION,
                true
            );

            wp_enqueue_script(
                'bluesky-feed-renderer',
                plugin_dir_url(__FILE__) . 'assets/js/feed-renderer.js',
                [ 'axios' ],
                BLUESKY_FEED_VERSION,
                true
            );
    
            wp_enqueue_script(
                'bluesky-widget',
                plugin_dir_url(__FILE__) . 'assets/js/widget.js',
                [ 'axios' ],
                BLUESKY_FEED_VERSION,
                true
            );

            wp_enqueue_style(
                'bluesky-widget-styles',
                plugin_dir_url(__FILE__) . 'assets/css/feed-styles.css',
                [],
                BLUESKY_FEED_VERSION
            );
        }
    }

    /**
     * Enqueues scripts and styles for the admin area.
     * 
     * @since  1.0.0
     * @return void
     */
    public function enqueue_admin_assets( $hook ) {
        if ( strpos( $hook, 'bluesky-feed' ) === false ) {
            return;
        }

        wp_enqueue_script( 
            'axios', 
            plugin_dir_url( __FILE__ ) . 'assets/js/axios.min.js', 
            [], 
            BLUESKY_FEED_VERSION, 
            true 
        );

        wp_enqueue_script(
            'bluesky-feed-renderer',
            plugin_dir_url(__FILE__) . 'assets/js/feed-renderer.js',
            [ 'axios' ],
            BLUESKY_FEED_VERSION,
            true
        );
    
        wp_enqueue_script(
            'bluesky-admin',
            plugin_dir_url(__FILE__) . 'assets/js/admin.js',
            [ 'bluesky-feed-renderer' ],
            BLUESKY_FEED_VERSION,
            true
        );
    
        wp_enqueue_script(
            'bluesky-widget',
            plugin_dir_url(__FILE__) . 'assets/js/widget.js',
            [ 'bluesky-feed-renderer' ],
            BLUESKY_FEED_VERSION,
            true
        );

        wp_enqueue_style( 
            'bluesky-admin-styles', 
            plugin_dir_url( __FILE__ ) . 'assets/css/feed-styles.css', 
            [], 
            BLUESKY_FEED_VERSION, 
            'all' 
        );

        wp_localize_script( 'bluesky-admin', 'blueskyAdminAjax', [
            'username'    => get_option( 'bluesky_username', '' ),
            'postCount'   => absint( get_option( 'bluesky_post_count', 5 ) ),
            'includePins' => absint( get_option( 'bluesky_include_pins', 1 ) ),
            'includeLink' => absint( get_option( 'bluesky_include_link', 1 ) ),
            'theme'       => esc_attr( get_option( 'bluesky_theme', 'light' ) ),
        ] );

    }
}

/**
 * Initialize the plugin.
 */
new Bluesky_Feed();

/**
 * Class Bluesky_Widget
 *
 * A widget to display recent Bluesky posts.
 *
 * @package Bluesky_Feed_For_WordPress
 * @since   1.0.0
 */
class Bluesky_Widget extends WP_Widget {

    /**
     * Constructor.
     *
     * Initializes the widget with ID, name, and description.
     *
     * @since  1.0.0
     * @return void
     */
    public function __construct() {
        parent::__construct(
            'bluesky_widget',
            esc_html__( 'Bluesky Feed Widget', 'bluesky-feed' ),
            [
                'description' => esc_html__( 'Display your recent Bluesky posts.', 'bluesky-feed' ),
            ]
        );
    }

    /**
     * Outputs the widget content on the frontend.
     *
     * @param array $args     Display arguments including 'before_widget' and 'after_widget'.
     * @param array $instance Settings for the current widget instance.
     *
     * @since  1.0.0
     * @return void
     */
    public function widget( $args, $instance ) {
        // Get settings from plugin options.
        $settings = [
            'username'    => esc_attr( get_option( 'bluesky_username', '' ) ),
            'postCount'   => absint( get_option( 'bluesky_post_count', 5 ) ),
            'includePins' => absint( get_option( 'bluesky_include_pins', 1 ) ),
            'includeLink' => absint( get_option( 'bluesky_include_link', 1 ) ),
            'theme'       => esc_attr( get_option( 'bluesky_theme', 'light' ) ),
        ];

        // Output widget content.
        echo $args['before_widget'];
        echo sprintf(
            '<div class="bluesky-feed-widget" data-settings="%s"></div>',
            esc_attr( wp_json_encode( $settings ) )
        );
        echo $args['after_widget'];
    }

    /**
     * Outputs the widget settings form in the admin area.
     *
     * @param array $instance Current settings for the widget instance.
     *
     * @since  1.0.0
     * @return void
     */
    public function form( $instance ) {
        echo '<p>' . esc_html__( 'This widget uses global settings. Update settings in the plugin options page.', 'bluesky-feed' ) . '</p>';
    }

    /**
     * Handles updating settings for the current widget instance.
     *
     * @param array $new_instance New settings for this instance as input by the user via form().
     * @param array $old_instance Old settings for this instance.
     *
     * @since  1.0.0
     * @return array Updated settings.
     */
    public function update( $new_instance, $old_instance ) {
        // Return sanitized new settings.
        return $new_instance;
    }
}

/**
 * Registers the Bluesky Feed Widget.
 *
 * @since  1.0.0
 * @return void
 */
function bluesky_register_widget() {
    register_widget( 'Bluesky_Widget' );
}
add_action( 'widgets_init', 'bluesky_register_widget' );


/**
 * Shortcode to display Bluesky feed.
 *
 * @param array $atts Shortcode attributes.
 * 
 * @since  1.0.0
 * @return string Rendered HTML for the feed.
 */
function bluesky_feed_shortcode( $atts ) {
    // Parse shortcode attributes with defaults.
    $atts = shortcode_atts(
        [
            'display' => 'list',
        ],
        $atts,
        'bluesky_feed'
    );

    // Get plugin settings.
    $settings = [
        'username'    => esc_attr( get_option( 'bluesky_username', '' ) ),
        'postCount'   => absint( get_option( 'bluesky_post_count', 5 ) ),
        'includePins' => absint( get_option( 'bluesky_include_pins', 1 ) ),
        'includeLink' => absint( get_option( 'bluesky_include_link', 1 ) ),
        'theme'       => esc_attr( get_option( 'bluesky_theme', 'light' ) ),
    ];

    // Prepare data attributes for JavaScript.
    $dataAttributes = esc_attr( json_encode( $settings ) );

    // Wrapper class for grid or list.
    $wrapperClass = $atts['display'] === 'grid' ? 'bluesky-feed-grid' : 'bluesky-feed-list';

    // Output the feed container.
    return sprintf(
        '<div class="bluesky-feed-widget %s" data-settings="%s"></div>',
        $wrapperClass,
        $dataAttributes
    );
}
add_shortcode( 'bluesky_feed', 'bluesky_feed_shortcode' );

/**
 * Helper function to handle WordPress.com environment checks.
 *
 * @param string $plugin_slug     The plugin slug.
 * @param string $learn_more_link The link to more information.
 * 
 * @since  1.1.0
 * @return bool
 */
function wp_com_plugin_check( $plugin_slug, $learn_more_link ) {
    // Check if the site is hosted on WordPress.com.
    if ( defined( 'IS_WPCOM' ) && IS_WPCOM ) {
        // Ensure the deactivate_plugins function is available.
        if ( ! function_exists( 'deactivate_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // Deactivate the plugin if in the admin area.
        if ( is_admin() ) {
            deactivate_plugins( $plugin_slug );

            // Add a deactivation notice for later display.
            add_option( 'wpcom_deactivation_notice', $learn_more_link );

            // Prevent further execution.
            return true;
        }
    }

    return false;
}

/**
 * Auto-deactivate the plugin if running in an unsupported environment.
 *
 * @since  1.1.0
 * @return void
 */
function wpcom_auto_deactivation() {
    if ( wp_com_plugin_check( plugin_basename( __FILE__ ), 'https://robertdevore.com/why-this-plugin-doesnt-support-wordpress-com-hosting/' ) ) {
        return; // Stop execution if deactivated.
    }
}
add_action( 'plugins_loaded', 'wpcom_auto_deactivation' );

/**
 * Display an admin notice if the plugin was deactivated due to hosting restrictions.
 *
 * @since  1.1.0
 * @return void
 */
function wpcom_admin_notice() {
    $notice_link = get_option( 'wpcom_deactivation_notice' );
    if ( $notice_link ) {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                echo wp_kses_post(
                    sprintf(
                        __( 'My Plugin has been deactivated because it cannot be used on WordPress.com-hosted websites. %s', 'bluesky-feed' ),
                        '<a href="' . esc_url( $notice_link ) . '" target="_blank" rel="noopener">' . __( 'Learn more', 'bluesky-feed' ) . '</a>'
                    )
                );
                ?>
            </p>
        </div>
        <?php
        delete_option( 'wpcom_deactivation_notice' );
    }
}
add_action( 'admin_notices', 'wpcom_admin_notice' );

/**
 * Prevent plugin activation on WordPress.com-hosted sites.
 *
 * @since  1.1.0
 * @return void
 */
function wpcom_activation_check() {
    if ( wp_com_plugin_check( plugin_basename( __FILE__ ), 'https://robertdevore.com/why-this-plugin-doesnt-support-wordpress-com-hosting/' ) ) {
        // Display an error message and stop activation.
        wp_die(
            wp_kses_post(
                sprintf(
                    '<h1>%s</h1><p>%s</p><p><a href="%s" target="_blank" rel="noopener">%s</a></p>',
                    __( 'Plugin Activation Blocked', 'bluesky-feed' ),
                    __( 'This plugin cannot be activated on WordPress.com-hosted websites. It is restricted due to concerns about WordPress.com policies impacting the community.', 'bluesky-feed' ),
                    esc_url( 'https://robertdevore.com/why-this-plugin-doesnt-support-wordpress-com-hosting/' ),
                    __( 'Learn more', 'bluesky-feed' )
                )
            ),
            esc_html__( 'Plugin Activation Blocked', 'bluesky-feed' ),
            [ 'back_link' => true ]
        );
    }
}
register_activation_hook( __FILE__, 'wpcom_activation_check' );

/**
 * Add a deactivation flag when the plugin is deactivated.
 *
 * @since  1.1.0
 * @return void
 */
function wpcom_deactivation_flag() {
    add_option( 'wpcom_deactivation_notice', 'https://robertdevore.com/why-this-plugin-doesnt-support-wordpress-com-hosting/' );
}
register_deactivation_hook( __FILE__, 'wpcom_deactivation_flag' );
