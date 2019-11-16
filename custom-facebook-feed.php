<?php 
/*
Plugin Name: Smash Balloon Custom Facebook Feed
Plugin URI: https://smashballoon.com/custom-facebook-feed
Description: Add completely customizable Facebook feeds to your WordPress site
Version: 2.12.1
Author: Smash Balloon
Author URI: http://smashballoon.com/
License: GPLv2 or later
Text Domain: custom-facebook-feed
*/
/* 
Copyright 2019  Smash Balloon LLC (email : hey@smashballoon.com)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('CFFVER', '2.12.1');

// Db version.
if ( ! defined( 'CFF_DBVERSION' ) ) {
    define( 'CFF_DBVERSION', '1.0' );
}

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
//Include admin
include dirname( __FILE__ ) .'/custom-facebook-feed-admin.php';

function cff_check_for_db_updates() {
    $db_ver = get_option( 'cff_db_version', 0 );
    if ( (float) $db_ver < 1.0 ) {
        global $wp_roles;
        $wp_roles->add_cap( 'administrator', 'manage_custom_facebook_feed_options' );
        $cff_statuses_option = get_option( 'cff_statuses', array() );
        if ( ! isset( $cff_statuses_option['first_install'] ) ) {
            $options_set = get_option( 'cff_page_id', false );
            if ( $options_set ) {
                $cff_statuses_option['first_install'] = 'from_update';
            } else {
                $cff_statuses_option['first_install'] = time();
            }
            $cff_rating_notice_option = get_option( 'cff_rating_notice', false );
            if ( $cff_rating_notice_option === 'dismissed' ) {
                $cff_statuses_option['rating_notice_dismissed'] = time();
            }
            $cff_rating_notice_waiting = get_transient( 'custom_facebook_rating_notice_waiting' );
            if ( $cff_rating_notice_waiting === false
                 && $cff_rating_notice_option === false ) {
                $time = 2 * WEEK_IN_SECONDS;
                set_transient( 'custom_facebook_rating_notice_waiting', 'waiting', $time );
                update_option( 'cff_rating_notice', 'pending', false );
            }
            update_option( 'cff_statuses', $cff_statuses_option, false );
        }
        update_option( 'cff_db_version', CFF_DBVERSION );
    }
}
add_action( 'wp_loaded', 'cff_check_for_db_updates' );


// Add shortcodes
add_shortcode('custom-facebook-feed', 'display_cff');
function display_cff($atts) {
    
    //Style options
    $options = get_option('cff_style_settings');
    //Create the types string to set as shortcode default
    $include_string = '';
    if($options[ 'cff_show_author' ]) $include_string .= 'author,';
    if($options[ 'cff_show_text' ]) $include_string .= 'text,';
    if($options[ 'cff_show_desc' ]) $include_string .= 'desc,';
    if($options[ 'cff_show_shared_links' ]) $include_string .= 'sharedlinks,';
    if($options[ 'cff_show_date' ]) $include_string .= 'date,';
    if($options[ 'cff_show_media' ]) $include_string .= 'media,';
    if( isset($options[ 'cff_show_media_link' ]) ){ //If not set yet then show link by default
        if($options[ 'cff_show_media_link' ]) $include_string .= 'medialink,';
    } else {
        $include_string .= 'medialink,';
    }    
    if($options[ 'cff_show_event_title' ]) $include_string .= 'eventtitle,';
    if($options[ 'cff_show_event_details' ]) $include_string .= 'eventdetails,';
    if($options[ 'cff_show_meta' ]) $include_string .= 'social,';
    if($options[ 'cff_show_link' ]) $include_string .= 'link,';
    if($options[ 'cff_show_like_box' ]) $include_string .= 'likebox,';
    //Pass in shortcode attrbutes
    $atts = shortcode_atts(
    array(
        'accesstoken' => trim( get_option('cff_access_token') ),
        'id' => get_option('cff_page_id'),
        'pagetype' => get_option('cff_page_type'),
        'num' => get_option('cff_num_show'),
        'limit' => get_option('cff_post_limit'),
        'others' => '',
        'showpostsby' => get_option('cff_show_others'),
        'cachetime' => get_option('cff_cache_time'),
        'cacheunit' => get_option('cff_cache_time_unit'),
        'locale' => get_option('cff_locale'),
        'ajax' => get_option('cff_ajax'),
        'offset' => '',
        'account' => '',

        //General
        'width' => isset($options[ 'cff_feed_width' ]) ? $options[ 'cff_feed_width' ] : '',
        'widthresp' => isset($options[ 'cff_feed_width_resp' ]) ? $options[ 'cff_feed_width_resp' ] : '',
        'height' => isset($options[ 'cff_feed_height' ]) ? $options[ 'cff_feed_height' ] : '',
        'padding' => isset($options[ 'cff_feed_padding' ]) ? $options[ 'cff_feed_padding' ] : '',
        'bgcolor' => isset($options[ 'cff_bg_color' ]) ? $options[ 'cff_bg_color' ] : '',
        'showauthor' => '',
        'showauthornew' => isset($options[ 'cff_show_author' ]) ? $options[ 'cff_show_author' ] : '',
        'class' => isset($options[ 'cff_class' ]) ? $options[ 'cff_class' ] : '',
        'layout' => isset($options[ 'cff_preset_layout' ]) ? $options[ 'cff_preset_layout' ] : '',
        'include' => $include_string,
        'exclude' => '',

        //Cols
        'cols' => isset($options[ 'cff_cols' ]) ? $options[ 'cff_cols' ] : '',
        'colsmobile' => isset($options[ 'cff_cols_mobile' ]) ? $options[ 'cff_cols_mobile' ] : '',
        'colsjs' => true,

        //Post Style
        'poststyle' => isset($options[ 'cff_post_style' ]) ? $options[ 'cff_post_style' ] : '',
        'postbgcolor' => isset($options[ 'cff_post_bg_color' ]) ? $options[ 'cff_post_bg_color' ] : '',
        'postcorners' => isset($options[ 'cff_post_rounded' ]) ? $options[ 'cff_post_rounded' ] : '',
        'boxshadow' => isset($options[ 'cff_box_shadow' ]) ? $options[ 'cff_box_shadow' ] : '',

        //Typography
        'textformat' => isset($options[ 'cff_title_format' ]) ? $options[ 'cff_title_format' ] : '',
        'textsize' => isset($options[ 'cff_title_size' ]) ? $options[ 'cff_title_size' ] : '',
        'textweight' => isset($options[ 'cff_title_weight' ]) ? $options[ 'cff_title_weight' ] : '',
        'textcolor' => isset($options[ 'cff_title_color' ]) ? $options[ 'cff_title_color' ] : '',
        'textlinkcolor' => isset($options[ 'cff_posttext_link_color' ]) ? $options[ 'cff_posttext_link_color' ] : '',
        'textlink' => isset($options[ 'cff_title_link' ]) ? $options[ 'cff_title_link' ] : '',
        'posttags' => isset($options[ 'cff_post_tags' ]) ? $options[ 'cff_post_tags' ] : '',
        'linkhashtags' => isset($options[ 'cff_link_hashtags' ]) ? $options[ 'cff_link_hashtags' ] : '',

        //Description
        'descsize' => isset($options[ 'cff_body_size' ]) ? $options[ 'cff_body_size' ] : '',
        'descweight' => isset($options[ 'cff_body_weight' ]) ? $options[ 'cff_body_weight' ] : '',
        'desccolor' => isset($options[ 'cff_body_color' ]) ? $options[ 'cff_body_color' ] : '',
        'linktitleformat' => isset($options[ 'cff_link_title_format' ]) ? $options[ 'cff_link_title_format' ] : '',
        'linktitlesize' => isset($options[ 'cff_link_title_size' ]) ? $options[ 'cff_link_title_size' ] : '',
        'linkdescsize' => isset($options[ 'cff_link_desc_size' ]) ? $options[ 'cff_link_desc_size' ] : '',
        'linkurlsize' => isset($options[ 'cff_link_url_size' ]) ? $options[ 'cff_link_url_size' ] : '',
        'linkdesccolor' => isset($options[ 'cff_link_desc_color' ]) ? $options[ 'cff_link_desc_color' ] : '',
        'linktitlecolor' => isset($options[ 'cff_link_title_color' ]) ? $options[ 'cff_link_title_color' ] : '',
        'linkurlcolor' => isset($options[ 'cff_link_url_color' ]) ? $options[ 'cff_link_url_color' ] : '',
        'linkbgcolor' => isset($options[ 'cff_link_bg_color' ]) ? $options[ 'cff_link_bg_color' ] : '',
        'linkbordercolor' => isset($options[ 'cff_link_border_color' ]) ? $options[ 'cff_link_border_color' ] : '',
        'disablelinkbox' => isset($options[ 'cff_disable_link_box' ]) ? $options[ 'cff_disable_link_box' ] : '',

        //Author
        'authorsize' => isset($options[ 'cff_author_size' ]) ? $options[ 'cff_author_size' ] : '',
        'authorcolor' => isset($options[ 'cff_author_color' ]) ? $options[ 'cff_author_color' ] : '',

        //Event title
        'eventtitleformat' => isset($options[ 'cff_event_title_format' ]) ? $options[ 'cff_event_title_format' ] : '',
        'eventtitlesize' => isset($options[ 'cff_event_title_size' ]) ? $options[ 'cff_event_title_size' ] : '',
        'eventtitleweight' => isset($options[ 'cff_event_title_weight' ]) ? $options[ 'cff_event_title_weight' ] : '',
        'eventtitlecolor' => isset($options[ 'cff_event_title_color' ]) ? $options[ 'cff_event_title_color' ] : '',
        'eventtitlelink' => isset($options[ 'cff_event_title_link' ]) ? $options[ 'cff_event_title_link' ] : '',
        //Event date
        'eventdatesize' => isset($options[ 'cff_event_date_size' ]) ? $options[ 'cff_event_date_size' ] : '',
        'eventdateweight' => isset($options[ 'cff_event_date_weight' ]) ? $options[ 'cff_event_date_weight' ] : '',
        'eventdatecolor' => isset($options[ 'cff_event_date_color' ]) ? $options[ 'cff_event_date_color' ] : '',
        'eventdatepos' => isset($options[ 'cff_event_date_position' ]) ? $options[ 'cff_event_date_position' ] : '',
        'eventdateformat' => isset($options[ 'cff_event_date_formatting' ]) ? $options[ 'cff_event_date_formatting' ] : '',
        'eventdatecustom' => isset($options[ 'cff_event_date_custom' ]) ? $options[ 'cff_event_date_custom' ] : '',
        //Event details
        'eventdetailssize' => isset($options[ 'cff_event_details_size' ]) ? $options[ 'cff_event_details_size' ] : '',
        'eventdetailsweight' => isset($options[ 'cff_event_details_weight' ]) ? $options[ 'cff_event_details_weight' ] : '',
        'eventdetailscolor' => isset($options[ 'cff_event_details_color' ]) ? $options[ 'cff_event_details_color' ] : '',
        'eventlinkcolor' => isset($options[ 'cff_event_link_color' ]) ? $options[ 'cff_event_link_color' ] : '',
        //Date
        'datepos' => isset($options[ 'cff_date_position' ]) ? $options[ 'cff_date_position' ] : '',
        'datesize' => isset($options[ 'cff_date_size' ]) ? $options[ 'cff_date_size' ] : '',
        'dateweight' => isset($options[ 'cff_date_weight' ]) ? $options[ 'cff_date_weight' ] : '',
        'datecolor' => isset($options[ 'cff_date_color' ]) ? $options[ 'cff_date_color' ] : '',
        'dateformat' => isset($options[ 'cff_date_formatting' ]) ? $options[ 'cff_date_formatting' ] : '',
        'datecustom' => isset($options[ 'cff_date_custom' ]) ? $options[ 'cff_date_custom' ] : '',
        'timezone' => isset($options[ 'cff_timezone' ]) ? $options[ 'cff_timezone' ] : 'America/Chicago',

        //Link to Facebook
        'linksize' => isset($options[ 'cff_link_size' ]) ? $options[ 'cff_link_size' ] : '',
        'linkweight' => isset($options[ 'cff_link_weight' ]) ? $options[ 'cff_link_weight' ] : '',
        'linkcolor' => isset($options[ 'cff_link_color' ]) ? $options[ 'cff_link_color' ] : '',
        'viewlinktext' => isset($options[ 'cff_view_link_text' ]) ? $options[ 'cff_view_link_text' ] : '',
        'linktotimeline' => isset($options[ 'cff_link_to_timeline' ]) ? $options[ 'cff_link_to_timeline' ] : '',
        //Social
        'iconstyle' => isset($options[ 'cff_icon_style' ]) ? $options[ 'cff_icon_style' ] : '',
        'socialtextcolor' => isset($options[ 'cff_meta_text_color' ]) ? $options[ 'cff_meta_text_color' ] : '',
        'socialbgcolor' => isset($options[ 'cff_meta_bg_color' ]) ? $options[ 'cff_meta_bg_color' ] : '',
        //Misc
        'textlength' => get_option('cff_title_length'),
        'desclength' => get_option('cff_body_length'),
        'likeboxpos' => isset($options[ 'cff_like_box_position' ]) ? $options[ 'cff_like_box_position' ] : '',
        'likeboxoutside' => isset($options[ 'cff_like_box_outside' ]) ? $options[ 'cff_like_box_outside' ] : '',
        'likeboxcolor' => isset($options[ 'cff_likebox_bg_color' ]) ? $options[ 'cff_likebox_bg_color' ] : '',
        'likeboxtextcolor' => isset($options[ 'cff_like_box_text_color' ]) ? $options[ 'cff_like_box_text_color' ] : '',
        'likeboxwidth' => isset($options[ 'cff_likebox_width' ]) ? $options[ 'cff_likebox_width' ] : '',
        'likeboxheight' => isset($options[ 'cff_likebox_height' ]) ? $options[ 'cff_likebox_height' ] : '',
        'likeboxfaces' => isset($options[ 'cff_like_box_faces' ]) ? $options[ 'cff_like_box_faces' ] : '',
        'likeboxborder' => isset($options[ 'cff_like_box_border' ]) ? $options[ 'cff_like_box_border' ] : '',
        'likeboxcover' => isset($options[ 'cff_like_box_cover' ]) ? $options[ 'cff_like_box_cover' ] : '',
        'likeboxsmallheader' => isset($options[ 'cff_like_box_small_header' ]) ? $options[ 'cff_like_box_small_header' ] : '',
        'likeboxhidebtn' => isset($options[ 'cff_like_box_hide_cta' ]) ? $options[ 'cff_like_box_hide_cta' ] : '',

        'credit' => isset($options[ 'cff_show_credit' ]) ? $options[ 'cff_show_credit' ] : '',
        'nofollow' => 'true',
        'disablestyles' => isset($options[ 'cff_disable_styles' ]) ? $options[ 'cff_disable_styles' ] : '',
        'textissue' => isset($options[ 'cff_format_issue' ]) ? $options[ 'cff_format_issue' ] : '',
        'restrictedpage' => isset($options[ 'cff_restricted_page' ]) ? $options[ 'cff_restricted_page' ] : '',

        //Page Header
        'showheader' => isset($options[ 'cff_show_header' ]) ? $options[ 'cff_show_header' ] : '',
        'headeroutside' => isset($options[ 'cff_header_outside' ]) ? $options[ 'cff_header_outside' ] : '',
        'headertext' => isset($options[ 'cff_header_text' ]) ? $options[ 'cff_header_text' ] : '',
        'headerbg' => isset($options[ 'cff_header_bg_color' ]) ? $options[ 'cff_header_bg_color' ] : '',
        'headerpadding' => isset($options[ 'cff_header_padding' ]) ? $options[ 'cff_header_padding' ] : '',
        'headertextsize' => isset($options[ 'cff_header_text_size' ]) ? $options[ 'cff_header_text_size' ] : '',
        'headertextweight' => isset($options[ 'cff_header_text_weight' ]) ? $options[ 'cff_header_text_weight' ] : '',
        'headertextcolor' => isset($options[ 'cff_header_text_color' ]) ? $options[ 'cff_header_text_color' ] : '',
        'headericon' => isset($options[ 'cff_header_icon' ]) ? $options[ 'cff_header_icon' ] : '',
        'headericoncolor' => isset($options[ 'cff_header_icon_color' ]) ? $options[ 'cff_header_icon_color' ] : '',
        'headericonsize' => isset($options[ 'cff_header_icon_size' ]) ? $options[ 'cff_header_icon_size' ] : '',

        'videoheight' => isset($options[ 'cff_video_height' ]) ? $options[ 'cff_video_height' ] : '',
        'videoaction' => isset($options[ 'cff_video_action' ]) ? $options[ 'cff_video_action' ] : '',
        'sepcolor' => isset($options[ 'cff_sep_color' ]) ? $options[ 'cff_sep_color' ] : '',
        'sepsize' => isset($options[ 'cff_sep_size' ]) ? $options[ 'cff_sep_size' ] : '',

        //Translate
        'seemoretext' => isset( $options[ 'cff_see_more_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_see_more_text' ] ) ) : '',
        'seelesstext' => isset( $options[ 'cff_see_less_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_see_less_text' ] ) ) : '',
        'photostext' => isset( $options[ 'cff_translate_photos_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_photos_text' ] ) ) : '',
        'phototext' => isset( $options[ 'cff_translate_photo_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_photo_text' ] ) ) : '',
        'videotext' => isset( $options[ 'cff_translate_video_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_video_text' ] ) ) : '', 

        'learnmoretext' => isset( $options[ 'cff_translate_learn_more_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_learn_more_text' ] ) ) : '',    
        'shopnowtext' => isset( $options[ 'cff_translate_shop_now_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_shop_now_text' ] ) ) : '',    
        'messagepage' => isset( $options[ 'cff_translate_message_page_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_message_page_text' ] ) ) : '',     

        'facebooklinktext' => isset( $options[ 'cff_facebook_link_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_facebook_link_text' ] ) ) : '',
        'sharelinktext' => isset( $options[ 'cff_facebook_share_text' ] ) ? stripslashes( esc_attr( $options[ 'cff_facebook_share_text' ] ) ) : '',
        'showfacebooklink' => isset($options[ 'cff_show_facebook_link' ]) ? $options[ 'cff_show_facebook_link' ] : '',
        'showsharelink' => isset($options[ 'cff_show_facebook_share' ]) ? $options[ 'cff_show_facebook_share' ] : '',

        'secondtext' => isset( $options[ 'cff_translate_second' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_second' ] ) ) : 'second',
        'secondstext' => isset( $options[ 'cff_translate_seconds' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_seconds' ] ) ) : 'seconds',
        'minutetext' => isset( $options[ 'cff_translate_minute' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_minute' ] ) ) : 'minute',
        'minutestext' => isset( $options[ 'cff_translate_minutes' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_minutes' ] ) ) : 'minutes',
        'hourtext' => isset( $options[ 'cff_translate_hour' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_hour' ] ) ) : 'hour',
        'hourstext' => isset( $options[ 'cff_translate_hours' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_hours' ] ) ) : 'hours',
        'daytext' => isset( $options[ 'cff_translate_day' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_day' ] ) ) : 'day',
        'daystext' => isset( $options[ 'cff_translate_days' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_days' ] ) ) : 'days',
        'weektext' => isset( $options[ 'cff_translate_week' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_week' ] ) ) : 'week',
        'weekstext' => isset( $options[ 'cff_translate_weeks' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_weeks' ] ) ) : 'weeks',
        'monthtext' => isset( $options[ 'cff_translate_month' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_month' ] ) ) : 'month',
        'monthstext' => isset( $options[ 'cff_translate_months' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_months' ] ) ) : 'months',
        'yeartext' => isset( $options[ 'cff_translate_year' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_year' ] ) ) : 'year',
        'yearstext' => isset( $options[ 'cff_translate_years' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_years' ] ) ) : 'years',
        'agotext' => isset( $options[ 'cff_translate_ago' ] ) ? stripslashes( esc_attr( $options[ 'cff_translate_ago' ] ) ) : 'ago'

    ), $atts);

    /********** GENERAL **********/
    $cff_page_type = $atts[ 'pagetype' ];
    ($cff_page_type == 'group') ? $cff_is_group = true : $cff_is_group = false;

    $cff_feed_width = $atts[ 'width' ];
    if ( is_numeric(substr($cff_feed_width, -1, 1)) ) $cff_feed_width = $cff_feed_width . 'px';

    //Set to be 100% width on mobile?
    $cff_feed_width_resp = $atts[ 'widthresp' ];
    ( $cff_feed_width_resp == 'on' || $cff_feed_width_resp == 'true' || $cff_feed_width_resp == true ) ? $cff_feed_width_resp = true : $cff_feed_width_resp = false;
    if( $atts[ 'widthresp' ] == 'false' ) $cff_feed_width_resp = false;

    $cff_feed_height = $atts[ 'height' ];
    if ( is_numeric(substr($cff_feed_height, -1, 1)) ) $cff_feed_height = $cff_feed_height . 'px';

    $cff_feed_padding = $atts[ 'padding' ];
    if ( is_numeric(substr($cff_feed_padding, -1, 1)) ) $cff_feed_padding = $cff_feed_padding . 'px';

    $cff_bg_color = $atts[ 'bgcolor' ];
    $cff_show_author = $atts[ 'showauthornew' ];
    $cff_cache_time = $atts[ 'cachetime' ];
    $cff_locale = $atts[ 'locale' ];
    if ( empty($cff_locale) || !isset($cff_locale) || $cff_locale == '' ) $cff_locale = 'en_US';
    if (!isset($cff_cache_time)) $cff_cache_time = '0';
    $cff_cache_time_unit = $atts[ 'cacheunit' ];

    if($cff_cache_time == 'nocaching') $cff_cache_time = 0;

    $cff_class = $atts['class'];
    //Compile feed styles
    $cff_feed_styles = '';
    if( !empty($cff_feed_width) || !empty($cff_feed_height) || !empty($cff_feed_padding) || (!empty($cff_bg_color) && $cff_bg_color !== '#') ) $cff_feed_styles = 'style="';
    if ( !empty($cff_feed_width) ) $cff_feed_styles .= 'width:' . $cff_feed_width . '; ';
    if ( !empty($cff_feed_height) ) $cff_feed_styles .= 'height:' . $cff_feed_height . '; ';
    if ( !empty($cff_feed_padding) ) $cff_feed_styles .= 'padding:' . $cff_feed_padding . '; ';
    if ( !empty($cff_bg_color) && $cff_bg_color !== '#' ) $cff_feed_styles .= 'background-color:#' . str_replace('#', '', $cff_bg_color) . '; ';
    if( !empty($cff_feed_width) || !empty($cff_feed_height) || !empty($cff_feed_padding) || (!empty($cff_bg_color) && $cff_bg_color !== '#') ) $cff_feed_styles .= '"';
    //Like box
    $cff_like_box_position = $atts[ 'likeboxpos' ];
    $cff_like_box_outside = $atts[ 'likeboxoutside' ];
    //Open links in new window?
    $target = 'target="_blank"';
    /********** POST TYPES **********/
    $cff_show_links_type = true;
    $cff_show_event_type = true;
    $cff_show_video_type = true;
    $cff_show_photos_type = true;
    $cff_show_status_type = true;
    $cff_show_albums_type = true;
    $cff_events_only = false;
    //Are we showing ONLY events?
    if ($cff_show_event_type && !$cff_show_links_type && !$cff_show_video_type && !$cff_show_photos_type && !$cff_show_status_type) $cff_events_only = true;
    /********** LAYOUT **********/
    $cff_includes = $atts[ 'include' ];
    //Look for non-plural version of string in the types string in case user specifies singular in shortcode
    $cff_show_author = false;
    $cff_show_text = false;
    $cff_show_desc = false;
    $cff_show_shared_links = false;
    $cff_show_date = false;
    $cff_show_media = false;
    $cff_show_media_link = false;
    $cff_show_event_title = false;
    $cff_show_event_details = false;
    $cff_show_meta = false;
    $cff_show_link = false;
    $cff_show_like_box = false;
    if ( stripos($cff_includes, 'author') !== false ) $cff_show_author = true;
    if ( stripos($cff_includes, 'text') !== false ) $cff_show_text = true;
    if ( stripos($cff_includes, 'desc') !== false ) $cff_show_desc = true;
    if ( stripos($cff_includes, 'sharedlink') !== false ) $cff_show_shared_links = true;
    if ( stripos($cff_includes, 'date') !== false ) $cff_show_date = true;
    if ( stripos($cff_includes, 'media') !== false ) $cff_show_media = true;
    if ( stripos($cff_includes, 'medialink') !== false ) $cff_show_media_link = true;
    if ( stripos($cff_includes, 'eventtitle') !== false ) $cff_show_event_title = true;
    if ( stripos($cff_includes, 'eventdetail') !== false ) $cff_show_event_details = true;
    if ( stripos($cff_includes, 'social') !== false ) $cff_show_meta = true;
    if ( stripos($cff_includes, ',link') !== false ) $cff_show_link = true; //comma used to separate it from 'sharedlinks' - which also contains 'link' string
    if ( stripos($cff_includes, 'like') !== false ) $cff_show_like_box = true;


    //Exclude string
    $cff_excludes = $atts[ 'exclude' ];
    //Look for non-plural version of string in the types string in case user specifies singular in shortcode
    if ( stripos($cff_excludes, 'author') !== false ) $cff_show_author = false;
    if ( stripos($cff_excludes, 'text') !== false ) $cff_show_text = false;
    if ( stripos($cff_excludes, 'desc') !== false ) $cff_show_desc = false;
    if ( stripos($cff_excludes, 'sharedlink') !== false ) $cff_show_shared_links = false;
    if ( stripos($cff_excludes, 'date') !== false ) $cff_show_date = false;
    if ( stripos($cff_excludes, 'media') !== false ) $cff_show_media = false;
    if ( stripos($cff_excludes, 'medialink') !== false ) $cff_show_media_link = false;
    if ( stripos($cff_excludes, 'eventtitle') !== false ) $cff_show_event_title = false;
    if ( stripos($cff_excludes, 'eventdetail') !== false ) $cff_show_event_details = false;
    if ( stripos($cff_excludes, 'social') !== false ) $cff_show_meta = false;
    if ( stripos($cff_excludes, ',link') !== false ) $cff_show_link = false; //comma used to separate it from 'sharedlinks' - which also contains 'link' string
    if ( stripos($cff_excludes, 'like') !== false ) $cff_show_like_box = false;


    //Set free version to thumb layout by default as layout option not available on settings page
    $cff_preset_layout = 'thumb';

    //If the old shortcode option 'showauthor' is being used then apply it
    $cff_show_author_old = $atts[ 'showauthor' ];
    if( $cff_show_author_old == 'false' ) $cff_show_author = false;
    if( $cff_show_author_old == 'true' ) $cff_show_author = true;

    
    /********** META **********/
    $cff_icon_style = $atts[ 'iconstyle' ];
    $cff_meta_text_color = $atts[ 'socialtextcolor' ];
    $cff_meta_bg_color = $atts[ 'socialbgcolor' ];
    $cff_meta_styles = '';
    if ( !empty($cff_meta_text_color) || ( !empty($cff_meta_bg_color) && $cff_meta_bg_color !== '#' ) ) $cff_meta_styles = 'style="';
    if ( !empty($cff_meta_text_color) ) $cff_meta_styles .= 'color:#' . str_replace('#', '', $cff_meta_text_color) . ';';
    if ( !empty($cff_meta_bg_color) && $cff_meta_bg_color !== '#' ) $cff_meta_styles .= 'background-color:#' . str_replace('#', '', $cff_meta_bg_color) . ';';
    if ( !empty($cff_meta_text_color) || ( !empty($cff_meta_bg_color) && $cff_meta_bg_color !== '#' ) ) $cff_meta_styles .= '"';
    $cff_nocomments_text = isset($options[ 'cff_nocomments_text' ]) ? $options[ 'cff_nocomments_text' ] : '';
    $cff_hide_comments = isset($options[ 'cff_hide_comments' ]) ? $options[ 'cff_hide_comments' ] : '';
    if (!isset($cff_nocomments_text) || empty($cff_nocomments_text)) $cff_hide_comments = true;
    /********** TYPOGRAPHY **********/
    //See More text
    $cff_see_more_text = $atts[ 'seemoretext' ];
    $cff_see_less_text = $atts[ 'seelesstext' ];
    //See Less text
    //Title
    $cff_title_format = $atts[ 'textformat' ];
    if (empty($cff_title_format)) $cff_title_format = 'p';
    $cff_title_size = $atts[ 'textsize' ];
    $cff_title_weight = $atts[ 'textweight' ];
    $cff_title_color = $atts[ 'textcolor' ];

    $cff_title_styles = '';
    if( ( !empty($cff_title_size) && $cff_title_size != 'inherit' ) || ( !empty($cff_title_weight) && $cff_title_weight != 'inherit' ) || ( !empty($cff_title_color) && $cff_title_color !== '#' ) ) $cff_title_styles = 'style="';
        if ( !empty($cff_title_size) && $cff_title_size != 'inherit' ) $cff_title_styles .=  'font-size:' . $cff_title_size . 'px; ';
        if ( !empty($cff_title_weight) && $cff_title_weight != 'inherit' ) $cff_title_styles .= 'font-weight:' . $cff_title_weight . '; ';
        if ( !empty($cff_title_color) && $cff_title_color !== '#' ) $cff_title_styles .= 'color:#' . str_replace('#', '', $cff_title_color) . ';';
    if( ( !empty($cff_title_size) && $cff_title_size != 'inherit' ) || ( !empty($cff_title_weight) && $cff_title_weight != 'inherit' ) || ( !empty($cff_title_color) && $cff_title_color !== '#' ) ) $cff_title_styles .= '"';

    $cff_title_link = $atts[ 'textlink' ];

    $cff_posttext_link_color = str_replace('#', '', $atts['textlinkcolor']);

    ( $cff_title_link == 'on' || $cff_title_link == 'true' || $cff_title_link == true ) ? $cff_title_link = true : $cff_title_link = false;
    if( $atts[ 'textlink' ] == 'false' ) $cff_title_link = false;

    //Author
    $cff_author_size = $atts[ 'authorsize' ];
    $cff_author_color = $atts[ 'authorcolor' ];

    $cff_author_styles = '';
    if( ( !empty($cff_author_size) && $cff_author_size != 'inherit' ) || ( !empty($cff_author_color) && $cff_author_color !== '#' ) ) $cff_author_styles = 'style="';
        if ( !empty($cff_author_size) && $cff_author_size != 'inherit' ) $cff_author_styles .=  'font-size:' . $cff_author_size . 'px; ';
        if ( !empty($cff_author_color) && $cff_author_color !== '#' ) $cff_author_styles .= 'color:#' . str_replace('#', '', $cff_author_color) . ';';
    if( ( !empty($cff_author_size) && $cff_author_size != 'inherit' ) || ( !empty($cff_author_color) && $cff_author_color !== '#' ) ) $cff_author_styles .= '"';

    //Description
    $cff_body_size = $atts[ 'descsize' ];
    $cff_body_weight = $atts[ 'descweight' ];
    $cff_body_color = $atts[ 'desccolor' ];

    $cff_body_styles = '';
    if( ( !empty($cff_body_size) && $cff_body_size != 'inherit' ) || ( !empty($cff_body_weight) && $cff_body_weight != 'inherit' ) || ( !empty($cff_body_color) && $cff_body_color !== '#' ) ) $cff_body_styles = 'style="';
        if ( !empty($cff_body_size) && $cff_body_size != 'inherit' ) $cff_body_styles .=  'font-size:' . $cff_body_size . 'px; ';
        if ( !empty($cff_body_weight) && $cff_body_weight != 'inherit' ) $cff_body_styles .= 'font-weight:' . $cff_body_weight . '; ';
        if ( !empty($cff_body_color) && $cff_body_color !== '#' ) $cff_body_styles .= 'color:#' . str_replace('#', '', $cff_body_color) . ';';
    if( ( !empty($cff_body_size) && $cff_body_size != 'inherit' ) || ( !empty($cff_body_weight) && $cff_body_weight != 'inherit' ) || ( !empty($cff_body_color) && $cff_body_color !== '#' ) ) $cff_body_styles .= '"';

    //Shared link title
    $cff_link_title_format = $atts[ 'linktitleformat' ];
    if (empty($cff_link_title_format)) $cff_link_title_format = 'p';
    $cff_link_title_size = $atts[ 'linktitlesize' ];
    $cff_link_title_color = str_replace('#', '', $atts[ 'linktitlecolor' ]);
    $cff_link_url_color = $atts[ 'linkurlcolor' ];

    $cff_link_title_styles = '';
    if ( !empty($cff_link_title_size) && $cff_link_title_size != 'inherit' ) $cff_link_title_styles =  'style="font-size:' . $cff_link_title_size . 'px;"';

    //Shared link description
    $cff_link_desc_size = $atts[ 'linkdescsize' ];
    $cff_link_desc_color = $atts[ 'linkdesccolor' ];

    //Shared link URL
    $cff_link_url_size = $atts[ 'linkurlsize' ];
    $cff_link_url_color = $atts[ 'linkurlcolor' ];

    //Shared link box
    $cff_link_bg_color = $atts[ 'linkbgcolor' ];
    $cff_link_border_color = $atts[ 'linkbordercolor' ];
    $cff_disable_link_box = $atts['disablelinkbox'];
    ($cff_disable_link_box == 'true' || $cff_disable_link_box == 'on') ? $cff_disable_link_box = true : $cff_disable_link_box = false;
    
    $cff_link_box_styles = '';
    if( !empty($cff_link_border_color) || (!empty($cff_link_bg_color) && $cff_link_bg_color !== '#') ) $cff_link_box_styles = 'style="';
        if ( !empty($cff_link_border_color) ) $cff_link_box_styles .=  'border: 1px solid #' . str_replace('#', '', $cff_link_border_color) . '; ';
        if ( !empty($cff_link_bg_color) && $cff_link_bg_color !== '#' ) $cff_link_box_styles .= 'background-color: #' . str_replace('#', '', $cff_link_bg_color) . ';';
    if( !empty($cff_link_border_color) || (!empty($cff_link_bg_color) && $cff_link_bg_color !== '#') ) $cff_link_box_styles .= '"';

    //Event Title
    $cff_event_title_format = $atts[ 'eventtitleformat' ];
    if (empty($cff_event_title_format)) $cff_event_title_format = 'p';
    $cff_event_title_size = $atts[ 'eventtitlesize' ];
    $cff_event_title_weight = $atts[ 'eventtitleweight' ];
    $cff_event_title_color = $atts[ 'eventtitlecolor' ];

    $cff_event_title_styles = '';
    if( ( !empty($cff_event_title_size) && $cff_event_title_size != 'inherit' ) || ( !empty($cff_event_title_weight) && $cff_event_title_weight != 'inherit' ) || ( !empty($cff_event_title_color) && $cff_event_title_color !== '#' ) ) $cff_event_title_styles = 'style="';
        if ( !empty($cff_event_title_size) && $cff_event_title_size != 'inherit' ) $cff_event_title_styles .=  'font-size:' . $cff_event_title_size . 'px; ';
        if ( !empty($cff_event_title_weight) && $cff_event_title_weight != 'inherit' ) $cff_event_title_styles .= 'font-weight:' . $cff_event_title_weight . '; ';
        if ( !empty($cff_event_title_color) && $cff_event_title_color !== '#' ) $cff_event_title_styles .= 'color:#' . str_replace('#', '', $cff_event_title_color) . ';';
    if( ( !empty($cff_event_title_size) && $cff_event_title_size != 'inherit' ) || ( !empty($cff_event_title_weight) && $cff_event_title_weight != 'inherit' ) || ( !empty($cff_event_title_color) && $cff_event_title_color !== '#' ) ) $cff_event_title_styles .= '"';
    
    $cff_event_title_link = $atts[ 'eventtitlelink' ];
    ( $cff_event_title_link == 'on' || $cff_event_title_link == 'true' || $cff_event_title_link == true ) ? $cff_event_title_link = true : $cff_event_title_link = false;
    if( $atts[ 'eventtitlelink' ] == 'false' ) $cff_event_title_link = false;

    //Event Date
    $cff_event_date_size = $atts[ 'eventdatesize' ];
    $cff_event_date_weight = $atts[ 'eventdateweight' ];
    $cff_event_date_color = $atts[ 'eventdatecolor' ];
    $cff_event_date_position = $atts[ 'eventdatepos' ];
    $cff_event_date_formatting = $atts[ 'eventdateformat' ];
    $cff_event_date_custom = $atts[ 'eventdatecustom' ];

    $cff_event_date_styles = '';
    if( ( !empty($cff_event_date_size) && $cff_event_date_size != 'inherit' ) || ( !empty($cff_event_date_weight) && $cff_event_date_weight != 'inherit' ) || ( !empty($cff_event_date_color) && $cff_event_date_color !== '#' ) ) $cff_event_date_styles = 'style="';
        if ( !empty($cff_event_date_size) && $cff_event_date_size != 'inherit' ) $cff_event_date_styles .=  'font-size:' . $cff_event_date_size . 'px; ';
        if ( !empty($cff_event_date_weight) && $cff_event_date_weight != 'inherit' ) $cff_event_date_styles .= 'font-weight:' . $cff_event_date_weight . '; ';
        if ( !empty($cff_event_date_color) && $cff_event_date_color !== '#' ) $cff_event_date_styles .= 'color:#' . str_replace('#', '', $cff_event_date_color) . ';';
    if( ( !empty($cff_event_date_size) && $cff_event_date_size != 'inherit' ) || ( !empty($cff_event_date_weight) && $cff_event_date_weight != 'inherit' ) || ( !empty($cff_event_date_color) && $cff_event_date_color !== '#' ) ) $cff_event_date_styles .= '"';

    //Event Details
    $cff_event_details_size = $atts[ 'eventdetailssize' ];
    $cff_event_details_weight = $atts[ 'eventdetailsweight' ];
    $cff_event_details_color = $atts[ 'eventdetailscolor' ];
    $cff_event_link_color = $atts[ 'eventlinkcolor' ];

    $cff_event_details_styles = '';
    if( ( !empty($cff_event_details_size) && $cff_event_details_size != 'inherit' ) || ( !empty($cff_event_details_weight) && $cff_event_details_weight != 'inherit' ) || ( !empty($cff_event_details_color) && $cff_event_details_color !== '#' ) ) $cff_event_details_styles = 'style="';
        if ( !empty($cff_event_details_size) && $cff_event_details_size != 'inherit' ) $cff_event_details_styles .=  'font-size:' . $cff_event_details_size . 'px; ';
        if ( !empty($cff_event_details_weight) && $cff_event_details_weight != 'inherit' ) $cff_event_details_styles .= 'font-weight:' . $cff_event_details_weight . '; ';
        if ( !empty($cff_event_details_color) && $cff_event_details_color !== '#' ) $cff_event_details_styles .= 'color:#' . str_replace('#', '', $cff_event_details_color) . ';';
    if( ( !empty($cff_event_details_size) && $cff_event_details_size != 'inherit' ) || ( !empty($cff_event_details_weight) && $cff_event_details_weight != 'inherit' ) || ( !empty($cff_event_details_color) && $cff_event_details_color !== '#' ) ) $cff_event_details_styles .= '"';

    //Date
    $cff_date_position = $atts[ 'datepos' ];
    if (!isset($cff_date_position)) $cff_date_position = 'below';
    $cff_date_size = $atts[ 'datesize' ];
    $cff_date_weight = $atts[ 'dateweight' ];
    $cff_date_color = $atts[ 'datecolor' ];

    $cff_date_styles = '';
    if( ( !empty($cff_date_size) && $cff_date_size != 'inherit' ) || ( !empty($cff_date_weight) && $cff_date_weight != 'inherit' ) || ( !empty($cff_date_color) && $cff_date_color !== '#' ) ) $cff_date_styles = 'style="';
        if ( !empty($cff_date_size) && $cff_date_size != 'inherit' ) $cff_date_styles .=  'font-size:' . $cff_date_size . 'px; ';
        if ( !empty($cff_date_weight) && $cff_date_weight != 'inherit' ) $cff_date_styles .= 'font-weight:' . $cff_date_weight . '; ';
        if ( !empty($cff_date_color) && $cff_date_color !== '#' ) $cff_date_styles .= 'color:#' . str_replace('#', '', $cff_date_color) . ';';
    if( ( !empty($cff_date_size) && $cff_date_size != 'inherit' ) || ( !empty($cff_date_weight) && $cff_date_weight != 'inherit' ) || ( !empty($cff_date_color) && $cff_date_color !== '#' ) ) $cff_date_styles .= '"';
    
    $cff_date_before = isset($options[ 'cff_date_before' ]) ? $options[ 'cff_date_before' ] : '';
    $cff_date_after = isset($options[ 'cff_date_after' ]) ? $options[ 'cff_date_after' ] : '';

    //Timezone. The post date is adjusted by the timezone offset in the cff_getdate function.
    $cff_timezone = $atts['timezone'];

    //Posted ago strings
    $cff_date_translate_strings = array(
        'cff_translate_second' => $atts['secondtext'],
        'cff_translate_seconds' => $atts['secondstext'],
        'cff_translate_minute' => $atts['minutetext'],
        'cff_translate_minutes' => $atts['minutestext'],
        'cff_translate_hour' => $atts['hourtext'],
        'cff_translate_hours' => $atts['hourstext'],
        'cff_translate_day' => $atts['daytext'],
        'cff_translate_days' => $atts['daystext'],
        'cff_translate_week' => $atts['weektext'],
        'cff_translate_weeks' => $atts['weekstext'],
        'cff_translate_month' => $atts['monthtext'],
        'cff_translate_months' => $atts['monthstext'],
        'cff_translate_year' => $atts['yeartext'],
        'cff_translate_years' => $atts['yearstext'],
        'cff_translate_ago' => $atts['agotext']
    );

    //Link to Facebook
    $cff_link_size = $atts[ 'linksize' ];
    $cff_link_weight = $atts[ 'linkweight' ];
    $cff_link_color = $atts[ 'linkcolor' ];

    $cff_link_styles = '';
    if( ( !empty($cff_link_size) && $cff_link_size != 'inherit' ) || ( !empty($cff_link_weight) && $cff_link_weight != 'inherit' ) || ( !empty($cff_link_color) && $cff_link_color !== '#' ) ) $cff_link_styles = 'style="';
        if ( !empty($cff_link_size) && $cff_link_size != 'inherit' ) $cff_link_styles .=  'font-size:' . $cff_link_size . 'px; ';
        if ( !empty($cff_link_weight) && $cff_link_weight != 'inherit' ) $cff_link_styles .= 'font-weight:' . $cff_link_weight . '; ';
        if ( !empty($cff_link_color) && $cff_link_color !== '#' ) $cff_link_styles .= 'color:#' . str_replace('#', '', $cff_link_color) . ';';
    if( ( !empty($cff_link_size) && $cff_link_size != 'inherit' ) || ( !empty($cff_link_weight) && $cff_link_weight != 'inherit' ) || ( !empty($cff_link_color) && $cff_link_color !== '#' ) ) $cff_link_styles .= '"';

    //Link custom text
    $cff_facebook_link_text = $atts[ 'facebooklinktext' ];
    $cff_facebook_share_text = $atts[ 'sharelinktext' ];
    if ($cff_facebook_share_text == '') $cff_facebook_share_text = 'Share';


    //Show Facebook link
    $cff_show_facebook_link = $atts[ 'showfacebooklink' ];
    ( $cff_show_facebook_link == 'on' || $cff_show_facebook_link == 'true' || $cff_show_facebook_link == true ) ? $cff_show_facebook_link = true : $cff_show_facebook_link = false;
    if( $atts[ 'showfacebooklink' ] === 'false' ) $cff_show_facebook_link = false;


    //Show Share link
    $cff_show_facebook_share = $atts[ 'showsharelink' ];
    ( $cff_show_facebook_share == 'on' || $cff_show_facebook_share == 'true' || $cff_show_facebook_share == true ) ? $cff_show_facebook_share = true : $cff_show_facebook_share = false;
    if( $atts[ 'showsharelink' ] === 'false' ) $cff_show_facebook_share = false;

    $cff_view_link_text = $atts[ 'viewlinktext' ];
    $cff_link_to_timeline = $atts[ 'linktotimeline' ];
    /********** MISC **********/
    //Like Box styles
    $cff_likebox_bg_color = $atts[ 'likeboxcolor' ];

    $cff_like_box_text_color = $atts[ 'likeboxtextcolor' ];
    $cff_like_box_colorscheme = 'light';
    if ($cff_like_box_text_color == 'white') $cff_like_box_colorscheme = 'dark';

    $cff_likebox_width = $atts[ 'likeboxwidth' ];
    if ( is_numeric(substr($cff_likebox_width, -1, 1)) ) $cff_likebox_width = $cff_likebox_width . 'px';

    $cff_likebox_height = $atts[ 'likeboxheight' ];
    $cff_likebox_height = preg_replace('/px$/', '', $cff_likebox_height);

    if ( !isset($cff_likebox_width) || empty($cff_likebox_width) || $cff_likebox_width == '' ) $cff_likebox_width = '100%';
    $cff_like_box_faces = $atts[ 'likeboxfaces' ];
    if ( !isset($cff_like_box_faces) || empty($cff_like_box_faces) ){
        $cff_like_box_faces = 'false';
    } else {
        $cff_like_box_faces = 'true';
    }

    $cff_like_box_border = $atts[ 'likeboxborder' ];
    if ($cff_like_box_border) {
        $cff_like_box_border = 'true';
    } else {
        $cff_like_box_border = 'false';
    }

    $cff_like_box_cover = $atts[ 'likeboxcover' ];
    if ($cff_like_box_cover) {
        $cff_like_box_cover = 'false';
    } else {
        $cff_like_box_cover = 'true';
    }

    $cff_like_box_small_header = $atts[ 'likeboxsmallheader' ];
    if ($cff_like_box_small_header) {
        $cff_like_box_small_header = 'true';
    } else {
        $cff_like_box_small_header = 'false';
    }

    $cff_like_box_hide_cta = $atts[ 'likeboxhidebtn' ];
    if ($cff_like_box_hide_cta) {
        $cff_like_box_hide_cta = 'true';
    } else {
        $cff_like_box_hide_cta = 'false';
    }

    //Compile Like box styles
    $cff_likebox_styles = 'style="width: ' . $cff_likebox_width . ';';
    if ( !empty($cff_likebox_bg_color) ) $cff_likebox_styles .= ' background-color: #' . str_replace('#', '', $cff_likebox_bg_color) . ';';

    //Set the left margin on the like box based on how it's being displayed
    if ( (!empty($cff_likebox_bg_color) && $cff_likebox_bg_color != '#') || ($cff_like_box_faces == 'true' || $cff_like_box_faces == 'on') ) $cff_likebox_styles .= ' margin-left: 0px;';  

    $cff_likebox_styles .= '"';

    //Get feed header settings
    $cff_header_bg_color = $atts['headerbg'];
    $cff_header_padding = $atts['headerpadding'];
    if ( is_numeric(substr($cff_header_padding, -1, 1)) ) $cff_header_padding = $cff_header_padding . 'px';

    $cff_header_text_size = $atts['headertextsize'];
    $cff_header_text_weight = $atts['headertextweight'];
    $cff_header_text_color = $atts['headertextcolor'];

    //Compile feed header styles
    $cff_header_styles = '';
    if( ( !empty($cff_header_bg_color) && $cff_header_bg_color !== '#' ) || !empty($cff_header_padding) || ( !empty($cff_header_text_size) && $cff_header_text_size != 'inherit' ) || ( !empty($cff_header_text_weight) && $cff_header_text_weight != 'inherit' ) || (!empty($cff_header_text_color) && $cff_header_text_color !== '#') ) $cff_header_styles = 'style="';
        if ( !empty($cff_header_bg_color) && $cff_header_bg_color !== '#' ) $cff_header_styles .= 'background-color: #' . str_replace('#', '', $cff_header_bg_color) . '; ';
        if ( !empty($cff_header_padding) ) $cff_header_styles .= 'padding: ' . $cff_header_padding . '; ';
        if ( !empty($cff_header_text_size) && $cff_header_text_size != 'inherit' ) $cff_header_styles .= 'font-size: ' . $cff_header_text_size . 'px; ';
        if ( !empty($cff_header_text_weight) && $cff_header_text_weight != 'inherit' ) $cff_header_styles .= 'font-weight: ' . $cff_header_text_weight . '; ';
        if ( !empty($cff_header_text_color) && $cff_header_text_color !== '#' ) $cff_header_styles .= 'color: #' . str_replace('#', '', $cff_header_text_color) . '; ';
    if( ( !empty($cff_header_bg_color) && $cff_header_bg_color !== '#' ) || !empty($cff_header_padding) || ( !empty($cff_header_text_size) && $cff_header_text_size != 'inherit' ) || ( !empty($cff_header_text_weight) && $cff_header_text_weight != 'inherit' ) || (!empty($cff_header_text_color) && $cff_header_text_color !== '#') ) $cff_header_styles .= '"';

    //Video
    //Dimensions
    $cff_video_width = 640;
    $cff_video_height = $atts[ 'videoheight' ];
    
    //Action
    $cff_video_action = $atts[ 'videoaction' ];

    //Post Style settings
    $cff_post_style = $atts['poststyle'];

    $cff_post_bg_color = str_replace('#', '', $atts['postbgcolor']);
    $cff_post_rounded = $atts['postcorners'];
    ( ($cff_post_bg_color !== '#' && $cff_post_bg_color !== '') && $cff_post_style != 'regular' ) ? $cff_post_bg_color_check = true : $cff_post_bg_color_check = false;

    $cff_box_shadow = $atts['boxshadow'];
    ( ($cff_box_shadow == 'true' || $cff_box_shadow == 'on') && $cff_post_style == 'boxed' ) ? $cff_box_shadow = true : $cff_box_shadow = false;

    //Separating Line
    $cff_sep_color = $atts[ 'sepcolor' ];
    if (empty($cff_sep_color)) $cff_sep_color = 'ddd';
    $cff_sep_size = $atts[ 'sepsize' ];
    $cff_sep_size_check = true;
    //If empty then set a 0px border
    if ( empty($cff_sep_size) || $cff_sep_size == '' ) {
        $cff_sep_size = 0;
        //Need to set a color otherwise the CSS is invalid
        $cff_sep_color = 'fff';
        $cff_sep_size_check = false;
    }
    ($cff_sep_color !== '#' && $cff_sep_color !== '') ? $cff_sep_color_check = true : $cff_sep_color_check = false;

    //CFF item styles
    $cff_item_styles = '';
    if( $cff_post_style == 'boxed' || $cff_post_bg_color_check ){
        $cff_item_styles = 'style="';
        if($cff_post_bg_color_check) $cff_item_styles .= 'background-color: #' . $cff_post_bg_color . '; ';
        if( isset($cff_post_rounded) && $cff_post_rounded !== '0' && !empty($cff_post_rounded) ) $cff_item_styles .= '-webkit-border-radius: ' . $cff_post_rounded . 'px; -moz-border-radius: ' . $cff_post_rounded . 'px; border-radius: ' . $cff_post_rounded . 'px; ';
        $cff_item_styles .= '"';
    }
    if( $cff_post_style == 'regular' && ($cff_sep_color_check || $cff_sep_size_check) ){
        $cff_item_styles .= 'style="border-bottom: ' . $cff_sep_size . 'px solid #' . str_replace('#', '', $cff_sep_color) . ';"';
    }
   
    //Text limits
    $title_limit = $atts['textlength'];
    if (!isset($title_limit)) $title_limit = 9999;
    $body_limit = $atts['desclength'];

    //Assign the Access Token and Page ID variables
    $access_token = $atts['accesstoken'];
    $page_id = trim( $atts['id'] );



    //If an 'account' is specified then use that instead of the Page ID/token from the settings
    $cff_account = trim($atts['account']);
    if( !empty( $cff_account ) ){
        $cff_connected_accounts = get_option('cff_connected_accounts');
        if( !empty($cff_connected_accounts) ){

            $cff_connected_accounts = json_decode( str_replace('\"','"', $cff_connected_accounts) );
            
            //Grab the ID and token from the connected accounts setting
            $page_id = $cff_connected_accounts->{ $cff_account }->{'id'};
            $access_token = $cff_connected_accounts->{ $cff_account }->{'accesstoken'};
            
            //Replace the encryption string in the Access Token
            if (strpos($access_token, '02Sb981f26534g75h091287a46p5l63') !== false) {
                $access_token = str_replace("02Sb981f26534g75h091287a46p5l63","",$access_token);
            }
        }
    }

    //If user pastes their full URL into the Page ID field then strip it out
    $cff_facebook_string = 'facebook.com';
    $cff_page_id_url_check = stripos($page_id, $cff_facebook_string);

    if ( $cff_page_id_url_check ) {
        //Remove trailing slash if exists
        $page_id = preg_replace('{/$}', '', $page_id);
        //Get last part of url
        $page_id = substr( $page_id, strrpos( $page_id, '/' )+1 );
    }

    //Masonry
    $masonry = false;
    $cff_cols = $atts['cols'];
    $cff_cols_mobile = $atts['colsmobile'];
    $cff_cols_js = $atts['colsjs'];

    if( intval($cff_cols) > 1 ) $masonry = true;
    $js_only = isset( $cff_cols_js ) ? $cff_cols_js : false;
    if( $js_only === 'false' ) $js_only = false;

    if( $masonry || $masonry == 'true' ) $atts['headeroutside'] = true;

    $masonry_classes = '';
    if( isset($masonry) ) {
        if( $masonry === 'on' || $masonry === true || $masonry === 'true' ) {

            $masonry_classes .= 'cff-masonry';

            if( $cff_cols != 3 ) {
                $masonry_classes .= sprintf( ' masonry-%s-desktop', $cff_cols );
            }
            if( $cff_cols_mobile == 2 ) {
                $masonry_classes .= ' masonry-2-mobile';
            }
            if( ! $js_only ) {
                $masonry_classes .= ' cff-masonry-css';
            } else {
                $masonry_classes .= ' cff-masonry-js';
            }
        }
    }

    //If the Page ID contains a query string at the end then remove it
    if ( stripos( $page_id, '?') !== false ) $page_id = substr($page_id, 0, strrpos($page_id, '?'));

    //Always remove slash from end of Page ID
    $page_id = preg_replace('{/$}', '', $page_id);

    //Get show posts attribute. If not set then default to 25
    $show_posts = $atts['num'];
    if (empty($show_posts)) $show_posts = 25;
    if ( $show_posts == 'undefined' ) $show_posts = 25;
    
    //If the 'Enter my own Access Token' box is unchecked then don't use the user's access token, even if there's one in the field
    get_option('cff_show_access_token') ? $cff_show_access_token = true : $cff_show_access_token = false;

    //Check whether a Page ID has been defined
    if ($page_id == '') {
        echo "Please enter the Page ID of the Facebook feed you'd like to display. You can do this in either the Custom Facebook Feed plugin settings or in the shortcode itself. For example, [custom-facebook-feed id=YOUR_PAGE_ID_HERE].<br /><br />";
        return false;
    }

    //Is it a restricted page?
    $cff_restricted_page = $atts['restrictedpage'];
    ($cff_restricted_page == 'true' || $cff_restricted_page == 'on') ? $cff_restricted_page = true : $cff_restricted_page = false;

    //Is it SSL?
    $cff_ssl = '';
    if (is_ssl()) $cff_ssl = '&return_ssl_resources=true';

    //Use posts? or feed?
    $old_others_option = get_option('cff_show_others'); //Use this to help depreciate the old option
    $show_others = $atts['others'];
    $show_posts_by = $atts['showpostsby'];
    $graph_query = 'posts';
    $cff_show_only_others = false;

    //If 'others' shortcode option is used then it overrides any other option
    if ($show_others || $old_others_option == 'on') {
        //Show posts by everyone
        if ( $old_others_option == 'on' || $show_others == 'on' || $show_others == 'true' || $show_others == true || $cff_is_group ) $graph_query = 'feed';

        //Only show posts by me
        if ( $show_others == 'false' ) $graph_query = 'posts';

    } else {
    //Else use the settings page option or the 'showpostsby' shortcode option

        //Only show posts by me
        if ( $show_posts_by == 'me' ) $graph_query = 'posts';

        //Show posts by everyone
        if ( $show_posts_by == 'others' || $cff_is_group ) $graph_query = 'feed';

        //Show posts ONLY by others
        if ( $show_posts_by == 'onlyothers' && !$cff_is_group ) {
            $graph_query = 'visitor_posts';
            $cff_show_only_others = true;
        }
    }

    $cff_post_limit = $atts['limit'];

    //If the limit isn't set then set it to be 7 more than the number of posts defined
    if ( isset($cff_post_limit) && $cff_post_limit !== '' ) {
        $cff_post_limit = $cff_post_limit;
    } else {
        if( intval($show_posts) >= 50 ) $cff_post_limit = intval(intval($show_posts) + 7);
        if( intval($show_posts) < 50 ) $cff_post_limit = intval(intval($show_posts) + 5);
        if( intval($show_posts) < 25  ) $cff_post_limit = intval(intval($show_posts) + 4);
        if( intval($show_posts) < 10  ) $cff_post_limit = intval(intval($show_posts) + 3);
        if( intval($show_posts) < 6  ) $cff_post_limit = intval(intval($show_posts) + 2);
        if( intval($show_posts) < 2  ) $cff_post_limit = intval(intval($show_posts) + 1);
    }
    if( $cff_post_limit >= 100 ) $cff_post_limit = 100;

    //If the number of posts is set to zero then don't show any and set limit to one
    if ( ($show_posts == '0' || $show_posts == 0) && $show_posts !== ''){
        $show_posts = 0;
        $cff_post_limit = 1;
    }


    //Calculate the cache time in seconds
    if($cff_cache_time_unit == 'minutes') $cff_cache_time_unit = 60;
    if($cff_cache_time_unit == 'hours') $cff_cache_time_unit = 60*60;
    if($cff_cache_time_unit == 'days') $cff_cache_time_unit = 60*60*24;
    $cache_seconds = $cff_cache_time * $cff_cache_time_unit;

    //Get like box vars
    $cff_likebox_width = $atts[ 'likeboxwidth' ];
    if ( !isset($cff_likebox_width) || empty($cff_likebox_width) || $cff_likebox_width == '' ) $cff_likebox_width = 300;

    $like_box_page_id = explode(",", str_replace(' ', '', $page_id) );

    //Like Box HTML
    $like_box = '<';
    //If the Like Box is at the top then change the element from a div so that it doesn't interfere with the "nth-of-type" used for grids in CSS
    ($cff_like_box_position == 'top') ? $like_box .= 'section' : $like_box .= 'div';
    $like_box .= ' class="cff-likebox';

    if ($cff_like_box_outside) $like_box .= ' cff-outside';
    $like_box .= ($cff_like_box_position == 'top') ? ' cff-top' : ' cff-bottom';
    $like_box .= '" >';

    //Calculate the like box height
    $cff_likebox_height = 130;
    if( $cff_like_box_small_header == 'true' ) $cff_likebox_height = 70;
    if( $cff_like_box_faces == 'true' ) $cff_likebox_height = 214;
    if( $cff_like_box_small_header == 'true' && $cff_like_box_faces == 'true' ) $cff_likebox_height = 154;

    //Src is set in JS now so can be dynamic based on screen size as 'adapt_container_width' doesn't work in iframe
    $like_box .= '<iframe src="" data-likebox-id="'.$like_box_page_id[0].'" data-likebox-width="'.$cff_likebox_width.'" data-likebox-header="'.$cff_like_box_small_header.'" data-hide-cover="'.$cff_like_box_cover.'" data-hide-cta="'.$cff_like_box_hide_cta.'" data-likebox-faces="'.$cff_like_box_faces.'" height="'.$cff_likebox_height.'" data-locale="'.$cff_locale.'" style="border:none;overflow:hidden" scrolling="no" allowTransparency="true" allow="encrypted-media" class="fb_iframe_widget"></iframe>';

    $like_box .= '</';

    ($cff_like_box_position == 'top') ? $like_box .= 'section' : $like_box .= 'div';
    $like_box .= '>';

    //Don't show like box if it's a group
    if($cff_is_group) $like_box = '';


    //Feed header
    $cff_show_header = $atts['showheader'];
    ($cff_show_header == 'true' || $cff_show_header == 'on') ? $cff_show_header = true : $cff_show_header = false;

    $cff_header_outside = $atts['headeroutside'];
    ($cff_header_outside == 'true' || $cff_header_outside == 'on') ? $cff_header_outside = true : $cff_header_outside = false;

    $cff_header_text = stripslashes( $atts['headertext'] );
    $cff_header_icon = $atts['headericon'];
    $cff_header_icon_color = $atts['headericoncolor'];
    $cff_header_icon_size = $atts['headericonsize'];

    $cff_header = '<h3 class="cff-header';
    if ($cff_header_outside) $cff_header .= ' cff-outside';
    $cff_header .= '" ' . $cff_header_styles . '>';
    $cff_header .= '<span class="fa fas fab fa-' . $cff_header_icon . '"';
    if(!empty($cff_header_icon_color) || !empty($cff_header_icon_size)) $cff_header .= ' style="';
    if(!empty($cff_header_icon_color)) $cff_header .= 'color: #' . str_replace('#', '', $cff_header_icon_color) . ';';
    if(!empty($cff_header_icon_size)) $cff_header .= ' font-size: ' . $cff_header_icon_size . 'px;';
    if(!empty($cff_header_icon_color) || !empty($cff_header_icon_size))$cff_header .= '"';
    $cff_header .= ' aria-hidden="true"></span>';
    $cff_header .= '<span class="header-text" style="height: '.$cff_header_icon_size.'px;">' . $cff_header_text . '</span>';
    $cff_header .= '</h3>';

    //Misc Settings
    $cff_nofollow = $atts['nofollow'];
    ( $cff_nofollow == 'on' || $cff_nofollow == 'true' || $cff_nofollow == true ) ? $cff_nofollow = true : $cff_nofollow = false;
    if( $atts[ 'nofollow' ] == 'false' ) $cff_nofollow = false;
    ( $cff_nofollow ) ? $cff_nofollow = ' rel="nofollow"' : $cff_nofollow = '';

    //If the number of posts is set to zero then don't show any and set limit to one
    if ( ($atts['num'] == '0' || $atts['num'] == 0) && $atts['num'] !== ''){
        $show_posts = 0;
        $cff_post_limit = 1;
    }

    //***START FEED***
    $cff_content = '';

    //Add the page header to the outside of the top of feed
    if ($cff_show_header && $cff_header_outside) $cff_content .= $cff_header;

    //Add like box to the outside of the top of feed
    if ($cff_like_box_position == 'top' && $cff_show_like_box && $cff_like_box_outside) $cff_content .= $like_box;

    //Create CFF container HTML
    $cff_content .= '<div class="cff-wrapper">';
    $cff_content .= '<div id="cff" data-char="'.$title_limit.'"';

    //Disable default CSS styles?
    $cff_disable_styles = $atts['disablestyles'];
    ( $cff_disable_styles == 'on' || $cff_disable_styles == 'true' || $cff_disable_styles == true ) ? $cff_disable_styles = true : $cff_disable_styles = false;
    if( $atts[ 'disablestyles' ] === 'false' ) $cff_disable_styles = false;

    //If there's a class then add it here
    if( !empty($cff_class) || !empty($cff_feed_height) || !$cff_disable_styles || $cff_feed_width_resp || !empty($masonry_classes) ) $cff_content .= ' class="';
        if( !empty($cff_class) ) $cff_content .= $cff_class . ' ';
        if( !empty($masonry_classes) ) $cff_content .= $masonry_classes;
        if ( !empty($cff_feed_height) ) $cff_content .= ' cff-fixed-height ';
        if ( $cff_feed_width_resp ) $cff_content .= ' cff-width-resp ';
        if ( !$cff_disable_styles ) $cff_content .= ' cff-default-styles';
    if( !empty($cff_class) || !empty($cff_feed_height) || !$cff_disable_styles || $cff_feed_width_resp ) $cff_content .= '"';

    $cff_content .= ' ' . $cff_feed_styles . '>';

    //Add the page header to the inside of the top of feed
    if ($cff_show_header && !$cff_header_outside) $cff_content .= $cff_header;

    //Add like box to the inside of the top of feed
    if ($cff_like_box_position == 'top' && $cff_show_like_box && !$cff_like_box_outside) $cff_content .= $like_box;
    //Limit var
    $i_post = 0;

    //Define array for post items
    $cff_posts_array = array();
    
    //ALL POSTS
    if (!$cff_events_only){

        $cff_posts_json_url = 'https://graph.facebook.com/v4.0/' . $page_id . '/' . $graph_query . '?fields=id,from{picture,id,name,link},message,message_tags,story,story_tags,status_type,created_time,backdated_time,call_to_action,attachments{title,description,media_type,unshimmed_url,target{id},media{source}}&access_token=' . $access_token . '&limit=' . $cff_post_limit . '&locale=' . $cff_locale . $cff_ssl;

        if( $cff_show_access_token && strlen($access_token) > 130 ){
            //If using a Page Access Token then set caching time to be minimum of 5 minutes
            if( $cache_seconds < 300 || !isset($cache_seconds) ) $cache_seconds = 300;
        } else {
            //Temporarily set caching time to be minimum of 1 hour
            if( $cache_seconds < 3600 || !isset($cache_seconds) ) $cache_seconds = 3600;

            //Temporarily increase default caching time to be 4 hours
            if( $cache_seconds == 3600 ) $cache_seconds = 14400;
        }

        //Don't use caching if the cache time is set to zero
        if ($cff_cache_time != 0){

            //Create the transient name
            //Split the Page ID in half and stick it together so we definitely have the beginning and end of it
            $trans_page_id = substr($page_id, 0, 16) . substr($page_id, -15);
            $transient_name = 'cff_' . substr($graph_query, 0, 1) . '_' . $trans_page_id . substr($cff_post_limit, 0, 3) . substr($show_posts_by, 0, 2) . substr($cff_locale, 0, 2);

            //Limit to 45 chars max
            $transient_name = substr($transient_name, 0, 45);

            //Get any existing copy of our transient data
            if ( false === ( $posts_json = get_transient( $transient_name ) ) || $posts_json === null ) {
                //Get the contents of the Facebook page
                $posts_json = cff_fetchUrl($cff_posts_json_url);

                //Check whether any data is returned from the API. If it isn't then don't cache the error response and instead keep checking the API on every page load until data is returned.
                $FBdata = json_decode($posts_json);

                if( !empty($FBdata) ) {

                    //Error returned by API
                    if( isset($FBdata->error) ){

                        //Cache the error JSON so doesn't keep making repeated requests
                        //See if a backup cache exists
                        if ( false !== get_transient( '!cff_' . $transient_name ) ) {

                            $posts_json = get_transient( '!cff_' . $transient_name );

                            //Add error message to backup cache so can be displayed at top of feed
                            isset( $FBdata->error->message ) ? $error_message = $FBdata->error->message : $error_message = '';
                            isset( $FBdata->error->type ) ? $error_type = $FBdata->error->type : $error_type = '';
                            $prefix = '{';
                            if (substr($posts_json, 0, strlen($prefix)) == $prefix) $posts_json = substr($posts_json, strlen($prefix));
                            $posts_json = '{"cached_error": { "message": "'.$error_message.'", "type": "'.$error_type.'" }, ' . $posts_json;
                        }

                    //Posts data returned by API
                    } else {
                        //If a backup should be created for this data then create one
                        set_transient( '!cff_' . $transient_name, $posts_json, YEAR_IN_SECONDS );
                    }

                    //Set regular cache
                    set_transient( $transient_name, $posts_json, $cache_seconds );
                    
                }
            } else {

                $posts_json = get_transient( $transient_name );
                //If we can't find the transient then fall back to just getting the json from the api
                if ($posts_json == false) $posts_json = cff_fetchUrl($cff_posts_json_url);

            }
        } else {
            $posts_json = cff_fetchUrl($cff_posts_json_url);
        }

        
        //Interpret data with JSON
        $FBdata = json_decode($posts_json);

        //If there's no data then show a pretty error message
        if( empty($FBdata->data) || isset($FBdata->cached_error) ) {

            //Check whether it's an error in the backup cache
            if( isset($FBdata->cached_error) ) $FBdata->error = $FBdata->cached_error;

            //Show custom message for the PPCA error
            if( isset($FBdata->error->message) && strpos($FBdata->error->message, 'Page Public Content Access') !== false ) {
                $FBdata->error->message = '(#10) To use "Page Public Content Access", your use of this endpoint must be reviewed and approved by Facebook.';
                $FBdata->error->type = $FBdata->error->code = $FBdata->error->error_subcode = NULL;
            }

            $cff_content .= '<div class="cff-error-msg">';
            $cff_content .= '<p><b>This message is only visible to admins.</b><br />';
            $cff_content .= '<p>Problem displaying Facebook posts.';
            if( isset($FBdata->cached_error) ) $cff_content .= ' Backup cache in use.';
            $cff_content .= '<br/><a href="javascript:void(0);" id="cff-show-error" onclick="cffShowError()">Click to show error</a>';
            $cff_content .= '<script type="text/javascript">function cffShowError() { document.getElementById("cff-error-reason").style.display = "block"; document.getElementById("cff-show-error").style.display = "none"; }</script>';
            $cff_content .= '</p><div id="cff-error-reason">';
            
            if( isset($FBdata->error->message) ) $cff_content .= '<b>Error:</b> ' . $FBdata->error->message;
            if( isset($FBdata->error->type) ) $cff_content .= '<br /><b>Type:</b> ' . $FBdata->error->type;
            if( isset($FBdata->error->error_subcode) ) $cff_content .= '<br />Subcode: ' . $FBdata->error->error_subcode;

            if( isset($FBdata->error_msg) ) $cff_content .= '<b>Error:</b> ' . $FBdata->error_msg;
            if( isset($FBdata->error_code) ) $cff_content .= '<br />Code: ' . $FBdata->error_code;
            
            if($FBdata == null) $cff_content .= '<b>Error:</b> Server configuration issue';
            if( empty($FBdata->error) && empty($FBdata->error_msg) && $FBdata !== null ) $cff_content .= '<b>Error:</b> No posts available for this Facebook ID';
            $cff_content .= '<br /><b>Solution:</b> <a href="https://smashballoon.com/custom-facebook-feed/docs/errors/" target="_blank">See here</a> for how to solve this error';
            $cff_content .= '</div></div>'; //End .cff-error-msg and #cff-error-reason
            //Only display errors to admins
            if( current_user_can( 'manage_options' ) ){
                $cff_content .= '<style>#cff .cff-error-msg{ display: block !important; }</style>';
            }
        }


        $numeric_page_id = '';
        if( !empty($FBdata->data) ){
            if ( ($cff_show_only_others || $show_posts_by == 'others') && count($FBdata->data) > 0 ) {
                //Get the numeric ID of the page so can compare it to the author of each post
                $first_post_id = explode("_", $FBdata->data[0]->id);
                $numeric_page_id = $first_post_id[0];
            }
        }

        //***STARTS POSTS LOOP***
        if( isset($FBdata->data) ){
            foreach ($FBdata->data as $news )
            {
                //Explode News and Page ID's into 2 values
                $PostID = '';
                $cff_post_id = '';
                if( isset($news->id) ){
                    $cff_post_id = $news->id;
                    $PostID = explode("_", $cff_post_id);
                }

                //Reassign variable changes from API v3.3 update
                $news->link = '';
                $news->description = '';
                $news->name = '';
                $news->caption = '';
                $news->source = '';
                $news->object_id = '';
                if( isset($news->attachments->data[0]->unshimmed_url) ) $news->link = $news->attachments->data[0]->unshimmed_url;
                if( isset($news->attachments->data[0]->description) ) $news->description = $news->attachments->data[0]->description;
                if( isset($news->attachments->data[0]->target->id) ) $news->object_id = $news->attachments->data[0]->target->id;
                if( isset($news->attachments->data[0]->media->source) ) $news->source = $news->attachments->data[0]->media->source;
                if( isset($news->attachments->data[0]->title) ){
                    $news->name = $news->attachments->data[0]->title;
                    $news->caption = $news->attachments->data[0]->title;
                }

                //Check the post type
                $cff_post_type = 'status';
                if( isset($news->attachments->data[0]->media_type) ) $cff_post_type = $news->attachments->data[0]->media_type;

                if ($cff_post_type == 'link') {
                    isset($news->story) ? $story = $news->story : $story = '';
                    //Check whether it's an event
                    $event_link_check = "facebook.com/events/";
                    if( isset($news->link) ){
                        $event_link_check = stripos($news->link, $event_link_check);
                        if ( $event_link_check ) $cff_post_type = 'event';
                    }
                }

                //Should we show this post or not?
                $cff_show_post = false;
                switch ($cff_post_type) {
                    case 'link':
                        if ( $cff_show_links_type ) $cff_show_post = true;
                        break;
                    case 'event':
                        if ( $cff_show_event_type ) $cff_show_post = true;
                        break;
                    case 'video':
                         if ( $cff_show_video_type ) $cff_show_post = true;
                        break;
                    case 'swf':
                         if ( $cff_show_video_type ) $cff_show_post = true;
                        break;
                    case 'photo':
                         if ( $cff_show_photos_type ) $cff_show_post = true;
                        break;
                    case 'offer':
                         $cff_show_post = true;
                        break;
                    default:
                        //Check whether it's a status (author comment or like)
                        if ( $cff_show_status_type && !empty($news->message) ) $cff_show_post = true;
                        break;
                }

                //Is it a duplicate post?
                if (!isset($prev_post_message)) $prev_post_message = '';
                if (!isset($prev_post_link)) $prev_post_link = '';
                if (!isset($prev_post_description)) $prev_post_description = '';
                isset($news->message) ? $pm = $news->message : $pm = '';
                isset($news->link) ? $pl = $news->link : $pl = '';
                isset($news->description) ? $pd = $news->description : $pd = '';

                if ( ($prev_post_message == $pm) && ($prev_post_link == $pl) && ($prev_post_description == $pd) ) $cff_show_post = false;

                //Offset. If the post index ($i_post) is less than the offset then don't show the post
                if( intval($i_post) < intval($atts['offset']) ){
                    $cff_show_post = false;
                    $i_post++;
                }

                //Check post type and display post if selected
                if ( $cff_show_post ) {
                    //If it isn't then create the post
                    //Only create posts for the amount of posts specified
                    if( intval($atts['offset']) > 0 ){
                        //If offset is being used then stop after showing the number of posts + the offset
                        if ( $i_post == (intval($show_posts) + intval($atts['offset'])) ) break;
                    } else {
                        //Else just stop after the number of posts to be displayed is reached
                        if ( $i_post == $show_posts ) break;
                    }
                    $i_post++;
                    //********************************//
                    //***COMPILE SECTION VARIABLES***//
                    //********************************//
                    //Set the post link
                    isset($news->link) ? $link = htmlspecialchars($news->link) : $link = '';
                    //Is it a shared album?
                    $shared_album_string = 'shared an album:';
                    isset($news->story) ? $story = $news->story : $story = '';
                    $shared_album = stripos($story, $shared_album_string);
                    if ( $shared_album ) {
                        $link = str_replace('photo.php?','media/set/?',$link);
                    }

                    //Check the post type
                    isset($cff_post_type) ? $cff_post_type = $cff_post_type : $cff_post_type = '';
                    if ($cff_post_type == 'link') {
                        isset($news->story) ? $story = $news->story : $story = '';
                        //Check whether it's an event
                        $event_link_check = "facebook.com/events/";
                        //Make sure URL doesn't include 'permalink' as that indicates someone else sharing a post from within an event (eg: https://www.facebook.com/events/617323338414282/permalink/617324268414189/) and the event ID is then not retrieved properly from the event URL as it's formatted like so: facebook.com/events/EVENT_ID/permalink/POST_ID
                        $event_link_check = stripos($news->link, $event_link_check);
                        $event_link_check_2 = stripos($news->link, "permalink/");
                        if ( $event_link_check && !$event_link_check_2 ) $cff_post_type = 'event';
                    }

                    //If it's an event then check whether the URL contains facebook.com
                    if(isset($news->link)){
                        if( stripos($news->link, "events/") && $cff_post_type == 'event' ){
                            //Facebook changed the event link from absolute to relative, and so if the link isn't absolute then add facebook.com to front
                            ( stripos($link, 'facebook.com') ) ? $link = $link : $link = 'https://facebook.com' . $link;
                        }
                    }

                    //Is it an album?
                    $cff_album = false;
                    if( isset($news->status_type) ){
                        if( $news->status_type == 'added_photos' ){
                            if( isset($news->attachments) ){
                                if( $news->attachments->data[0]->media_type == 'album' ) $cff_album = true;
                            }
                        }
                    }

                    //If there's no link provided then link to either the Facebook page or the individual status
                    if (empty($news->link)) {
                        if ($cff_link_to_timeline == true){
                            //Link to page
                            $link = 'https://facebook.com/' . $page_id;
                        } else {
                            //Link to status
                            $link = "https://www.facebook.com/" . $page_id . "/posts/" . $PostID[1];
                        }
                    }

                    //DATE
                    $cff_date_formatting = $atts[ 'dateformat' ];
                    $cff_date_custom = $atts[ 'datecustom' ];

                    isset($news->created_time) ? $post_time = $news->created_time : $post_time = '';
                    if( isset($news->backdated_time) ) $post_time = $news->backdated_time; //If the post is backdated then use that as the date instead

                    $cff_date = '<p class="cff-date" '.$cff_date_styles.'>'. $cff_date_before . ' ' . cff_getdate(strtotime($post_time), $cff_date_formatting, $cff_date_custom, $cff_date_translate_strings, $cff_timezone) . ' ' . $cff_date_after;
                    if($cff_date_position == 'below' || (!$cff_show_author && $cff_date_position == 'author') ) $cff_date .= '<span class="cff-date-dot">&nbsp;&middot;&nbsp;&nbsp;</span>';
                    $cff_date .= '</p>';

                    //Page name
                    if( isset($news->from->name) ){
                        $cff_author_name = $news->from->name;
                        $cff_author_name = str_replace('"', "", $cff_author_name);
                    } else {
                        $cff_author_name = '';
                    }

                    //Story/post text vars
                    $post_text = '';
                    $cff_post_text_type = '';
                    $cff_story_raw = '';
                    $cff_message_raw = '';
                    $cff_name_raw = '';
                    $text_tags = '';
                    $post_text_story = '';
                    $post_text_message = '';

                    //STORY TAGS
                    $cff_post_tags = $atts[ 'posttags' ];

                    //Use the story
                    if (!empty($news->story)) {
                        $cff_story_raw = $news->story;
                        $post_text_story .= htmlspecialchars($cff_story_raw);
                        $cff_post_text_type = 'story';


                        //Add message and story tags if there are any and the post text is the message or the story
                        if( $cff_post_tags && isset($news->story_tags) && !$cff_title_link){
                            
                            $text_tags = $news->story_tags;

                            //Does the Post Text contain any html tags? - the & symbol is the best indicator of this
                            $cff_html_check_array = array('&lt;', '’', '“', '&quot;', '&amp;', '&gt;&gt;');

                            //always use the text replace method
                            if( cff_stripos_arr($post_text_story, $cff_html_check_array) !== false || ($cff_locale == 'el_GR' && count($news->story_tags) > 3) ) {

                                //Loop through the tags
                                foreach($text_tags as $message_tag ) {

                                    ( isset($message_tag->id) ) ? $message_tag = $message_tag : $message_tag = $message_tag[0];

                                    $tag_name = $message_tag->name;
                                    $tag_link = '<a href="https://facebook.com/' . $message_tag->id . '">' . $message_tag->name . '</a>';

                                    $post_text_story = str_replace($tag_name, $tag_link, $post_text_story);
                                }

                            } else {

                                //If it doesn't contain HTMl tags then use the offset to replace message tags
                                $message_tags_arr = array();

                                $tag = 0;
                                foreach($text_tags as $message_tag ) {
                                    $tag++;
                                    ( isset($message_tag->id) ) ? $message_tag = $message_tag : $message_tag = $message_tag[0];

                                    isset($message_tag->type) ? $tag_type = $message_tag->type : $tag_type = '';

                                    $message_tags_arr = cff_array_push_assoc(
                                        $message_tags_arr,
                                        $tag,
                                        array(
                                            'id' => $message_tag->id,
                                            'name' => $message_tag->name,
                                            'type' => isset($message_tag->type) ? $message_tag->type : '',
                                            'offset' => $message_tag->offset,
                                            'length' => $message_tag->length
                                        )
                                    );
                                    
                                }

                                //Keep track of the offsets so that if two tags have the same offset then only one is used. Need this as API 2.5 update changed the story_tag JSON format. A duplicate offset usually means '__ was with __ and 3 others'. We don't want to link the '3 others' part.
                                $cff_story_tag_offsets = '';
                                $cff_story_duplicate_offset = '';

                                //Check if there are any duplicate offsets. If so, assign to the cff_story_duplicate_offset var.
                                for($tag = count($message_tags_arr); $tag >= 1; $tag--) {
                                    $c = (string)$message_tags_arr[$tag]['offset'];
                                    if( strpos( $cff_story_tag_offsets, $c ) !== false && $c !== '0' ){
                                        $cff_story_duplicate_offset = $c;
                                    } else {
                                        $cff_story_tag_offsets .= $c . ',';  
                                    }
                                                                              
                                }

                                for($tag = count($message_tags_arr); $tag >= 1; $tag--) {

                                    //If the name is blank (aka the story tag doesn't work properly) then don't use it
                                    if( $message_tags_arr[$tag]['name'] !== '' ) {

                                        //If it's an event tag or it has the same offset as another tag then don't display it
                                        if( $message_tags_arr[$tag]['type'] == 'event' || $message_tags_arr[$tag]['offset'] == $cff_story_duplicate_offset || $message_tags_arr[$tag]['type'] == 'page' ){
                                            //Don't use the story tag in this case otherwise it changes '__ created an event' to '__ created an Name Of Event'
                                            //Don't use the story tag if it's a page as it causes an issue when sharing a page: Smash Balloon Dev shared a Smash Balloon.
                                        } else {
                                            $b = '<a href="https://facebook.com/' . $message_tags_arr[$tag]['id'] . '" target="_blank">' . $message_tags_arr[$tag]['name'] . '</a>';
                                            $c = $message_tags_arr[$tag]['offset'];
                                            $d = $message_tags_arr[$tag]['length'];
                                            $post_text_story = cff_mb_substr_replace( $post_text_story, $b, $c, $d);
                                        }

                                    }

                                }
                                
                            } // end if/else

                        } //END STORY TAGS

                    }
                    
                    //POST AUTHOR
                    $cff_author = '';
                    if( isset($news->from->id) ){

                        $cff_author .= '<div class="cff-author">';

                        //Check if the author from ID exists, as sometimes it doesn't
                        isset($news->from->id) ? $cff_from_id = $news->from->id : $cff_from_id = '';
                        
                        $cff_author_link_atts = 'href="https://facebook.com/' . $cff_from_id . '" '.$target.$cff_nofollow.' '.$cff_author_styles;

                        //Link to the post if it's a visitor post as profile link no longer available
                        $cff_author_link_el = 'a';
                        $cff_author_link_atts = ' href="https://facebook.com/' . $cff_from_id . '" '.$target.$cff_nofollow.' '.$cff_author_styles;

                        //If no link is available then change to span
                        if( !isset($news->from->link) ){
                            $cff_author_link_el = 'span';
                            $cff_author_link_atts = '';
                        }

                        //Remove the first occurence of the author name from the story
                        if( !empty($cff_author_name) ){
                            $cff_author_name_pos = strpos($post_text_story, $cff_author_name);
                            if ($cff_author_name_pos !== false) {
                                $post_text_story = substr_replace($post_text_story, '', $cff_author_name_pos, strlen($cff_author_name));
                            }
                        }
                        
                        //Author text
                        $cff_author .= '<div class="cff-author-text">';
                        if($cff_show_date && $cff_date_position !== 'above' && $cff_date_position !== 'below'){
                            $cff_author .= '<p class="cff-page-name cff-author-date" '.$cff_author_styles.'><'.$cff_author_link_el.$cff_author_link_atts.'>'.$cff_author_name.'</'.$cff_author_link_el.'><span class="cff-story"> '.$post_text_story.'</span></p>';
                            $cff_author .= $cff_date;
                        } else {
                            $cff_author .= '<span class="cff-page-name"><'.$cff_author_link_el.$cff_author_link_atts.'>'.$cff_author_name.'</'.$cff_author_link_el.'><span class="cff-story"> '.$post_text_story.'</span></span>';
                        }

                        $cff_author .= '</div>';

                        //Author image
                        isset($news->from->picture->data->url) ? $cff_author_src = $news->from->picture->data->url : $cff_author_src = '';

                        $cff_author .= '<div class="cff-author-img"><'.$cff_author_link_el.$cff_author_link_atts.'><img src="'.$cff_author_src.'" title="'.$cff_author_name.'" alt="'.$cff_author_name.'" width=40 height=40 onerror="this.style.display=\'none\'"></'.$cff_author_link_el.'></div>';

                        $cff_author .= '</div>'; //End .cff-author

                    } else {

                        $cff_author .= '<div class="cff-author cff-no-author-info">';
                                        
                        //Author text
                        $cff_author .= '<div class="cff-author-text">';
                        if($cff_show_date && $cff_date_position !== 'above' && $cff_date_position !== 'below'){
                            if( !empty($post_text_story) ) $cff_author .= '<p class="cff-page-name cff-author-date"><span class="cff-story"> '.$post_text_story.'</span></p>';
                            $cff_author .= $cff_date;
                        } else {
                            if( !empty($post_text_story) ) $cff_author .= '<span class="cff-page-name"><span class="cff-story"> '.$post_text_story.'</span></span>';
                        }
                        $cff_author .= '</div>';

                        //Author image
                        $cff_author .= '<div class="cff-author-img"></div>';
                        $cff_author .= '</div>'; //End .cff-author

                    }


                    //POST TEXT
                    
                    //Get the actual post text
                    //Which content should we use?
                    //Use the message
                    if (!empty($news->message)) {
                        $cff_message_raw = $news->message;
                        
                        $post_text_message = htmlspecialchars($cff_message_raw);
                        $cff_post_text_type = 'message';

                        //MESSAGE TAGS
                        //Add message and story tags if there are any and the post text is the message or the story
                        if( $cff_post_tags && isset($news->message_tags) && !$cff_title_link){
                            
                            $text_tags = $news->message_tags;

                            //Does the Post Text contain any html tags? - the & symbol is the best indicator of this
                            $cff_html_check_array = array('&lt;', '’', '“', '&quot;', '&amp;', '&gt;&gt;', '&gt;');

                            //always use the text replace method
                            if( cff_stripos_arr($post_text_message, $cff_html_check_array) !== false ) {
                                //Loop through the tags
                                foreach($text_tags as $message_tag ) {

                                    ( isset($message_tag->id) ) ? $message_tag = $message_tag : $message_tag = $message_tag[0];

                                    $tag_name = $message_tag->name;
                                    $tag_link = '<a href="https://facebook.com/' . $message_tag->id . '">' . $message_tag->name . '</a>';

                                    $post_text_message = str_replace($tag_name, $tag_link, $post_text_message);
                                }

                            } else {
                            //If it doesn't contain HTMl tags then use the offset to replace message tags
                                $message_tags_arr = array();

                                $tag = 0;
                                foreach($text_tags as $message_tag ) {
                                    $tag++;

                                    ( isset($message_tag->id) ) ? $message_tag = $message_tag : $message_tag = $message_tag[0];

                                    $message_tags_arr = cff_array_push_assoc(
                                        $message_tags_arr,
                                        $tag,
                                        array(
                                            'id' => $message_tag->id,
                                            'name' => $message_tag->name,
                                            'type' => isset($message_tag->type) ? $message_tag->type : '',
                                            'offset' => $message_tag->offset,
                                            'length' => $message_tag->length
                                        )
                                    );
                                }

                                //Keep track of the offsets so that if two tags have the same offset then only one is used. Need this as API 2.5 update changed the story_tag JSON format.
                                $cff_msg_tag_offsets = '';
                                $cff_msg_duplicate_offset = '';

                                //Check if there are any duplicate offsets. If so, assign to the cff_duplicate_offset var.
                                for($tag = count($message_tags_arr); $tag >= 1; $tag--) {
                                    $c = (string)$message_tags_arr[$tag]['offset'];
                                    if( strpos( $cff_msg_tag_offsets, $c ) !== false && $c !== '0' ){
                                        $cff_msg_duplicate_offset = $c;
                                    } else {
                                        $cff_msg_tag_offsets .= $c . ',';  
                                    }
                                }

                                //Sort the array by the "offset" key as Facebook doesn't always return them in the correct order
                                usort($message_tags_arr, "cffSortTags");

                                for($tag = count($message_tags_arr)-1; $tag >= 0; $tag--) {

                                    //If the name is blank (aka the story tag doesn't work properly) then don't use it
                                    if( $message_tags_arr[$tag]['name'] !== '' ) {

                                        if( $message_tags_arr[$tag]['offset'] == $cff_msg_duplicate_offset ){
                                            //If it has the same offset as another tag then don't display it
                                        } else {
                                            $b = '<a href="https://facebook.com/' . $message_tags_arr[$tag]['id'] . '">' . $message_tags_arr[$tag]['name'] . '</a>';
                                            $c = $message_tags_arr[$tag]['offset'];
                                            $d = $message_tags_arr[$tag]['length'];
                                            $post_text_message = cff_mb_substr_replace( $post_text_message, $b, $c, $d);
                                        }

                                    }

                                }   

                            } // end if/else

                        } //END MESSAGE TAGS

                    }


                    //Check to see whether it's an embedded video so that we can show the name above the post text if necessary
                    $cff_soundcloud = false;
                    $cff_is_video_embed = false;
                    if ($cff_post_type == 'video' || $cff_post_type == 'music'){
                        if( isset($news->source) && !empty($news->source) ){
                            $url = $news->source;
                        } else if ( isset($news->link) ) {
                            $url = $news->link;
                        } else {
                            $url = '';
                        }
                        //Embeddable video strings
                        $youtube = 'youtube';
                        $youtu = 'youtu';
                        $vimeo = 'vimeo';
                        $youtubeembed = 'youtube.com/embed';
                        $soundcloud = 'soundcloud.com';
                        $swf = '.swf';
                        //Check whether it's a youtube video
                        $youtube = stripos($url, $youtube);
                        $youtu = stripos($url, $youtu);
                        $youtubeembed = stripos($url, $youtubeembed);
                        //Check whether it's a SoundCloud embed

                        $soundcloudembed = stripos($url, $soundcloud);
                        //Check whether it's a youtube video
                        if($youtube || $youtu || $youtubeembed || (stripos($url, $vimeo) !== false)) {
                            $cff_is_video_embed = true;
                        }
                        //If it's soundcloud then add it into the shared link box at the bottom of the post
                        if( $soundcloudembed ) $cff_soundcloud = true;

                        $cff_video_name = '';
                        //If the name exists and it's a non-embedded video then show the name at the top of the post text
                        if( isset($news->name) && !$cff_is_video_embed ){

                            if (!$cff_title_link) $cff_video_name .= '<a href="'.$link.'" '.$target.$cff_nofollow.' style="color: #'.$cff_posttext_link_color.'">';
                            $cff_video_name .= htmlspecialchars($news->name);
                            if (!$cff_title_link) $cff_video_name .= '</a>';
                            $cff_video_name .= '<br />';

                            //Only show the video name if there's no post text
                            if( empty($post_text_message) || $post_text_message == '' || strlen($post_text_message) < 1 ){

                                //If there's no description then show the video name above the post text, otherwise we'll show it below
                                if( empty($cff_description) || $cff_description == '' ) $post_text = $cff_video_name;

                            }
                        }
                    }

                    //Add the story and message together
                    $post_text = '';

                    //DESCRIPTION
                    $cff_description = '';
                    if ( !empty($news->description) || !empty($news->caption) ) {
                        $description_text = '';

                        if ( !empty($news->description) ) {
                            $description_text = $news->description;
                        }

                        //Replace ellipsis char in description text
                        $description_text = str_replace( '…','...', $description_text);

                        //If the description is the same as the post text then don't show it
                        if( $description_text ==  $cff_story_raw || $description_text ==  $cff_message_raw || $description_text ==  $cff_name_raw ){
                            $cff_description = '';
                        } else {
                            //Add links and create HTML
                            $cff_description .= '<span class="cff-post-desc" '.$cff_body_styles.'>';

                            if ($cff_title_link) {
                                $cff_description_tagged = cff_wrap_span( htmlspecialchars($description_text) );
                            } else {
                                $cff_description_text = cff_autolink( htmlspecialchars($description_text), $link_color=$cff_posttext_link_color );
                                $cff_description_tagged = cff_desc_tags($cff_description_text);
                            }
                            
                            $cff_description .= $cff_description_tagged;
                            $cff_description .= ' </span>';
                        }
                        
                        if( $cff_post_type == 'event' || $cff_is_video_embed || $cff_soundcloud ) $cff_description = '';
                    }

                    //Add the message
                    if($cff_show_text) $post_text .= $post_text_message;

                    //If it's a shared video post then add the video name after the post text above the video description so it's all one chunk
                    if ($cff_post_type == 'video'){
                        if( !empty($cff_description) && $cff_description != '' ){
                            if( (!empty($post_text) && $post_text != '') && !empty($cff_video_name) ) $post_text .= '<br /><br />';
                            $post_text .= $cff_video_name;
                        }
                    }


                    //Use the name
                    if (!empty($news->name) && empty($news->message)) {
                        $cff_name_raw = $news->name;
                        $post_text = htmlspecialchars($cff_name_raw);
                        $cff_post_text_type = 'name';
                    }

                    //OFFER TEXT
                    if ($cff_post_type == 'offer'){
                        isset($news->story) ? $post_text = htmlspecialchars($news->story) . '<br /><br />' : $post_text = '';
                        $post_text .= htmlspecialchars($news->name);
                        $cff_post_text_type = 'story';
                    }

                    //Add the description
                    if( $cff_show_desc && $cff_post_type != 'offer' && $cff_post_type != 'link' ) $post_text .= $cff_description;

                    //Change the linebreak element if the text issue setting is enabled
                    $cff_format_issue = $atts['textissue'];
                    ($cff_format_issue == 'true' || $cff_format_issue == 'on') ? $cff_format_issue = true : $cff_format_issue = false;
                    $cff_linebreak_el = '<br />';
                    if( $cff_format_issue ) $cff_linebreak_el = '<img class="cff-linebreak" />';

                    //EVENT
                    $cff_event_has_cover_photo = false;
                    $cff_event = '';


                    //Create note
                    if ($cff_post_type == 'note') {
                        
                        // Get any existing copy of our transient data from previous versions
                        $transient_name = 'cff_tle_' . $cff_post_id;
                        $transient_name = substr($transient_name, 0, 45);
                        if ( false !== ( $cff_note_json = get_transient( $transient_name ) ) ) {
                            $cff_note_json = get_transient( $transient_name );

                            //Interpret data with JSON
                            $cff_note_obj = json_decode($cff_note_json);
                            $cff_note_object = $cff_note_obj->attachments->data[0];
                            isset($cff_note_object->title) ? $cff_note_title = htmlentities($cff_note_object->title, ENT_QUOTES, 'UTF-8') : $cff_note_title = '';
                            isset($cff_note_object->description) ? $cff_note_description = htmlentities($cff_note_object->description, ENT_QUOTES, 'UTF-8') : $cff_note_description = '';
                            isset($cff_note_object->url) ? $cff_note_link = $cff_note_object->url : $cff_note_link = '';
                            isset( $cff_note_object->media->image->src ) ? $cff_note_media_src = $cff_note_object->media->image->src : $cff_note_media_src = false;
                        } else {
                            $attachment_data = '';
                            if(isset($news->attachments->data[0])){
                                $attachment_data = $news->attachments->data[0];
                                isset($attachment_data->title) ? $cff_note_title = htmlentities($attachment_data->title, ENT_QUOTES, 'UTF-8') : $cff_note_title = '';
                                isset($attachment_data->description) ? $cff_note_description = htmlentities($attachment_data->description, ENT_QUOTES, 'UTF-8') : $cff_note_description = '';
                                isset($attachment_data->unshimmed_url) ? $cff_note_link = $attachment_data->unshimmed_url : $cff_note_link = '';
                                $cff_note_media_src = '';
                            }
                        }


                        //Note details
                        $cff_note = '<span class="cff-details">';
                        $cff_note = '<span class="cff-note-title">'.$cff_note_title.'</span>';
                        $cff_note .= $cff_note_description;
                        $cff_note .= '</span>';

                        //Notes don't include any post text and so just replace the post text with the note content
                        if($cff_show_text) $post_text = $cff_note;
                    }


                    //Create the HTML for the post text elemtent, if the post has text
                    $cff_post_text = '';

                    if( !empty($post_text) ){
                        $cff_post_text = '<' . $cff_title_format . ' class="cff-post-text" ' . $cff_title_styles . '>';

                        //Start HTML for post text
                        $cff_post_text .= '<span class="cff-text" data-color="'.$cff_posttext_link_color.'">';
                        if ($cff_title_link){
                            //Link to the Facebook post if it's a link or a video;
                            ($cff_post_type == 'link' || $cff_post_type == 'video') ? $text_link = "https://www.facebook.com/" . $page_id . "/posts/" . $PostID[1] : $text_link = $link;

                            $cff_post_text .= '<a class="cff-post-text-link" '.$cff_title_styles.' href="'.$text_link.'" '.$target.$cff_nofollow.'>';
                        }
                        
                        //Replace line breaks in text (needed for IE8 and to prevent lost line breaks in HTML minification)
                        $post_text = preg_replace("/\r\n|\r|\n/",$cff_linebreak_el, $post_text);

                        //If the text is wrapped in a link then don't hyperlink any text within
                        if ($cff_title_link) {
                            //Remove links from text
                            $result = preg_replace('/<a href=\"(.*?)\">(.*?)<\/a>/', "\\2", $post_text);
                            //Wrap links in a span so we can break the text if it's too long
                            $cff_post_text .= cff_wrap_span( $result ) . ' ';
                        } else {
                            //Don't use htmlspecialchars for post_text as it's added above so that it doesn't mess up the message_tag offsets
                            $cff_post_text .= cff_autolink( $post_text ) . ' ';
                        }
                        
                        if ($cff_title_link) $cff_post_text .= '</a>';
                        $cff_post_text .= '</span>';
                        //'See More' link
                        $cff_post_text .= '<span class="cff-expand">... <a href="#" style="color: #'.$cff_posttext_link_color.'"><span class="cff-more">' . $cff_see_more_text . '</span><span class="cff-less">' . $cff_see_less_text . '</span></a></span>';
                        $cff_post_text .= '</' . $cff_title_format . '>';

                        //Facebook returns the text as "'s cover photo" for some reason, so ignore it
                        if( $post_text == "'s cover photo" ) $cff_post_text = '';
                    }

                    //Add a call to action button if included
                    if( isset($news->call_to_action->value->link) ){
                        $cff_cta_link = $news->call_to_action->value->link;
                        //If it's not an absolute link then it means it's a relative Facebook one so prefix it with facebook.com
                        if (strpos($cff_cta_link, 'http') === false) $cff_cta_link = 'https://facebook.com' . $cff_cta_link;

                        $cff_button_type = $news->call_to_action->type;

                        switch ($cff_button_type) {
                            case 'SHOP_NOW':
                                $cff_translate_shop_now_text = $atts['shopnowtext'];
                                if (!isset($cff_translate_shop_now_text) || empty($cff_translate_shop_now_text)) $cff_translate_shop_now_text = 'Shop Now';
                                $cff_cta_button_text = $cff_translate_shop_now_text;
                                break;
                            case 'MESSAGE_PAGE':
                                $cff_translate_message_page_text = $atts['messagepage'];
                                if (!isset($cff_translate_message_page_text) || empty($cff_translate_message_page_text)) $cff_translate_message_page_text = 'Message Page';
                                $cff_cta_button_text = $cff_translate_message_page_text;
                                break;
                            case 'LEARN_MORE':
                                $cff_translate_learn_more_text = $atts['learnmoretext'];
                                if (!isset($cff_translate_learn_more_text) || empty($cff_translate_learn_more_text)) $cff_translate_learn_more_text = 'Learn More';
                                $cff_cta_button_text = $cff_translate_learn_more_text;
                                break;
                            default:
                               $cff_cta_button_text = ucwords(strtolower( str_replace('_',' ',$cff_button_type) ) );
                        }

                        isset($news->call_to_action->value->app_link) ? $cff_app_link = $news->call_to_action->value->app_link : $cff_app_link = '';
                        $cff_post_text .= '<p class="cff-cta-link" '.$cff_title_styles.'><a href="'.$cff_cta_link.'" target="_blank" data-app-link="'.$cff_app_link.'" style="color: #'.$cff_posttext_link_color.';" >'.$cff_cta_button_text.'</a></p>';
                    }

                    //LINK
                    $cff_shared_link = '';
                    //Display shared link
                    if ($cff_post_type == 'link' || $cff_soundcloud || $cff_is_video_embed) {

                        $cff_shared_link .= '<div class="cff-shared-link';
                        if($cff_disable_link_box) $cff_shared_link .= ' cff-no-styles';

                        $cff_shared_link .= '" ';

                        if(!$cff_disable_link_box) $cff_shared_link .= $cff_link_box_styles;
                        $cff_shared_link .= '>';
                        $cff_link_image = '';

                        //Display link name and description
                        $cff_shared_link .= '<div class="cff-text-link ';
                        if (!$cff_link_image) $cff_shared_link .= 'cff-no-image';
                        //The link title:
                        if( isset($news->name) ) $cff_shared_link .= '"><'.$cff_link_title_format.' class="cff-link-title" '.$cff_link_title_styles.'><a href="'.$link.'" '.$target.$cff_nofollow.' style="color:#' . $cff_link_title_color . ';">'. $news->name . '</a></'.$cff_link_title_format.'>';
                        //The link source:
                        if( !empty($news->link) ){
                            $cff_link_caption = htmlentities($news->link, ENT_QUOTES, 'UTF-8');
                            $cff_link_caption_parts = explode('/', $cff_link_caption);
                            if( isset($cff_link_caption_parts[2]) ) $cff_link_caption = $cff_link_caption_parts[2];
                        } else {
                            $cff_link_caption = '';
                        }

                        //Shared link styles
                        $cff_link_url_color_html = '';
                        $cff_link_url_size_html = '';
                        if( isset($cff_link_url_color) && !empty($cff_link_url_color) && $cff_link_url_color != '#' ) $cff_link_url_color_html = 'color: #'.str_replace('#', '', $cff_link_url_color).';';
                        if( $cff_link_url_size != 'inherit' && !empty($cff_link_url_size) ) $cff_link_url_size_html = 'font-size:'.$cff_link_url_size.'px;';

                        $cff_link_styles_html = '';
                        if( strlen($cff_link_url_color_html) > 1 || strlen($cff_link_url_size_html) > 1 ) $cff_link_styles_html = 'style="';
                        if( strlen($cff_link_url_color_html) > 1 ) $cff_link_styles_html .= $cff_link_url_color_html;
                        if( strlen($cff_link_url_size_html) > 1 ) $cff_link_styles_html .= $cff_link_url_size_html;
                        if( strlen($cff_link_url_color_html) > 1 || strlen($cff_link_url_size_html) > 1 ) $cff_link_styles_html .= '"';
                        
                        if(!empty($cff_link_caption)) $cff_shared_link .= '<p class="cff-link-caption" '.$cff_link_styles_html.'>'.$cff_link_caption.'</p>';
                        if ($cff_show_desc) {
                            //Truncate desc
                            if (!empty($body_limit)) {
                                if (strlen($description_text) > $body_limit) $description_text = substr($description_text, 0, $body_limit) . '...';
                            }

                            //Shared link desc styles
                            $cff_link_desc_color_html = '';
                            $cff_link_desc_size_html = '';
                            if( isset($cff_link_desc_color) && !empty($cff_link_desc_color) && $cff_link_desc_color != '#' ) $cff_link_desc_color_html = 'color: #'.str_replace('#', '', $cff_link_desc_color).';';
                            if( $cff_link_desc_size != 'inherit' && !empty($cff_link_desc_size) ) $cff_link_desc_size_html = 'font-size:'.$cff_link_desc_size.'px;';

                            $cff_link_desc_styles_html = '';
                            if( strlen($cff_link_desc_color_html) > 1 || strlen($cff_link_desc_size_html) > 1 ) $cff_link_desc_styles_html = 'style="';
                            if( strlen($cff_link_desc_color_html) > 1 ) $cff_link_desc_styles_html .= $cff_link_desc_color_html;
                            if( strlen($cff_link_desc_size_html) > 1 ) $cff_link_desc_styles_html .= $cff_link_desc_size_html;
                            if( strlen($cff_link_desc_color_html) > 1 || strlen($cff_link_desc_size_html) > 1 ) $cff_link_desc_styles_html .= '"';

                            //Add links and create HTML
                            $cff_link_description = '<span class="cff-post-desc" '.$cff_link_desc_styles_html.'>';
                            if ($cff_title_link) {
                                $cff_link_description .= cff_wrap_span( htmlspecialchars($description_text) );
                            } else {
                                $description_text = cff_autolink( htmlspecialchars($description_text), $link_color=$cff_posttext_link_color );
                                //Replace line breaks with <br> tags
                                $cff_link_description .= nl2br($description_text);
                            }
                            $cff_link_description .= ' </span>';


                            if( $description_text != $cff_link_caption ) $cff_shared_link .= $cff_link_description;
                        }

                        $cff_shared_link .= '</div></div>';
                    }

                    /* VIDEO */

                    //Check to see whether it's an embedded video so that we can show the name above the post text if necessary
                    $cff_is_video_embed = false;
                    if ( $cff_post_type == 'video' && isset($news->source) ){
                        $url = $news->source;
                        //Embeddable video strings
                        $youtube = 'youtube';
                        $youtu = 'youtu';
                        $vimeo = 'vimeo';
                        $youtubeembed = 'youtube.com/embed';
                        //Check whether it's a youtube video
                        $youtube = stripos($url, $youtube);
                        $youtu = stripos($url, $youtu);
                        $youtubeembed = stripos($url, $youtubeembed);
                        //Check whether it's a youtube video
                        if($youtube || $youtu || $youtubeembed || (stripos($url, $vimeo) !== false)) {
                            $cff_is_video_embed = true;
                        }
                    }


                    $cff_media = '';
                    if ($cff_post_type == 'video') {
                        //Add the name to the description if it's a video embed
                        if($cff_is_video_embed) {
                            isset($news->name) ? $video_name = $news->name : $video_name = $link;
                            isset($news->description) ? $description_text = $news->description : $description_text = '';
                            //Add the 'cff-shared-link' class so that embedded videos display in a box
                            $cff_description = '<div class="cff-desc-wrap cff-shared-link ';
                            if (empty($picture)) $cff_description .= 'cff-no-image';
                            if($cff_disable_link_box) $cff_description .= ' cff-no-styles"';
                            if(!$cff_disable_link_box) $cff_description .= '" ' . $cff_link_box_styles;
                            $cff_description .= '>';

                            if( isset($news->name) ) $cff_description .= '<'.$cff_link_title_format.' class="cff-link-title" '.$cff_link_title_styles.'><a href="'.$link.'" '.$target.$cff_nofollow.' style="color:#' . $cff_link_title_color . ';">'. $news->name . '</a></'.$cff_link_title_format.'>';

                            if (!empty($body_limit)) {
                                if (strlen($description_text) > $body_limit) $description_text = substr($description_text, 0, $body_limit) . '...';
                            }

                            $cff_description .= '<p class="cff-post-desc" '.$cff_body_styles.'><span>' . cff_autolink( htmlspecialchars($description_text) ) . '</span></p></div>';
                        } else {
                            isset($news->name) ? $video_name = $news->name : $video_name = $link;
                            if( isset($news->name) ) $cff_description .= '<'.$cff_link_title_format.' class="cff-link-title" '.$cff_link_title_styles.'><a href="'.$link.'" '.$target.$cff_nofollow.' style="color:#' . $cff_link_title_color . ';">'. $news->name . '</a></'.$cff_link_title_format.'>';
                        }
                    }


                    //Display the link to the Facebook post or external link
                    $cff_link = '';
                    //Default link
                    $cff_viewpost_class = 'cff-viewpost-facebook';
                    if ($cff_facebook_link_text == '') $cff_facebook_link_text = 'View on Facebook';
                    $link_text = $cff_facebook_link_text;

                    //Link to the Facebook post if it's a link or a video
                    if($cff_post_type == 'link' || $cff_post_type == 'video') $link = "https://www.facebook.com/" . $page_id . "/posts/" . $PostID[1];

                    //Social media sharing URLs
                    $cff_share_facebook = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($link);
                    $cff_share_twitter = 'https://twitter.com/intent/tweet?text=' . urlencode($link);
                    $cff_share_google = 'https://plus.google.com/share?url=' . urlencode($link);
                    $cff_share_linkedin = 'https://www.linkedin.com/shareArticle?mini=true&amp;url=' . urlencode($link) . '&amp;title=' . rawurlencode( strip_tags($cff_post_text) );
                    $cff_share_email = 'mailto:?subject=Facebook&amp;body=' . urlencode($link) . '%20-%20' . rawurlencode( strip_tags($cff_post_text) );

                    //If it's a shared post then change the link to use the Post ID so that it links to the shared post and not the original post that's being shared
                    if( isset($news->status_type) ){
                        if( $news->status_type == 'shared_story' ) $link = "https://www.facebook.com/" . $cff_post_id;
                    }

                    //If it's an offer post then change the text
                    if ($cff_post_type == 'offer') $link_text = 'View Offer';

                    //Create post action links HTML
                    $cff_link = '';
                    if($cff_show_facebook_link || $cff_show_facebook_share){
                        $cff_link .= '<div class="cff-post-links">';

                        //View on Facebook link
                        if($cff_show_facebook_link) $cff_link .= '<a class="' . $cff_viewpost_class . '" href="' . $link . '" title="' . $link_text . '" ' . $target . $cff_nofollow . ' ' . $cff_link_styles . '>' . $link_text . '</a>';

                        //Share link
                        if($cff_show_facebook_share){
                            $cff_link .= '<div class="cff-share-container">';
                            //Only show separating dot if both links are enabled
                            if($cff_show_facebook_link) $cff_link .= '<span class="cff-dot" ' . $cff_link_styles . '>&middot;</span>';
                            $cff_link .= '<a class="cff-share-link" href="'.$cff_share_facebook.'" title="' . $cff_facebook_share_text . '" ' . $cff_link_styles . '>' . $cff_facebook_share_text . '</a>';
                            $cff_link .= "<p class='cff-share-tooltip'><a href='".$cff_share_facebook."' target='_blank' class='cff-facebook-icon'><span class='fa fab fa-facebook-square' aria-hidden='true'></span><span class='cff-screenreader'>Share on Facebook</span></a><a href='".$cff_share_twitter."' target='_blank' class='cff-twitter-icon'><span class='fa fab fa-twitter' aria-hidden='true'></span><span class='cff-screenreader'>Share on Twitter</span></a><a href='".$cff_share_linkedin."' target='_blank' class='cff-linkedin-icon'><span class='fa fab fa-linkedin' aria-hidden='true'></span><span class='cff-screenreader'>Share on Linked In</span></a><a href='".$cff_share_email."' target='_blank' class='cff-email-icon'><span class='fa fa-envelope' aria-hidden='true'></span><span class='cff-screenreader'>Share by Email</span></a><span class='fa fa-play fa-rotate-90' aria-hidden='true'></span></p></div>";
                        }
                        
                        $cff_link .= '</div>'; 
                    }


                    /* MEDIA LINK */
                    $cff_translate_photo_text = $atts['phototext'];
                    if (!isset($cff_translate_photo_text) || empty($cff_translate_photo_text)) $cff_translate_photo_text = 'Photo';
                    $cff_translate_video_text = $atts['videotext'];
                    if (!isset($cff_translate_video_text) || empty($cff_translate_video_text)) $cff_translate_video_text = 'Video';

                    $cff_media_link = '';

                    if( $cff_show_media_link && ($cff_post_type == 'photo' || $cff_post_type == 'video' || $cff_album) ){
                        $cff_media_link .= '<p class="cff-media-link"><a href="'.$link.'" '.$target.' style="color: #'.$cff_posttext_link_color.';"><span style="padding-right: 5px;" class="fa fas fa-';
                        if($cff_post_type == 'photo' || $cff_album) $cff_media_link .=  'picture-o fa-image" aria-hidden="true"></span>'. $cff_translate_photo_text;
                        // if($cff_post_type == 'video') $cff_media_link .=  'file-video-o';
                        if($cff_post_type == 'video') $cff_media_link .=  'video-camera fa-video" aria-hidden="true"></span>'. $cff_translate_video_text;
                        $cff_media_link .= '</a></p>';
                    }

                    //**************************//
                    //***CREATE THE POST HTML***//
                    //**************************//
                    //Start the container
                    $cff_post_item = '<div class="cff-item ';
                    $cff_post_type_class = 'cff-status-post';
                    if ($cff_post_type == 'link') $cff_post_type_class = 'cff-link-item';
                    if ($cff_post_type == 'event') $cff_post_type_class = 'cff-timeline-event';
                    if ($cff_post_type == 'photo') $cff_post_type_class = 'cff-photo-post';
                    if ($cff_post_type == 'video') $cff_post_type_class = 'cff-video-post';
                    if ($cff_post_type == 'swf') $cff_post_type_class = 'cff-swf-post';
                    if ($cff_post_type == 'offer') $cff_post_type_class = 'cff-offer-post';
                    $cff_post_item .= $cff_post_type_class;
                    if ($cff_album) $cff_post_item .= ' cff-album';

                    if ($cff_post_bg_color_check || $cff_post_style == "boxed") $cff_post_item .= ' cff-box';
                    if( $cff_box_shadow ) $cff_post_item .= ' cff-shadow';
                    if(isset($news->from->name)) $cff_post_item .=  ' author-'. cff_to_slug($news->from->name);
                    $cff_post_item .= '" id="cff_'. $cff_post_id .'" ' . $cff_item_styles . '>';
                    
                        //POST AUTHOR
                        if($cff_show_author) $cff_post_item .= $cff_author;
                        //DATE ABOVE
                        if ($cff_show_date && $cff_date_position == 'above') $cff_post_item .= $cff_date;
                        //POST TEXT
                        if($cff_show_text || $cff_show_desc) $cff_post_item .= $cff_post_text;
                        //LINK
                        if($cff_show_shared_links) $cff_post_item .= $cff_shared_link;
                        //DATE BELOW
                        if ( (!$cff_show_author && $cff_date_position == 'author') || $cff_show_date && $cff_date_position == 'below') {
                            if($cff_show_date && $cff_post_type !== 'event') $cff_post_item .= $cff_date;
                        }
                        //DATE BELOW (only for Event posts)
                        if ( (!$cff_show_author && $cff_date_position == 'author') || $cff_show_date && $cff_date_position == 'below') {
                            if($cff_show_date && $cff_post_type == 'event') $cff_post_item .= $cff_date;
                        }

                        //MEDIA LINK
                        if($cff_show_media_link) $cff_post_item .= $cff_media_link;
                        //VIEW ON FACEBOOK LINK
                        if($cff_show_link) $cff_post_item .= $cff_link;
                    
                    //End the post item
                    $cff_post_item .= '</div>';

                    //PUSH TO ARRAY
                    $cff_posts_array = cff_array_push_assoc($cff_posts_array, $i_post, $cff_post_item);

                } // End post type check

                if (isset($news->message)) $prev_post_message = $news->message;
                if (isset($news->link))  $prev_post_link = $news->link;
                if (isset($news->description))  $prev_post_description = $news->description;

            } // End the loop
        } //End isset($FBdata->data)

        //Sort the array in reverse order (newest first)
        if(!$cff_is_group) ksort($cff_posts_array);

    } // End ALL POSTS


    //Output the posts array
    $p = 0;
    foreach ($cff_posts_array as $post ) {
        if ( $p == $show_posts ) break;
        $cff_content .= $post;
        $p++;
    }

    //Add the Like Box inside
    if ($cff_like_box_position == 'bottom' && $cff_show_like_box && !$cff_like_box_outside) $cff_content .= $like_box;
    /* Credit link */
    $cff_show_credit = $atts['credit'];
    ($cff_show_credit == 'true' || $cff_show_credit == 'on') ? $cff_show_credit = true : $cff_show_credit = false;

    if($cff_show_credit) $cff_content .= '<p class="cff-credit"><a href="https://smashballoon.com/custom-facebook-feed/" target="_blank" style="color: #'.$link_color=$cff_posttext_link_color.'" title="Smash Balloon Custom Facebook Feed WordPress Plugin"><img src="'.plugins_url( '/img/smashballoon-tiny.png' , __FILE__ ).'" alt="Smash Balloon Custom Facebook Feed WordPress Plugin" />The Custom Facebook Feed plugin</a></p>';
    //End the feed
    $cff_content .= '</div><div class="cff-clear"></div>';
    //Add the Like Box outside
    if ($cff_like_box_position == 'bottom' && $cff_show_like_box && $cff_like_box_outside) $cff_content .= $like_box;
    
    //If the feed is loaded via Ajax then put the scripts into the shortcode itself
    $ajax_theme = $atts['ajax'];
    ( $ajax_theme == 'on' || $ajax_theme == 'true' || $ajax_theme == true ) ? $ajax_theme = true : $ajax_theme = false;
    if( $atts[ 'ajax' ] == 'false' ) $ajax_theme = false;
    if ($ajax_theme) {
        //Minify files?
        $options = get_option('cff_style_settings');
        isset($options[ 'cff_minify' ]) ? $cff_minify = $options[ 'cff_minify' ] : $cff_minify = '';
        $cff_minify ? $cff_min = '.min' : $cff_min = '';

        $cff_link_hashtags = $atts['linkhashtags'];
        ($cff_link_hashtags == 'true' || $cff_link_hashtags == 'on') ? $cff_link_hashtags = 'true' : $cff_link_hashtags = 'false';
        if($cff_title_link == 'true' || $cff_title_link == 'on') $cff_link_hashtags = 'false';
        $cff_content .= '<script type="text/javascript">var cfflinkhashtags = "' . $cff_link_hashtags . '";</script>';
        $cff_content .= '<script type="text/javascript" src="' . plugins_url( '/js/cff-scripts'.$cff_min.'.js?ver='.CFFVER , __FILE__ ) . '"></script>';
    }

    $cff_content .= '</div>';

    if( isset( $cff_posttext_link_color ) && !empty( $cff_posttext_link_color ) ) $cff_content .= '<style>#cff .cff-post-text a{ color: #'.$cff_posttext_link_color.'; }</style>';

    //Return our feed HTML to display
    return $cff_content;
}

