<?php
namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Util;

/**
 * Class Min_Max_Loans
 * @package Financer\FilterSlider\Shortcode
 */
class Min_Max_Loans extends Shortcode {

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
					'MIN( amount_range_minimum ) AS min',
					'MAX( amount_range_maximum ) AS max',
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
		<?php echo Util::moneyFormat( $min ) ?>&#160;<?php _e( 'usd', 'fs' ) ?>&#160;-&#160;<?php echo Util::moneyFormat( $max ) ?>&#160;<?php _e( 'usd', 'fs' ) ?>
		<?php
		return ob_get_clean();
	}
}
