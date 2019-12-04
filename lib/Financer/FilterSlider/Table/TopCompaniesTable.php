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
 * Class TopCompaniesTable
 * @package Financer\FilterSlider\Table
 */
class TopCompaniesTable extends Table implements TableInterface {

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

                //$url_link_affiliate = "window.open('".$url_link."')";
                $url_link_affiliate = $url_link;
                $companyLinkReview = '#';
                if ($pod->field( 'company')) {
                    $companyLinkReview = get_permalink( $result->ID ). '#msform';
                }

                $displayVisitCounter = ($result->visits >= 0) ? '<p class="chosen-amount">' . __( 'Chosen', 'fs' ) .'<strong> ' . $result->visits . '</strong> ' . __( 'times', 'fs' ) .'</p>' : '';


                $mobileButton = '<div class="mobile-button"><a href="' . $url_link . '" class="button small applyYellow" data-cname="'. get_the_title($result->ID) .'" data-cid="'. $result->ID .'" data-plink="'. get_permalink($result->ID) .'" target="_blank" rel="nofollow"> ' . __( 'Application', 'fs' ) . ' </a>' . ( $generalSettings->field( 'show_sponsored_text' ) ? '<div class="sponsored">' . __( 'Sponsored', 'fs' ) . '</div>' : '' . $displayVisitCounter .'' ) . '</div>';


                $items    = [];
                $items [] =
                    new Row(
                        [
                            // Logo
                            new Data( 'logo', '<a class="company-logo" href="' . get_permalink( $result->ID ) . '">' . '<img title="' . $result->title . '" src="' . $pod->field( 'logo._src' ) . '"/>' . '</a> <span class="totalReviews">' . self::showStars( $result->ID ) . '<a href="' . get_permalink( $result->ID ) . '#read-reviews">' . __( 'Read', 'fs' ) . '&nbsp;' . do_shortcode('[total_rating id='.$result->ID.']') . ' ' . __( 'reviews.', 'fs' ) . '</a>'.$loanName.'</span>', [
                                'class' => 'display-mobile vit company-listing ' . ( $result->favorite ? 'premium' : '' )
                            ] ),
                            // Loan Amounts
                            new Data( 'loan_amount', '<span class="mobile-only">' . __( 'Loan amount:', 'fs' ) . '</span>' . Util::moneyFormat( $result->amount_range_minimum ) . ' - ' . Util::moneyFormat( $result->amount_range_maximum ) . ' ' . __( 'usd', 'fs' ) . $mobileButton , ['class'=> 'loan-total'] ),
                            new Data( 'bad_credit_history', '<span class="mobile-only">' . __( 'Bad credit history', 'fs' ) . '</span><p class="' . ( $result->bad_history ? 'true' : 'false' ) . '"></p>', [ 'class' => 'sliderm' ] ),
                            new Data( 'weekend_payout', '<span class="mobile-only">' . __( 'Weekend Payout', 'fs' ) . '</span><p class="' . ( $result->weekend_payout ? 'true' : 'false' ) . '"></p>', [ 'class' => 'sliderm' ] ),
                            new Data( 'minimum_age', '<span class="mobile-only">' . __( 'Minimum age:', 'fs' ) . '</span> ' . $result->minalder, [ 'class' => 'minimum-age' ] ),

                            // Loan Amounts - Mobile
                            new Data( 'loan_amount', '<span class="mobile-only">' . __( 'Loan amount:', 'fs' ) . '</span>' . Util::moneyFormat( $result->amount_range_minimum ) . ' - ' . Util::moneyFormat( $result->amount_range_maximum ) . ' ' . __( 'usd', 'fs' ) . $mobileButton , ['class'=> 'loan-total main-column hide-desktop'] ),

                            new Data( 'application', '<a href="'.$url_link.'" class="button small applyYellow" data-cname="'. get_the_title($result->ID) .'" data-cid="'. $result->ID .'" data-plink="'. get_permalink($result->ID) .'" rel="nofollow"> ' . __( 'Application', 'fs' ) . ' </a>'. $displayVisitCounter . ( $generalSettings->field( 'show_sponsored_text' ) ? '<div class="sponsored">' . __( 'Sponsored', 'fs' ) . '</div>' : '' ), [ 'class' => 'loan-apply' ] ),
                        ], [ 'data-id' => $result->ID, 'class' => 'flex-columns sort-item ' . ( $pos % 2 ? ' even' : ' odd' ) ]  );

                $items[] = new Row( [
                    //tags
                    new Data( 'more_information', '<i class="toggle-details fa fa-plus" >' . __( 'More information', 'fs' ) . '</i>', [ 'class' => 'more-information' ] ),
                ], [ 'class' => 'more-information-row' ] );

                $items [] = new Row( [
                    // Loan Amounts
                    new Data( 'loan_amount', '<div class="data-description"><strong>' . __( 'Loan amount:', 'fs' ) . '</strong></div><div class="data-result">' . Util::moneyFormat( $result->amount_range_minimum ) . ' - ' . Util::moneyFormat( $result->amount_range_maximum ) . ' ' . __( 'usd', 'fs' ) . '</div>', [ 'class' => 'loan-amounts' , 'before' => '<div class="left-details">'] ),
                    new Data( 'bad_credit_historys', '<div class="data-description"><strong>' . __( 'Bad credit historys', 'fs' ) . '</strong></div><div class="data-result"><p class="' . ( $result->bad_history ? 'true' : 'false' ) . '"></p></div>', [ 'class' => 'sliderm' ] ),
                    new Data( 'weekend_payout', '<div class="data-description"><strong>' . __( 'Weekend Payout', 'fs' ) . '</strong></div><div class="data-result"><p class="' . ( $result->weekend_payout ? 'true' : 'false' ) . '"></p></div>', [ 'class' => 'sliderm' ] ),
                    new Data( 'minimum_age', '<div class="data-description"><strong>' . __( 'Minimum age:', 'fs' ) . '</strong></div><div class="data-result">' . $result->minalder . '</div>', [ 'class' => 'minimum-age' , 'after' => '</div>'] ),
                    new Data( 'review_breakdown', '<div class="overall_ratings topcompaniestable">'.do_shortcode('[company_ratings id='.$result->ID.']').'</div>', [ 'colspan' => 2, 'class' => 'full-width', 'before' => '<div class="right-details">' ] ),
                    new Data( 'reviews', '<a href="' . get_permalink( $result->ID ) . '" class="button small blue-border">' . __( 'Details', 'fs' ) . '</a>', [ 'colspan' => 2, 'class' => 'full-width', 'after' => '</div>' ] ),
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
