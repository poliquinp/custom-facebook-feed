<?php
/**
 * Custom Facebook Feed Header Template
 * Adds account information and an avatar to the top of the feed
 *
 * @version 2.15 Custom Facebook Feed by Smash Balloon
 *
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$link = CFF_Parse::get_link( $header_data );
$avatar = CFF_Parse::get_avatar( $header_data );
$name = CFF_Parse::get_name( $header_data );
$cover_url = CFF_Parse::get_cover_source( $header_data );
$likes_count = CFF_Parse::get_likes( $header_data );
$bio  = CFF_Parse::get_bio( $header_data );
$should_show_bio = $bio !== '' ? $cff_header_bio : false;
$bio_class = $cff_header_bio ? ' cff-has-about' : '';
$avatar_class = $cff_header_name ? ' cff-has-name' : '';
$cover_class = $cff_header_cover ? ' cff-has-cover' : '';
!empty($cff_header_text_size) ? $header_font_size = 'style="font-size:'. $cff_header_text_size .'px;"' : $header_font_size = '';

//If it's a group it doesn't return the 'link' field so use the group ID instead
if( $link == 'https://facebook.com' ) $link .= '/'.$page_id;

if( empty($cover_url) ){
	$cff_header_cover = false;
	$cover_class = '';
}
if( empty($likes_count) ){
	$cff_header_bio = false;
}

$header_text_color_style = '';
$header_hero_style = $cff_header_cover_height !== 300 ? ' style="height: '.$cff_header_cover_height.'px";' : '';

$square_logo = '<svg aria-hidden="true" focusable="false" data-prefix="fab" data-icon="facebook-square" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-facebook-square fa-w-14"><path fill="currentColor" d="M400 32H48A48 48 0 0 0 0 80v352a48 48 0 0 0 48 48h137.25V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.27c-30.81 0-40.42 19.12-40.42 38.73V256h68.78l-11 71.69h-57.78V480H400a48 48 0 0 0 48-48V80a48 48 0 0 0-48-48z" class=""></path></svg>';
?>
<div id="cff-visual-header-<?php echo esc_attr( preg_replace( "/[^A-Za-z0-9]/", '', $page_id ) ); ?>" class="cff-visual-header<?php echo $avatar_class . $bio_class . $cover_class ?>">
	<?php if ( $cff_header_cover ) : ?>
	<div class="cff-header-hero"<?php echo $header_hero_style; ?>>
		<img src="<?php echo esc_url( $cover_url ); ?>" alt="<?php echo esc_attr( sprintf( __( 'Cover for %s', 'custom-facebook-feed' ), $name ) ); ?>">
		<?php if ( $cff_header_bio ) : ?>
		<div class="cff-likes-box">
			<div class="cff-square-logo"><?php echo $square_logo; ?></div>
			<div class="cff-likes-count">
				<?php echo number_format( $likes_count, 0 ); ?>
			</div>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>
	<div class="cff-header-inner-wrap">
		<?php if ( $cff_header_name && $avatar !== '' ) : ?>
			<div class="cff-header-img">
                <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="nofollow noopener" title="<?php echo esc_attr( $name ); ?>"><img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $name ); ?>"></a>
			</div>
		<?php endif; ?>
		<div class="cff-header-text"<?php echo $header_style_attribute; ?>>

		<?php if ( $cff_header_name ) : ?>
            <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="nofollow noopener" title="<?php echo esc_attr( $name ); ?>" class="cff-header-name"><h3 <?php echo $header_font_size; ?>><?php echo esc_html( $name ); ?></h3></a>
		<?php endif; ?>
        <?php if ( $cff_header_bio && !$cff_header_cover ) : ?>
            <div class="cff-bio-info">
                <span class="cff-posts-count"><?php echo $square_logo . number_format( $likes_count, 0 ); ?></span>
            </div>
        <?php endif; ?>
		<?php if ( $should_show_bio ) : ?>
			<p class="cff-bio" <?php echo $header_text_color_style; ?>><?php echo str_replace( '&lt;br /&gt;', '<br>', esc_html( nl2br( $bio ) ) ); ?></p>
		<?php endif; ?>
		</div>
	</div>
</div>