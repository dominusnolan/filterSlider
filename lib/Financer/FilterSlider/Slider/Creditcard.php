<?php

namespace Financer\FilterSlider\Slider;


use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\SortUtil;
use Financer\FilterSlider\Util;

/**
 * Class Creditcard
 * @package Financer\FilterSlider\Slider
 */
class Creditcard extends Slider {
	/**
	 * @var string
	 */
	protected $postType = 'creditcard';
	/**
	 * @var string
	 */
	protected $tableClass = 'CreditcardTable';

	/**
	 * @var bool
	 */
	protected $hasCompany = false;
	/**
	 * @var array
	 */
	protected $fields = [
		't.post_title as title',
		't.post_name as name',
		't.ID as ID',
		'period',
		'percent',
		'summ',
		'looses',
		'travel_insurance',
		'atm_fee',
        /*'atm_fee_unit',*/
		'card_type',
		'affiliate',
		'featured',
		'card_details',
	];
	/**
	 * @var array
	 */
	protected $filterList = [
		'travel_insurance',
		'creditcard_free_withdrawals'
	];
	protected $sortEnabled = false;
	/**
	 * Pod setting storing slider comparison run count
	 * @var string
	 */
	protected $compareCounterSetting = 'creditcard_comparisons';
	protected $cardtype;
	protected $travel_insurance;
	protected $creditcard_free_withdrawals;
	/**
	 * Limit results
	 * @var int
	 */
	protected $limit = 50;

