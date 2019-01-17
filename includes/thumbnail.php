<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Related posts by taxonomy thumbnail gallery.
 *
 * Similar to the WordPress gallery_shortcode().
 *
 * @since 0.2
 *
 * @global string $wp_version
 * @global string $post
 *
 * @param array $args          {
 *     Arguments of the related posts thumbnail gallery.
 *
 *     @type int          $id            Post ID.
 *     @type string       $itemtag       HTML tag to use for each image in the gallery.
 *                                       Default 'dl', or 'figure' when the theme registers HTML5 gallery support.
 *     @type string       $icontag       HTML tag to use for each image's icon.
 *                                       Default 'dt', or 'div' when the theme registers HTML5 gallery support.
 *     @type string       $captiontag    HTML tag to use for each image's caption.
 *                                       Default 'dd', or 'figcaption' when the theme registers HTML5 gallery support.
 *     @type boolean      $show_date     Whether to display the post date after the caption. Default false.
 *     @type int          $columns       Number of columns of images to display. Default 3.
 *     @type string|array $size          Size of the images to display. Accepts any valid image size. Default 'thumbnail'.
 *     @type string       $caption       Caption text for the post thumbnail.
 *                                       Accepts 'post_title', 'post_excerpt', 'attachment_caption', 'attachment_alt', or
 *                                       a custom string. Default 'post_title'
 *     @type boolean      $link_caption  Whether to link the caption to the related post. Default false.
 *     @type string       $gallery_class Default class for the gallery. Default 'gallery'.
 *     @type string       $type          Gallery type. Default gallery type 'rpbt_gallery'.
 * }
 * @param array $related_posts Array with related post objects that have a post thumbnail.
 * @return string HTML string of a gallery.
 */
function related_posts_thumbnail( $args, $related_posts = array() ) {

	if ( empty( $related_posts ) ) {
		return '';
	}

	$post = get_post();

	static $instance = 0;
	$instance++;

	/**
	 * Filter whether to print default gallery styles.
	 *
	 * Note: This is a WordPress core filter hook
	 *
	 * @since 3.1.0
	 *
	 * @param bool $print Whether to print default gallery styles.
	 *                       Defaults to false if the theme supports HTML5 galleries.
	 *                       Otherwise, defaults to true.
	 */

  $output = '';

	$item_output = '';

  $output .= "<ul class='list-unstyled'>";

	foreach ( (array) $related_posts as $related ) {
    global $post;

		$caption       = '';
		$thumbnail_id  = get_post_thumbnail_id( $related->ID );
		$title         = apply_filters( 'the_title', $related->post_title, $related->ID );

    $date    = $args['show_date'] ? ' ' . km_rpbt_get_post_date( $related ) : '';
    $caption = $title . $date;

    $post = $related;

    setup_postdata( $related );

    $excerpt = get_the_excerpt();

    wp_reset_postdata();

		/**
		 * Filter the related post thumbnail caption.
		 *
		 * @since 0.3
		 *
		 * @param string $caption Options 'post_title', 'attachment_caption', attachment_alt, or a custom string. Default: post_title.
		 * @param object $related Related post object.
		 * @param array  $args    Function arguments.
		 */
		$caption = apply_filters( 'related_posts_by_taxonomy_caption',  wptexturize( $caption ), $related, $args );

		$thumbnail   = wp_get_attachment_image( $thumbnail_id, $args['size'], false );
		$permalink   = km_rpbt_get_permalink(  $related, $args );
		$title_attr  = esc_attr( $title );
		$image_link  = ( $thumbnail ) ? "<a href='$permalink' title='$title_attr'>$thumbnail</a>" : '';
		$image_attr  = compact( 'thumbnail_id', 'thumbnail', 'permalink', '', 'title_attr' );

		/**
		 * Filter the gallery image link.
		 *
		 * @since 0.3
		 *
		 * @param string $post_thumbnail Html image tag or empty string.
		 * @param object $related        Related post object
		 * @param array  $args           Function arguments.
		 */
		$image_link = apply_filters( 'related_posts_by_taxonomy_post_thumbnail_link', $image_link, $image_attr, $related, $args );

		if ( ! $image_link ) {
			continue;
		}

		$item_output .= "<li class='media'>";
		$item_output .= "
			<div class='post-thumb mr-3'>
				$image_link
			</div>";

    $item_output .= "
      <div class='media-body pt-1' id='rp-{$related->ID}'>
      <h2><a href=\"" . $permalink . "\">" . $caption . "</a></h2>
      <p>". $excerpt ."</p>
      </div>";

		$item_output .= "</li>";

	}

	$output .= $item_output;

	$output .= "
		</ul>\n";

	return $output;
}
