<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;

/**
 * Class Bank_Amount
 * @package Financer\FilterSlider\Shortcode
 */
class Bank_Amount extends Shortcode {

	/**
	 * @param array       $atts
	 *
	 * @param null|string $content
	 *
	 * @param string      $tag
	 *
	 * @param bool        $ajax
	 *
	 * @return mixed|string
	 */
	public static function render( $atts, string $content = null, string $tag = null, $ajax = false ): string {
		return (string) pods(
			'company_single', [
				'limit' => - 1,
				'where' => [ 'company_type.slug' => 'bank' ],
			]
		)->total_found();
	}
}
