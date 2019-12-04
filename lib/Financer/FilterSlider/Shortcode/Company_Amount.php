<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;

/**
 * Class Company_Amount
 * @package Financer\FilterSlider\Shortcode
 */
class Company_Amount extends Shortcode {

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
			'company_single', [
				'select' => [ 't.ID' ],
				'limit'  => - 1,
				'where'  => [ 'company_type.slug' => 'loan_company' ],
			]
		)->total_found();
	}
}
