<?php
namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Util;

/**
 * Class Min_Save_Amount
 * @package Financer\FilterSlider\Shortcode
 */
class Max_Save_Amount extends Shortcode {
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
					'MAX(max_save_amount) AS max'
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
		ob_start();
		?>
		<?php echo - 1 == $max ? __( 'N/A', 'fs' ) : Util::moneyFormat( $max ) . '&nbsp;' . __( 'usd', 'fs' ) ?>
		<?php
		return ob_get_clean();
	}
}
