<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Util;

/**
 * Class Loan_Amount
 * @package Financer\FilterSlider\Shortcode
 */
class Lowest_Interest_Rate_OR_Default extends Shortcode {

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

        $company = pods('company_single', [ 'select'  => [ 'lowest_interest_from AS lowest_interest_from'], 'where'   => [ 'ID' => get_the_ID() ]]);

		$loan_dataset = pods(
			'loan_dataset',
			[
				'select'  => [
					'MIN( interest_rate ) AS min'
				],
				'where'   => [ 'company_parent.ID' => get_the_ID() ]
			]
		);
        $companyMin = $company->field( 'lowest_interest_from' );
        $loanMin = $loan_dataset->field( 'min' );

        $min = 0;
		if ( $companyMin > 0 ) {
			$min = $companyMin;
		} else if ( $loanMin > 0 ) {
            $min = Util::numberFormat($loanMin) . '%';
        }

		return $min;

	}
}