//***FUNCTIONS***

//Link @[] or \u0040[Page ID:274:Page Name] post tagging format
function cff_desc_tags($description){
    preg_match_all( "/@\[(.*?)\]/", $description, $cff_tag_matches );
    $replace_strings_arr = array();
    foreach ( $cff_tag_matches[1] as $cff_tag_match ) {
        $cff_tag_parts = explode( ':', $cff_tag_match );
        $replace_strings_arr[] = '<a href="https://facebook.com/'.$cff_tag_parts[0].'">'.$cff_tag_parts[2].'</a>';
    }
    $cff_tag_iterator = 0;
    $cff_description_tagged = '';
    $cff_text_split = preg_split( "/@\[(.*?)\]/" , $description );
    foreach ( $cff_text_split as $cff_desc_split ) {
        if ( $cff_tag_iterator < count( $replace_strings_arr ) ) {
            $cff_description_tagged .= $cff_desc_split . $replace_strings_arr[ $cff_tag_iterator ];
        } else {
            $cff_description_tagged .= $cff_desc_split;
        }
        $cff_tag_iterator++;
    }

    return $cff_description_tagged;
}
//Sort message tags by offset value
function cffSortTags($a, $b) {
    return $a['offset'] - $b['offset'];
}

//Get JSON object of feed data
function cff_fetchUrl($url){
    $response = wp_remote_get($url);
    $feedData = wp_remote_retrieve_body( $response );

    $feedData = apply_filters( 'cff_filter_api_data', $feedData );

    return $feedData;
}

