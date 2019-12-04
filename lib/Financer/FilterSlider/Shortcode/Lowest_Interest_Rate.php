<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Util;

/**
 * Class Loan_Amount
 * @package Financer\FilterSlider\Shortcode
 */
class Lowest_Interest_Rate extends Shortcode {

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

		$filter = pods(
			'loan_dataset',
			[
				'select'  => [
					'MIN( interest_rate ) AS min'
				],
				'where'   => [ 'company_parent.ID' => get_the_ID() ]
			]
		);

		if ( $filter->total() > 0 ) {
			$min = $filter->field( 'min' );
		} else {
			$min = 0;
		}
		
		return Util::numberFormat( $min ) . '%';

	}
}
