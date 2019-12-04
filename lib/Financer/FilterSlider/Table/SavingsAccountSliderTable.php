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
 * Class SavingsAccountSliderTable
 * @package Financer\FilterSlider\Table
 */
class SavingsAccountSliderTable extends Table implements TableInterface {

	/**
	 * @param null|\Pods $pod
	 *
	 * @param Slider     $slider
	 *
	 * @return void
	 *
	 * @internal param array $query
	 */
	public static function build( \Pods $pod, Slider $slider = null ) {
		$query = $pod->data();
		if ( ! $query ) {
			$query = [];
		}
		$table = new Surface( [ 'class' => 'table table-striped savings_table' ] );
		$table->setHead(
			[
				new Data( 'bank', __( 'Bank', 'fs' ), [
						'title' => __( 'Name of the bank', 'fs' ),
						'class' => 'vit',
					]
				),
				new Data( 'saving_account', __( 'Saving Account', 'fs' ), [ 'title' => __( 'Name of the savings account', 'fs' ) ] ),
				new Data( 'interest_rate', __( 'Interest rate', 'fs' ), [ 'title' => __( 'The current interest rate for the savings account', 'fs' ) ] ),
				new Data( 'period', __( 'Period', 'fs' ), [
						'title' => __( 'The period of time you are saving', 'fs' ),
						'class' => 'sliderm',
					]
				),
				new Data( 'free_withdrawals', __( 'Free Withdrawals', 'fs' ), [
						'title' => __( 'The amount of free withdrawals you are allowed to during the saving period', 'fs' ),
						'class' => 'sliderm',
					]
				),
				new Data( 'gov_guarantee', __( 'Gov Guarantee', 'fs' ), [
						'title' => __( 'If the bank is backed up with a governmental guarantee or not', 'fs' ),
						'class' => 'sliderm',
					]
				),
				new Data( 'saving_profit', __( 'Saving Profit', 'fs' ), [ 'title' => __( 'The total amount of profit during the whole saving period', 'fs' ) ] ),
				new Data( 'review', __( 'Review', 'fs' ), [ 'title' => __( 'Read more about the bank', 'fs' ) ] ),
			]
		);
        if ( count( $query ) > 0 ) {
            $slider->showResultsTitle($slider, $query, $pod);
            $getPeriod = 3;
            if( !empty($slider->getPeriod()) ){
            	$getPeriod = $slider->getPeriod();
            }

            $period = $getPeriod;
			$period = $period < 1 ? Util::getPeriod( $period * 365 ) : $period . ' ' . __( 'Years', 'fs' );
			foreach ( $query as &$result ) {
				$result->logo = $pod->field( 'bank.logo._src' );
				$pod->fetch();
			}
			unset( $result );
			foreach ( $query as $pos => $result ) {
				if ( $result->floating_rate ) {
					$small = '(' . __( 'floating', 'fs' ) . ')';
				} else {
					$small = '(' . __( 'fixed', 'fs' ) . ')';

				}

				$url_link = user_trailingslashit( get_permalink( $result->bank_id ) . 'redirect' );

				$profit   = round( (int) $slider->getAmount() * pow( 1 + (float) $result->interest_rate / 100, (int) $getPeriod ) - (int) $slider->getAmount(), 2 );

				$items = [];

				if( $slider->getMinimalStatus() != 'true' ){
					$applyButton = '<a href="' . ( ! empty( $result->specific_affiliate_url_account ) ? add_query_arg( [
									'id' => $result->ID,
									'b'  => 's'
								], $url_link ) : add_query_arg( [ 'b' => 's' ], $url_link ) ) . '" target="_blank" class="button small applyYellow' . ( $result->ej_partner ? ' greyed' : '' ) . '" target="_blank" data-cname="'. $result->bank_title .'" data-cid="'. $result->bank_id .'" data-plink="'. get_permalink($result->bank_id) .'" rel="nofollow" title="' . __( 'Apply at', 'fs' ) . ' ' . $result->bank_title . '">' . __( 'Open account', 'fs' ) . '</a>';
				}else{
					$applyButton = '';
				}

				$items[] =
					new Row(
						[
							// Logo
							new Data( 'logo', '<a href="' . get_permalink( $result->bank_id ) . '" class="company-logo"><img title="' . $result->bank_title . '" src="' . $result->logo . '"/></a><span class="totalReviews">' . self::showStars( $result->bank_id ) . '<a href="' . get_permalink( $result->bank_id ) . '#reviews">' . __( 'Read', 'fs' ) . '&nbsp;' . do_shortcode('[total_rating id='.$result->bank_id.']')  . ' ' . __( 'reviews.', 'fs' ) . '</a></span>', [ 'class' => 'sliderm vit company-listing' . ( $result->favorite ? '  premium' : '' ) . ( $result->ej_partner ? ' np' : '' ) ] ),
							// Title
							new Data( 'saving_account', '<span class="mobile-only">' . __( 'Saving account:', 'fs' ) . '</span> ' . $result->title, [ 'class' => 'product-name' ] ),

							// Interest Rate
							new Data( 'saving_interest_rate', '<span class="mobile-only">' . __( 'Saving interest rate:', 'fs' ) . '</span> ' . ( ( ! empty( $result->interest_rate ) || $result->interest_rate == 0 ) ? $result->interest_rate . '%' : '&nbsp;' ) . ' ' . $small, [ 'class' => 'saving-interest' ] ),
							// Period
							new Data( 'saving_time', '<span class="mobile-only">' . __( 'Saving time:', 'fs' ) . '</span> ' . $period, [ 'class' => 'sliderm' ] ),
							// Free Widthdrawls
							new Data( 'free_withdrawals', '<span class="mobile-only">' . __( 'Free withdrawals:', 'fs' ) . '</span> ' . ( $result->free_withdrawals == - 1 ? __( 'N/A', 'fs' ) : $result->free_withdrawals ), [ 'class' => 'sliderm' ] ),
							// Governmental Guarantee
							new Data( 'governmental_guarantee', '<span class="mobile-only">' . __( 'Governmental guarantee:', 'fs' ) . '</span><p class="' . ( $result->governmental_guarantee ? 'true' : 'false' ) . '"></p>', [ 'class' => 'sliderm' ] ),
							// Profit
							new Data( 'saving_profit', '<span class="mobile-only">' . __( 'Saving profit:', 'fs' ) . '</span> ' . $profit . ' ' . __( 'usd', 'fs' ) .'<span class="hide-desktop">'.$applyButton.'</span>', [ 'class' => 'saving-profit main-column hide-only' ] ),

							// Interest Rate
							new Data( 'saving_interest_rate', '<span class="mobile-only">' . __( 'Saving interest rate:', 'fs' ) . '</span> ' . ( ( ! empty( $result->interest_rate ) || $result->interest_rate == 0 ) ? $result->interest_rate . '%' : '&nbsp;' ) . ' ' . $small.'<span class="hide-desktop">'.$applyButton.'</span>' , [ 'class' => 'saving-interest main-column hide-desktop' ] ),

							// Apply/Review
							new Data ( 'application',$applyButton, [ 'class' => 'company-apply', 'minimal' => $slider->getMinimalStatus() ] ),

				], [ 'class' => 'flex-columns sort-item' ]  );

                if ( $slider->getMinimalStatus() != 'true') {
                    $items[] = new Row([
                        //tags
                        new Data(false, '<a href="' . get_permalink($result->bank_id) . '" title="' . __('Read more', 'fs') . '" class="toggle-details fa fa-plus" >' . __('Read more', 'fs') . '</a>', ['class' => 'more-information']),
                    ], ['class' => 'more-information-row hide-only']);
                    $items[] = new Row( [
                        //tags
                        new Data( 'more_information', '<i class="toggle-details fa fa-plus" >' . __( 'More information', 'fs' ) . '</i>', [ 'class' => 'more-information' ] ),
                    ], [ 'class' => 'more-information-row mobile-only hide-desktop' ] );
                }

				$items[] = new Row( [
					// Title
					new Data( 'saving_account', '<div class="data-description"><span class="mobile-only">' . __( 'Saving account:', 'fs' ) . '</span> </div><div class="data-result">' . $result->title  . '</div>', [ 'colspan' => 2 , 'class' => 'product-name' ] ),
					// Interest Rate
					new Data( 'saving_interest_rate', '<div class="data-description"><span class="mobile-only">' . __( 'Saving interest rate:', 'fs' ) . '</span></div><div class="data-result"> ' . ( ( ! empty( $result->interest_rate ) || $result->interest_rate == 0 ) ? $result->interest_rate . '%' : '&nbsp;' ) . ' ' . $small  . '</div>', [ 'colspan' => 2 , 'class' => 'saving-interest' ] ),
					// Period
					new Data( 'saving_time', '<div class="data-description"><span class="mobile-only">' . __( 'Saving time:', 'fs' ) . '</span> </div><div class="data-result">' . $period  . '</div>', [ 'colspan' => 2  , 'class' => 'sliderm' ] ),
					// Free Widthdrawls
					new Data( 'free_withdrawals', '<div class="data-description"><span class="mobile-only">' . __( 'Free withdrawals:', 'fs' ) . '</span> </div><div class="data-result">' . ( $result->free_withdrawals == - 1 ? __( 'N/A', 'fs' ) : $result->free_withdrawals )  . '</div>', [ 'colspan' => 2 , 'class' => 'sliderm' ] ),
					// Governmental Guarantee
					new Data( 'governmental_guarantee', '<div class="data-description"><span class="mobile-only">' . __( 'Governmental guarantee:', 'fs' ) . '</span></div><div class="data-result"><p class="' . ( $result->governmental_guarantee ? 'true' : 'false' ) . '"></p>'  . '</div>', [ 'colspan' => 2 , 'class' => 'sliderm' ] ),
				], [ 'class' => 'details' ] );

				$table->addRow( new Item( $items, [ 'data-id' => $result->ID, 'class' => ( ( $pos % 2 ) ? 'even sort-item' : 'odd sort-item' ) . ( $result->favorite ? '  premium' : '' ) ] ) );

			}
		} else {
			$table->addRow( new Item( [ new Row( [ new Data( false, __( 'No savings accounts found in that search. Try using less filters.', 'fs' ), [ 'colspan' => 100 ] ) ] ) ] ) );
		}
		echo $table->render();
	}
}