//Make links into span instead when the post text is made clickable
function cff_wrap_span($text) {
    $pattern  = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
    return preg_replace_callback($pattern, 'cff_wrap_span_callback', $text);
}
function cff_wrap_span_callback($matches) {
    $max_url_length = 100;
    $max_depth_if_over_length = 2;
    $ellipsis = '&hellip;';
    $target = 'target="_blank"';
    $url_full = $matches[0];
    $url_short = '';
    if (strlen($url_full) > $max_url_length) {
        $parts = parse_url($url_full);
        $url_short = $parts['scheme'] . '://' . preg_replace('/^www\./', '', $parts['host']) . '/';
        $path_components = explode('/', trim($parts['path'], '/'));
        foreach ($path_components as $dir) {
            $url_string_components[] = $dir . '/';
        }
        if (!empty($parts['query'])) {
            $url_string_components[] = '?' . $parts['query'];
        }
        if (!empty($parts['fragment'])) {
            $url_string_components[] = '#' . $parts['fragment'];
        }
        for ($k = 0; $k < count($url_string_components); $k++) {
            $curr_component = $url_string_components[$k];
            if ($k >= $max_depth_if_over_length || strlen($url_short) + strlen($curr_component) > $max_url_length) {
                if ($k == 0 && strlen($url_short) < $max_url_length) {
                    // Always show a portion of first directory
                    $url_short .= substr($curr_component, 0, $max_url_length - strlen($url_short));
                }
                $url_short .= $ellipsis;
                break;
            }
            $url_short .= $curr_component;
        }
    } else {
        $url_short = $url_full;
    }
    return "<span class='cff-break-word'>$url_short</span>";
}

