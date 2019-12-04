<?php
namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Abstracts\Slider;


/**
 * Class Min_Save_Amount
 * @package Financer\FilterSlider\Shortcode
 */
class Max_Save_Interest extends Shortcode {
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
		$filter = pods( 'savings_account',
			[
				'select'  => [
					'MAX(interest_rate) AS max'
				],
				'where'   => [ 'bank.ID' => $atts['company'] ],
				'expires' => Slider::CACHE_PERIOD,
			]
		);
		if ( $filter->total() > 0 ) {
			$max = $filter->field( 'max' );
		} else {
			$max = 0;
		}

		return $max;
	}
}
