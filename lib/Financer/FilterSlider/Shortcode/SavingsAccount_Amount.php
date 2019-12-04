<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;

/**
 * Class SavingsAccount_Amount
 * @package Financer\FilterSlider\Shortcode
 */
class SavingsAccount_Amount extends Shortcode {

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
	static function render( $atts, string $content = null, string $tag = null, $ajax = false ): string {
		return (string) pods(
			'savings_account', [
				'select' => [ 't.ID' ],
				'limit'  => - 1
			]
		)->total_found();
	}
}
