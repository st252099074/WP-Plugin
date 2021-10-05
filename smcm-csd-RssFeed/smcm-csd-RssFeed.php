<?php
/*
Plugin Name: SMCM CSD RSS Feed
Description: Add InsideSMCM CSD event to www.smcm.edu/democracy site
Version: 1.0
Author: Tu Shi
License: GPL2
*/

/* Prevent direct access to the plugin */
if ( !defined( 'ABSPATH' ) ) {
    die( "Sorry, you are not allowed to access this page directly." );
}

    function feed_rss(){
     try {    
           $rss = fetch_feed('https://inside.smcm.edu/events/democracy/feed');
            foreach (array_reverse($rss->get_items(0, 20)) as $item){
            $html .='<a href="'.$item->get_permalink().'" target="_blank"><h4>' . $item->get_title() . '</a></h4></a>';
            $html .='<p>'. $item->get_date('F j Y | g:i a') . '<br></p>';
            } 
            return $html;     
         }
        
     catch(Exception $e) {
     echo 'Error importing events: ',  $e->getMessage(), "\n";
    }      
 }
    add_shortcode( 'feed_events', 'feed_rss' );