//Use the timezone to offset the date as all post dates are in UTC +0000
function cff_set_timezone($original, $cff_timezone){
    $cff_date_time = new DateTime(date('m/d g:i a'), new DateTimeZone('UTC'));
    $cff_date_time->setTimeZone(new DateTimeZone($cff_timezone));
    $cff_date_time_offset = $cff_date_time->getOffset();

    $original = $original + $cff_date_time_offset;

    return $original;
}
//Time stamp function - used for posts
function cff_getdate($original, $date_format, $custom_date, $cff_date_translate_strings, $cff_timezone) {

    //Offset the date by the timezone
    $original = cff_set_timezone($original, $cff_timezone);
    
    switch ($date_format) {
        
        case '2':
            $print = date_i18n('F jS, g:i a', $original);
            break;
        case '3':
            $print = date_i18n('F jS', $original);
            break;
        case '4':
            $print = date_i18n('D F jS', $original);
            break;
        case '5':
            $print = date_i18n('l F jS', $original);
            break;
        case '6':
            $print = date_i18n('D M jS, Y', $original);
            break;
        case '7':
            $print = date_i18n('l F jS, Y', $original);
            break;
        case '8':
            $print = date_i18n('l F jS, Y - g:i a', $original);
            break;
        case '9':
            $print = date_i18n("l M jS, 'y", $original);
            break;
        case '10':
            $print = date_i18n('m.d.y', $original);
            break;
        case '11':
            $print = date_i18n('m/d/y', $original);
            break;
        case '12':
            $print = date_i18n('d.m.y', $original);
            break;
        case '13':
            $print = date_i18n('d/m/y', $original);
            break;
        default:
            
            $cff_second = $cff_date_translate_strings['cff_translate_second'];
            $cff_seconds = $cff_date_translate_strings['cff_translate_seconds'];
            $cff_minute = $cff_date_translate_strings['cff_translate_minute'];
            $cff_minutes = $cff_date_translate_strings['cff_translate_minutes'];
            $cff_hour = $cff_date_translate_strings['cff_translate_hour'];
            $cff_hours = $cff_date_translate_strings['cff_translate_hours'];
            $cff_day = $cff_date_translate_strings['cff_translate_day'];
            $cff_days = $cff_date_translate_strings['cff_translate_days'];
            $cff_week = $cff_date_translate_strings['cff_translate_week'];
            $cff_weeks = $cff_date_translate_strings['cff_translate_weeks'];
            $cff_month = $cff_date_translate_strings['cff_translate_month'];
            $cff_months = $cff_date_translate_strings['cff_translate_months'];
            $cff_year = $cff_date_translate_strings['cff_translate_years'];
            $cff_years = $cff_date_translate_strings['cff_translate_years'];
            $cff_ago = $cff_date_translate_strings['cff_translate_ago'];

            
            $periods = array($cff_second, $cff_minute, $cff_hour, $cff_day, $cff_week, $cff_month, $cff_year, "decade");
            $periods_plural = array($cff_seconds, $cff_minutes, $cff_hours, $cff_days, $cff_weeks, $cff_months, $cff_years, "decade");

            $lengths = array("60","60","24","7","4.35","12","10");
            $now = time();
            
            // is it future date or past date
            if($now > $original) {    
                $difference = $now - $original;
                $tense = $cff_ago;
            } else {
                $difference = $original - $now;
                $tense = $cff_ago;
            }
            for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
                $difference /= $lengths[$j];
            }
            
            $difference = round($difference);
            
            if($difference != 1) {
                $periods[$j] = $periods_plural[$j];
            }
            $print = "$difference $periods[$j] {$tense}";

            break;
        
    }
    if ( !empty($custom_date) ){
        $print = date_i18n($custom_date, $original);
    }

    return $print;
}
function cff_eventdate($original, $date_format, $custom_date) {
    switch ($date_format) {
        
        case '2':
            $print = date_i18n('F jS, g:ia', $original);
            break;
        case '3':
            $print = date_i18n('g:ia - F jS', $original);
            break;
        case '4':
            $print = date_i18n('g:ia, F jS', $original);
            break;
        case '5':
            $print = date_i18n('l F jS - g:ia', $original);
            break;
        case '6':
            $print = date_i18n('D M jS, Y, g:iA', $original);
            break;
        case '7':
            $print = date_i18n('l F jS, Y, g:iA', $original);
            break;
        case '8':
            $print = date_i18n('l F jS, Y - g:ia', $original);
            break;
        case '9':
            $print = date_i18n("l M jS, 'y", $original);
            break;
        case '10':
            $print = date_i18n('m.d.y - g:iA', $original);
            break;
        case '11':
            $print = date_i18n('m/d/y, g:ia', $original);
            break;
        case '12':
            $print = date_i18n('d.m.y - g:iA', $original);
            break;
        case '13':
            $print = date_i18n('d/m/y, g:ia', $original);
            break;
        default:
            $print = date_i18n('F j, Y, g:ia', $original);
            break;
    }
    if ( !empty($custom_date) ){
        $print = date_i18n($custom_date, $original);
    }
    return $print;
}
//Use custom stripos function if it's not available (only available in PHP 5+)
if(!is_callable('stripos')){
    function stripos($haystack, $needle){
        return strpos($haystack, stristr( $haystack, $needle ));
    }
}
function cff_stripos_arr($haystack, $needle) {
    if(!is_array($needle)) $needle = array($needle);
    foreach($needle as $what) {
        if(($pos = stripos($haystack, ltrim($what) ))!==false) return $pos;
    }
    return false;
}
function cff_mb_substr_replace($string, $replacement, $start, $length=NULL) {
    if (is_array($string)) {
        $num = count($string);
        // $replacement
        $replacement = is_array($replacement) ? array_slice($replacement, 0, $num) : array_pad(array($replacement), $num, $replacement);
        // $start
        if (is_array($start)) {
            $start = array_slice($start, 0, $num);
            foreach ($start as $key => $value)
                $start[$key] = is_int($value) ? $value : 0;
        }
        else {
            $start = array_pad(array($start), $num, $start);
        }
        // $length
        if (!isset($length)) {
            $length = array_fill(0, $num, 0);
        }
        elseif (is_array($length)) {
            $length = array_slice($length, 0, $num);
            foreach ($length as $key => $value)
                $length[$key] = isset($value) ? (is_int($value) ? $value : $num) : 0;
        }
        else {
            $length = array_pad(array($length), $num, $length);
        }
        // Recursive call
        return array_map(__FUNCTION__, $string, $replacement, $start, $length);
    }
    preg_match_all('/./us', (string)$string, $smatches);
    preg_match_all('/./us', (string)$replacement, $rmatches);
    if ($length === NULL) $length = mb_strlen($string);
    array_splice($smatches[0], $start, $length, $rmatches[0]);
    return join($smatches[0]);
}

