<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Table\MortgageTable;

/**
 * Class Savings_Accounts
 * @package Financer\FilterSlider\Shortcode
 */
class Mortgages extends Shortcode {

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
		$mortgage = pods( 'mortgage', [
			'select' => [
				'3_mir',
				'1_yir',
				'2_yir',
				'3_yir',
				'4_yir',
				'5_yir',
				'7_yir',
				'10_yir',
			],
			'where'  => [
				'bank.ID' => $atts['company'],
			]
		] );
		if ( 0 == $mortgage->total() ) {
			return '';
		}
		ob_start();
		?>
		<div class="tw-bs table_cont tabs_inner fN">
			<?php MortgageTable::build( $mortgage ); ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
