<?php
namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Util;

/**
 * Class Min_Save_Amount
 * @package Financer\FilterSlider\Shortcode
 */
class Min_Save_Amount extends Shortcode {
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
					'MIN( min_save_amount ) AS min'
				],
				'where'   => [ 'bank.ID' => $atts['company'] ],
				'expires' => Slider::CACHE_PERIOD,
			]
		);
		if ( $filter->total() > 0 ) {
			$min = $filter->field( 'min' );
		} else {
			$min = 0;
		}
		ob_start();
		?>
		<?php echo - 1 == $min ? __( 'N/A', 'fs' ) : Util::moneyFormat( $min ) . '&nbsp;' . __( 'usd', 'fs' ) ?>
		<?php
		return ob_get_clean();
	}
}