//Push to assoc array
function cff_array_push_assoc($array, $key, $value){
    $array[$key] = $value;
    return $array;
}
//Convert string to slug
function cff_to_slug($string){
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
}


//Allows shortcodes in theme
add_filter('widget_text', 'do_shortcode');

//Enqueue stylesheet
add_action( 'wp_enqueue_scripts', 'cff_add_my_stylesheet' );
function cff_add_my_stylesheet() {

    //Minify files?
    $options = get_option('cff_style_settings');
    isset($options[ 'cff_minify' ]) ? $cff_minify = $options[ 'cff_minify' ] : $cff_minify = '';
    $cff_minify ? $cff_min = '.min' : $cff_min = '';

    // Respects SSL, Style.css is relative to the current file
    wp_register_style( 'cff', plugins_url('css/cff-style'.$cff_min.'.css', __FILE__), array(), CFFVER );
    wp_enqueue_style( 'cff' );

    $options = get_option('cff_style_settings');

    if( !isset( $options[ 'cff_font_source' ] ) ){
        wp_enqueue_style( 'sb-font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
    } else {

        if( $options[ 'cff_font_source' ] == 'none' ){
            //Do nothing
        } else if( $options[ 'cff_font_source' ] == 'local' ){
            wp_enqueue_style( 'sb-font-awesome', plugins_url('css/font-awesome.min.css', __FILE__), array(), '4.7.0' );
        } else {
            wp_enqueue_style( 'sb-font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
        }

    }

    
}
//Enqueue scripts
add_action( 'wp_enqueue_scripts', 'cff_scripts_method' );
function cff_scripts_method() {

    //Minify files?
    $options = get_option('cff_style_settings');
    isset($options[ 'cff_minify' ]) ? $cff_minify = $options[ 'cff_minify' ] : $cff_minify = '';
    $cff_minify ? $cff_min = '.min' : $cff_min = '';

    //Register the script to make it available
    wp_register_script( 'cffscripts', plugins_url( '/js/cff-scripts'.$cff_min.'.js' , __FILE__ ), array('jquery'), CFFVER, true );
    //Enqueue it to load it onto the page
    wp_enqueue_script('cffscripts');
}

function cff_activate() {
    $options = get_option('cff_style_settings');

    //Show all post types
    $options[ 'cff_show_links_type' ] = true;
    $options[ 'cff_show_event_type' ] = true;
    $options[ 'cff_show_video_type' ] = true;
    $options[ 'cff_show_photos_type' ] = true;
    $options[ 'cff_show_status_type' ] = true;
    $options[ 'cff_show_albums_type' ] = true;
    $options[ 'cff_show_author' ] = true;
    $options[ 'cff_show_text' ] = true;
    $options[ 'cff_show_desc' ] = true;
    $options[ 'cff_show_shared_links' ] = true;
    $options[ 'cff_show_date' ] = true;
    $options[ 'cff_show_media' ] = true;
    $options[ 'cff_show_media_link' ] = true;
    $options[ 'cff_show_event_title' ] = true;
    $options[ 'cff_show_event_details' ] = true;
    $options[ 'cff_show_meta' ] = true;
    $options[ 'cff_show_link' ] = true;
    $options[ 'cff_show_like_box' ] = true;
    $options[ 'cff_show_facebook_link' ] = true;
    $options[ 'cff_show_facebook_share' ] = true;
    $options[ 'cff_event_title_link' ] = true;

    update_option( 'cff_style_settings', $options );

    get_option('cff_show_access_token');
    update_option( 'cff_show_access_token', true );

    //Run cron twice daily when plugin is first activated for new users
    wp_schedule_event(time(), 'twicedaily', 'cff_cron_job');
}
register_activation_hook( __FILE__, 'cff_activate' );

function cff_deactivate() {
    wp_clear_scheduled_hook('cff_cron_job');
}
register_deactivation_hook(__FILE__, 'cff_deactivate');

//Uninstall
function cff_uninstall()
{
    if ( ! current_user_can( 'activate_plugins' ) )
        return;

    //If the user is preserving the settings then don't delete them
    $cff_preserve_settings = get_option('cff_preserve_settings');
    if($cff_preserve_settings) return;

    //Settings
    delete_option( 'cff_show_access_token' );
    delete_option( 'cff_access_token' );
    delete_option( 'cff_page_id' );
    delete_option( 'cff_num_show' );
    delete_option( 'cff_post_limit' );
    delete_option( 'cff_show_others' );
    delete_option( 'cff_cache_time' );
    delete_option( 'cff_cache_time_unit' );
    delete_option( 'cff_locale' );
    delete_option( 'cff_ajax' );
    delete_option( 'cff_preserve_settings' );
    //Style & Layout
    delete_option( 'cff_title_length' );
    delete_option( 'cff_body_length' );
    delete_option('cff_style_settings');
}
register_uninstall_hook( __FILE__, 'cff_uninstall' );
add_action( 'wp_head', 'cff_custom_css' );
function cff_custom_css() {
    $options = get_option('cff_style_settings');
    isset($options[ 'cff_custom_css' ]) ? $cff_custom_css = $options[ 'cff_custom_css' ] : $cff_custom_css = '';

    if( !empty($cff_custom_css) ) echo "\r\n";
    if( !empty($cff_custom_css) ) echo '<!-- Custom Facebook Feed Custom CSS -->';
    if( !empty($cff_custom_css) ) echo "\r\n";
    if( !empty($cff_custom_css) ) echo '<style type="text/css">';
    if( !empty($cff_custom_css) ) echo "\r\n";
    if( !empty($cff_custom_css) ) echo stripslashes($cff_custom_css);
    if( !empty($cff_custom_css) ) echo "\r\n";
    if( !empty($cff_custom_css) ) echo '</style>';
    if( !empty($cff_custom_css) ) echo "\r\n";
}
add_action( 'wp_footer', 'cff_js' );
function cff_js() {
    $options = get_option('cff_style_settings');
    $cff_custom_js = isset($options[ 'cff_custom_js' ]) ? $options[ 'cff_custom_js' ] : '';

    //Link hashtags?
    isset($options[ 'cff_link_hashtags' ]) ? $cff_link_hashtags = $options[ 'cff_link_hashtags' ] : $cff_link_hashtags = 'true';
    ($cff_link_hashtags == 'true' || $cff_link_hashtags == 'on') ? $cff_link_hashtags = 'true' : $cff_link_hashtags = 'false';

    //If linking the post text then don't link the hashtags
    isset($options[ 'cff_title_link' ]) ? $cff_title_link = $options[ 'cff_title_link' ] : $cff_title_link = false;
    ($cff_title_link == 'true' || $cff_title_link == 'on') ? $cff_title_link = true : $cff_title_link = false;
    if ($cff_title_link) $cff_link_hashtags = 'false';

    
    echo '<!-- Custom Facebook Feed JS -->';
    echo "\r\n";
    echo '<script type="text/javascript">';
    echo "\r\n";
    echo 'var cfflinkhashtags = "' . $cff_link_hashtags . '";';
    echo "\r\n";
    if( !empty($cff_custom_js) ) echo "jQuery( document ).ready(function($) {";
    if( !empty($cff_custom_js) ) echo "\r\n";
    if( !empty($cff_custom_js) ) echo stripslashes($cff_custom_js);
    if( !empty($cff_custom_js) ) echo "\r\n";
    if( !empty($cff_custom_js) ) echo "});";
    if( !empty($cff_custom_js) ) echo "\r\n";
    echo '</script>';
    echo "\r\n";
}



//AUTOLINK
$GLOBALS['autolink_options'] = array(

    # Should http:// be visibly stripped from the front
    # of URLs?
    'strip_protocols' => true,

);

####################################################################

function cff_autolink($text, $link_color='', $span_tag = false, $limit=100, $tagfill='class="cff-break-word"', $auto_title = true){

    $text = cff_autolink_do($text, $link_color, '![a-z][a-z-]+://!i',    $limit, $tagfill, $auto_title, $span_tag);
    $text = cff_autolink_do($text, $link_color, '!(mailto|skype):!i',    $limit, $tagfill, $auto_title, $span_tag);
    $text = cff_autolink_do($text, $link_color, '!www\\.!i',         $limit, $tagfill, $auto_title, 'http://', $span_tag);
    return $text;
}

####################################################################

function cff_autolink_do($text, $link_color, $sub, $limit, $tagfill, $auto_title, $span_tag, $force_prefix=null){

    $text_l = StrToLower($text);
    $cursor = 0;
    $loop = 1;
    $buffer = '';

    while (($cursor < strlen($text)) && $loop){

        $ok = 1;
        $matched = preg_match($sub, $text_l, $m, PREG_OFFSET_CAPTURE, $cursor);

        if (!$matched){

            $loop = 0;
            $ok = 0;

        }else{

            $pos = $m[0][1];
            $sub_len = strlen($m[0][0]);

            $pre_hit = substr($text, $cursor, $pos-$cursor);
            $hit = substr($text, $pos, $sub_len);
            $pre = substr($text, 0, $pos);
            $post = substr($text, $pos + $sub_len);

            $fail_text = $pre_hit.$hit;
            $fail_len = strlen($fail_text);

            #
            # substring found - first check to see if we're inside a link tag already...
            #

            $bits = preg_split("!</a>!i", $pre);
            $last_bit = array_pop($bits);
            if (preg_match("!<a\s!i", $last_bit)){

                #echo "fail 1 at $cursor<br />\n";

                $ok = 0;
                $cursor += $fail_len;
                $buffer .= $fail_text;
            }
        }

        #
        # looks like a nice spot to autolink from - check the pre
        # to see if there was whitespace before this match
        #

        if ($ok){

            if ($pre){
                if (!preg_match('![\s\(\[\{>]$!s', $pre)){

                    #echo "fail 2 at $cursor ($pre)<br />\n";

                    $ok = 0;
                    $cursor += $fail_len;
                    $buffer .= $fail_text;
                }
            }
        }

        #
        # we want to autolink here - find the extent of the url
        #

        if ($ok){
            if (preg_match('/^([a-z0-9\-\.\/\-_%~!?=,:;&+*#@\(\)\$]+)/i', $post, $matches)){

                $url = $hit.$matches[1];

                $cursor += strlen($url) + strlen($pre_hit);
                $buffer .= $pre_hit;

                $url = html_entity_decode($url);


                #
                # remove trailing punctuation from url
                #

                while (preg_match('|[.,!;:?]$|', $url)){
                    $url = substr($url, 0, strlen($url)-1);
                    $cursor--;
                }
                foreach (array('()', '[]', '{}') as $pair){
                    $o = substr($pair, 0, 1);
                    $c = substr($pair, 1, 1);
                    if (preg_match("!^(\\$c|^)[^\\$o]+\\$c$!", $url)){
                        $url = substr($url, 0, strlen($url)-1);
                        $cursor--;
                    }
                }


                #
                # nice-i-fy url here
                #

                $link_url = $url;
                $display_url = $url;

                if ($force_prefix) $link_url = $force_prefix.$link_url;

                if ($GLOBALS['autolink_options']['strip_protocols']){
                    if (preg_match('!^(http|https)://!i', $display_url, $m)){

                        $display_url = substr($display_url, strlen($m[1])+3);
                    }
                }

                $display_url = cff_autolink_label($display_url, $limit);


                #
                # add the url
                #
                
                if ($display_url != $link_url && !preg_match('@title=@msi',$tagfill) && $auto_title) {

                    $display_quoted = preg_quote($display_url, '!');

                    if (!preg_match("!^(http|https)://{$display_quoted}$!i", $link_url)){

                        $tagfill .= ' title="'.$link_url.'"';
                    }
                }

                $link_url_enc = HtmlSpecialChars($link_url);
                $display_url_enc = HtmlSpecialChars($display_url);

                
                if( substr( $link_url_enc, 0, 4 ) !== "http" ) $link_url_enc = 'http://' . $link_url_enc;
                $buffer .= "<a href=\"{$link_url_enc}\">{$display_url_enc}</a>";
                
            
            }else{
                #echo "fail 3 at $cursor<br />\n";

                $ok = 0;
                $cursor += $fail_len;
                $buffer .= $fail_text;
            }
        }

    }

    #
    # add everything from the cursor to the end onto the buffer.
    #

    $buffer .= substr($text, $cursor);

    return $buffer;
}

####################################################################

function cff_autolink_label($text, $limit){

    if (!$limit){ return $text; }

    if (strlen($text) > $limit){
        return substr($text, 0, $limit-3).'...';
    }

    return $text;
}

####################################################################

function cff_autolink_email($text, $tagfill=''){

    $atom = '[^()<>@,;:\\\\".\\[\\]\\x00-\\x20\\x7f]+'; # from RFC822

    #die($atom);

    $text_l = StrToLower($text);
    $cursor = 0;
    $loop = 1;
    $buffer = '';

    while(($cursor < strlen($text)) && $loop){

        #
        # find an '@' symbol
        #

        $ok = 1;
        $pos = strpos($text_l, '@', $cursor);

        if ($pos === false){

            $loop = 0;
            $ok = 0;

        }else{

            $pre = substr($text, $cursor, $pos-$cursor);
            $hit = substr($text, $pos, 1);
            $post = substr($text, $pos + 1);

            $fail_text = $pre.$hit;
            $fail_len = strlen($fail_text);

            #die("$pre::$hit::$post::$fail_text");

            #
            # substring found - first check to see if we're inside a link tag already...
            #

            $bits = preg_split("!</a>!i", $pre);
            $last_bit = array_pop($bits);
            if (preg_match("!<a\s!i", $last_bit)){

                #echo "fail 1 at $cursor<br />\n";

                $ok = 0;
                $cursor += $fail_len;
                $buffer .= $fail_text;
            }
        }

        #
        # check backwards
        #

        if ($ok){
            if (preg_match("!($atom(\.$atom)*)\$!", $pre, $matches)){

                # move matched part of address into $hit

                $len = strlen($matches[1]);
                $plen = strlen($pre);

                $hit = substr($pre, $plen-$len).$hit;
                $pre = substr($pre, 0, $plen-$len);

            }else{

                #echo "fail 2 at $cursor ($pre)<br />\n";

                $ok = 0;
                $cursor += $fail_len;
                $buffer .= $fail_text;
            }
        }

        #
        # check forwards
        #

        if ($ok){
            if (preg_match("!^($atom(\.$atom)*)!", $post, $matches)){

                # move matched part of address into $hit

                $len = strlen($matches[1]);

                $hit .= substr($post, 0, $len);
                $post = substr($post, $len);

            }else{
                #echo "fail 3 at $cursor ($post)<br />\n";

                $ok = 0;
                $cursor += $fail_len;
                $buffer .= $fail_text;
            }
        }

        #
        # commit
        #

        if ($ok) {

            $cursor += strlen($pre) + strlen($hit);
            $buffer .= $pre;
            $buffer .= "<a href=\"mailto:$hit\"$tagfill>$hit</a>";

        }

    }

    #
    # add everything from the cursor to the end onto the buffer.
    #

    $buffer .= substr($text, $cursor);

    return $buffer;
}

####################################################################


?>