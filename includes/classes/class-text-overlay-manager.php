<?php
/**
 * Text Overlay Manager
 *
 * @package TIMU_CORE
 * @subpackage TIMU_MEDIA_HUB
 */

declare(strict_types=1);

namespace TIMU\MediaSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages text overlays on images
 */
class Text_Overlay_Manager extends Image_Processor {

	/**
	 * Default fonts and colors
	 *
	 * @var array
	 */
	private static $defaults = array(
		'font_size'   => 48,
		'font_color'  => '#FFFFFF',
		'bg_color'    => '#000000',
		'bg_opacity'  => 0.5,
		'position'    => 'bottom',
		'padding'     => 20,
	);

	/**
	 * Add text overlay to image
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $text Text to add.
	 * @param array  $options Options for text overlay.
	 * @return array|false Result with URL and path, or false on failure.
	 */
	public static function add_text_overlay( int $attachment_id, string $text, array $options = array() ) {
		$options = wp_parse_args( $options, self::$defaults );
		
		$file_path = get_attached_file( $attachment_id );
		if ( ! $file_path || ! file_exists( $file_path ) ) {
			return false;
		}

		$editor = self::get_image_editor();
		
		if ( $editor === 'imagick' ) {
			return self::add_text_imagick( $attachment_id, $file_path, $text, $options );
		} elseif ( $editor === 'gd' ) {
			return self::add_text_gd( $attachment_id, $file_path, $text, $options );
		}

		return false;
	}

