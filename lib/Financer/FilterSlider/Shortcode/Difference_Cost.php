<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Util;

/**
 * Class Difference_Cost
 * @package Financer\FilterSlider\Shortcode
 */
class Difference_Cost extends Shortcode {

	/**
     * Period to cache a db query
     * @var int
     */
    const LOAN_TOTAL_SQL = [
        '(1+((interest_rate/100) * (1/12))) monthly_rate',
        '({AMOUNT} * POW((SELECT monthly_rate), ({PERIOD} / 30))*((1-(SELECT monthly_rate))/(1-POW((SELECT monthly_rate),({PERIOD} / 30))))) annuity',
        '(IF(fee_flat = 0 OR fee_flat IS NULL, IF (fee_percent = 0 OR fee_percent IS NULL, 0, (fee_percent / 100) * {AMOUNT}), fee_flat)) fee',
        '((SELECT fee) + (IF((SELECT annuity) IS NULL, 0,(SELECT annuity)) * ({PERIOD}/30)) +  (IF (monthly_fee IS NULL, 0, monthly_fee) *  IF({PERIOD} < 30, 1, {PERIOD}/30)) - IF((SELECT annuity) IS NULL or (SELECT annuity) = 0, 0,{AMOUNT})) total_cost',
        '(((SELECT total_cost) + {AMOUNT})/ IF({PERIOD} < 30, 1, {PERIOD}/30) ) total_monthly_payback'
    ];


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
		if ( !isset( $atts['amount'] ) ) {
			$atts['amount'] = 500;
		}

		if ( !isset( $atts['period'] ) ) {
			$atts['period'] = 90;
		}
		ob_start();
		$amount = (int) $atts['amount'];
		$period = (int) $atts['period'];


		$pod = pods(
			'loan_dataset', [
				'select'  => 
					self::generateLoanTotal( $amount, $period)
				,
				'limit'   => - 1,
				'where'     => [
	                [
	                    'key'     => "amount_range_minimum",
	                    'value'   => $amount,
	                    'compare' => '<=',
	                ],
	                [
	                    'key'     => "amount_range_maximum",
	                    'value'   => $amount,
	                    'compare' => '>=',
	                ],
	                "CAST(period_range_minimum AS DECIMAL(12,4)) <= {$period}",
	                "CAST(period_range_maximum AS DECIMAL(12,4)) >= {$period}",
	                "company_parent.post_status"  => 'publish',
	            ],
			]
		);


		if ( !empty( $pod ) ){
			$query = $pod->data();

	        if ( ! $query ) {
	            $query = [];
	        }
	        
	        $homepage_settings = pods( 'homepage_settings' );
	        if( !empty($homepage_settings) ){
	        	$link = get_the_permalink( $homepage_settings->field( 'personal' )['ID'] );
        	}else{
        		$link = home_url();
        	}
	       

	        $max = self::calc_attribute_in_array($query, 'total_cost', 'max');
			$min = self::calc_attribute_in_array($query, 'total_cost', 'min');
	
			$currency = __( 'usd', 'fs' );

			$difference = $max - $min;
			$difference_format = round($difference, 2);
			$button_text = __('Find out how', 'fs');
			$string_text = sprintf( __( 'The price difference for a %s %s loan over %s days is %s %s.', 'fs'), $amount, $currency, $period, $difference_format, $currency);
			$header_text1 = __('How to save', 'fs');
			$header_text2 = __('on your loan', 'fs');


			echo <<<HTML
				<div class="msg micro-compare">
				<h3>{$header_text1} <span class="compare-amount">{$difference_format} {$currency}</span> {$header_text2}</h3>
				<p>{$string_text}</p>

				<br><p><a href="{$link}" class="button small applyYellow">{$button_text}</a></p>
				</div>
HTML;
			$pod->fetch();
		}
		return ob_get_clean();
	}

	/**
     * @param int $amount
     * @param int $period
     *
     * @return array
     */
    public static function generateLoanTotal( int $amount, int $period ) {
        return array_map( function ( string $line ) use ( $amount, $period ) {
            return str_replace( [ '{AMOUNT}', '{PERIOD}' ], [ $amount, $period ], $line );
        }, self::LOAN_TOTAL_SQL );
    }


    /**
     * @param int $amount
     * @param int $period
     *
     * @return array
     */
    public static function calc_attribute_in_array( $array, $prop, $func) {
        $result = array_map(function($o) use($prop) {
	                            return $o->$prop;
	                        },
	                        $array);
	    if(function_exists($func)) {
	        return $func($result);
	    }
	    return false;
    }
}