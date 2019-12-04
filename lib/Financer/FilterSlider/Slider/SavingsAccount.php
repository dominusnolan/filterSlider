<?php

namespace Financer\FilterSlider\Slider;


use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\SortUtil;
use Financer\FilterSlider\Util;

/**
 * Class SavingsAccount
 * @package Financer\FilterSlider\Slider
 */
class SavingsAccount extends Slider {
	/**
	 * @var string
	 */
	protected $postType = 'savings_account';
	/**
	 * @var string
	 */
	protected $tableClass = 'SavingsAccountSliderTable';
	/**
	 * @var array
	 */
	protected $fields = [
		't.ID AS ID',
		't.post_title AS title',
		'min_save_amount AS min_save_amount',
		'min_save_time AS min_save_time',
		'interest_rate AS interest_rate',
		'specific_affiliate_url_account AS specific_affiliate_url_account',
		'floating_interest_rate AS floating_rate',
		'free_withdrawals AS free_withdrawals',
		'bank.ID AS bank_id',
		'bank.post_title AS bank_title',
		'bank.d.ej_partner',
		'bank.d.governmental_guarantee',
		'bank.d.favorite',
		'bank.d.overall_rating AS total_reviews',
	];
	/**
	 * @var array
	 */
	protected $filterList = [
		'governmental_guarantee',
		'saving_free_withdrawals'
	];
	/**
	 * @var array
	 */
	protected $limitList = [
		10  => false,
		20  => false,
		50  => false,
		100 => true,
		- 1 => false
	];
	/**
	 * @var bool
	 */
	protected $sortEnabled = false;
	/**
	 * Pod setting storing slider comparison run count
	 * @var string
	 */
	protected $compareCounterSetting = 'savingsaccount_comparisons';

	protected $saving_free_withdrawals;

	protected $governmental_guarantee;

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	public function generateJsMaps( $params = [] ): array {

		$slider_settings = pods( 'slider_settings' );

		$amount_list = $slider_settings->field( 'savings_account_amounts' );
		$period_list = $slider_settings->field( 'savings_account_periods' );

		if ( $amount_list ) {
			$amount_list = explode( "\n", $amount_list );
			array_walk( $amount_list, 'trim' );
		} else {
			$amount_list = [];
		}
		if ( $period_list ) {
			$period_list = explode( "\n", $period_list );
			array_walk( $period_list, 'trim' );
		} else {
			$period_list = [];
		}
		$periods = [];
		$amounts = [];
		foreach ( $amount_list as $amount ) {
			$amount = (int) $amount;
			if ( ! isset( $amounts[ $amount ] ) ) {
				$amounts[ $amount ] = Util::moneyFormat( $amount ) . ' ' . __( 'usd', 'fs' );
			}
		}
		foreach ( $period_list as $period ) {
			$period = (float) $period;
			if ( ! isset( $periods[ $period ] ) ) {
				$periods[ (string) $period ] = $period < 1 ? Util::getPeriod( $period * 365 ) : $period . ' ' . __( 'Years', 'fs' );
			}
		}
		ksort( $periods );
		ksort( $amounts );

		return [
			$this->instanceId . '_amountMap' => $amounts,
			$this->instanceId . '_periodMap' => $periods,
		];
	}


	protected function sort() {

		SortUtil::processFavorite( $this->pod->rows, [ 'interest_rate' ] );
	}

	/**
	 * @return void
	 * @internal param Slider $slider
	 *
	 */

