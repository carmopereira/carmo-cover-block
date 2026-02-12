<?php
declare(strict_types=1);

/**
 * Plugin Name:       Carmo Cover Block
 * Description:       Extends the core Cover block with ACF background images (desktop/mobile).
 * Version:           0.2.0
 * Requires at least: 6.9
 * Requires PHP:      8.2
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       carmo-cover-block
 *
 * @package CarmoCoverBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue editor script that adds ACF controls to core/cover.
 */
function carmo_cover_block_enqueue_editor_assets(): void {
	$asset_file = __DIR__ . '/build/index.asset.php';

	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = require $asset_file;

	wp_enqueue_script(
		'carmo-cover-block-editor',
		plugins_url( 'build/index.js', __FILE__ ),
		$asset['dependencies'],
		$asset['version'],
		false
	);
}
add_action( 'enqueue_block_editor_assets', 'carmo_cover_block_enqueue_editor_assets' );

/**
 * Enqueue frontend view script for responsive background switching.
 */
function carmo_cover_block_enqueue_frontend_assets(): void {
	if ( is_admin() ) {
		return;
	}

	$asset_file = __DIR__ . '/build/view.asset.php';

	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$asset = require $asset_file;

	wp_enqueue_script(
		'carmo-cover-block-view',
		plugins_url( 'build/view.js', __FILE__ ),
		$asset['dependencies'],
		$asset['version'],
		true
	);
}
add_action( 'wp_enqueue_scripts', 'carmo_cover_block_enqueue_frontend_assets' );

/**
 * Filter core/cover block output to inject ACF background images.
 *
 * @param string $block_content The block HTML.
 * @param array  $block         The parsed block data.
 * @return string Modified block HTML.
 */
function carmo_cover_block_render_cover( string $block_content, array $block ): string {
	if ( ! function_exists( 'get_field' ) ) {
		return $block_content;
	}

	$attrs              = $block['attrs'] ?? [];
	$desktop_field_key  = $attrs['acfDesktopFieldKey'] ?? '';
	$mobile_field_key   = $attrs['acfMobileFieldKey'] ?? '';

	if ( empty( $desktop_field_key ) && empty( $mobile_field_key ) ) {
		return $block_content;
	}

	$desktop_image_url = '';
	$mobile_image_url  = '';

	if ( ! empty( $desktop_field_key ) ) {
		$desktop_image = get_field( $desktop_field_key );

		if ( is_array( $desktop_image ) && isset( $desktop_image['url'] ) ) {
			$desktop_image_url = $desktop_image['url'];
		} elseif ( is_string( $desktop_image ) ) {
			$desktop_image_url = $desktop_image;
		}
	}

	if ( ! empty( $mobile_field_key ) ) {
		$mobile_image = get_field( $mobile_field_key );

		if ( is_array( $mobile_image ) && isset( $mobile_image['url'] ) ) {
			$mobile_image_url = $mobile_image['url'];
		} elseif ( is_string( $mobile_image ) ) {
			$mobile_image_url = $mobile_image;
		}
	}

	if ( empty( $desktop_image_url ) && empty( $mobile_image_url ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );

	if ( ! $processor->next_tag() ) {
		return $block_content;
	}

	// Inject desktop background image as inline style
	if ( ! empty( $desktop_image_url ) ) {
		$existing_style = $processor->get_attribute( 'style' ) ?? '';
		$bg_style       = sprintf( 'background-image: url(%s);', esc_url( $desktop_image_url ) );
		$new_style      = ! empty( $existing_style ) ? $existing_style . ' ' . $bg_style : $bg_style;

		$processor->set_attribute( 'style', $new_style );
	}

	// Add data attribute for mobile background switching
	if ( ! empty( $mobile_image_url ) && $mobile_image_url !== $desktop_image_url ) {
		$processor->set_attribute( 'data-mobile-bg', esc_url( $mobile_image_url ) );
	}

	return $processor->get_updated_html();
}
add_filter( 'render_block_core/cover', 'carmo_cover_block_render_cover', 10, 2 );
