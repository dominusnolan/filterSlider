<?php
namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Util;

/**
 * Class Min_Max_Period
 * @package Financer\FilterSlider\Shortcode
 */
class Min_Max_Period extends Shortcode {

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
		$filter = pods(
			'loan_dataset',
			[
				'select'  => [
					'MIN(CAST(period_range_minimum AS SIGNED)) AS min',
					'MAX(CAST(period_range_maximum AS SIGNED)) AS max',
				],
				'where'   => [ 'company_parent.ID' => $atts['company'] ],
				'expires' => Slider::CACHE_PERIOD,
			]
		);
		if ( $filter->total() > 0 ) {
			$min = $filter->field( 'min' );
			$max = $filter->field( 'max' );
		} else {
			$min = $max = 0;
		}
		ob_start();
		?>
		<?php echo Util::getPeriod( $min ) ?>&#160;-&#160;<?php echo Util::getPeriod( $max ) ?>
		<?php
		return ob_get_clean();
	}
}
