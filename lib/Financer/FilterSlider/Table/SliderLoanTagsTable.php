<?php

namespace Financer\FilterSlider\Table;


use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Abstracts\Table;
use Financer\FilterSlider\Interfaces\TableInterface;
use Financer\FilterSlider\Surface\Item;
use Financer\FilterSlider\Surface\Row;
use Financer\FilterSlider\Util;
use Financer\FilterSlider\RepresentativeExampleUtil;
use Financer\FilterSlider\Surface\Data;
use Financer\FilterSlider\Surface\Surface;

/**
 * Class SliderLoanTagsTable
 * @package Financer\FilterSlider\Table
 */
class SliderLoanTagsTable extends Table implements TableInterface {
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

		$tags = [];

		$terms = get_terms( array(
		    'taxonomy' => 'loan_tags',
		    'hide_empty' => false,
		    'orderby' => 'count',
		    'order' => 'DESC',
		    'number' => 3
		) );

	 	if($terms){
			foreach ( $terms as $term ) {
				array_push( $tags, $term->term_id );
			}
		}

		$tags_array = [];
		foreach ( $query as $pos => $result ) {

			$loan_tag_terms = get_the_terms($result->pid, 'loan_tags');
			if( $loan_tag_terms ){
				foreach ($loan_tag_terms as $cat) {
					if( in_array( $cat->term_id, $tags ) ){
						$tags_array[$cat->term_id][] = $result;
						//array_push($tags_array[$cat->term_id],$result);
					}

				}
			}
		}

		if ( ! $query ) {
			$query = [];
		}

		$generalSettings = pods( 'general_settings' );
		$sliderSetting   = pods( 'slider_settings' );
		$remove_apr      = $sliderSetting->field( 'remove_apr' );
		$remove_style    = "";
		if ( $remove_apr == 1 ) {
			$remove_style = "display:none;";
		}

		$table = new Surface( [ 'class' => 'table table-striped' ] );

