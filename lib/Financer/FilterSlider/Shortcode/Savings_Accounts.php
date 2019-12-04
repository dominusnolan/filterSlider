<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Table\SavingsAccountTable;

/**
 * Class Savings_Accounts
 * @package Financer\FilterSlider\Shortcode
 */
class Savings_Accounts extends Shortcode {

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
		$savings_account = pods( 'savings_account', [
			'select'  => [
				't.post_title as title',
				'min_save_time',
				'min_save_amount',
				'max_save_amount',
				'free_withdrawals',
				'interest_rate',
				'floating_interest_rate',
			],
			'where'   => [
				'bank.ID' => $atts['company'],
			],
			'orderby' => 'CAST(interest_rate AS DECIMAL(10,2)) ASC',
		] );
		if ( 0 == $savings_account->total() ) {
			return '';
		}
		ob_start();
		?>
		<div class="tw-bs table_cont tabs_inner fN">
			<?php SavingsAccountTable::build( $savings_account ); ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
