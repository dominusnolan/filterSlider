<?php

namespace Financer\FilterSlider\Slider;


use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\SortUtil;
use Financer\FilterSlider\Util;

/**
 * Class Mortgage
 * @package Financer\FilterSlider\Slider
 */
class Mortgage extends Slider {
	protected $postType = 'mortgage';

	protected $tableClass = 'MortgageSliderTable';

	protected $percent = 0;

	protected $fields = [
		't.ID AS ID',
		't.post_title AS title',
		'bank.post_title AS bank_title',
		'bank.ID AS bank_id',
		'bank.d.ej_partner AS ej_partner',
		'bank.d.favorite AS favorite',
		'min_amount',
		'max_amount',
		'total_fees',
		'd.url',
		'bank.d.overall_rating AS rating',
	];
	protected $sortEnabled = false;
	protected $hasCompany = false;
    protected $filterList = [];
	/**
	 * Pod setting storing slider comparison run count
	 * @var string
	 */
	protected $compareCounterSetting = 'mortgage_comparisons';

	/**
	 * @inheritDoc
	 */
	public function __construct( array $atts ) {
		$this->paramsList                                                   = array_merge( $this->paramsList, [ 'percent' ] );
		$this->shareableParamsList                                       [] = 'percent';
		$this->paramsNoSanitize                                       []    = 'period';
		$this->steps[ array_search( 'filters', $this->steps ) ]             = 'percent';
		parent::__construct( $atts );
	}

	/**
	 * @return int
	 */
	public function getPercent() {

		return $this->percent;
	}

    protected function stepLimits_sort()
    {

    }

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	public function generateJsMaps( $params = [] ): array {

		$slider_settings = pods( 'slider_settings' );

		$amount_min      = (int) $slider_settings->field( 'mortgage_amount_min' );
		$amount_max      = (int) $slider_settings->field( 'mortgage_amount_max' );
		$amount_interval = (int) $slider_settings->field( 'mortgage_amount_interval' );
		$period_list     = $slider_settings->field( 'mortgage_periods' );
		if ( $period_list ) {
			$period_list = explode( "\n", $period_list );
			array_walk( $period_list, 'trim' );
		} else {
			$period_list = [];
		}
		$percent_min      = (int) $slider_settings->field( 'mortgage_percent_min' );
		$percent_max      = (int) $slider_settings->field( 'mortgage_percent_max' );
		$percent_interval = (int) $slider_settings->field( 'mortgage_percent_interval' );

		$periods     = [];
		$amounts     = [];
		$percentages = [];
		for ( $i = $amount_min; $i <= $amount_max; $i += $amount_interval ) {
			$amounts[ $i ] = Util::moneyFormat( $i ) . ' ' . __( 'usd', 'fs' );
		}
		for ( $i = $percent_min; $i <= $percent_max; $i += $percent_interval ) {
			$percentages[ $i ] = $i . '%';
		}
		foreach ( $period_list as $period ) {
			$periods[ $period ] = Util::getPeriod( (float) $period * 365 );
		}
		ksort( $amounts );
		ksort( $percentages );

		return [
			$this->instanceId . '_amountMap'  => $amounts,
			$this->instanceId . '_periodMap'  => $periods,
			$this->instanceId . '_percentMap' => $percentages,
		];
	}

	protected function sort() {
		if ( $this->period < 1 ) {
			$field = ( $this->period * 12 ) . '_mir';
		} else {
			$field = $this->period . '_yir';
		}

		SortUtil::processFavorite( $this->pod->rows, [ $field ] );
	}

	/**
	 * @inheritDoc
	 */
	protected function table() {
		parent::table();
		if ( $this->isAjax && $this->total > 0 ):
			$house_multiplier = 100 / ( (float) $this->percent );
			$total = $house_multiplier * $this->amount;
			/** @noinspection PhpUndefinedMethodInspection */
			$cover             = ( 100 - $this->percent ) / 100;
			$left_over         = $cover * $total;
			$homepage_settings = pods( 'homepage_settings' );
			?>
            <div class="msg mortgage-info boxed slider-msg">
                <h2><?php _e( 'Mortgage example', 'fs' ) ?></h2>
				<?php _e( 'This tool is used to give you an estimation - an idea of the costs. We have calculated some data for you, based on your inputs:', 'fs' ) ?>
                <br/>
				<?php _e( 'You want to lend', 'fs' ) ?> <?php echo Util::moneyFormat( $this->amount ) ?> <?php _e( 'usd', 'fs' ); ?>,&nbsp;<?php _e( 'with an interest fixed for', 'fs' ) ?>
	            <?php echo Util::getPeriod( $this->period * 360 ) ?>.
				<?php _e( 'The total cost of the house is', 'fs' ) ?> <?php echo round( $total ) ?> <?php _e( 'usd', 'fs' ); ?>.<br/>
				<?php _e( 'Remaining cost of the house', 'fs' ) ?>, <?php echo round( $left_over ) ?> <?php _e( 'usd', 'fs' ); ?>, <?php _e( 'will have to be paid by you', 'fs' ) ?>.
	            <?php _e( 'This part can be covered up with a', 'fs' ) ?> <a href="<?php echo get_the_permalink( $homepage_settings->field( 'personal' )['ID'] ) ?>"><?php _e( 'personal loan', 'fs' ) ?></a>.
            </div>
		<?php
		endif;
	}

