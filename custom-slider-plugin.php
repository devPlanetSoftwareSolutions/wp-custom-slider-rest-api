<?php
/*
Plugin Name: Custom Slider Plugin
Description: A plugin to manage custom sliders with REST API support.
Version: 1.0
Author: Syed Naseer (devPlanet Software Solutions)
Author URI: https://clutch.co/profile/devplanet-software-solutions
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Custom_Slider_Plugin {

    public function __construct() {
        // Register custom post type
        add_action('init', array($this, 'register_slider_post_type'));
        
        // Register REST API route
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        
        // Add meta box for slider image
        add_action('add_meta_boxes', array($this, 'add_slider_meta_box'));
        add_action('save_post', array($this, 'save_slider_meta'));
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    // Register custom post type for sliders
    public function register_slider_post_type() {
        $args = array(
            'public' => true,
            'label'  => 'Sliders',
            'show_in_rest' => true,
            'supports' => array('title', 'editor'),
            'menu_icon' => 'dashicons-images-alt2',
        );
        register_post_type('custom_slider', $args);
    }

    // Add meta box for slider image
    public function add_slider_meta_box() {
        add_meta_box(
            'slider_image_meta_box',
            'Slider Image',
            array($this, 'render_slider_meta_box'),
            'custom_slider',
            'normal',
            'high'
        );
    }

    // Render meta box content
    public function render_slider_meta_box($post) {
        wp_nonce_field('save_slider_meta', 'slider_meta_nonce');
        
        $image_id = get_post_meta($post->ID, '_slider_image_id', true);
        $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
        
        echo '<div class="slider-image-container">';
        if ($image_url) {
            echo '<img src="' . esc_url($image_url) . '" style="max-width:100%; height:auto; display:block; margin-bottom:10px;">';
        }
        echo '</div>';
        
        echo '<input type="hidden" id="slider_image_id" name="slider_image_id" value="' . esc_attr($image_id) . '">';
        echo '<button type="button" class="button" id="upload_slider_image">Select Image</button>';
        echo '<button type="button" class="button" id="remove_slider_image" style="' . ($image_id ? '' : 'display:none;') . '">Remove Image</button>';
        
        // JavaScript for media uploader
        ?>
        <script>
        jQuery(document).ready(function($) {
            var frame;
            
            $('#upload_slider_image').on('click', function(e) {
                e.preventDefault();
                
                if (frame) {
                    frame.open();
                    return;
                }
                
                frame = wp.media({
                    title: 'Select or Upload Slider Image',
                    button: { text: 'Use this image' },
                    multiple: false
                });
                
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#slider_image_id').val(attachment.id);
                    $('.slider-image-container').html('<img src="' + attachment.url + '" style="max-width:100%; height:auto; display:block; margin-bottom:10px;">');
                    $('#remove_slider_image').show();
                });
                
                frame.open();
            });
            
            $('#remove_slider_image').on('click', function(e) {
                e.preventDefault();
                $('#slider_image_id').val('');
                $('.slider-image-container').html('');
                $(this).hide();
            });
        });
        </script>
        <?php
    }

    // Save slider meta data
    public function save_slider_meta($post_id) {
        if (!isset($_POST['slider_meta_nonce']) || !wp_verify_nonce($_POST['slider_meta_nonce'], 'save_slider_meta')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        if (isset($_POST['slider_image_id'])) {
            update_post_meta($post_id, '_slider_image_id', sanitize_text_field($_POST['slider_image_id']));
        }
    }

    // Register REST API routes
    public function register_rest_routes() {
        register_rest_route('custom/v1', '/sliders', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_sliders_data'),
            'permission_callback' => '__return_true'
        ));
    }

    // Get sliders data for REST API
    public function get_sliders_data() {
        $args = array(
            'post_type' => 'custom_slider',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        );
        
        $sliders = get_posts($args);
        $data = array();
        
        foreach ($sliders as $slider) {
            $image_id = get_post_meta($slider->ID, '_slider_image_id', true);
            $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
            
            // Clean the content by removing block comments and HTML tags
            $clean_content = $this->clean_content($slider->post_content);
            
            $data[] = array(
                'title' => $slider->post_title,
                'content' => $clean_content,
                'image' => $image_url
            );
        }
        
        return $data;
    }

    // Clean content by removing block comments and HTML tags
    private function clean_content($content) {
        // Remove block comments
        $content = preg_replace('/<!-- \/?wp:.* -->/', '', $content);
        
        // Convert HTML entities
        $content = html_entity_decode($content);
        
        // Strip HTML tags
        $content = wp_strip_all_tags($content);
        
        // Trim whitespace
        $content = trim($content);
        
        return $content;
    }

    // Add admin menu
    public function add_admin_menu() {
        add_menu_page(
            'Sliders',
            'Sliders',
            'manage_options',
            'edit.php?post_type=custom_slider',
            '',
            'dashicons-images-alt2',
            20
        );
    }
}

// Initialize the plugin
new Custom_Slider_Plugin();
