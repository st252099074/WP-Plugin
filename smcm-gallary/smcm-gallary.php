<?php
/*
Plugin Name: SMCM Gallary
Plugin URI: http://www.smcm.edu
Description: Includes "Gallary" custom post type
Version: 1.0
Author: Tu Shi
License: GPL2
*/

/* Prevent direct access to the plugin */
if ( !defined( 'ABSPATH' ) ) {
    die( "Sorry, you are not allowed to access this page directly." );
}


// Register the custom post type
add_action( 'genesis_init', 'smcm_gallary_custom_post_types');
flush_rewrite_rules( true );
function smcm_gallary_custom_post_types(){

    $smcm_gallary_labels = array(
        'name'               => 'Exhibitions',
        'singular_name'      => 'Exhibitions',
        'add_new'            => 'Add Exhibitions',
        'add_new_item'       => 'Add New Exhibition Items',
        'edit_item'          => 'Edit Exhibitions',
        'new_item'           => 'New Exhibitions',
        'all_items'          => 'All Exhibitions',
        'view_item'          => 'View Exhibitions',
        'search_items'       => 'Search Exhibitions',
        'not_found'          => 'No Exhibitions found',
        'not_found_in_trash' => 'No Exhibitions found in Trash',
        'parent_item_colon'  => '',
        'menu_name'          => 'Exhibitions',

    );

    $smcm_gallary_args = array(
        'labels'             => $smcm_gallary_labels,
        'public'             => true,
        'rewrite'            => array( 'slug' => 'exhibitions' ),
        'has_archive'        => true,
        'menu_position'      => 5,
        'menu_icon' 		 => 'dashicons-media-text',
        'supports'           => array( 'title', 'author', 'thumbnail','tag', 'editor','excerpt', 'custom-fields', 'post-formats' )
    );
    register_post_type( 'exhibitions', $smcm_gallary_args );

 }



function wpse_allowedtags() {
    // Add custom tags to this string
    return '<script>,<style>,<br>,<em>,<i>,<ul>,<ol>,<li>,<a>,<p>,<img>,<video>,<audio>';
}

if ( ! function_exists( 'wpse_custom_wp_trim_excerpt' ) ) :

    function wpse_custom_wp_trim_excerpt($wpse_excerpt) {
        $raw_excerpt = $wpse_excerpt;
        if ( '' == $wpse_excerpt ) {

            $wpse_excerpt = get_the_content('');
            $wpse_excerpt = strip_shortcodes( $wpse_excerpt );
            $wpse_excerpt = apply_filters('the_content', $wpse_excerpt);
            $wpse_excerpt = str_replace(']]>', ']]&gt;', $wpse_excerpt);
            $wpse_excerpt = strip_tags($wpse_excerpt, wpse_allowedtags()); /*IF you need to allow just certain tags. Delete if all tags are allowed */

            //Set the excerpt word count and only break after sentence is complete.
            $excerpt_word_count = 75;
            $excerpt_length = apply_filters('excerpt_length', $excerpt_word_count);
            $tokens = array();
            $excerptOutput = '';
            $count = 0;

            // Divide the string into tokens; HTML tags, or words, followed by any whitespace
            preg_match_all('/(<[^>]+>|[^<>\s]+)\s*/u', $wpse_excerpt, $tokens);

            foreach ($tokens[0] as $token) {

                if ($count >= $excerpt_length && preg_match('/[\,\;\?\.\!]\s*$/uS', $token)) {
                    // Limit reached, continue until , ; ? . or ! occur at the end
                    $excerptOutput .= trim($token);
                    break;
                }

                // Add words to complete sentence
                $count++;

                // Append what's left of the token
                $excerptOutput .= $token;
            }

            $wpse_excerpt = trim(force_balance_tags($excerptOutput));

            $excerpt_end = ' <a href="'. esc_url( get_permalink() ) . '">' . '&nbsp;&raquo;&nbsp;' . sprintf(__( 'Read more about: %s &nbsp;&raquo;', 'wpse' ), get_the_title()) . '</a>';
            $excerpt_more = apply_filters('excerpt_more', ' ' . $excerpt_end);

            // After the content
            $wpse_excerpt .= $excerpt_more; /*Add read more in new paragraph */

            return $wpse_excerpt;

        }
        return apply_filters('wpse_custom_wp_trim_excerpt', $wpse_excerpt, $raw_excerpt);
    }

endif;

remove_filter('get_the_excerpt', 'wp_trim_excerpt');
add_filter('get_the_excerpt', 'wpse_custom_wp_trim_excerpt');



//adding a short code to display the current exhibitions
function current_exhibitions($atts) {
    date_default_timezone_set("American/New_York");

    $args = array(
        'post_type' => 'exhibitions'
    );

    $date = date("h:i:sa");
    $date = strtotime($date);


    $query = new WP_Query($args);

    while($query->have_posts()) :
        $query->the_post();
        $link = get_permalink();
        $title = get_the_title();
        $custom = get_post_custom();
        $opening_date = $custom['opening_date'][0];
        $closing_date = $custom['closing_date'][0];
        $opening_date = strtotime($opening_date);
        $closing_date =  strtotime($closing_date);
        $exhibition_image =  get_field('exhibition_image');

        if( $date >=  $opening_date && $date <= $closing_date) {
            $content .= '<div class="latest-posts">';
            $content .= '<h3><a href=' . $link . ' target="_top">' . $title . '</a></h3>';
            $content .= '<div class="row"><div class="one-half first"><div><em>' .  date("F j Y" ,$opening_date) . '-' . date("F j Y" ,$closing_date) . '</em><br><br>' .
            '<div>' .get_the_excerpt() .'</div>'. '</div></div>';
            $content .=  '<div class="one-half first"><div><img src='. esc_url($exhibition_image['url']) . ' alt='. esc_attr($exhibition_image['alt'])  .'; /></div></div></div>';
        }

     endwhile;
    wp_reset_postdata();
     return $content;
}

add_shortcode('current_exhibitions', 'current_exhibitions');





add_action('acf/save_post', 'test_update_post_date_from_acf', 20);
function test_update_post_date_from_acf($post_id) {
    // remove this filter to prevent potential infinite loop
    remove_filter('acf/save_post', 'test_update_post_date_from_acf', 20);
    // date format must be "Y-m-d H:i:s"
    $post_date = get_field('opening_date');
    $acfDateUnix = strtotime($post_date);
    $newDate = date('Y-m-d H:i:s', $acfDateUnix);

    $post = wp_update_post(array(
        'ID' => $post_id,
        'post_date' => $newDate));
}