	protected function labels() {
		parent::labels();
		$this->htmlLabels = [
                                'compare_now' => __( 'What amount do you need for the house?', 'fs' ),
                                'display'            => __( 'Amount of mortgages to display', 'fs' ),
			                    'display_-1'         => __( 'All mortgages', 'fs' ),
			                    'percent'            => __( 'Percentage to of house cover', 'fs' ),
			                    'period'             => __( 'Fixed period', 'fs' ),
			                    'step4'              => __( 'Compare mortgages', 'fs' ),
			                    'submit'             => __( 'Find me the best mortgages', 'fs' ),
			                    'total_count'        => __( 'Difference', 'fs' ),
			                    'min_interest'       => __( 'Lowest', 'fs' ),
			                    'mortgage_note'      => __( 'Please note that this slider is just for simulation purposes, to provide estimated costs. <br><br>All banks have different qualifications and specific limits for every individual. ', 'fs' ),
			                    'max_interest'       => __( 'Highest', 'fs' ),
			                    'slider_type'        => __( 'mortgage', 'fs' ),
			                    'slider_type_plural' => __( 'mortgages', 'fs' ),
			                    'guide_1'            => __( 'Drag the sliders on the left to find your mortgage', 'fs' ),
		                    ] + $this->htmlLabels;


        if ($this->getMinimalStatus() == 'single') {
            $this->htmlLabels['compare_now'] = __( 'Find a mortgage that suits your needs - compare quick and easy', 'fs' );
        }

	}

	protected function buildQuery() {

		$this->amount  = (int) $this->amount;
		$this->period  = (float) $this->period;
		$this->percent = (float) $this->percent;
		$this->limit   = (int) $this->limit;
		$this->query   = [
			'limit'   => $this->limit,
			'where'   => [
				[
					'key'   => 'post_status',
					'value' => 'publish',
				],
				[
					'key'     => 'bank',
					'compare' => 'EXISTS',
				],
			],
			'expires' => 3600,
		];
		switch ( $this->period ) {
			case 0.25:
				$field = '3_mir';
				break;
			case 1:
				$field = '1_yir';
				break;
			case 2:
				$field = '2_yir';
				break;
			case 3:
				$field = '3_yir';
				break;
			case 4:
				$field = '4_yir';
				break;
			case 5:
				$field = '5_yir';
				break;
			case 7:
				$field = '7_yir';
				break;
			case 10:
				$field = '10_yir';
				break;
			default:
				$field = '3_mir';
				break;
		}
		$this->fields[]        = $field . ' AS ' . $field;
		$this->query['select'] = ! empty( $this->fields ) ? $this->fields : null;

		if ($this->singleCompany == false) {
		$this->query['where'][] = [
			'key'   => 'min_amount',
                'value'   => $this->amount,
			'compare' => '<=',
		];

		$this->query['where'][] = [
			'key'   => 'max_amount',
                'value'   => $this->amount,
			'compare' => '>=',
		];
        }

		if ($this->param_single_company_name) {
            $this->query['where'][] = [
                'key'   => 'bank.post_title',
                'value' => $this->param_single_company_name
            ];
        }

		$this->query['orderby'] = 'CAST(' . $field . ' as DECIMAL(10,2)) ASC';

	}

	protected function renderCounterItems() {
		parent::renderTotalCount();
		static::renderMaximumInterest();
		static::renderDifference();
	}

	private function renderMaximumInterest() {
		$query = $this->query;
		if ( $this->period < 1 ) {
			$field = ( $this->period * 12 ) . '_mir';
		} else {
			$field = $this->period . '_yir';
		}
		$query['select'] = 'MAX( CAST(' . $field . '  as DECIMAL(10,2) ) ) AS max';
		unset( $query['orderby'] );
		$pod = pods( $this->postType, $query );
		echo <<<HTML
<div class="block-circle">
    <p class="block-text">{$this->htmlLabels['max_interest']}</p>

    <p class="block-count"><span class="counter">{$pod->field( 'max' )}</span>%</p>
</div>
HTML;
	}

	/**
	 *
	 */
	protected function renderDifference() {
		global $wp_locale;
		if ( $this->period < 1 ) {
			$field = ( $this->period * 12 ) . '_mir';
		} else {
			$field = $this->period . '_yir';
		}
		$data    = $this->pod->data();
		$amounts = [];
		if ( ! empty( $data ) ) {
			$amounts = wp_list_pluck( $this->pod->data(), $field );
			$amounts = array_map( 'floatval', $amounts );
			natsort( $amounts );
		}
		natsort( $amounts );
		$min                                       = $amounts[0];
		$max                                       = end( $amounts );
		$old_decimal_point                         = $wp_locale->number_format['decimal_point'];
		$wp_locale->number_format['decimal_point'] = '.';
		$difference                                = Util::moneyFormat( $max - $min );
		$wp_locale->number_format['decimal_point'] = $old_decimal_point;
		echo <<<HTML
<div class="block-circle">
    <p class="block-text">{$this->htmlLabels['difference_text']}</p>

    <p class="block-count"><span class="counter">{$difference}</span>%
    </p>
</div>
HTML;
	}

	protected function stepPercentHeader() {
		echo <<<HTML
<div class="step percent" style="margin-left:0;">	
HTML;
	}

    protected function stepFilters()
    {
        parent::stepFilters($type = 'mortgage'); //mortgage
    }

}
