<?php
declare(strict_types=1);

/**
 * Plugin Name:       Carmo Cover Block
 * Description:       Extends the core Cover block with ACF background images.
 * Version:           0.2.6
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
 * Filter core/cover block output to inject ACF background image.
 *
 * @param string $block_content The block HTML.
 * @param array  $block         The parsed block data.
 * @return string Modified block HTML.
 */
function carmo_cover_block_render_cover( string $block_content, array $block ): string {
	if ( ! function_exists( 'get_field' ) ) {
		return $block_content;
	}

	$attrs             = $block['attrs'] ?? [];
	$desktop_field_key = $attrs['acfDesktopFieldKey'] ?? '';

	if ( empty( $desktop_field_key ) ) {
		return $block_content;
	}

	$desktop_image     = get_field( $desktop_field_key );
	$desktop_image_url = '';

	if ( is_array( $desktop_image ) && isset( $desktop_image['url'] ) ) {
		$desktop_image_url = $desktop_image['url'];
	} elseif ( is_string( $desktop_image ) ) {
		$desktop_image_url = $desktop_image;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$is_debug = isset( $_GET['debug'] ) && '1' === $_GET['debug'];

	if ( $is_debug ) {
		$debug_data = wp_json_encode( [
			'desktop_field_key' => $desktop_field_key ?: null,
			'desktop_raw_value' => $desktop_image ?? null,
			'desktop_image_url' => $desktop_image_url ?: null,
			'post_id'           => get_the_ID(),
		] );
		$block_content = '<script>console.log("[carmo-cover-block]", ' . $debug_data . ');</script>' . $block_content;
	}

	if ( empty( $desktop_image_url ) ) {
		return $block_content;
	}

	$unique_id = 'carmo-cover-' . wp_unique_id();
	$processor = new WP_HTML_Tag_Processor( $block_content );

	if ( ! $processor->next_tag( [ 'class_name' => 'wp-block-cover' ] ) ) {
		return $block_content;
	}

	$processor->set_attribute( 'data-carmo-id', $unique_id );
	$updated_html = $processor->get_updated_html();

	$style = sprintf(
		'<style>@media screen and (min-width: 768px) { [data-carmo-id="%s"] { background-size: cover; background-image: url(%s) !important; } }</style>',
		esc_attr( $unique_id ),
		esc_url( $desktop_image_url )
	);

	return $style . $updated_html;
}
add_filter( 'render_block_core/cover', 'carmo_cover_block_render_cover', 10, 2 );
