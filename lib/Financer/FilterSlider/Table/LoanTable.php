<?php

namespace Financer\FilterSlider\Table;


use Financer\FilterSlider\Abstracts\Table;

use Financer\FilterSlider\Surface\Item;
use Financer\FilterSlider\Util;
use Financer\FilterSlider\Surface\Data;
use Financer\FilterSlider\Surface\Row;
use Financer\FilterSlider\Surface\Surface;

/**
 * Class LoanTable
 * @package Financer\FilterSlider\Table
 */
class LoanTable extends Table {
	/**
	 * @param array $query
	 *
	 * @return void
	 * @internal param null|\Pods $pod
	 *
	 * @internal param Slider|null $slider
	 *
	 */
	public static function build( array $query ) {
		$table         = new Surface( [ 'class' => 'table table-striped' ] );
		$sliderSetting = pods( 'slider_settings' );
		$remove_apr    = $sliderSetting->field( 'remove_apr' );
		$remove_style  = "";
		if ( $remove_apr == 1 ) {
			$remove_style = "display:none;";
		}

		$table->setHead( new Row( [
			new Data( 'loan_amount', __( 'Loan amount', 'fs' ), [
				'title' => __( 'Max standard credit', 'fs' ),
				'class' => 'sliderm',
			] ),
			new Data( 'loan_period', __( 'Loan period', 'fs' ), [
				'title' => __( 'Searched loan period', 'fs' ),
				'class' => 'sliderm',
			] ),
			new Data( 'apr', __( 'APR', 'fs' ), [ 'title' => __( 'Annual Interest Rate', 'fs' ), 'class' => 'sliderm', 'style' => $remove_style ] ),
			new Data( 'monthly_payback', __( 'Monthly Payback', 'fs' ), [ 'title' => __( 'Monthly Payback', 'fs' ), 'class' => 'sliderm' ] ),
			new Data( 'total_cost', __( 'Total Cost', 'fs' ), [
				'title' => __( 'Total cost of the loan', 'fs' ),
				'class' => 'sliderm sorted',
			] )
		] ) );
		if ( count( $query ) > 0 ) {
			foreach ( $query as $pos => $result ) {
				if ( 'new' == $result->loan_restriction ) {
					$restriction = '<span class="newSpan">' . __( 'New', 'fs' ) . '</span>';
				} else if ( 'old' == $result->loan_restriction ) {
					$restriction = '<span class="oldSpan">' . __( 'Old', 'fs' ) . '</span>';
				} else {
					$restriction = '<span class="allSpan">' . __( 'All', 'fs' ) . '</span>';
				}
				$date = new \DateTime();
				$date->add( new \DateInterval( 'P' . $result->period . 'D' ) );
				$table->addRow(
					new Item( [
						new Row( [
							// Loan Amounts
							new Data( 'amount', Util::moneyFormat( $result->amount ) . ' ' . __( 'usd', 'fs' ) . '<br />' . ' ' . $restriction ),
							// Loan Period
							new Data( 'period', Util::getPeriod( $result->period ) ),
							// Loan APR
							new Data( 'interest_rate', Util::numberFormat( $result->interest_rate ) . ' %' . ( $result->highest_annual_interest_rate != 0 ? ' - ' . Util::numberFormat( $result->highest_annual_interest_rate ) . '%' : '' ), [ 'style' => $remove_style ] ),
							// Loan Fees
							new Data( 'total_monthly_payback', Util::moneyFormat( $result->total_monthly_payback ) . ' ' . __( 'usd', 'fs' ) ),
							// Loan Total Cost
							new Data('total_cost', Util::moneyFormat( $result->total_cost ) . ' ' . __( 'usd', 'fs' ) . ( 0 == $result->total_cost ? ' <span class="greenSpan">' . __( 'Free loan', 'fs' ) . ' </span>' : '' ), [ 'class' => 'sliderm fet' . ( 0 == $result->total_cost ? ' green' : '' ) ] ),
						] )
					], [ 'data-id' => $result->ID, 'data-period' => $result->period, 'class' => ( $pos % 2 ? ' even' : ' odd' ) ] ) );
			}
		} else {
			$table->addRow( new Row( [ new Data( false, __( 'No loans exist for this company', 'fs' ) ) ] ) );
		}
		echo $table->render();
	}
}
