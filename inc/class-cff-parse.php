<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class CFF_Parse
{
	public static function get_link( $header_data ) {
		$link = isset( $header_data->link) ? $header_data->link : "https://facebook.com";
		return $link;
	}

	public static function get_cover_source( $header_data ) {
		$url = isset( $header_data->cover->source ) ? $header_data->cover->source : '';
		return $url;
	}

	public static function get_avatar( $header_data ) {
		$avatar = isset( $header_data->picture->data->url ) ? $header_data->picture->data->url : '';
		return $avatar;
	}

	public static function get_name( $header_data ) {
		$name = isset( $header_data->name ) ? $header_data->name : '';
		return $name;
	}

	public static function get_bio( $header_data ) {
		$about = isset( $header_data->about ) ? $header_data->about : '';
		return $about;
	}

	public static function get_likes( $header_data ) {
		$likes = isset( $header_data->fan_count ) ? $header_data->fan_count : '';
		return $likes;
	}
}