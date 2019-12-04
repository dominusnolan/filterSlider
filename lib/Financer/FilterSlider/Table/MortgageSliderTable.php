<?php

namespace Financer\FilterSlider\Table;


use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Abstracts\Table;
use Financer\FilterSlider\Interfaces\TableInterface;
use Financer\FilterSlider\SortUtil;
use Financer\FilterSlider\Surface\Item;
use Financer\FilterSlider\Util;
use Financer\FilterSlider\Surface\Data;
use Financer\FilterSlider\Surface\Row;
use Financer\FilterSlider\Surface\Surface;

/**
 * Class MortgageSliderTable
 * @package Financer\FilterSlider\Table
 */
class MortgageSliderTable extends Table implements TableInterface {
	/**
	 * @param null|\Pods $pod
	 *
	 * @param Slider     $slider
	 *
	 * @return void
	 * @internal param null $postType
	 *
	 * @internal param array $query
	 */
	public static function build( \Pods $pod, Slider $slider = null ) {
		$query = $pod->data();
		if ( ! $query ) {
			$query = [];
		}

		$table = new Surface( [ 'class' => 'table table-striped mortgage_table' ] );
		$table->setHead(
			new Row(
				[
					new Data( 'bank', __( 'Bank', 'fs' ), [ 'title' => __( 'Name of the bank', 'fs' ) ] ),
					new Data( 'monthly_interest', __( 'Monthly interest', 'fs' ), [ 'title' => __( 'An estimation of the monthly fee from the interest rate, not including installments', 'fs' ) ] ),
					new Data( 'other_fees', __( 'Other fees', 'fs' ), [ 'title' => __( 'The total fees, like start and maintenance fees of the mortgage', 'fs' ) ] ),
					new Data( 'mortgage_interest', __( 'Mortgage interest', 'fs' ) . ' ' . Util::getPeriod( $slider->getPeriod() * 365 ), [ 'title' => __( 'The period of the fixed interest rate', 'fs' ) ] ),
					new Data( 'more_information', __( 'More info', 'fs' ), [ 'title' => __( 'Find more information about the bank', 'fs' ) ] ),
				]
			)
		);

		if ( count( $query ) > 0 ) {
			$slider->showResultsTitle($slider, $query, $pod);
			foreach ( $query as $pos => $result ) {
				$pod->fetch( $result->ID );

				$mortgagePeriods = [
					'3_mir',
					'1_yir',
					'2_yir',
					'3_yir',
					'4_yir',
					'5_yir',
					'7_yir',
					'10_yir',
				];

				$interest = 0;
				foreach ($mortgagePeriods as $period) {
					if (isset($result->{$period})) {
						$interest = ! empty( $result->{$period} ) ? $result->{$period} : 0;
						break;
					}
				}

				$items = [];
				$rows = [];

				if ($pod->singleCompany == false) {
					// Logo
					$rows[] = new Data( 'logo', '<a href="' . get_permalink( $result->bank_id ) . '" class="company-logo"><img title="' . $result->bank_title . '" src="' . $pod->field( 'bank.logo._src' ) . '"/></a><span class="totalReviews">' . self::showStars( $result->bank_id ) . '<a href="' . get_permalink( $result->bank_id ) . '#reviews">' . __( 'Read', 'fs' ) . '&nbsp;' . do_shortcode('[total_rating id='.$result->bank_id.']') . ' ' . __( 'reviews.', 'fs' ) . '</a></span>', [ 'class' => 'vit company-listing ' . ( $result->ej_partner ? ' np' : '' ) ] );

				}


				$companyLinkReview = '#';
				if ($pod->field( 'company')) {
					$companyLinkReview = "window.location = '".get_permalink( $result->bank_id )."#msform'";
				}

				if( $slider->getMinimalStatus() != 'true' ){
					$applyButton = '<a href="' . user_trailingslashit( get_permalink( $result->bank_id ) . 'redirect?pid='. $result->ID ) . '" rel="nofollow" target="_blank" class="button small applyYellow" title="' . __( 'Apply at', 'fs' ) . ' ' . $result->bank_title . '"> ' . __( 'Application', 'fs' ) . ' </a>';
				}else{
					$applyButton = '';
				}

				if ($pod->singleCompany == false) {
				// Montly Cost
				$rows[] = - 1 == $interest ? __( 'N/A', 'fs' ) : new Data( 'estimated_monthly_cost', '<span class="mobile-only">' . __( 'Estimated monthly cost', 'fs' ) . '</span> ' . Util::moneyFormat( round( ( $slider->getAmount() * ( (float) $interest / 100 ) ) / 12, 2 ) ) . ' ' . __( 'usd', 'fs' ) );
				}
				// Total Fees
				$rows[] = new Data( 'mortgage_fee', '<span class="mobile-only">' . __( 'Mortgage fee', 'fs' ) . '</span> ' . ( $result->total_fees == - 1 ? __( 'N/A', 'fs' ) : Util::moneyFormat( $result->total_fees ) . ' ' . __( 'usd', 'fs' ) ) );
				// Interest
				$rows[] = new Data( 'mortgage_rate', '<span class="mobile-only">' . __( 'Mortgage rate:', 'fs' ) . '</span> ' . ( _isset( $interest ) ? ( - 1 == $interest ? __( 'N/A', 'fs' ) : Util::numberFormat( $interest ) . '%' ) : '&nbsp;' ) .'<span class="hide-desktop">'. $applyButton.'</span>', [ 'class' => 'mortgage-interest main-column' ] );
				// Apply/Review
				$rows[] = new Data ('application', $applyButton , [ 'class' => 'company-apply', 'minimal' => $slider->getMinimalStatus() ] );

				$items[] = new Row( $rows, [ 'class' => 'flex-columns sort-item' ]   );

				if ($pod->singleCompany == false && ($slider->getMinimalStatus() != 'true')) {
					$items[] = new Row([
						//tags
						new Data(false, '<a href="' . get_permalink($result->bank_id) . '" class="toggle-details fa fa-plus" >' . __('Read more', 'fs') . '</a>', ['class' => 'more-information']),
					], ['class' => 'more-information-row hide-only']);
					$items[] = new Row( [
						//tags
						new Data( 'more_information', '<i class="toggle-details fa fa-plus" >' . __( 'More information', 'fs' ) . '</i>', [ 'class' => 'more-information' ] ),
					], [ 'class' => 'more-information-row mobile-only hide-desktop' ] );
				}

                $items[] = new Row( [
					// Title
					new Data( 'estimated_monthly_cost', '<div class="data-description"><span class="mobile-only">' . __( 'Estimated monthly cost', 'fs' ) . '</span></div><div class="data-result">' . Util::moneyFormat( round( ( $slider->getAmount() * ( (float) $interest / 100 ) ) / 12, 2 ) ) . ' ' . __( 'usd', 'fs' ) . '</div>', [ 'colspan' => 2 , 'class' => 'product-name' ] ),
					// Minimum Save Amount
					new Data( 'mortgage_fee', '<div class="data-description"><span class="mobile-only">' . __( 'Mortgage fee', 'fs' ) . '</span></div><div class="data-result">' . ( $result->total_fees == -1 ? __( 'N/A', 'fs' ) : Util::moneyFormat( $result->total_fees ) . ' ' . __( 'usd', 'fs' ) ). '</div>', [ 'colspan' => 2 ,'class' => 'sliderm' ] ),
				], [ 'class' => 'details' ] );


				$table->addRow( new Item( $items, [ 'data-id' => $result->ID, 'class' => ( ( $pos % 2 ) ? 'even sort-item' : 'odd sort-item' ) . ( $result->favorite ? ' premium' : '' ) . ( $result->ej_partner ? ' greyed' : '' ) ] ) );
			}
			/** @noinspection PhpUndefinedMethodInspection */

		} else {
			$table->addRow( new Row( [ new Data( false, __( 'No mortgages found in that search.', 'fs' ), [ 'colspan' => 100 ] ) ] ) );
		}
		echo $table->render();
		if ( count( $query ) > 0 ) :
			?>

		<?php
		endif;
	}
}
