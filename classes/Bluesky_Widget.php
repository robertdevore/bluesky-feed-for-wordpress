<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

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
