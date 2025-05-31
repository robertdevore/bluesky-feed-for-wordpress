<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Main plugin class for building the Bluesky Feed.
 *
 * @package Bluesky_Feed_For_WordPress
 * @since   1.0.0
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
                <img src="<?php echo esc_url( plugin_dir_url( BLUESKY_FEED_PLUGIN_PATH ) . 'assets/img/bluesky-logo.svg' ); ?>"
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

        // Get Global variable; used for code init (example: do_shortcode("[bluesky_feed]"))
        global $loadBlueSkyApp;

        // Check if the widget is active or the shortcode is used on the current page.
        if ($loadBlueSkyApp ||
            is_active_widget( false, false, 'bluesky_widget', true ) ||
            ( is_singular() && has_shortcode( get_post()->post_content, 'bluesky_feed' ) )
        ) {
            wp_enqueue_script(
                'axios',
                plugin_dir_url( BLUESKY_FEED_PLUGIN_PATH ) . 'assets/js/axios.min.js',
                [],
                BLUESKY_FEED_VERSION,
                true
            );

            wp_enqueue_script(
                'bluesky-feed-renderer',
                plugin_dir_url( BLUESKY_FEED_PLUGIN_PATH ) . 'assets/js/feed-renderer.js',
                [ 'axios' ],
                BLUESKY_FEED_VERSION,
                true
            );

            wp_enqueue_script(
                'bluesky-widget',
                plugin_dir_url( BLUESKY_FEED_PLUGIN_PATH ) . 'assets/js/widget.js',
                [ 'axios' ],
                BLUESKY_FEED_VERSION,
                true
            );

            wp_enqueue_style(
                'bluesky-widget-styles',
                plugin_dir_url( BLUESKY_FEED_PLUGIN_PATH ) . 'assets/css/feed-styles.css',
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
            plugin_dir_url( BLUESKY_FEED_PLUGIN_PATH ) . 'assets/js/axios.min.js',
            [],
            BLUESKY_FEED_VERSION,
            true
        );

        wp_enqueue_script(
            'bluesky-feed-renderer',
            plugin_dir_url( BLUESKY_FEED_PLUGIN_PATH ) . 'assets/js/feed-renderer.js',
            [ 'axios' ],
            BLUESKY_FEED_VERSION,
            true
        );

        wp_enqueue_script(
            'bluesky-admin',
            plugin_dir_url( BLUESKY_FEED_PLUGIN_PATH ) . 'assets/js/admin.js',
            [ 'bluesky-feed-renderer' ],
            BLUESKY_FEED_VERSION,
            true
        );

        wp_enqueue_script(
            'bluesky-widget',
            plugin_dir_url( BLUESKY_FEED_PLUGIN_PATH ) . 'assets/js/widget.js',
            [ 'bluesky-feed-renderer' ],
            BLUESKY_FEED_VERSION,
            true
        );

        wp_enqueue_style(
            'bluesky-admin-styles',
            plugin_dir_url( BLUESKY_FEED_PLUGIN_PATH ) . 'assets/css/feed-styles.css',
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
