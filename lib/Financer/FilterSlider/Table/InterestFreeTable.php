<?php


namespace Financer\FilterSlider\Table;


use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Abstracts\Table;
use Financer\FilterSlider\Interfaces\TableInterface;
use Financer\FilterSlider\Surface\Data;
use Financer\FilterSlider\Surface\Item;
use Financer\FilterSlider\Surface\Row;
use Financer\FilterSlider\Surface\Surface;
use Financer\FilterSlider\Util;

/**
 * Class InterestFreeTable
 * @package Financer\FilterSlider\Table
 */
class InterestFreeTable extends Table implements TableInterface {

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
		$table = new Surface( [ 'class' => 'table table-striped' ] );

		$table->setHead( new Row( [
			new Data( 'company', __( 'Company', 'fs' ), [ 'title' => __( 'Company', 'fs' ) ] ),
			new Data( 'interest_free_amount', __( 'Interest Free Amount', 'fs' ), [ 'title' => __( 'Interest Free Amount', 'fs' ) ] ),
			new Data( 'interest_free_period', __( 'Interest Free Period', 'fs' ), [ 'title' => __( 'Interest Free Period', 'fs' ) ] ),
			new Data( 'min_age', __( 'Min. age', 'fs' ), [ 'class' => 'sliderm', 'title' => __( 'The minimum age you need to borrow money', 'fs' ) ] ),
			new Data( 'bad_credit_history', __( 'Bad credit history', 'fs' ), [ 'class' => 'sliderm', 'title' => __( 'The policy regarding a bad credit history', 'fs' ) ] ),
			__( 'Read more', 'fs' ),
		] ) );
		if ( count( $query ) > 0 ) {
			$generalSettings = pods( 'general_settings' );

			foreach ( $query as $pos => $result ) {
				$total_ratings = get_post_meta($result->ID, 'crfp-total-ratings',true);
				$url_link = user_trailingslashit( get_permalink( $result->ID ) ) ."redirect?b=t')";
				//$url_link = "window.open('". user_trailingslashit( get_permalink( $result->ID ) ) ."redirect?b=t')";

				$companyLinkReview = '#';
                if ($pod->field( 'company')) {
                    $companyLinkReview = get_permalink( $result->ID ). '#msform';
                }


				$table->addRow(
					new Item( [
						new Row(
							[
						// Logo
						new Data( 'logo', '<a class="company-logo" href="' . get_permalink( $result->ID ) . '">' . '<img title="' . $result->title . '" src="' . $pod->field( 'logo._src' ) . '"/>' . '</a> <span class="totalReviews">' . self::showStars( $result->ID ) . '<a href="' . get_permalink( $result->ID ) . '#read-reviews">' . __( 'Read', 'fs' ) . '&nbsp;' . $total_ratings . ' ' . __( 'reviews.', 'fs' ) . '</a></span>' . '<span class="sort-company" style="display: none;">' . preg_replace( '/\PL/u', '', lcfirst( $result->title ) ) . '</span><span class="sort-rating" style="display: none;">' . $result->rating . '</span>', [
							'class' => 'vit company-listing ' . ( $result->favorite ? 'premium' : '' )
						] ),
								// Loan Amount
								new Data( 'loan_amount', '<span class="mobile-only">' . __( 'Interest free amount:', 'fs' ) . '</span>' . Util::moneyFormat( $result->amount_range_minimum ) . ' - ' . Util::moneyFormat( $result->amount_range_maximum ) . ' ' . __( 'usd', 'fs' ) ),
								// Loan Period
								new Data( 'loan_period', '<span class="mobile-only">' . __( 'Interest free period:', 'fs' ) . '</span> ' . ( $result->period_range_minimum == $result->period_range_maximum ? Util::getPeriod( $result->period_range_minimum ) : Util::getPeriod( $result->period_range_minimum ) . ' - ' . Util::getPeriod( $result->period_range_maximum ) ) ),
								// Minimum Age
								new Data( 'minimum_age', '<span class="mobile-only">' . __( 'Minimum age:', 'fs' ) . '</span> ' . $result->minalder, [ 'class' => 'minimum-age' ] ),
								// Bad History
								new Data( 'bad_history', '<span class="mobile-only">' . __( 'Bad credit history', 'fs' ) . '</span><p class="' . ( $result->bad_history ? 'true' : 'false' ) . '"></p>', [ 'class' => 'sliderm' ] ),
								// See more
								//new Data( 'see_more', '<a href="'.$companyLinkReview.'" class="button small applyYellow" onclick="'.$url_link.'" rel="nofollow" title="' . __( 'Borrow money from', 'fs' ) . ' ' . $result->title . '">' . __( 'Apply now', 'fs' ) . '</a>' . ( $generalSettings->field( 'show_sponsored_text' ) ? '<div class="sponsored">' . __( 'Sponsored', 'fs' ) . '</div>' : '' ), [ 'class' => 'company-apply full-width' ] ),
                                new Data( 'see_more', '<a href="'.$url_link.'" target="_blank" class="button small applyYellow" rel="nofollow" title="' . __( 'Borrow money from', 'fs' ) . ' ' . $result->title . '">' . __( 'Apply now', 'fs' ) . '</a>' . ( $generalSettings->field( 'show_sponsored_text' ) ? '<div class="sponsored">' . __( 'Sponsored', 'fs' ) . '</div>' : '' ), [ 'class' => 'company-apply full-width' ] ),
                            ]
						)
					], [ 'class' => ( $pos % 2 ? 'even sort-item' : 'odd sort-item' ) . ( $result->ej_partner ? ' greyed' : '' ) . ( $result->favorite ? ' premium' : '' ) ] )
				);
				$pod->fetch();
			}
		} else {
			$table->addRow( new Row( [ __( 'No interest free companies found.', 'fs' ) ] ) );
		}
		echo $table->render();
	}
}
