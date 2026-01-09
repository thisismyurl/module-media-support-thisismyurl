<?php
/**
 * Hashtag Generator
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
 * Generates hashtags and captions based on image content
 */
class Hashtag_Generator {

	/**
	 * Default hashtag sets by category
	 *
	 * @var array
	 */
	private static $default_hashtags = array(
		'general'     => array( 'photography', 'photooftheday', 'instagood', 'picoftheday' ),
		'business'    => array( 'business', 'entrepreneur', 'marketing', 'branding', 'success' ),
		'travel'      => array( 'travel', 'wanderlust', 'travelgram', 'instatravel', 'adventure' ),
		'food'        => array( 'food', 'foodie', 'foodporn', 'instafood', 'delicious' ),
		'fashion'     => array( 'fashion', 'style', 'ootd', 'fashionista', 'instafashion' ),
		'technology'  => array( 'tech', 'technology', 'innovation', 'digital', 'startup' ),
		'nature'      => array( 'nature', 'naturephotography', 'landscape', 'outdoors', 'wilderness' ),
		'fitness'     => array( 'fitness', 'workout', 'gym', 'health', 'fitlife' ),
	);

	/**
	 * Generate hashtags for an image
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $category Category hint.
	 * @param int    $count Number of hashtags to generate.
	 * @return array Suggested hashtags.
	 */
	public static function generate_hashtags( int $attachment_id, string $category = 'general', int $count = 10 ): array {
		$hashtags = array();

		// Get image metadata
		$metadata = wp_get_attachment_metadata( $attachment_id );
		$post     = get_post( $attachment_id );
		
		// Extract keywords from title, caption, and alt text
		$keywords = self::extract_keywords( $attachment_id );

		// Get category-specific hashtags
		if ( array_key_exists( $category, self::$default_hashtags ) ) {
			$hashtags = array_merge( $hashtags, self::$default_hashtags[ $category ] );
		} else {
			$hashtags = array_merge( $hashtags, self::$default_hashtags['general'] );
		}

		// Add keywords as hashtags
		foreach ( $keywords as $keyword ) {
			$hashtag = self::sanitize_hashtag( $keyword );
			if ( $hashtag && ! in_array( $hashtag, $hashtags, true ) ) {
				$hashtags[] = $hashtag;
			}
		}

		// Limit to requested count
		$hashtags = array_slice( $hashtags, 0, $count );

		// Add # prefix
		return array_map(
			function( $tag ) {
				return '#' . $tag;
			},
			$hashtags
		);
	}

	/**
	 * Generate caption for an image
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $style Caption style ('short', 'medium', 'long').
	 * @return string Generated caption.
	 */
	public static function generate_caption( int $attachment_id, string $style = 'medium' ): string {
		$post = get_post( $attachment_id );
		
		// Try to use existing caption or description
		if ( ! empty( $post->post_excerpt ) ) {
			$base_caption = $post->post_excerpt;
		} elseif ( ! empty( $post->post_content ) ) {
			$base_caption = $post->post_content;
		} else {
			$base_caption = $post->post_title;
		}

		// Clean up caption
		$caption = wp_strip_all_tags( $base_caption );
		$caption = trim( $caption );

		// Adjust length based on style
		switch ( $style ) {
			case 'short':
				$caption = wp_trim_words( $caption, 10, '...' );
				break;
			case 'long':
				// Keep full caption
				break;
			case 'medium':
			default:
				$caption = wp_trim_words( $caption, 25, '...' );
				break;
		}

		return $caption;
	}

	/**
	 * Extract keywords from image metadata
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return array Keywords.
	 */
	private static function extract_keywords( int $attachment_id ): array {
		$keywords = array();
		$post     = get_post( $attachment_id );

		if ( ! $post ) {
			return $keywords;
		}

		// Extract from title
		$title_words = explode( ' ', strtolower( $post->post_title ) );
		$keywords    = array_merge( $keywords, $title_words );

		// Extract from alt text
		$alt_text = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		if ( $alt_text ) {
			$alt_words = explode( ' ', strtolower( $alt_text ) );
			$keywords  = array_merge( $keywords, $alt_words );
		}

		// Extract from caption
		if ( $post->post_excerpt ) {
			$caption_words = explode( ' ', strtolower( $post->post_excerpt ) );
			$keywords      = array_merge( $keywords, $caption_words );
		}

		// Remove common words
		$stop_words = array( 'a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by' );
		$keywords   = array_diff( $keywords, $stop_words );

		// Remove duplicates and empty values
		$keywords = array_filter( array_unique( $keywords ) );

		return array_values( $keywords );
	}

	/**
	 * Sanitize hashtag
	 *
	 * @param string $tag Tag to sanitize.
	 * @return string Sanitized tag.
	 */
	private static function sanitize_hashtag( string $tag ): string {
		// Remove non-alphanumeric characters except underscores
		$tag = preg_replace( '/[^a-zA-Z0-9_]/', '', $tag );
		
		// Remove leading/trailing underscores
		$tag = trim( $tag, '_' );
		
		// Ensure minimum length
		if ( strlen( $tag ) < 3 ) {
			return '';
		}

		return strtolower( $tag );
	}

	/**
	 * Get available categories
	 *
	 * @return array Categories.
	 */
	public static function get_categories(): array {
		return array_keys( self::$default_hashtags );
	}
}
