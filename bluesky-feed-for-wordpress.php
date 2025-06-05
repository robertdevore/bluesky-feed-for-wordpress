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
 * Version:     1.2.0
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
define( 'BLUESKY_FEED_VERSION', '1.2.0' );
define( 'BLUESKY_FEED_PLUGIN_PATH', __FILE__ );

// Check if Composer's autoloader is already registered globally.
if ( ! class_exists( 'RobertDevore\WPComCheck\WPComPluginHandler' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use RobertDevore\WPComCheck\WPComPluginHandler;

new WPComPluginHandler( plugin_basename( __FILE__ ), 'https://robertdevore.com/why-this-plugin-doesnt-support-wordpress-com-hosting/' );

// Load the required classes.
require 'classes/Bluesky_Feed.php';
require 'classes/Bluesky_Widget.php';

/**
 * Load plugin text domain for translations
 *
 * @since  1.1.0
 * @return void
 */
function bluesky_feed_load_textdomain() {
    load_plugin_textdomain(
        'bluesky-feed',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages/'
    );
}
add_action( 'plugins_loaded', 'bluesky_feed_load_textdomain' );

/**
 * Add settings link to plugin page.
 *
 * @param array $links Existing links.
 * 
 * @since  1.1.0
 * @return array Modified links.
 */
function bluesky_feed_add_settings_link( $links ) {
    $settings_link = '<a href="options-general.php?page=bluesky-feed">' . esc_html__( 'Settings', 'bluesky-feed' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'bluesky_feed_add_settings_link' );

/**
 * Initialize the main class.
 */
new Bluesky_Feed();

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
            'post_count' => 0
        ],
        $atts,
        'bluesky_feed'
    );

    // Get plugin settings.
    $settings = [
        'username'    => esc_attr( get_option( 'bluesky_username', '' ) ),
        'postCount'   => $atts['post_count'] ?: absint( get_option( 'bluesky_post_count', 5 ) ),
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