	/**
	 * @inheritDoc
	 */
	public function __construct( array $atts ) {
		unset( $this->paramsList[ array_search( 'sort', $this->paramsList ) ] );
		$this->paramsList[]          = 'cardtype';
		$this->shareableParamsList[] = 'cardtype';
		//var_dump($atts);
		parent::__construct( $atts );
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	public function generateJsMaps( $params = [] ): array {

		$params  = [
			'select' => [
				'period',
				'summ AS amount',
			],
			'limit'  => - 1,
			'where'  => [
				[
					'key'   => 'post_status',
					'value' => 'publish',
				],
			],
		];
		$pod     = pods( $this->postType, $params );
		$periods = [];
		$amounts = [];
		while ( $pod->fetch() ) {
			$period = (int) $pod->field( 'period' );
			$amount = (int) $pod->field( 'amount' );

			if ( false == isset( $periods[ $period ] ) &&
			     (
				     $period <= $this->maxPeriod ||
				     true == empty( $this->maxPeriod )
			     ) &&
			     - 1 < $period
			) {
				if ( 0 == $period ) {
					$periods[ $period ] = __( 'Prepaid', 'fs' );
				} else {
					$periods[ $period ] = $period . ' ' . ( 1 < $period ? __( 'days', 'fs' ) : __( 'day', 'fs' ) );
				}
			}
			if ( false == isset( $amounts[ $amount ] ) && ( $amount <= $this->maxAmount || empty( $this->maxAmount ) ) && $amount > - 1 ) {
				$amounts[ $amount ] = Util::moneyFormat( $amount ) . ' ' . __( 'usd', 'fs' );
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
		$featured = [];
		foreach ( $this->pod->rows as $pos => $result ) {
			if ( $result->featured ) {
				$featured[] = $result;
				unset( $this->pod->rows[ $pos ] );
			}
		}
		$featured        = SortUtil::processUnlimited( $featured, [ 'percent' ] );
		$items           = SortUtil::processUnlimited( $this->pod->rows, [ 'percent' ] );
		$this->pod->rows = array_merge( $featured, $items );
	}

	/**
	 * @param array $atts
	 */
	protected function processFilters( array $atts ) {
            parent::processFilters( $atts );
            if ( isset( $atts['cardtype'] ) ) {
                $this->cardtype = sanitize_key( $atts['cardtype'] );
            }
	}

	/**
	 *
	 */
	protected function labels() {
		parent::labels();
		$this->htmlLabels = [
                                'compare_now' => __( 'How much credit do you need?', 'fs' ),
                                'step4'              => __( 'Compare the best credit cards', 'fs' ),
			                    'amount'             => __( 'Max credit amount', 'fs' ),
			                    'period'             => __( 'Interest free days', 'fs' ),
			                    'filters'            => __( 'Apply credit cards filters', 'fs' ),
			                    'display'            => __( 'Amount of cards to display', 'fs' ),
			                    'display_10'         => __( 'Top 10 lowest interest', 'fs' ),
			                    'display_20'         => __( 'Top 20 lowest interest', 'fs' ),
			                    'display_50'         => __( 'Top 50 lowest interest', 'fs' ),
			                    'display_100'        => __( 'Top 100 lowest interest', 'fs' ),
			                    'display_-1'         => __( 'All credit cards', 'fs' ),
			                    'submit'             => __( 'Find me the best credit cards', 'fs' ),
			                    'display_cardtype'   => __( 'Specific card types', 'fs' ),
			                    'visa'               => __( 'Visa', 'fs' ),
			                    'mastercard'         => __( 'Mastercard', 'fs' ),
			                    'americanexpress'    => __( 'American Express', 'fs' ),
			                    'cardtype_all'       => __( 'All credit cards', 'fs' ),
			                    'travel_insurance'   => __( 'Show only cards with travel insurance', 'fs' ),
			                    'creditcard_free_withdrawals'   => __( 'Show only cards with free withdrawals', 'fs' ),
			                    'total_count'        => __( 'Difference', 'fs' ),
			                    'min_percent'        => __( 'Lowest', 'fs' ),
			                    'max_percent'        => __( 'Highest', 'fs' ),
			                    'slider_type'        => __( 'credit card', 'fs' ),
			                    'slider_type_plural' => __( 'credit cards', 'fs' ),
			'guide_1'           => __( 'Drag the sliders on the left to find your credit card', 'fs' ),
		                    ] + $this->htmlLabels;

        if ($this->getMinimalStatus() == 'single') {
            $this->htmlLabels['compare_now'] = __( 'Find a credit card with low rates - in less than a minute', 'fs' );
        }
	}

	/**
	 * @inheritDoc
	 */
	protected function stepFilters_Header() {
		echo <<<HTML
	<div class="step filters" style="margin-left:0;">
	   <label class="fl_l_m_10">{$this->htmlLabels['filters']} </label>
HTML;
	}


	protected function stepFilters() {
		parent::stepFilters($type='creditcard');
		$selected = selected( $this->cardtype, null, false );
		echo <<<HTML
			<div class="cardType checkboxFive dropdown-select" style="display: none;" >
            <div class="inline">{$this->htmlLabels['display_cardtype']}:</div>
            <select class="loan_amount"  name="param_cardtype">
             <option{$selected}  value="">{$this->htmlLabels['cardtype_all']}</option>
HTML;
		foreach ( [ 'visa', 'mastercard', 'americanexpress' ] as $cardType ) {
			$selected = selected( $this->cardtype, $cardType, false );
			echo <<<HTML
				    <option{$selected} value="$cardType">{$this->htmlLabels[$cardType]}</option>
HTML;
		}
		echo <<<HTML
		</select>
</div>
HTML;
	}

	/**
	 *
	 */
	protected function buildQuery() {
		parent::buildQuery();
		$this->query['select']   = $this->fields;
		$this->query['where']    = [
			'summ >= ' . $this->amount . ' or summ = -1',
			'period >= ' . $this->period . ' or period = -1',
			[
				'key'   => 'post_status',
				'value' => 'publish',
			],
		];
		$this->query['meta_key'] = 'percent';
		$this->query['orderby']  = 'CAST(percent as DECIMAL(10,2)) ASC';

		if ( false == empty( $this->cardtype ) ) {
			$this->query['where'][] = [
				'key'     => 'd.card_type',
				'value'   => esc_textarea( $this->cardtype ),
				'compare' => '=',
			];
		}
		if ( false == empty( $this->travel_insurance ) && 1 == $this->travel_insurance
		) {
			$this->query['where'][] = [
				'key'     => 'd.travel_insurance',
				'value'   => '1',
				'compare' => '=',
			];
		}

		if ( false == empty( $this->creditcard_free_withdrawals ) && 1 == $this->creditcard_free_withdrawals ) {
			$this->query['where'][] = [
				'key'     => 'd.atm_fee',
				'value'   => '0',
				'compare' => '=',
			];
		}

        if ($this->param_single_company_name) {
            $this->query['where'][] = [
                'key'   => 'company',
                'value' => $this->param_single_company_name
            ];
        }

	}

	/**
	 *
	 */
	protected function renderCounterItems() {
		parent::renderTotalCount();
		static::renderMinimumPercent();
		static::renderMaximumPercent();
	}
	protected function initQuery() {
		if(!empty(get_query_var( 'query' ))){
		$params = explode('/', get_query_var( 'query' ));
		$this->amount = $params[(array_search('param_amount', $params))+1];
		$this->period = $params[(array_search('param_period', $params))+1];
		$this->age = $params[(array_search('param_age', $params))+1];
		}
		if ( empty( $this->amount ) ) {
			$this->amount = (int) $this->s_atts['param_amount'];
		}
		if ( empty( $this->period ) ) {
			$this->period = (int) $this->s_atts['param_period'];
		}
		if ( empty( $this->age ) ) {
			$this->age = (int) $this->s_atts['param_age'];
		}

		parent::initQuery();

	}
	/**
	 *
	 */
	private function renderMinimumPercent() {
		$query            = $this->query;
		$query['select']  = 'MIN( percent ) AS min';
		//$query['select']  = 'percent';
		$query['where'][] = 'CAST( percent as DECIMAL(10,2) ) != -1';
		unset( $this->query['orderby'] );
		$pod2 = pods( $this->postType, $query );

		$minper = str_replace(",", ".", $pod2->field( 'min' ));
		echo <<<HTML
<div class="block-circle">
    <p class="block-text">{$this->htmlLabels['min_percent']}</p>

    <p class="block-count"><span class="counter">{$minper}</span>%</p>
</div>
HTML;
	}

	/**
	 *
	 */
	private function renderMaximumPercent() {
		$query           = $this->query;
		$query['select'] = 'MAX( CAST(percent as DECIMAL(10,2) ) ) AS max';
		$query['where'][] = 'CAST( percent as DECIMAL(10,2) ) != -1';
		unset( $this->query['orderby'] );
		$pod3     = pods( $this->postType, $query );

		echo <<<HTML
<div class="block-circle">
    <p class="block-text">{$this->htmlLabels['max_percent']}</p>

    <p class="block-count"><span class="counter">{$pod3->field( 'max' )}</span>%</p>
</div>
HTML;
	}

	/**
	 *
	 */
	protected function renderDifference() {
	}
}