	protected function labels() {
		parent::labels();
		$this->htmlLabels = [
                                'compare_now' => __( 'How much would you like to save?', 'fs' ),
                                'amount'                 => __( 'Saving amount', 'fs' ),
			                    'period'                 => __( 'Saving time', 'fs' ),
			                    'filters'                => __( 'Apply saving account filters', 'fs' ),
			                    'display'                => __( 'Amount of savings accounts', 'fs' ),
			                    'display_10'             => __( 'Top 10 highest interest', 'fs' ),
			                    'display_20'             => __( 'Top 20 highest interest', 'fs' ),
			                    'display_50'             => __( 'Top 50 highest interest', 'fs' ),
			                    'display_100'            => __( 'Top 100 highest interest', 'fs' ),
			                    'display_-1'             => __( 'All savings accounts', 'fs' ),
			                    'submit'                 => __( 'Find me the best saving accounts', 'fs' ),
			                    'saving_free_withdrawals'       => __( 'Show only savings accounts with free withdrawals', 'fs' ),
			                    'governmental_guarantee' => __( 'Show only savings accounts with govermental guarantee', 'fs' ),
			                    'total_count'            => __( 'Showing %d savings accounts out of %d possible', 'fs' ),
			                    'step4'                  => __( 'Compare savings accounts', 'fs' ),
			                    'min_interest_rate'      => __( 'Lowest', 'fs' ),
			                    'max_interest_rate'      => __( 'Highest', 'fs' ),
			                    'max_savings'            => __( 'Save up to', 'fs' ),
			                    'slider_type'            => __( 'savings account', 'fs' ),
			                    'slider_type_plural'     => __( 'savings accounts', 'fs' ),
			                    'guide_1'           => __( 'Drag the sliders on the left to find your saving account', 'fs' ),
		                    ] + $this->htmlLabels;

        if ($this->getMinimalStatus() == 'single') {
            $this->htmlLabels['compare_now'] = __( 'Compare saving accounts and find better interest rates', 'fs' );
        }
	}

	/**
	 *
	 */
	protected function buildQuery() {

		$this->query = [
			'select'  => ! empty( $this->fields ) ? $this->fields : null,
			'limit'   => $this->limit,
			'orderby' => 'CAST(interest_rate as DECIMAL(10,2)) DESC',
			'where'   => [
				[
					'key'     => 'min_save_time',
					'value'   => $this->period,
					'compare' => '<=',
					'type'    => 'DECIMAL',
				],
				[
					'key'   => 'post_status',
					'value' => 'publish',
				],
				[
					'key'     => 'bank',
					'compare' => 'EXISTS',
				],
			],
			'expires' => Slider::CACHE_PERIOD,
		];

        if ($this->singleCompany == false) {
            $this->query['where'][] = [
                    'key'     => 'min_save_amount',
                    'value'   => $this->amount,
                    'type'    => 'numeric',
                    'compare' => '<=',
                ];
            $this->query['where'][] = [
                    'key'     => 'max_save_amount',
                    'value'   => $this->amount,
                    'type'    => 'numeric',
                    'compare' => '>=',
                ];
        }

		if ( false == empty( $this->saving_free_withdrawals ) && 1 == $this->saving_free_withdrawals ) {
			$this->query['where'][] = [
				'key'     => 'free_withdrawals',
				'value'   => '^[1-9]+[0-9]*$',
				'compare' => 'REGEXP',
			];
		}
		if ( false == empty( $this->governmental_guarantee ) && 1 == $this->governmental_guarantee ) {
			$this->query['where'][] = [
				'key'   => 'bank.d.governmental_guarantee',
				'value' => 1,
			];
		}
	}

	/**
	 *
	 */
	protected function renderCounterItems() {
		parent::renderCounterItems();
		static::renderMaximumPercent();
		static::renderMaximumProfit();
	}

	/**
	 *
	 */
	private function renderMaximumPercent() {
		$query           = $this->query;
		$query['select'] = 'MAX( CAST(interest_rate as DECIMAL(10,2) ) ) AS max';
		unset( $query['orderby'] );
		$pod = pods( $this->postType, $query );
		echo <<<HTML
<div class="block-circle">
    <p class="block-text">{$this->htmlLabels['max_interest_rate']}</p>

    <p class="block-count"><span class="counter">{$pod->field( 'max' )}</span>%</p>
</div>
HTML;
	}

	/**
	 *
	 */
	private function renderMaximumProfit() {
		$query           = $this->query;
		$query['select'] = 'MAX( ROUND( ' . (float) $this->amount . ' * pow( 1 + interest_rate / 100, ' . (float) $this->period . ' ) - ' . (float) $this->amount . ', 2 ) ) AS profit';
		unset( $query['orderby'] );
		$pod    = pods( $this->postType, $query );
		$profit = (float) $pod->field( 'profit' );
		$number = strstr( $profit, '.', true );
		$symbol = '&nbsp;' . __( 'usd', 'fs' );
		if ( $number ) {
			$profit = $number;
		}
		echo <<<HTML
<div class="block-circle">
    <p class="block-text">{$this->htmlLabels['max_savings']}</p>

    <p class="block-count"><span class="counter">$profit</span>$symbol</p>
</div>
HTML;
	}

	/**
	 *
	 */
	protected function renderDifference() {
	}

    protected function stepFilters()
    {
        parent::stepFilters($type = 'saving');
    }
}
