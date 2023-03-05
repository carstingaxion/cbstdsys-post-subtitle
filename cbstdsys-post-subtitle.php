<?php
/**
 * Plugin Name:       Cbstdsys Post Subtitle
 * Description:       Block to show and style a semantic subtitle – build step required.
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Carsten Bach
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cbstdsys-post-subtitle
 *
 * @package           create-block
 */

add_action( 'plugins_loaded', function ()
{
	// load i18n & register block
	\add_action( 'init', 'cbstdsys_post_subtitle_block__init' );

	// register post_meta
	\add_action( 'init', 'cbstdsys_post_subtitle_block__register_post_meta', 11 );

});

/**
 * Renders the `cbstdsys_post_subtitle` block on the server.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 *
 * @return string Returns the filtered post title for the current post wrapped inside "h1" tags.
 */
function cbstdsys_post_subtitle_block__render( $attributes, $content, $block )
{
	if ( ! isset( $block->context['postId'] ) )
		return '';

	$sub_title   = \get_post_meta(
		$block->context['postId'],
		'ft_sub_title',
		true
	);

	if ( ! $sub_title )
		return '';


	$align_class_name = empty( $attributes['textAlign'] ) ? '' : "has-text-align-{$attributes['textAlign']}";

	// default, so far
	// as long as there where no tag-selector
	$tag_name = 'h2';

	if ( isset( $attributes['level'] ) ) {
		$tag_name = 0 === $attributes['level'] ? 'p' : 'h' . $attributes['level'];
	}

	$wrapper_attributes = \get_block_wrapper_attributes( array( 'class' => $align_class_name ) );

	return sprintf(
		'<%1$s %2$s>%3$s</%1$s>',
		$tag_name,
		$wrapper_attributes,
		$sub_title
	);
}


/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/writing-your-first-block-type/
 */
function cbstdsys_post_subtitle_block__init() {

	\load_plugin_textdomain( 
		'cbstdsys-post-subtitle',
		false,
		dirname( \plugin_basename( __FILE__ ) ) . '/languages'
	);

	// \register_block_type_from_metadata(
	\register_block_type(
		__DIR__,
		array(
			'render_callback' => 'cbstdsys_post_subtitle_block__render',
		)
	);

	// Load available translations.
	wp_set_script_translations( 
		'create-block-cbstdsys-post-subtitle-editor-script',
		'cbstdsys-post-subtitle',
		\plugin_dir_path( __FILE__ ) . 'languages'

	);
}

// register custom meta tag field
function cbstdsys_post_subtitle_block__register_post_meta() {

	// prepare builtin post_types
	\add_post_type_support( 'post', 'ft_sub_title' );
	\add_post_type_support( 'page', 'ft_sub_title' );
	
	$post_types = \get_post_types_by_support( 'ft_sub_title' );
	// \do_action( 'qm/debug', $post_types );

	array_walk(
		$post_types,
		function( $post_type, $i )
		{
			\register_post_meta( $post_type, 'ft_sub_title', array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			) );
		}
	);
}
