<?php
/*
 * Generic loan slider
 */

namespace Financer\FilterSlider\Slider;


use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Util;


/**
 * Class GenericLoan
 *
 * Generic loan slider
 *
 * @package Financer\FilterSlider\Slider
 */
class GenericLoan extends Slider {

	protected $fields = [
		'{PARENTTABLE}post_title as title',
		'{PARENTTABLE}post_name as name',
		'{PARENTTABLE}ID as ID',
		'{COMPANYPARENT}ej_partner',
		'{COMPANYPARENT}bad_history',
		'{COMPANYPARENT}helgutbetalning AS weekend_payout',
		'{COMPANYPARENT}favorite',
		'{COMPANYPARENT}minalder',
		'{COMPANYPARENT}minimum_inkomst',
		'{COMPANYPARENT}loan_broker',
		'{COMPANYPARENT}foretag',
		'{COMPANYPARENT}url',
		'{COMPANYPARENT}company_ranking',
		'{COMPANYPARENT}special_text',
		'{COMPANYPARENT}overall_rating AS rating',
		'{PREFIX}period_range_maximum',
		'{PREFIX}period_range_minimum',
        /*'{PREFIX}age_dropdown_range_maximum',
		'{PREFIX}age_dropdown_range_minimum',*/
        '{PREFIX}loan_name as loan_name',
        '{PREFIX}credit_score as credit_score',
        '{PREFIX}effective_interest_rate as effective_interest_rate',
		'{PREFIX}amount_range_minimum',
		'{PREFIX}amount_range_maximum',
		'{PREFIX}interest_rate AS interest_rate',
		'{PREFIX}loan_restriction',
		'{PREFIX}highest_annual_interest_rate AS highest_annual_interest_rate',
		'{PREFIX}custom_interest_rate',
		'{PREFIX}specific_affiliate_url',
		'{PREFIX}fee_flat AS fee_flat',
		'{PREFIX}fee_percent AS fee_percent',
		'{PREFIX}monthly_fee AS monthly_fee',
		'{TABLEFIELD}ID AS pid',
		'{PREFIX}representative_example',
        '{COMPANYPARENT}visits as visits',
        '{COMPANYPARENT}approval_rate as approval_rate',
        '{COMPANYPARENT}mandag_fredag as mandag_fredag',
        '{COMPANYPARENT}lordag as lordag',
        '{COMPANYPARENT}open_weekdays as open_weekdays',
        '{COMPANYPARENT}close_weekdays as close_weekdays',
        '{COMPANYPARENT}open_saturday as open_saturday',
        '{COMPANYPARENT}close_saturday as close_saturday',
        '{COMPANYPARENT}open_sunday as open_sunday',
        '{COMPANYPARENT}close_sunday as close_sunday',
    ];
	/**
	 * Pod setting storing slider comparison run count
	 * @var string
	 */
	protected $compareCounterSetting = 'loan_comparisons';

	/**
	 * @return string
	 */
	protected function preHeader(): string {
		$general_settings = pods( 'general_settings' );
		if ( $general_settings->field( 'charity_page' ) ) {
			return <<<HTML
    <a href="{$general_settings->field( 'charity_page.permalink' )}" rel="nofollow"><div class="charity-notice">{$this->htmlLabels['charity_notice']}</div></a>
HTML;
		}

		return '';
	}

	protected function afterHeader(): string {
		$slider_settings = pods( 'slider_settings' );
		if ( $slider_settings->field( 'loan_notice' ) && ($this->minimal != 'true') ) {
			return <<<HTML
    <div class="loan-notice">{$slider_settings->field( 'loan_notice' )}</div>
HTML;
		}else{
			return '';
		}
	}

	protected function loanQuiz(): string {
		$general_settings = pods( 'general_settings' );
		if ( $general_settings->field( 'loan_quiz_page' ) ) {
			return <<<HTML
<a href="{$general_settings->field( 'loan_quiz_page.permalink' )}" class="quiz-button">{$this->htmlLabels['loan_quiz_text']}</a>
HTML;
		}

		return '';
	}

	/**
	 * @inheritdoc
	 */
	protected function buildQuery() {
		if ( 'total-cost' === $this->sort ) {
			$this->postType                 = 'loan_dataset';
			$this->prefixCompanyParentMain  = 'company_parent.';
			$this->prefixCompanyParentTable = 'company_parent.';
			$this->prefix                   = '';

			$prefixCompanyParentTableMainField = 'company_parent.d.';
			$prefixCompanyParentFieldTable     = 't.';
			$creditCheck = 'company_parent.';

		} else {
			$prefixCompanyParentTableMainField = '';
			$prefixCompanyParentFieldTable     = 'loan_datasets.d.';
			$creditCheck = '';
		}

		$this->fields = str_replace( [ '{COMPANYPARENT}', '{PREFIX}', '{PARENTTABLE}', '{TABLEFIELD}', '{CREDITCHECK}' ], [ $prefixCompanyParentTableMainField, $this->prefix, $this->prefixCompanyParentTable, $prefixCompanyParentFieldTable,$creditCheck  ], $this->fields);

		parent::buildQuery();
	}


