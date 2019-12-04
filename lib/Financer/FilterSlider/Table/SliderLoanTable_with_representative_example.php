<?php

namespace Financer\FilterSlider\Table;


use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Abstracts\Table;
use Financer\FilterSlider\Interfaces\TableInterface;
use Financer\FilterSlider\Util;
use Financer\FilterSlider\Surface\Data;
use Financer\FilterSlider\Surface\Row;
use Financer\FilterSlider\Surface\Surface;

/**
 * Class SliderLoanTable
 * @package Financer\FilterSlider\Table
 */
class SliderLoanTable extends Table implements TableInterface {
	/**
	 * @param null|\Pods  $pod
	 *
	 * @param Slider|null $slider
	 *
	 * @return void
	 *
	 */
	public static function build( \Pods $pod, Slider $slider = null ) {
		$query = $pod->data();
		if ( ! $query ) {
			$query = [];
		}
		$table = new Surface( [ 'class' => 'table table-striped' ] );

		$table->setHead( new Row( [
			new Data( 'loan_company', __( 'Loan company', 'fs' ), [ 'title' => __( 'Logo for company', 'fs' ), 'class' => 'vit' ] ),
			new Data( 'loan_amount', __( 'Loan amount', 'fs' ), [
				'title' => __( 'Max standard credit', 'fs' ),
				'class' => 'sliderm',
			] ),
			new Data( 'loan_period', __( 'Loan period', 'fs' ), [
				'title' => __( 'Searched loan period', 'fs' ),
				'class' => 'sliderm',
			] ),
			new Data( 'apr', __( 'APR', 'fs' ), [ 'title' => __( 'Annual Interest Rate', 'fs' ), 'class' => 'sliderm' ] ),
			new Data( 'monthly_payback', __( 'Monthly Payback', 'fs' ), [ 'title' => __( 'Monthly Payback', 'fs' ), 'class' => 'sliderm' ] ),
			new Data( 'total_cost', __( 'Total Cost', 'fs' ), [
				'title' => __( 'Total cost of the loan', 'fs' ),
				'class' => 'sliderm sorted',
			] ),
			new Data( 'loan', __( 'Loan', 'fs' ), [ 'title' => __( 'Apply for a loan below', 'fs' ), 'class' => 'sliderd' ] ),
			new Data( 'apply', __( 'Apply', 'fs' ), [ 'title' => __( 'Apply for a loan below', 'fs' ) ] ),
		] ) );
		if ( count( $query ) > 0 ) {
			foreach ( $query as $pos => $result ) {
				$date = new \DateTime();
				$date->add( new \DateInterval( 'P' . $slider->getPeriod() . 'D' ) );
				if ( 'new' == $result->loan_restriction ) {
					$restriction = '<span class="newSpan">' . __( 'New', 'fs' ) . '</span>';
				} else if ( 'old' == $result->loan_restriction ) {
					$restriction = '<span class="oldSpan">' . __( 'Old', 'fs' ) . '</span>';
				} else {
					$restriction = '<span class="allSpan">' . __( 'All', 'fs' ) . '</span>';
				}
				if ( ! empty( $result->specific_affiliate_url ) ) {
					$url_link = $result->specific_affiliate_url;
				} else {
					$url_link = user_trailingslashit( get_permalink( $result->ID ) . 'redirect' );
				}
				//Representative example
				$representative_example = "";
				$representative_example .= __( ' Borrow: ', 'fs' ) . Util::moneyFormat( $slider->getAmount() ) . ' ' . __( 'usd', 'fs' );
				$representative_example .= __( ' Max Loan Period: ', 'fs' ) . Util::getPeriod( $slider->getPeriod() );
				$representative_example .= __( ' Loan Fee: ', 'fs' );

				if ( ! empty( $result->fee_flat ) ) {
					$representative_example .= Util::moneyFormat( $result->fee_flat ) . ' ' . __( 'usd', 'fs' );
				} else {
					$representative_example .= Util::moneyFormat( $slider->getAmount() * ( $result->fee_percent / 100 ) ) . ' ' . __( 'usd', 'fs' );
				}
				$representative_example .= __( ' Annual interest rate: ', 'fs' ) . Util::numberFormat( $result->interest_rate ) . '&nbsp;%' . ( $result->highest_annual_interest_rate != 0 ? ' to ' . Util::numberFormat( $result->highest_annual_interest_rate ) . ' %' : '' );
				$representative_example .= __( ' Effective rate: ', 'fs' );
				$formula1               = ( 1 + ( ( $result->interest_rate / 100 ) / ( $slider->period / 30 ) ) );
				$formula2               = ( 1 + ( ( $result->highest_annual_interest_rate / 100 ) / ( $slider->period / 30 ) ) );
				$representative_example .= Util::numberFormat( ( pow( $formula1, ( $slider->period / 30 ) ) - 1 ) * 100 ) . '%';
				if ( $result->highest_annual_interest_rate != 0 ) {
					$representative_example .= ' to ' . Util::numberFormat( ( pow( $formula2, ( $slider->period / 30 ) ) - 1 ) * 100 ) . '%';
				}

				$representative_example .= __( ' Monthly payback: ', 'fs' );
				if ( $slider->period < 30 ) {
					$monthly = 1;
				} else {
					$monthly = $slider->period / 30;
				}
				$cost1                  = ( $slider->getAmount() * ( $result->interest_rate / 100 ) );
				$cost2                  = ( $slider->getAmount() * ( $result->highest_annual_interest_rate / 100 ) );
				$representative_example .= Util::moneyFormat( ( $cost1 + $slider->getAmount() ) / $monthly ) . ' ' . __( 'usd', 'fs' );
				if ( $result->highest_annual_interest_rate != 0 ) {
					$representative_example .= ' to ' . Util::moneyFormat( ( $cost2 + $slider->getAmount() ) / $monthly ) . ' ' . __( 'usd', 'fs' );
				}

				$representative_example .= __( ' Total paid back amount: ', 'fs' );
				$cost1                  = ( $slider->getAmount() * ( $result->interest_rate / 100 ) );
				$cost2                  = ( $slider->getAmount() * ( $result->highest_annual_interest_rate / 100 ) );
				$representative_example .= Util::moneyFormat( ( $cost1 + $slider->getAmount() ) ) . ' ' . __( 'usd', 'fs' );
				if ( $result->highest_annual_interest_rate != 0 ) {
					$representative_example .= ' to ' . Util::moneyFormat( ( $cost2 + $slider->getAmount() ) ) . ' ' . __( 'usd', 'fs' );
				}
				//
				/*loan tags*/
				$term_list             = wp_get_post_terms( $result->pid, 'loan_tags', [ "fields" => "all" ] );
				$output_loan_tags      = '';
				$output_loan_tags_slug = '';
				foreach ( $term_list as $loan_tag ) {
					$output_loan_tags      .= $loan_tag->name . ', ';
					$output_loan_tags_slug .= $loan_tag->slug . ' ';

				}
				//

				$table->addRow( new Row( [
					// Logo

					new Data( 'logo', '<i class="mega-icon-eraser report"><a href="#" title="' . __( 'Wrong data? Report this item', 'fs' ) . '">&nbsp;</a></i>' . ( ( null !== $slider && ! $slider->isPdf() ) || false === (bool) $result->ej_partner ? '<i title="' . __( 'More information', 'fs' ) . '" class="toggle-details fa fa-plus" ></i>' : '' ) . '<a href="' . get_permalink( $result->ID ) . '">' . '<img title="' .
					          $result->title . '" src="' . $pod->field( 'logo._src' ) . '" />' . '</a>' .
					          self::showStars( $result->ID ) . ' <span class="totalReviews"><a href="' . get_permalink( $result->ID ) . '#reviews">' . __( 'Read', 'fs' ) . '&nbsp;' . $result->total_reviews . ' ' . __( 'reviews.', 'fs' ) . '</a></span>' .
					          '<div class="tag-example-wrapper">' . '<div class="representative-example">' .
					          ( $output_loan_tags ? '<div class="data-tag ' . $output_loan_tags_slug . '">' . $output_loan_tags . '</div>' : '' ) . __( 'Representative Example', 'fs' ) . ':' . $representative_example . '</div>' .
					          '</div>',

						[
							'class' => 'loan-company ' .
							           ( $result->favorite ? 'vit premium' : 'vit' ) . ( $result->ej_partner ? ' np' : '' )
						] ),
					// Loan Amounts
					new Data( 'loan_amount', '<span class="mobile-only">' . __( 'Loan amount:', 'fs' ) . '</span> ' . Util::moneyFormat( $slider->getAmount() ) . ' ' . __( 'usd', 'fs' ) . '<br>' . ' ' . $restriction, [ 'class' => 'loan-amount' ] ),
					// Loan Period
					new Data( 'loan_period', '<span class="mobile-only">' . __( 'Loan period:', 'fs' ) . '</span> ' . Util::getPeriod( $slider->getPeriod() ), [ 'class' => 'loan-period' ] ),
					// Loan APR
					new Data( 'nominal_apr', '<span class="mobile-only">' . __( 'Nominal APR:', 'fs' ) . '</span><span class="sort-interest">' . Util::numberFormat( $result->interest_rate ) . '</span>&nbsp;%' . ( $result->highest_annual_interest_rate != 0 ? ' to ' . Util::numberFormat( $result->highest_annual_interest_rate ) . ' %' : '' ), [ 'class' => 'loan-apr' ] ),
					// Loan Fees
					new Data( 'monthly_payback', '<span class="mobile-only">' . __( 'Monthly payback:', 'fs' ) . '</span> ' . Util::moneyFormat( $result->total_monthly_payback ) . ' ' . __( 'usd', 'fs' ), [ 'class' => 'loan-monthly-payback' ] ),
					// Loan Total Cost
					new Data( 'what_you_pay', '<span class="mobile-only">' . __( 'This is what you pay:', 'fs' ) . '</span>' . Util::moneyFormat( $result->total_cost ) . ' ' . __( 'usd', 'fs' ) . ( 0 == $result->total_cost ? ' <span class="greenSpan">' . __( 'Free loan', 'fs' ) . ' </span>' : '' ), [ 'class' => 'loan-total fet' . ( 0 == $result->total_cost ? ' green' : '' ) ] ),
					// Loan Apply
					/*new Data( 'application', '<a href="' . user_trailingslashit( get_permalink( $result->ID ) . 'redirect' ) . '" class="button small applyYellow" target="_blank" rel="nofollow"> ' . __( 'Application', 'fs' ) . ' </a><a href="' . get_permalink( $result->ID ) . '" class="applyNow">' . __( 'Read more', 'fs' ) . '</a>', [ 'class' => 'loan-apply' ] ),*/


					new Data( 'application', '<a href="' . $url_link . '" class="button small applyYellow" target="_blank" rel="nofollow"> ' . __( 'Application', 'fs' ) . ' </a><a href="' . get_permalink( $result->ID ) . '" class="applyNow">' . __( 'Read more', 'fs' ) . '</a>', [ 'class' => 'loan-apply' ] ),
					//

					//tags

					//

				], [ 'data-id' => $result->ID, 'class' => 'sort-item ' . ( $pos % 2 ? ' even' : ' odd' ) . ( $result->ej_partner ? ' greyed' : '' ) . ( $result->favorite ? ' premium' : '' ) ] ) );

				if ( ( null !== $slider && ! $slider->isPdf() ) || false === (bool) $result->ej_partner ) {
					$table->addRow( new Row( [

						new Data( 'borrow_up_to', '<strong>' . __( 'Borrow up to:', 'fs' ) . '</strong>&nbsp;<br />' . Util::moneyFormat( $result->amount_range_maximum ) . '&nbsp;' . __( 'usd', 'fs' ) . '<br /><br /><strong>' .
						          __( 'Estimated pay back:', 'fs' ) . '</strong>&nbsp;<br />' . $date->format( 'd-m-Y' ), [ 'colspan' => 2 ] ),
						new Data( 'bad_credit_history', '<strong>' . __( 'Bad credit history:', 'fs' ) . '</strong>&nbsp;<br/><p class="' . ( $result->bad_history ? 'true' : 'false' ) . '" ></p><br/><br/><strong>' .
						          __( 'Weekend payout:', 'fs' ) . '</strong>&nbsp;<br /><p class="' . ( $result->weekend_payout ? 'true' : 'false' ) . '"></p>', [ 'colspan' => 2 ] ),
						new Data( 'age_minalder', '<strong>' . __( 'Age:', 'fs' ) . '</strong>&nbsp;<br />' . $pod->display( 'minalder' ) . '<br /><br /><strong>' .
						          __( 'Credit check:', 'fs' ) . '</strong>&nbsp;<br />' . $pod->display( 'credit_check' ), [ 'colspan' => 2 ] ),

					], [ 'class' => 'details' ] ) );
				}
				$pod->fetch();
			}
			echo $table->render();
		} else {

			/*$table->addRow( new Row( [ new Data( false, __( 'No loan companies found in that search. Try using less filters.', 'fs' ), [ 'colspan' => 100 ] ) ] ) );*/
			echo __( 'No loan companies found in that search. Try using less filters.', 'fs' );

			echo do_shortcode( '[top_rated_companies title="Top Rated Companies" limit="5"]' );
		}


	}
}

?>
