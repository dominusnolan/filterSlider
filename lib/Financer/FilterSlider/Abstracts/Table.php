<?php
namespace Financer\FilterSlider\Abstracts;


/**
 * Class Table
 * @package Financer\FilterSlider\Abstracts
 */
abstract class Table {
	/**
	 * @param $item
	 *
	 * @return mixed
	 */
	public static function showStars( $item ) {
		global $shortcode_tags, $wp_query;
		$in_the_loop           = $wp_query->in_the_loop;
		$wp_query->in_the_loop = true;
		$result                = do_shortcode('[rating_stars cid='.$item.' stars=1]');
		$wp_query->in_the_loop = $in_the_loop;

		return $result;
	}

}
