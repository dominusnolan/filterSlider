<?php
namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Abstracts\Slider;

use Financer\FilterSlider\Table\NewLoansTable;


/**
 * Class New_loans
 * @package Financer\FilterSlider\Shortcode
 */
class NewLoans extends Shortcode {
//echo do_shortcode( '[new_loans Title="New Loans" number="3" type="" year="2019" month="january"]' );
	private static $_instance = 0;

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
		if ( !isset( $atts['number'] ) ) {
			$atts['number'] = - 1;
		}

		if ( !isset( $atts['month'] ) ) {
			$atts['month'] = 'january';
		}

		if ( !isset( $atts['year'] ) ) {
			$atts['year'] = date('Y');
		}

		$atts['number'] = (int) $atts['number'];

		$where         = [
			[
				'key'     => 'company_parent.d.ej_partner',
				'value'   => '0',
			],
			[
				'key'     => 't.post_date_gmt',
				'value'   => [ ( new \DateTime( 'first day of '. $atts['month'] . ' ' . $atts['year'] ) )->format( 'Y-m-d' ), ( new \DateTime( '-1 days ago'   ) )->format( 'Y-m-d H:i:s' ) ],
				'compare' => 'BETWEEN'
			],

		];
		if ( ! empty( $atts['type'] ) ) {
			$where['company_type.slug'] = $atts['type'];
		}

		ob_start();
		$pod = pods(
			'loan_dataset', [
				'select'  => [
					't.post_title as title',
					't.post_name as name',
					't.ID as ID',
					't.post_date as pdate',
					'company_parent.ID as pid',
					'company_parent.d.ej_partner',
					'company_parent.d.bad_history',
					'company_parent.d.helgutbetalning AS weekend_payout',
					'company_parent.d.minalder',
					'company_parent.d.minimum_inkomst',
					'company_parent.d.loan_broker',
					'company_parent.d.foretag',
					'company_parent.d.url',
					'company_parent.d.company_ranking',
					'company_parent.d.special_text',
					'company_parent.d.overall_rating AS rating',
					'period_range_maximum',
					'period_range_minimum',
					'amount_range_minimum',
					'amount_range_maximum',
					'interest_rate AS interest_rate',
					'loan_restriction',
					'highest_annual_interest_rate AS highest_annual_interest_rate',
					'custom_interest_rate',
					'specific_affiliate_url',
					'fee_flat AS fee_flat',
					'fee_percent AS fee_percent',
					'monthly_fee AS monthly_fee',
					'representative_example',
				],
				'limit'   => $atts['number'],
				'where'   => $where,
				'expires' => Slider::CACHE_PERIOD,
			]
		);
		?>
		<?php if ( ! empty( $atts['title'] ) ): ?>

		<?php endif;

		?>
        <div class="tw-bs table_cont tabs_inner fN no-result">
			<?php NewLoansTable::build( $pod, null ); ?>
        </div>
		<?php
		return ob_get_clean();
	}
}
