<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;

/**
 * Class Mortgage_Amount
 * @package Financer\FilterSlider\Shortcode
 */
class Mortgage_Amount extends Shortcode {

	/**
	 * @param array       $atts
	 *
	 * @param null|string $content
	 *
	 * @param string      $tag
	 * @param bool        $ajax
	 *
	 * @return mixed|string
	 */
	public static function render( $atts, string $content = null, string $tag = null, $ajax = false ): string {
		return (string) pods(
			'mortgage', [
				'select' => [ 't.ID' ],
				'limit'  => - 1
			]
		)->total_found();
	}
}
