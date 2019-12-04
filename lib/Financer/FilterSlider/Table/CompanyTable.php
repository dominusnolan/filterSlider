<?php

namespace Financer\FilterSlider\Table;


use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Abstracts\Table;
use Financer\FilterSlider\Interfaces\TableInterface;
use Financer\FilterSlider\RepresentativeExampleUtil;
use Financer\FilterSlider\Surface\Item;
use Financer\FilterSlider\Util;
use Financer\FilterSlider\Surface\Data;
use Financer\FilterSlider\Surface\Row;
use Financer\FilterSlider\Surface\Surface;

/**
 * Class LoanTable
 * @package Financer\FilterSlider\Table
 */
class CompanyTable extends Table implements TableInterface {
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

		$generalSettings = pods( 'general_settings' );

		$sliderSetting = pods( 'slider_settings' );

		$remove_credit_check = $sliderSetting->field( 'remove_credit_check' );
		$remove_apr    = $sliderSetting->field( 'remove_apr' );
		$remove_style  = "";
		$rating = "";


		$table = new Surface( [ 'class' => 'table table-striped company_table' ] );
		$table->setHead( new Row( [
			new Data( 'loan_company', __( 'Loan company', 'fs' ), [ 'title' => __( 'Logo of the loan company', 'fs' ), 'class' => 'vit' ] ),
			new Data( 'loan_amount', __( 'Loan amount', 'fs' ), [ 'title' => __( 'The range of loan amounts possible to borrow', 'fs' ) ] ),
			new Data( 'loan_period', __( 'Loan period', 'fs' ), [
				'title' => __( 'The loan period you are searching for. The lender may lend in shorter or longer periods', 'fs' ),
				'class' => 'sliderm',
			] ),
			new Data( 'bad_credit_history', __( 'Bad credit history', 'fs' ), [ 'class' => 'sliderm', 'title' => __( 'The policy regarding a bad credit history', 'fs' ) ] ),
			new Data( 'weekend_payout', __( 'Weekend payout', 'fs' ), [ 'class' => 'sliderm', 'title' => __( 'Possibility to get payout during weekends', 'fs' ) ] ),
			new Data( 'credit_check', __( 'Credit check', 'fs' ), [ 'class' => 'sliderm', 'title' => __( 'Which credit check company the lender uses', 'fs' ), 'style' => $remove_credit_check == 1 ? 'display:none;' : '' ] ),
			new Data( 'min_age', __( 'Min. age', 'fs' ), [ 'class' => 'sliderm', 'title' => __( 'The minimum age you need to borrow money', 'fs' )  ] ),
			new Data( 'apr', __( 'APR', 'fs' ), [ 'title' => __( 'The annual interest rate (not effective rate)', 'fs' ), 'class' => 'sliderm', 'style' => $remove_apr == 1 ? 'display:none;' : '' ] ),
			__( 'Read more', 'fs' ),
		] ) );
		if ( count( $query ) > 0 ) {

			foreach ( $query as $pos => $result ) {
				$filter = pods(
					'loan_dataset', [
						'select' => [
							'min( amount_range_minimum ) AS min',
							'max( amount_range_maximum ) AS max',
						],
						'where'  => [
							'company_parent.ID' => $result->ID,
						],
					]
				);

				$filter2 = pods(
					'loan_dataset', [
						'select' => [
							'period_range_minimum AS min',
							'period_range_maximum AS max',
						],
						'where'  => [
							'company_parent.ID' => $result->ID,
						],
					]
				);

				$filter3 = pods(
					'loan_dataset', [
						'select' => [
							'min( interest_rate ) AS min',
							'max( highest_annual_interest_rate ) AS max',
						],
						'where'  => [
							'company_parent.ID' => $result->ID,
						],
					]
				);
				if ( $filter2 ) {
					$period_min = $filter2->field( 'min' );
					$period_max = $filter2->field( 'max' );
				} else {
					$period_min = 380;
					$period_max = 380;
				}
				$show_period = ! ( empty( $period_min ) || empty( $period_max ) );
				$items       = [];

                $loan_dataset = pods( 'loan_dataset',  ['where'  => ['company_parent.ID' => $result->ID]]);

                $displayVisitCounter = ($result->visits > 0) ? '<p class="chosen-amount">' . __( 'Chosen', 'fs' ) .'<strong> ' . $result->visits . '</strong> ' . __( 'times', 'fs' ) .'</p>' : '';
                $loanName = ($loan_dataset->display('loan_name')) ? '<br>' . $loan_dataset->display('loan_name') : '';

                $url_link = user_trailingslashit( get_permalink( $result->ID ) . 'redirect' ) . "?b=s";

                if ( ! empty( $result->specific_affiliate_url ) ) {
                    $url_link = user_trailingslashit( get_permalink( $result->ID ) ) . 'redirect/?id=' . $result->pid . "&b=s";
                }

				//<a href="' . user_trailingslashit( get_permalink( $result->ID ) . 'redirect' ) . '?b=t" class="button small applyYellow" target="_blank" rel="nofollow" title="' . __( 'Borrow money from', 'fs' ) . ' ' . $result->title . '">' . __( 'Apply now', 'fs' ) . '</a>'

                $companyLinkReview = '#';
                if ($pod->field( 'company')) {
                    $companyLinkReview = get_permalink( $result->ID ). '#msform';
                }

                $pixel_impression = Util::trackingUrl($result->offer_tracking_url, $result->impression_tracking_url, $loan_dataset->offer_tracking_url, $loan_dataset->impression_tracking_url);

                //$new_url_link = "window.open('".$url_link."')";
                //$redirectLinkPopup = '<a href="' . $companyLinkReview . '" onclick="'.$new_url_link.'" class="button small applyYellow" rel="nofollow"> ' . __( 'Application', 'fs' ) . ' </a>' . $loanName . $pixel_impression;
                $new_url_link = $url_link;
                $redirectLinkPopup = '<a href="' . $url_link.'" target="_blank" class="button small applyYellow" rel="nofollow"> ' . __( 'Application', 'fs' ) . ' </a>' . $pixel_impression;

				//$url_link = "window.open('".$url_link."')";
                //$loan_apr = "window.open('". user_trailingslashit( get_permalink( $result->ID ) ) ."redirect?b=t')";
                //$mobileButton = '<div class="mobile-button"><a href="' . $companyLinkReview . '" onclick="'.$url_link.'" class="button small applyYellow"  rel="nofollow"> ' . __( 'Application', 'fs' ) . ' </a>' . ( $generalSettings->field( 'show_sponsored_text' ) ? '<div class="sponsored">' . __( 'Sponsored', 'fs' ) . '</div>' : '' ) . '</div>';
                $loan_apr = user_trailingslashit( get_permalink( $result->ID ) ) ."redirect?b=t')";
                $mobileButton = '';
                $mobileButton = '<div class="mobile-button"><a href="' . $url_link . '" class="button small applyYellow"  rel="nofollow"> ' . __('Application', 'fs') . ' </a>' . ($generalSettings->field('show_sponsored_text') ? '<div class="sponsored">' . __('Sponsored', 'fs') . '</div>' : '' . $displayVisitCounter .'') . '</div>';

                if( !empty($result->rating ) ){ $rating = $result->rating; }

                $items[] = new Row(
					[
						// Logo
						new Data( 'logo', '<a class="company-logo" href="' . get_permalink( $result->ID ) . '">' . '<img title="' . $result->title . '" src="' . $pod->field( 'logo._src' ) . '"/>' . '</a> <span class="totalReviews">' . self::showStars( $result->ID ) . '<a href="' . get_permalink( $result->ID ) . '#read-reviews">' . __( 'Read', 'fs' ) . '&nbsp;' . do_shortcode('[total_rating id='.$result->ID.']') . ' ' . __( 'reviews.', 'fs' ) . '</a>'.$loanName.'</span>', [
							'class' => 'display-mobile vit company-listing ' . ( $result->favorite ? 'premium' : '' )
						] ),
						// Loan Amount
						new Data( 'loan_amount', '<span class="mobile-only">' . __( 'Loan amount:', 'fs' ) . '</span> ' . Util::moneyFormat( $filter->field( 'min' ) ) . ' - ' . Util::moneyFormat( $filter->field( 'max' ) ) . ' ' . __( 'usd', 'fs' ), [ 'class' => 'loan-amounts' ] ),
						// Loan Period
						new Data( 'loan_period', '<span class="mobile-only">' . ( $show_period ? __( 'Loan period:', 'fs' ) . '</span> ' . Util::getPeriod( $period_min ) . ' - ' . Util::getPeriod( $period_max ) : __( 'N/A', 'fs' ) ), [ 'class' => 'loan-periods' ] ),
						// Minimum Age
						new Data( 'minimum_age', '<span class="mobile-only">' . __( 'Minimum age:', 'fs' ) . '</span> ' . $result->minalder, [ 'class' => 'minimum-age' ] ),
						// Loan APR numberFormat
                       // Loan APR
                       //new Data( 'nominal_apr', '<span class="mobile-only">' . __( 'Lowest rate:', 'fs' ) . '</span> ' . Util::numberFormat( $filter3->field( 'min' ) ) . '%' . ( $filter3->field( 'max' ) != '0' ? ' - ' . Util::numberFormat( $filter3->field( 'max' ) ) . '%' : '' ) . '<div class="mobile-button"><a href="' . $companyLinkReview . '" onclick="'.$loan_apr.'" class="button small applyYellow" rel="nofollow" title="' . __( 'Borrow money from', 'fs' ) . ' ' . $result->title . '">' . __( 'Apply now', 'fs' ) . ' </a>' . ( $generalSettings->field( 'show_sponsored_text' ) ? '<div class="sponsored">' . __( 'Sponsored', 'fs' ) . '</div>' : '' ) . '</div>', [ 'class' => 'display-mobile interest-from main-column', 'style' => $remove_apr == 1 ? 'display:none;' : '' ] ),
                       new Data( 'nominal_apr', '<span class="mobile-only">' . __( 'Lowest rate:', 'fs' ) . '</span> ' . Util::numberFormat( $filter3->field( 'min' ) ) . '%' . ( $filter3->field( 'max' ) != '0' ? ' - ' . Util::numberFormat( $filter3->field( 'max' ) ) . '%' : '' ) . '<div class="mobile-button"><a href="'.$loan_apr.'" class="button small applyYellow" rel="nofollow" title="' . __( 'Borrow money from', 'fs' ) . ' ' . $result->title . '">' . __( 'Apply now', 'fs' ) . ' </a>' . ( $generalSettings->field( 'show_sponsored_text' ) ? '<div class="sponsored">' . __( 'Sponsored', 'fs' ) . '</div>' : '' . $displayVisitCounter .'' ) . '</div>', [ 'class' => 'display-mobile interest-from main-column', 'style' => $remove_apr == 1 ? 'display:none;' : '' ] ),
                       new Data( 'daily_interest', '<span class="mobile-only">' . __( 'Daily Interest test:', 'fs' ) . '</span> ' . Util::calcuateDailyRate( $pod->display( 'interest_rate' ) ) . '&nbsp;% </span>' . $mobileButton, [ 'mobileButtonSpecial' => $mobileButton, 'class' => 'loan-apr loan-mobile-view main-column ', 'style' => $remove_style ] ),
                       new Data( 'effective_interest_rate', '<span class="mobile-only">' . __( 'Effective Interest Rate:', 'fs' ) . '</span> ' . Util::numberFormat($loan_dataset->display( 'effective_interest_rate' ) ) . '% </span>', [ 'mobileButtonSpecial' => $mobileButton, 'class' => 'loan-apr loan-mobile-view main-column', 'style' => $remove_style ] ),
                       new Data( 'custom_interest_rate', '<span class="mobile-only">' . __( 'Most Likely Interest Rate:', 'fs' ) . '</span> ' . Util::numberFormat($loan_dataset->display( 'custom_interest_rate' ) ) . '% </span>', [ 'mobileButtonSpecial' => $mobileButton, 'class' => 'loan-apr loan-mobile-view main-column', 'style' => $remove_style ] ),
                       //

                        // See more
						new Data( 'see_more', $redirectLinkPopup . ( $generalSettings->field( 'show_sponsored_text' ) ? '<div class="sponsored">' . __( 'Sponsored', 'fs' ) . '</div>' : '' . $displayVisitCounter .'' ), [ 'class' => 'company-apply' ] ),
						/**/
					], ['class' => 'sort-item flex-columns']
				) ;
				if ( ! empty( $result->custom_representative_example ) ) {
					$items[] = new Row( [
						//tags
						new Data( false, '<div class="representative-example">' . $result->custom_representative_example . '</div>', [ 'class' => 'tag-example-column' ] )
					], [ 'class' => 'tag-example' ] );
				}
				$items[] = new Row( [
						//tags
						new Data( 'more_information', '<i class="toggle-details fa fa-plus" >' . __( 'More information', 'fs' ) . '</i>', [ 'class' => 'more-information' ] ),
					], [ 'class' => 'more-information-row' ] );

				$items[] = new Row( [
					// Loan Amount
					new Data( 'loan_amount', '<div><strong>' . __( 'Loan amount:', 'fs' ) . '</strong></div><div>' . Util::moneyFormat( $filter->field( 'min' ) ) . ' - ' . Util::moneyFormat( $filter->field( 'max' ) ) . ' ' . __( 'usd', 'fs' ) . '</div>', [ 'class' => 'loan-amounts hide-desktop' , 'before' => '<div class="left-details">'] ),
					// Loan Period
					new Data( 'loan_period', '<div><strong>' . ( $show_period ? __( 'Loan period:', 'fs' ) . '</strong></div><div>' . Util::getPeriod( $period_min ) . ' - ' . Util::getPeriod( $period_max ) : __( 'N/A', 'fs' )  ) .'</div>', [ 'class' => 'loan-periods hide-desktop' ] ),
					// Bad History
					new Data( 'accepts_bad_credit', '<div class="data-description"><strong>' . __( 'Accepts bad credit:', 'fs' ) . '</strong></div><div class="data-result"><p class="' . ( $result->bad_history ? 'true' : 'false' ) . '"></p></div>', [ 'class' => 'bad-credit' ] ),
					// Weekend Payout
					new Data( 'weekend_payout', '<div class="data-description"><strong>' . __( 'Weekend payout:', 'fs' ) . '</strong></div><div class="data-result"><p class="' . ( $result->helgutbetalning ? 'true' : 'false' ) . '"></p></div>', [ 'class' => 'weekend-payout' ] ),
                    // Visit Counter new Data( 'visit_counter', $displayVisitCounter, [ 'class' => '' ] ),
					// Minimum Age
					new Data( 'credit_check', '<div class="data-description"><strong>' . __( 'Credit check:', 'fs' ) . '</strong></div><div class="data-result">' . $pod->display( 'credit_check' ) .'</div>', [ 'class' => 'credit-check', 'style' => $remove_credit_check == 1 ? 'display:none;' : '' ] ),
					// Minimum Age
					new Data( 'minimum_age', '<div><strong>' . __( 'Minimum age:', 'fs' ) . '</strong></div><div>' . $result->minalder .'</div>', [ 'class' => 'minimum-age hide-desktop' , 'after' => '</div>'] ),
					new Data( 'review_breakdown', '<div class="overall_ratings">'.do_shortcode('[company_ratings id='.$result->ID.']').'</div>', [ 'colspan' => 2, 'class' => 'full-width', 'before' => '<div class="right-details">' ] ),
                    new Data( 'reviews', '<a href="' . get_permalink( $result->ID ) . '" class="button small blue-border">' . __( 'Details', 'fs' ) . '</a>', [ 'colspan' => 2, 'class' => 'full-width', 'after' => '</div>' ] ),
				], [ 'class' => 'details' ] );

				$table->addRow( new Item( $items, [
						'class' => 'company-listing ' .
					           ( $result->favorite ? 'premium' : '' ) . ( $result->ej_partner ? ' np' : '' )
				] ) );


				$pod->fetch();
			}
		} else {
			$table->addRow( new Row( [ new Data( false, __( 'No loan companies found in that search. Try using less filters.', 'fs' ), [ 'colspan' => 100 ] ) ] ) );
		}
		echo $table->render();
		$slider_settings = pods( 'slider_settings' );
            if ( $slider_settings->field( 'loan_notice' ) ) {
                echo <<<HTML
                <div class="loan-notice">{$slider_settings->field( 'loan_notice' )}</div>
HTML;
}
		?>
		<script>
			jQuery(function ($) {
				$(document).on('click', '.table_cont .fa', function () {
			        var detail = $(this).closest('.item-row').next('.details');
			        detail.toggleClass('expanded');
			        $(this).toggleClass('fa-plus', !detail.hasClass('expanded'));
			        $(this).toggleClass('fa-minus', detail.hasClass('expanded'));
			    });
			});
		</script>
		<?php
	}
}