	/**
	 * Add text using Imagick
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $file_path File path.
	 * @param string $text Text to add.
	 * @param array  $options Options.
	 * @return array|false
	 */
	private static function add_text_imagick( int $attachment_id, string $file_path, string $text, array $options ) {
		if ( ! class_exists( 'Imagick' ) ) {
			return false;
		}

		$imagick = new \Imagick( $file_path );
		$draw    = new \ImagickDraw();

		// Set text properties
		$draw->setFillColor( new \ImagickPixel( $options['font_color'] ) );
		$draw->setFontSize( $options['font_size'] );
		$draw->setGravity( self::get_imagick_gravity( $options['position'] ) );

		// Add semi-transparent background
		$metrics = $imagick->queryFontMetrics( $draw, $text );
		$text_width = (int) $metrics['textWidth'];
		$text_height = (int) $metrics['textHeight'];

		// Annotate image with text
		$imagick->annotateImage( $draw, $options['padding'], $options['padding'], 0, $text );

		// Save the image
		$upload_dir = wp_upload_dir();
		$pathinfo   = pathinfo( $file_path );
		$new_filename = $pathinfo['filename'] . '-text-overlay.' . $pathinfo['extension'];
		$new_filepath = $pathinfo['dirname'] . '/' . $new_filename;

		$imagick->writeImage( $new_filepath );
		$imagick->clear();
		$imagick->destroy();

		return array(
			'path' => $new_filepath,
			'url'  => str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $new_filepath ),
		);
	}

	/**
	 * Add text using GD
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $file_path File path.
	 * @param string $text Text to add.
	 * @param array  $options Options.
	 * @return array|false
	 */
	private static function add_text_gd( int $attachment_id, string $file_path, string $text, array $options ) {
		$image_info = getimagesize( $file_path );
		if ( ! $image_info ) {
			return false;
		}

		$mime = $image_info['mime'];
		switch ( $mime ) {
			case 'image/jpeg':
				$image = imagecreatefromjpeg( $file_path );
				break;
			case 'image/png':
				$image = imagecreatefrompng( $file_path );
				break;
			case 'image/gif':
				$image = imagecreatefromgif( $file_path );
				break;
			default:
				return false;
		}

		if ( ! $image ) {
			return false;
		}

		// Convert hex color to RGB
		$color_rgb = self::hex_to_rgb( $options['font_color'] );
		$text_color = imagecolorallocate( $image, $color_rgb[0], $color_rgb[1], $color_rgb[2] );

		// Calculate text position
		$width  = imagesx( $image );
		$height = imagesy( $image );
		$font_size = $options['font_size'];

		// Use built-in font (GD limitation without external font files)
		// Font 5 is the largest built-in GD font
		define( 'TIMU_GD_LARGE_FONT', 5 );
		$font = TIMU_GD_LARGE_FONT;
		$text_width  = imagefontwidth( $font ) * strlen( $text );
		$text_height = imagefontheight( $font );

		list( $x, $y ) = self::calculate_text_position( $width, $height, $text_width, $text_height, $options['position'], $options['padding'] );

		// Draw semi-transparent background
		$bg_rgb = self::hex_to_rgb( $options['bg_color'] );
		$bg_color = imagecolorallocatealpha( $image, $bg_rgb[0], $bg_rgb[1], $bg_rgb[2], (int) ( ( 1 - $options['bg_opacity'] ) * 127 ) );
		imagefilledrectangle( $image, $x - 10, $y - 5, $x + $text_width + 10, $y + $text_height + 5, $bg_color );

		// Add text
		imagestring( $image, $font, $x, $y, $text, $text_color );

		// Save the image
		$upload_dir = wp_upload_dir();
		$pathinfo   = pathinfo( $file_path );
		$new_filename = $pathinfo['filename'] . '-text-overlay.' . $pathinfo['extension'];
		$new_filepath = $pathinfo['dirname'] . '/' . $new_filename;

		switch ( $mime ) {
			case 'image/jpeg':
				imagejpeg( $image, $new_filepath, 90 );
				break;
			case 'image/png':
				imagepng( $image, $new_filepath );
				break;
			case 'image/gif':
				imagegif( $image, $new_filepath );
				break;
		}

		imagedestroy( $image );

		return array(
			'path' => $new_filepath,
			'url'  => str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $new_filepath ),
		);
	}

	/**
	 * Get Imagick gravity constant from position
	 *
	 * @param string $position Position string.
	 * @return int
	 */
	private static function get_imagick_gravity( string $position ): int {
		$gravity_map = array(
			'top'          => \Imagick::GRAVITY_NORTH,
			'top-left'     => \Imagick::GRAVITY_NORTHWEST,
			'top-right'    => \Imagick::GRAVITY_NORTHEAST,
			'center'       => \Imagick::GRAVITY_CENTER,
			'bottom'       => \Imagick::GRAVITY_SOUTH,
			'bottom-left'  => \Imagick::GRAVITY_SOUTHWEST,
			'bottom-right' => \Imagick::GRAVITY_SOUTHEAST,
		);

		return $gravity_map[ $position ] ?? \Imagick::GRAVITY_SOUTH;
	}

	/**
	 * Calculate text position for GD
	 *
	 * @param int    $img_width Image width.
	 * @param int    $img_height Image height.
	 * @param int    $text_width Text width.
	 * @param int    $text_height Text height.
	 * @param string $position Position.
	 * @param int    $padding Padding.
	 * @return array [x, y] coordinates.
	 */
	private static function calculate_text_position( int $img_width, int $img_height, int $text_width, int $text_height, string $position, int $padding ): array {
		$x = $y = 0;

		switch ( $position ) {
			case 'top':
				$x = (int) ( ( $img_width - $text_width ) / 2 );
				$y = $padding;
				break;
			case 'top-left':
				$x = $padding;
				$y = $padding;
				break;
			case 'top-right':
				$x = $img_width - $text_width - $padding;
				$y = $padding;
				break;
			case 'center':
				$x = (int) ( ( $img_width - $text_width ) / 2 );
				$y = (int) ( ( $img_height - $text_height ) / 2 );
				break;
			case 'bottom':
				$x = (int) ( ( $img_width - $text_width ) / 2 );
				$y = $img_height - $text_height - $padding;
				break;
			case 'bottom-left':
				$x = $padding;
				$y = $img_height - $text_height - $padding;
				break;
			case 'bottom-right':
				$x = $img_width - $text_width - $padding;
				$y = $img_height - $text_height - $padding;
				break;
		}

		return array( $x, $y );
	}

	/**
	 * Convert hex color to RGB
	 *
	 * @param string $hex Hex color.
	 * @return array RGB values.
	 */
	private static function hex_to_rgb( string $hex ): array {
		$hex = ltrim( $hex, '#' );
		return array(
			hexdec( substr( $hex, 0, 2 ) ),
			hexdec( substr( $hex, 2, 2 ) ),
			hexdec( substr( $hex, 4, 2 ) ),
		);
	}
}