		$table->setHead( new Row( [
			new Data( 'loan_company', __( 'Loan company', 'fs' ), [ 'title' => __( 'The lender with the loan offer', 'fs' ), 'class' => 'vit' ] ),
			new Data( 'loan_amount', __( 'Loan amount', 'fs' ), [
				'title' => __( 'The searched loan amount. Lenders may lend other amounts', 'fs' ),
				'class' => 'sliderm',
			] ),
			new Data( 'loan_period', __( 'Loan period', 'fs' ), [
				'title' => __( 'The loan period you are searching for. The lender may lend in shorter or longer periods', 'fs' ),
				'class' => 'sliderm',
			] ),
			new Data( 'apr', __( 'APR', 'fs' ), [ 'title' => __( 'The annual interest rate (not effective rate)', 'fs' ), 'class' => 'sliderm', 'style' => $remove_style ] ),
			new Data( 'monthly_payback', __( 'Monthly Payback *', 'fs' ), [ 'title' => __( 'Estimated monthly payback, based upon lowest rates', 'fs' ), 'class' => 'sliderm' ] ),
			new Data( 'total_cost', __( 'Total Cost *', 'fs' ), [
				'title' => __( 'This is the estimated lowest total cost of the loan, based upon lowest rates. Be aware that the total cost may vary and be set individually sometimes', 'fs' ),
				'class' => 'sliderm sorted',
			] ),
			new Data( 'loan', __( 'Loan', 'fs' ), [ 'title' => __( 'Apply for a loan below', 'fs' ), 'class' => 'sliderd' ] ),
			new Data( 'apply', __( 'Apply', 'fs' ), [ 'title' => __( 'Apply for a loan below', 'fs' ) ] ),
		] ) );
		if ( count( $tags_array ) > 0 ) {
			foreach ( $tags_array as $key => $loan_data ) {
				$tag_term =  get_term_by('term_taxonomy_id', $key, 'loan-tags');
				$items_header   = [];
				$items_header[] = new Row( [
						//tags
						new Data( false, '<h3>'.$tag_term->name.'</h3>', [ 'class' => 'items-header-title' ] ),
						new Data( false, '<a href="'.home_url().'/results/?slug='.$tag_term->slug.'" class="button light-orange small">' . __( 'View all', 'fs' ) . '</a>', [ 'class' => 'items-header-button' ] ),
					] );
				$table->addRow( new Item( $items_header, [
						'class' => 'company-listing-header '
				] ) );

				foreach( $loan_data as $pos => $result){
					$date = new \DateTime();
					$date->add( new \DateInterval( 'P' . $slider->getPeriod() . 'D' ) );
					if ( 'new' == $result->loan_restriction ) {
						$restriction = '<span class="newSpan">' . __( 'New', 'fs' ) . '</span>';
					} else if ( 'old' == $result->loan_restriction ) {
						$restriction = '<span class="oldSpan">' . __( 'Old', 'fs' ) . '</span>';
					} else {
						$restriction = '<span class="allSpan">' . __( 'All', 'fs' ) . '</span>';
					}
					$url_link = user_trailingslashit( get_permalink( $result->ID ) . 'redirect' ) . "?b=s";

					if ( ! empty( $result->specific_affiliate_url ) ) {
						$url_link = user_trailingslashit( get_permalink( $result->ID ) ) . 'redirect/?id=' . $result->pid . "&b=s";
					}


					/*loan tags*/
					$term_list        = wp_get_post_terms( $result->pid, 'loan_tags', [ "fields" => "all" ] );
					$output_loan_tags = '';
					$output_loan_slug = '';

					$sliderSetting = pods( 'slider_settings' );
					$display_tags  = $sliderSetting->field( 'display_tags_in_slider_results' );
					if ( $display_tags == 0 ) {
						foreach ( $term_list as $loan_tag ) {
							$output_loan_tags .= '<div class="data-tag ' . $loan_tag->slug . '" style="background:' . $loan_tag->description . ';">' . $loan_tag->name . '</div> ';
							$output_loan_slug .= ' ' . $loan_tag->slug;
						}
					}

					/*representative example
					* if loan dataset representative example is not empty
					* display loan dataset rep examples
					* else if slider settings rep example is not empty
					* show slider settings rep example
					* else display nothing
					*/
					$repexample = "";

					if ( ! empty( $sliderSetting->field( 'representative_example' ) ) ) {

						$repexample = do_shortcode( RepresentativeExampleUtil::RepresentativeExample( $sliderSetting->field( 'representative_example' ), $result, $slider ) );
					}
					$podInner = pods( 'company_single', $result->ID );

					if ( $result->special_text ) {
						$span = '<span class="logo-text">' . $result->special_text . '</span>';
					} else {
						$span = '';
					}

					$mobileButton = '';
					if (method_exists('Slider', 'getMinimalStatus') && $slider->getMinimalStatus() != 'true') {
						$mobileButton = '<div class="mobile-button"><a href="' . $url_link . '" class="button small applyYellow" target="_blank" rel="nofollow"> ' . __( 'Application', 'fs' ) . ' </a>' . ( $generalSettings->field( 'show_sponsored_text' ) ? '<div class="sponsored">' . __( 'Sponsored', 'fs' ) . '</div>' : '' ) . '</div>';
                    }

					$items   = [];
					$items[] = new Row( [
						// Logo
						new Data( 'logo', $span . '<a class="company-logo" href="' . get_permalink( $result->ID ) . '">' . '<img title="' . $result->title . '" src="' . $podInner->field( 'logo._src' ) . '" />' . '</a>' . '<span class="totalReviews">' .
						          self::showStars( $result->ID ) . ' <a href="' . get_permalink( $result->ID ) . '#read-reviews">' . __( 'Read', 'fs' ) . '&nbsp;' . do_shortcode('[total_rating id='.$result->ID.']') . ' ' . __( 'reviews.', 'fs' ) . '</a></span>' . '<span class="sort-company" style="display: none;">' . preg_replace( '/\PL/u', '', lcfirst( $result->title ) ) . '</span><span class="sort-rating" style="display: none;">' . $result->rating . '</span>', [ 'class' => 'vit company-listing' ] ),
						// Loan Amounts
						new Data( 'loan_amount', '<span class="mobile-only">' . __( 'Loan amount:', 'fs' ) . '</span> ' . Util::moneyFormat( $slider->getAmount() ) . ' ' . __( 'usd', 'fs' ) . '<br>' . ' ' . $restriction, [ 'class' => 'loan-amount' ] ),
						// Loan Period
						new Data( 'loan_period', '<span class="mobile-only">' . __( 'Loan period:', 'fs' ) . '</span> ' . Util::getPeriod( $slider->getPeriod() ), [ 'class' => 'loan-period' ] ),
						// Loan APR
						new Data( 'nominal_apr', '<span class="mobile-only">' . __( 'Nominal APR:', 'fs' ) . '</span> ' . ( $result->personal_loans ? __( 'from', 'fs' ) . ' ' : '' ) . '<span class="sort-interest">' . $result->interest_rate . '</span>&nbsp;% ' . ( $result->highest_annual_interest_rate != 0 ? __( 'to', 'fs' ) . ' ' . Util::numberFormat( $result->highest_annual_interest_rate ) . ' %' : '' ), [ 'class' => 'loan-apr', 'style' => $remove_style ] ),
						// Loan Fees
						new Data( 'monthly_payback', ( $sliderSetting->field( 'remove_apr' ) == 1 ? '<span class="sort-interest"></span>' : '' ) . '<span class="mobile-only">' . __( 'Monthly payback:', 'fs' ) . '</span> ' . Util::moneyFormat( $result->total_monthly_payback ) . ' ' . __( 'usd', 'fs' ), [ 'class' => 'loan-monthly-payback' ] ),
						// Loan Total Cost
						new Data( 'total_cost_from', '<span class="mobile-only">' . __( 'Total cost from:', 'fs' ) . '</span>' . Util::moneyFormat( $result->total_cost ) . ' ' . __( 'usd', 'fs' ) . $mobileButton, [ 'class' => 'loan-total main-column' . ( 0 == $result->total_cost ? ' green' : '' ) ] ),
						// Loan Apply
						new Data( 'application', '<a href="' . $url_link . '" class="button small applyYellow" target="_blank" rel="nofollow"> ' . __( 'Application', 'fs' ) . ' </a>' . ( $generalSettings->field( 'show_sponsored_text' ) ? '<div class="sponsored">' . __( 'Sponsored', 'fs' ) . '</div>' : '' ), [ 'class' => 'company-apply' ] ),
						/**/
					], [ 'data-id' => $result->ID, 'class' => 'flex-columns sort-item' . ( $pos % 2 ? ' even' : ' odd' ) . ( $result->ej_partner ? ' greyed' : '' ) . ( $result->favorite ? ' premium' : '' ) . ( $repexample || $output_loan_tags ? ' tag-rep-more' : '' ) ] );
					if(!empty($output_loan_tags) ||  !empty($repexample)) {
						$items[] = new Row( [
							//tag
							new Data( false, ( $repexample ? '<div class="representative-example">' . $repexample . '</div>' : '' ), [ 'class' => 'tag-example-column' ] ),
						], [ 'class' => 'tag-example' ] );
					}
					$items[] = new Row( [
						//tags
						new Data( 'more_information', '<i class="toggle-details fa fa-plus" >' . __( 'More information', 'fs' ) . '</i>', [ 'class' => 'more-information' ] ),
					], [ 'class' => 'more-information-row' ] );

					$items[] = new Row( [
						new Data( 'loan_amount', '<div><strong>' . __( 'Loan amount:', 'fs' ) . '</strong></div><div>' . Util::moneyFormat( $slider->getAmount() ) . ' ' . __( 'usd', 'fs' ) . ' ' . $restriction . '</div>', [ 'colspan' => 2 , 'class' => 'mobile-only' ] ),
						new Data( 'loan_period', '<div><strong>' . __( 'Loan period:', 'fs' ) . '</strong></div><div>' . Util::getPeriod( $slider->getPeriod() ) . '</div>', [ 'colspan' => 2 , 'class' => 'mobile-only'] ),
						new Data( 'nominal_apr', '<div><strong>' . __( 'Nominal APR:', 'fs' ) . '</strong></div><div>' . ( $result->personal_loans ? __( 'from', 'fs' ) . ' ' : '' ) . '<span class="sort-interest">' . $result->interest_rate . '</span>&nbsp;% ' . ( $result->highest_annual_interest_rate != 0 ? __( 'to', 'fs' ) . ' ' . Util::numberFormat( $result->highest_annual_interest_rate ) . ' %' : '' ) . '</div>', [ 'colspan' => 2 ,'class' => 'mobile-only'] ),
						new Data( 'monthly_payback', ( $sliderSetting->field( 'remove_apr' ) == 1 ? '<span class="sort-interest"></span>' : '' ) . '<div><strong>' . __( 'Monthly payback:', 'fs' ) . '</strong></div><div>' . Util::moneyFormat( $result->total_monthly_payback ) . ' ' . __( 'usd', 'fs' ) .'</div>', [ 'colspan' => 2 , 'class' => 'mobile-only']),
						new Data( 'borrow_up_to', '<div><strong>' . __( 'Borrow up to:', 'fs' ) . '</strong></div><div>' . Util::moneyFormat( $result->amount_range_maximum ) . '&nbsp;' . __( 'usd', 'fs' ) . '</div>', [ 'colspan' => 2 ] ),
						new Data( 'estimated_pay_back', '<div><strong>' . __( 'Estimated pay back:', 'fs' ) . '</strong>&nbsp;</div><div>' . $date->format( 'd-m-Y' ) . '</div>', [ 'colspan' => 2 ] ),
						new Data( 'bad_credit_history', '<div><strong>' . __( 'Bad credit history:', 'fs' ) . '</strong>&nbsp;</div><div><p class="' . ( $result->bad_history ? 'true' : 'false' ) . '" ></p></div>', [ 'colspan' => 2 ] ),
						new Data( 'credit_score', '<div><strong>' . __( 'Credit Score:', 'fs' ) . '</strong>&nbsp;</div><div>' . $podInner->display( 'credit_Score' ) . '</div>', [ 'colspan' => 2 ] ),
						new Data( 'weekend_payout', '<div><strong>' . __( 'Weekend payout:', 'fs' ) . '</strong>&nbsp;</div><div><p class="' . ( $result->weekend_payout ? 'true' : 'false' ) . '"></p></div>', [ 'colspan' => 2 ] ),
						new Data( 'age_minalder', '<div><strong>' . __( 'Age:', 'fs' ) . '</strong>&nbsp;</div><div>' . $result->minalder . '</div>', [ 'colspan' => 2 ] ),
						new Data( 'credit_check', '<div><strong>' . __( 'Credit check:', 'fs' ) . '</strong>&nbsp;</div><div>' . $podInner->display( 'credit_check' ) . '</div>', [ 'colspan' => 2 ] ),
						new Data( 'minimum_income', '<div><strong>' . __( 'Minimum Income:', 'fs' ) . '</strong>&nbsp;</div><div>' . ( $result->minimum_inkomst ? $result->minimum_inkomst. '&nbsp;'.__( 'usd', 'fs' ) : 'n/a' ) . '</div>', [ 'colspan' => 2 ] ),
						new Data( 'banks', '<div><strong>' . __( 'Banks:', 'fs' ) . '</strong>&nbsp;</div><div>' . ( $podInner->display( 'banks' ) ? $podInner->display( 'banks', [ 'serial_params' => [ 'and' => __( ', ', 'fs' ) ] ] ) : 'n/a' ) . '</div>', [ 'colspan' => 2 ] ),
						new Data( 'data_report', '<i class="mega-icon-eraser report"><a href="#" class="report-data" title="' . __( 'Wrong data? Report this item', 'fs' ) . '">' . __( 'Report incorrect data', 'fs' ) . '</a></i><a href="' . get_permalink( $result->ID ) . '" class="blue-border">' . __( 'Details', 'fs' ) . '</a>', [ 'colspan' => 2, 'class' => 'full-width' ] ),
					], [ 'class' => 'details' ] );

					$table->addRow( new Item( $items, [
						'class' => 'company-listing ' .
					           ( $result->favorite ? 'premium' : '' ) . ( $result->ej_partner ? ' np' : '' )
				] ) );

				$pod->fetch();
				}

			}

			echo $table->render();
		} else {

			echo '<div class="msg info slider-msg"> ' . __( 'No loan companies found in that search. Try using less filters, or consider the recommended lenders below based on best ratings.', 'fs' ) . '</div>';

			echo do_shortcode( '[top_rated_companies title="Top Rated Companies" limit="5" type="loan_company"]' );
		}


	}
}

?>
