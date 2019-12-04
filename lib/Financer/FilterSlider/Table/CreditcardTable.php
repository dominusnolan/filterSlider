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
 * Class CreditcardTable
 * @package Financer\FilterSlider\Table
 */
class CreditcardTable extends Table implements TableInterface {

	/**
	 * @param null|\Pods  $pod
	 *
	 * @param Slider|null $slider
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
		$generalSettings = pods( 'general_settings' );
        $sliderSetting   = pods( 'slider_settings' );
		
		$table = new Surface( [ 'class' => 'table table-striped' ] );
		$table->setHead(
			new Row(
				[
					new Data( 'credit_card', __( 'Credit card', 'fs' ), [ 'title' => __( 'Logo for creditcard', 'fs' ), 'class' => 'vit', ] ),
					new Data( 'max_credit', __( 'Max credit', 'fs' ), [
							'title' => __( 'Max standard credit', 'fs' ),
							'class' => 'sliderm',
						]
					),
					new Data( 'interest_free_days', __( 'Interest free days', 'fs' ), [
							'title' => __( 'How many days without interest', 'fs' ),
							'class' => 'sliderm',
						]
					),

					new Data( 'fees', __( 'Fees', 'fs' ), [
							'title' => __( 'Fees and other costs', 'fs' ),
							'class' => 'sliderm',
						]
					),
					new Data( 'travel_insurance', __( 'Travel insurance', 'fs' ), [
							'title' => __( 'Is travel insurance included in this card?', 'fs' ),
							'class' => 'sliderm',
						]
					),
					new Data( 'withdrawal', __( 'Withdrawal fee', 'fs' ), [
							'title' => __( 'Cost of using the card in an ATM', 'fs' ),
							'class' => 'sliderm',
						]
					),
					new Data( 'card_type', __( 'Card type', 'fs' ), [
							'title' => __( 'Card type', 'fs' ),
							'class' => 'sliderm',
						]
					),
					new Data( 'interest', __( 'Interest', 'fs' ), [
							'title' => __( 'Total interest', 'fs' ),
						]
					),
					new Data( 'apply', __( 'Apply', 'fs' ), [ 'title' => __( 'Apply for the card below', 'fs' ) ] ),
				]
			)
		);

        if ( count( $query ) > 0 ) {
            $slider->showResultsTitle($slider, $query, $pod);
            foreach ( $query as $pos => $result ) {
				$func = function ( array $attr ) use ( $result ) {
					$attr['alt'] = get_the_title( $result->ID );

					return $attr;
				};
				add_filter( 'wp_get_attachment_image_attributes', $func );
				$pod->fetch( $result->ID );
				$days     = (float) $result->period;

                $url_link = user_trailingslashit( get_permalink( $result->ID ) . 'redirect' );
                //$url_link = "window.open('".user_trailingslashit( get_permalink( $result->ID ) . 'redirect' )."')";
                $companyLinkReview = '#';
                if ($pod->field( 'company')) {
                    $companyLinkReview = get_permalink( $result->ID ). '#msform';
                }

                $mobileButton = '';
                if ($slider->getMinimalStatus() != 'true') {
                    $mobileButton = '<div class="mobile-button"><a href="' . user_trailingslashit($pod->field('permalink') . 'redirect') . '" class="button small applyYellow"  rel="nofollow" title="' . sprintf(__('Apply for %s', 'fs'), $result->title) . '"> ' . __('Application', 'fs') . ' </a>' . ( $generalSettings->field( 'show_sponsored_text' ) ? '<div class="sponsored">' . __( 'Sponsored', 'fs' ) . '</div>' : '' ) . '</div>';
                }

                $items    = [];
				$items [] =
					new Row(
						[
                // Logo
							new Data( 'logo', '' . $pod->display( 'logo._img.full' ) . '<br>' . $result->title, [
                        'class' => 'display-mobile vit cctd company-listing' . ( $result->featured ? ' premium' : '' )
							] ),
                // Max Credit
							new Data( 'max_credit', '<span class="mobile-only">' . __( 'Max credit:', 'fs' ) . '</span> ' . ( _isset( $result->summ ) ? ( - 1 == $result->summ ? __( 'N/A', 'fs' ) : Util::moneyFormat( $result->summ ) . ' ' . __( 'usd', 'fs' ) ) : '&nbsp;' ), [ 'class' => 'max-credit', ] ),
                // Interest Free Days
							new Data( 'interest_free_days', '<span class="mobile-only">' . __( 'Interest free days:', 'fs' ) . '</span> ' . $days . ' ' . ( - 1 == $days ? __( 'N/A', 'fs' ) : ( ( 1 < $days || 0 == $days )
									? __( 'days', 'fs' ) : __( 'day', 'fs' ) ) ), [ 'class' => 'sliderm' ] ),                                // Loan Fees
							new Data( 'card_fee', '<span class="mobile-only">' . __( 'Card fee:', 'fs' ) . '</span> ' . ( _isset( $result->looses ) ? Util::moneyFormat( $result->looses ) . ' ' . __( 'usd', 'fs' ) : '&nbsp;' ), [ 'class' => 'card-fee' ] ),
                // Travel Insurance
							new Data( 'travel_insurance', '<span class="mobile-only">' . __( 'Travel insurance:', 'fs' ) . '</span> ' . ( $result->travel_insurance ? ( '<p class="true" />' ) : ( '<p class="false" />' ) ), [ 'class' => 'travel-insurance' ] ),
                // Withdrawl Fee
							new Data( 'atm_fee', '<span class="mobile-only">' . __( 'ATM fee:', 'fs' ) . '</span> ' . ( _isset( $result->atm_fee ) ? ( ( - 1 == $result->atm_fee ) ? __( 'N/A', 'fs' ) : Util::numberFormat( $result->atm_fee ) . ' ' . Util::atmUnit($result->atm_fee_unit) ) : '&nbsp;' ), [ 'class' => 'atm-fee' ] ),
                // Card Type
							new Data( 'card_type', '<span class="mobile-only">' . __( 'Card type:', 'fs' ) . '</span> ' . ( _isset( $result->card_type ) ? '<p class="' . $result->card_type . '"></p>' : '&nbsp;' ), [ 'class' => 'card-type' ] ),
                // Loan Percent
							new Data( 'interest_rate', '<span class="mobile-only">' . __( 'Interest rate:', 'fs' ) . '</span><strong class="cardInterest">' . ( _isset( $result->percent ) ? ( - 1 == $result->percent ? __( 'N/A', 'fs' ) : $result->percent . '%' ) : '&nbsp;' ) . '</strong>' . $mobileButton, [ 'class' => 'display-mobile card-interest main-column loan-total' ] ),
                // Loan Apply
                //new Data ( 'apply','<a href="'.$companyLinkReview.'" class="button small applyYellow" onclick="'.$url_link.'"  rel="nofollow" title="' . sprintf( __( 'Apply for %s', 'fs' ), $result->title ) . '"> ' . __( 'Application', 'fs' ) . ' </a>', [ 'class' => 'company-apply' ] ),
                            new Data ( 'application','<a href="'.$url_link.'" class="button small applyYellow" target="_blank" rel="nofollow" title="' . sprintf( __( 'Apply for %s', 'fs' ), $result->title ) . '"> ' . __( 'Application', 'fs' ) . ' </a>' . ( $generalSettings->field( 'show_sponsored_text' ) ? '<div class="sponsored">' . __( 'Sponsored', 'fs' ) . '</div>' : '' ), [ 'class' => 'company-apply', 'minimal' => $slider->getMinimalStatus() ] ),
                        ], ['class' => 'flex-columns'] );
				if ( ! empty( $result->card_details ) ) {

                    if ($pod->singleCompany == false && ($slider->getMinimalStatus() != 'true')) {
                        $items [] = new Row([
                            //tags
                            new Data('more_information', '<i class="toggle-details fa fa-plus" >' . __('More information', 'fs') . '</i>', ['class' => 'more-information']),
                        ], ['class' => 'more-information-row']);
                    }

					$items [] = new Row( [
						// Max Credit
						new Data( 'max_credit', '<div><strong>' . __( 'Max credit:', 'fs' ) . '</strong></div><div>' .( _isset( $result->summ ) ? ( - 1 == $result->summ ? __( 'N/A', 'fs' ) : Util::moneyFormat( $result->summ ) . ' ' . __( 'usd', 'fs' ) ) : '&nbsp;' ) . '</div>', [ 'class' => 'max-credit hide-desktop', ] ),
						// Interest Free Days
						new Data( 'interest_free_days', '<div><strong>' . __( 'Interest free days:', 'fs' ) . '</strong></div><div>' . $days . ' ' . ( - 1 == $days ? __( 'N/A', 'fs' ) : ( ( 1 < $days || 0 == $days )
								? __( 'days', 'fs' ) : __( 'day', 'fs' ) ) )  . '</div>', [ 'class' => 'sliderm hide-desktop' ] ),
						// Loan Fees
						new Data( 'card_fee', '<div><strong>' . __( 'Card fee:', 'fs' ) . '</strong></div><div>' . ( _isset( $result->looses ) ? Util::moneyFormat( $result->looses ) . ' ' . __( 'usd', 'fs' ) : '&nbsp;' ) . '</div>', [ 'class' => 'card-fee hide-desktop' ] ),
						// Travel Insurance
						new Data( 'travel_insurance', '<div><strong>' . __( 'Travel insurance:', 'fs' ) . '</strong></div><div>' .( $result->travel_insurance ? ( '<p class="true" />' ) : ( '<p class="false" />' ) )  . '</div>', [ 'class' => 'travel-insurance hide-desktop' ] ),
						// Withdrawl Fee
						new Data( 'atm_fee', '<div><strong>'. __( 'ATM fee:', 'fs' ) . '</strong></div><div>' .( _isset( $result->atm_fee ) ? ( ( - 1 == $result->atm_fee ) ? __( 'N/A', 'fs' ) : Util::numberFormat( $result->atm_fee ) . ' ' . Util::atmUnit($result->atm_fee_unit) ) : '&nbsp;' )  . '</div>', [ 'class' => 'atm-fee hide-desktop' ] ),
						// Card Type
						new Data( 'card_type', '<div><strong>'. __( 'Card type:', 'fs' ) . '</strong></div><div>' . ( _isset( $result->card_type ) ? '<p class="' . $result->card_type . '"></p>' : '&nbsp;' )  . '</div>', [ 'class' => 'card-type hide-desktop' ] ),
						new Data( 'card_details', $result->card_details ),
					], [ 'class' => 'details' ] );
				}
				$table->addRow( new Item( $items, [ 'data-period' => $days, 'class' => 'company-listing '.( ( $pos % 2 ) ? 'even sort-item' : 'odd sort-item' ) . ( $result->featured ? '  premium' : '' ) . ( $result->affiliate ? '' : ' greyed' ) ] ) );
				remove_filter( 'wp_get_attachment_image_attributes', $func );
			}
		} else {

		    if ($pod->singleCompany) {
                $table->addRow( new Row( [ new Data( false, __( 'No credit cards found.', 'fs' ), [ 'colspan' => 100 ] ) ] ) );
            } else {
                $table->addRow( new Row( [ new Data( false, __( 'No credit cards found in your search. Try using less filters.', 'fs' ), [ 'colspan' => 100 ] ) ] ) );
            }

		}
		echo $table->render();
	}
}