    protected function stepLimits_sort() {

        echo '<div class="item-heading mob-arrow-down">';
        echo( get_post_meta( $post->ID, 'sorting_title', true ) ? get_post_meta( $post->ID, 'sorting_title', true ) : _e( 'Additional Options', 'fs' ) );
        echo '</div>';

        /*$minimalClass = '';
        if (parent::getMinimalStatus() == 'true') {
            $minimalClass = ' class="dropdown-desktop"';
        }*/

        if ( $this->limitsEnabled || ! $this->sortEnabled ) {
            echo <<<HTML
	    <div id="slider_options" class="dropdown-desktop">
HTML;
            if ( $this->limitsEnabled ) {
                echo <<<HTML
	        <div class="itemCount">
	            {$this->htmlLabels['display']}:
	            <select class="filter_nav" name="param_limit">
HTML;
                foreach ( $this->limitList as $limit => $selected ) {
                    $selected = selected( true, $selected );
                    echo <<<HTML
		<option value="$limit"$selected>{$this->htmlLabels['display_' . $limit]}</option>
HTML;
                }
                echo <<<HTML
	            </select>
	        </div>
HTML;
            }
            if ( ! $this->sortEnabled ) {
                echo '<div class="dropdown-mobile"><div class="dropdown-button sort_type sort-text">' . $this->htmlLabels['sort'] . '</div><div class="dropdown-content">';
                foreach ( [ 'company-name', 'best-rating', 'interest-rate', 'total-cost', 'most-like-interest-rate', 'yearly-best-review'] as $sortType ) {
                    $sortTitle        = ucfirst( $sortType );
                    $selected_current = ( $this->sort == $sortType ) ? ' current' : '';
                    echo '<div class="sort_type sort_type_trigger ' . $sortType . $selected_current . '" data-toggle="' . $sortType . '"><a href="javascript:void(0);" class="show-sort">' . __( $sortTitle, 'fs' ) . '</a></div>';
                }
            }


            echo <<<HTML
			</div></div>
HTML;
        }


    }

	/**
	 * @inheritdoc
	 */
	protected function initQuery() {
		if ( ! empty( get_query_var( 'query' ) ) ) {
			$params       = explode( '/', get_query_var( 'query' ) );
			$this->amount = $params[ ( array_search( 'param_amount', $params ) ) + 1 ];
			$this->period = $params[ ( array_search( 'param_period', $params ) ) + 1 ];
			$this->age = $params[ ( array_search( 'param_age', $params ) ) + 1 ];
		}
		if ( empty( $this->amount ) || (strpos($this->amount, '_') !== false) ) {
			$this->amount = (int) $this->sliderSettings->field( 'default_loan_slider_amount' );
		}
		if ( empty( $this->period ) || (strpos($this->period, '_') !== false) ) {
			$this->period = (int) $this->sliderSettings->field( 'default_loan_slider_period' );
		}
		if ( empty( $this->age ) || (strpos($this->age, '_') !== false) ) {
			$this->age = (int) $this->sliderSettings->field( 'age_range_minimum' );
		}

		parent::initQuery();
	}


	protected function labels() {
		parent::labels();
		$this->htmlLabels = [
			                    'lowest_interest_rate' => __( 'Lowest rate', 'fs' ),
		                    ] + $this->htmlLabels;
	}

	/**
	 * @return void
	 */
	protected function renderDifference() {
		global $wp_locale;
		$data    = $this->pod->data();
		$amounts = [];
		if ( ! empty( $data ) ) {
			$amounts = wp_list_pluck( $this->pod->data(), 'total_cost' );
			$amounts = array_map( 'floatval', $amounts );
			natsort( $amounts );
		}
		if ( empty( $amounts ) ) {
			$amounts[] = 0;
		}
		$min                                       = $amounts[0];
		$max                                       = end( $amounts );
		$old_decimal_point                         = $wp_locale->number_format['decimal_point'];
		$wp_locale->number_format['decimal_point'] = '.';
		$difference                                = Util::numberFormat( $max - $min );
		$wp_locale->number_format['decimal_point'] = $old_decimal_point;
		$symbol                                    = '&nbsp;' . __( 'usd', 'fs' );
		echo <<<HTML
<div class="block-circle">
    <p class="block-text">{$this->htmlLabels['difference_text']}</p>
    <p class="block-count"><span class="counter">{$difference}</span>$symbol</p>
</div>
HTML;

	}

	/**
	 *
	 */
	protected function renderCounterItems() {
		parent::renderCounterItems();
		static::renderLowestRate();
	}

	private function renderLowestRate() {
		$query           = $this->query;
		//$query['select'] = 'MIN( interest_rate ) lowest_rate';
		unset( $query['orderby'] );
		$pod     = pods( $this->postType, $query );
		$percent = Util::numberFormat( $pod->field( 'lowest_rate' ) );
		echo <<<HTML
<div class="block-circle">
    <p class="block-text">{$this->htmlLabels['lowest_interest_rate']}</p>

    <p class="block-count"><span class="counter">{$percent}</span>%</p>
</div>
HTML;
	}


	protected function stepAge() {

		parent::stepAge();
/*		if ( $this->sliderSettings->field( 'enable_age_dropdown' ) == '1' ) {
			echo __( 'Age', 'fs' ) . ':
		<select class="filter_nav" name="param_age_dropdown" id="param_age_dropdown">
			<option value="">' . __( 'select', 'fs' ) . '</option>
			<option value="18">18</option>
			<option value="19">19</option>
			<option value="20">20</option>
			<option value="21">21</option>
			<option value="22">22</option>
			<option value="23+">23+</option>
		</select>
		';
		}*/
	}

    protected function stepFilters()
    {
        parent::stepFilters($type = 'generic');
    }
}
