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
 * Class NewLoansTable
 * @package Financer\FilterSlider\Table
 */
class NewLoansTable extends Table implements TableInterface {

	/**
	 * @param null|\Pods $pod
	 *
	 * @param Slider     $slider
	 *
	 * @internal param int $year
	 *
	 * @internal param Slider $slider
	 *
	 * @internal param null $postType
	 *
	 * @internal param array $query
	 */
	public static function build( \Pods $pod, Slider $slider = null ) {
		$query = $pod->data();
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
			new Data( 'loan_company', __( 'Loan company', 'fs' ), [ 'title' => __( 'Logo for company', 'fs' ), 'class' => 'vit' ] ),
			new Data( 'loan_amount', __( 'Loan amount', 'fs' ), [
				'title' => __( 'Max standard credit', 'fs' ),
				'class' => 'sliderm',
			] ),
			new Data( 'bad_history', __( 'Bad History', 'fs' ), [
				'title' => __( 'Lenders accepting bad credit history', 'fs' ),
				'class' => 'sliderm',
			] ),
			new Data( 'weekend_payout', __( 'Weekend payout', 'fs' ), [ 'title' => __( 'Weekend Payout', 'fs' ), 'class' => 'sliderm', 'style' => 'sliderm' ] ),
			new Data( 'min_age', __( 'Min. age', 'fs' ), [ 'class' => 'sliderm', 'title' => __( 'The minimum age you need to borrow money', 'fs' ) ] ),
			new Data( 'apply', __( 'Apply', 'fs' ), [ 'title' => __( 'Apply for a loan below', 'fs' ) ] ),
		] ) );
		if ( count( $query ) > 0 ) {
            foreach ( $query as $pos => $result ) {

				if ( ! empty( $result->specific_affiliate_url ) ) {
					$url_link = $result->specific_affiliate_url;
				} else {
					$url_link = user_trailingslashit( get_permalink( $result->ID ) . 'redirect' );
				}

				$func = function ( $meta_value, $post_id, $meta_key ) use ( $result ) {
					if ( $post_id == $result->ID ) {
						if ( 'crfp-average-rating' == $meta_key ) {
							$meta_value = ( intval( ( $result->rating * 2 ) + 0.5 ) / 2 );
						}
					}

					return $meta_value;
				};
				add_filter( 'get_post_metadata', $func, 10, 3 );
				$logo_array = get_post_meta( $result->pid, 'logo', true );

                $mobileButton = '';
                if (method_exists('Slider', 'getMinimalStatus')  && $slider->getMinimalStatus() != 'true') {
                    $mobileButton = '<div class="mobile-button"><a href="' . $url_link . '" class="button small applyYellow" target="_blank" rel="nofollow"> ' . __('Application', 'fs') . ' </a><a href="' . get_permalink($result->company_parent . ID) . '" class="applyNow">' . __('Read more', 'fs') . '</a>' . ($generalSettings->field('show_sponsored_text') ? '<div class="sponsored">' . __('Sponsored', 'fs') . '</div>' : '') . '</div>';
                }

				$items    = [];
				$items [] =
					new Row(
						[
							// Logo
							new Data( 'logo', '<a class="company-logo" href="' . get_permalink( $result->pid ) . '">' . '<img title="' . $result->title . '" src="' . $logo_array['guid'] . '" />' . '</a>' . '<span class="totalReviews">' .
                        self::showStars( $result->pid ) . '</span> ', [ 'class' => 'display-mobile vit company-listing' ] ),
							// Loan Amounts
							new Data( 'loan_amount', '<span class="mobile-only">' . __( 'Loan amount:', 'fs' ) . '</span>' . Util::moneyFormat( $result->amount_range_minimum ) . ' - ' . Util::moneyFormat( $result->amount_range_maximum ) . ' ' . __( 'usd', 'fs' )  , ['class'=> 'display-mobile loan-total'] ),
							new Data( 'bad_credit_history', '<span class="mobile-only">' . __( 'Bad credit history', 'fs' ) . '</span><p class="' . ( $result->bad_history ? 'true' : 'false' ) . '"></p>', [ 'class' => 'sliderm' ] ),
							new Data( 'weekend_payout', '<span class="mobile-only">' . __( 'Weekend Payout', 'fs' ) . '</span><p class="' . ( $result->weekend_payout ? 'true' : 'false' ) . '"></p>', [ 'class' => 'sliderm' ] ),
							new Data( 'minimum_age', '<span class="mobile-only">' . __( 'Minimum age:', 'fs' ) . '</span> ' . $result->minalder, [ 'class' => 'minimum-age' ] ),

							new Data( 'application', '<a href="' . $url_link . '" class="button small applyYellow" target="_blank" rel="nofollow"> ' . __( 'Application', 'fs' ) . ' </a><a href="' . get_permalink( $result->company_parent.ID ) . '" class="applyNow">' . __( 'Read more', 'fs' ) . '</a>' . ( $generalSettings->field( 'show_sponsored_text' ) ? '<div class="sponsored">' . __( 'Sponsored', 'fs' ) . '</div>' : '' ), [ 'class' => 'loan-apply' ] ),
						], [ 'data-id' => $result->ID, 'class' => 'flex-columns sort-item ' . ( $pos % 2 ? ' even' : ' odd' ) ]  );

					$items [] = new Row( [
						// Loan Amounts
						new Data( 'loan_amount', '<div><strong>' . __( 'Loan amount:', 'fs' ) . '</strong></div><div>' . Util::moneyFormat( $result->amount_range_minimum ) . ' - ' . Util::moneyFormat( $result->amount_range_maximum ) . ' ' . __( 'usd', 'fs' ) . '</div>' ),
						new Data( 'bad_credit_historys', '<div><strong>' . __( 'Bad credit historys', 'fs' ) . '</strong></div><div><p class="' . ( $result->bad_history ? 'true' : 'false' ) . '"></p></div>', [ 'class' => 'sliderm' ] ),
						new Data( 'weekend_payout', '<div><strong>' . __( 'Weekend Payout', 'fs' ) . '</strong></div><div><p class="' . ( $result->weekend_payout ? 'true' : 'false' ) . '"></p></div>', [ 'class' => 'sliderm' ] ),
						new Data( 'minimum_age', '<div><strong>' . __( 'Minimum age:', 'fs' ) . '</strong></div><div>' . $result->minalder . '</div>', [ 'class' => 'minimum-age' ] ),
					], [ 'class' => 'details' ] );

				$table->addRow( new Item( $items, [ 'class' => 'company-listing ' ] ) );

				remove_filter( 'wp_get_attachment_image_attributes', $func );
				remove_filter( 'get_post_metadata', $func );
				$pod->fetch();
			}
		} else {
			$table->addRow( new Row( [ new Data( false, __( 'No companies found', 'fs' ), [ 'colspan' => 100 ] ) ] ) );
		}
		echo $table->render();
	}
}
