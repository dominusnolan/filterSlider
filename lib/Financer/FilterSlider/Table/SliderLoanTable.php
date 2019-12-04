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

        $generalSettings = pods( 'general_settings' );
        $sliderSetting   = pods( 'slider_settings' );

        $table = new Surface( [ 'class' => 'table table-striped' ] );
        $remove_style = '';
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
        if ( count( $query ) > 0 ) {
            $slider->showResultsTitle($slider, $query, $pod);
            foreach ( $query as $pos => $result ) {
                if(  get_post_status( $result->pid ) == 'private' )
                    continue;
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

                $isOpen = Util::isCompanyOpenToday($result->open_hours);
                if ($isOpen) {
                    $output_loan_tags .= '<div class="data-tag is_open">' . __($isOpen, 'fs') . '</div> ';
                }

                if (isset($result->most_lowest_cost)) {
                    $output_loan_tags .= '<div class="data-tag lowest_price">' . __('Lowest cost', 'fs') . '</div> ';
                }
                if (isset($result->most_visited)) {
                    $output_loan_tags .= '<div class="data-tag most_visited">' . __('Most chosen', 'fs') . '</div> ';
                }
                if (isset($result->most_approval_rate)) {
                    $output_loan_tags .= '<div class="data-tag approval_rate">' . __('High approval rate', 'fs') . '</div> ';
                }
                if (isset($result->most_overall_rating)) {
                    $output_loan_tags .= '<div class="data-tag overall_rating">' . __('Best reviews', 'fs') . '</div> ';
                }


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
                //$loan_dataset = pods( 'loan_dataset',  ['where'  => ['company_parent.ID' => $result->ID]]);

                if ( $result->special_text ) {
                    $span = '<span class="logo-text">' . $result->special_text . '</span>';
                } else {
                    $span = '';
                }

                //$url_link = "window.open('".$url_link."')";
                $companyLinkReview = '#';
                if ($pod->field( 'company')) {
                    $companyLinkReview = get_permalink( $result->ID ). '#msform';
                }
				
				$displayVisitCounter = ($podInner->display('visits') > 0 && $visitorCounterStatus != 'hide') ? '<p class="chosen-amount">' . __( 'Chosen', 'fs' ) . ' <strong>' . $podInner->display('visits') . '</strong> '. __( 'times', 'fs' ) .'</p>' : '';
				
                //$mobileButton = '<div class="mobile-button"><a href="' . $companyLinkReview . '" onclick="'.$url_link.'" class="button small applyYellow" rel="nofollow"> ' . __( 'Application', 'fs' ) . ' </a>' . ( $generalSettings->field( 'show_sponsored_text' ) ? '<div class="sponsored">' . __( 'Sponsored', 'fs' ) . '</div>' : '' ) . '</div>';
                $mobileButton = '';
                if ($slider->getMinimalStatus() != 'true') {
                    $mobileButton = '<div class="mobile-button"><a href="'.$url_link.'" class="button small applyYellow" data-cname="'. get_the_title($result->ID) .'" data-cid="'. $result->ID .'" data-plink="'. get_permalink($result->ID) .'" rel="nofollow"> ' . __( 'Application', 'fs' ) . ' </a>' . ( $generalSettings->field( 'show_sponsored_text' ) ? '<div class="sponsored">' . __( 'Sponsored', 'fs' ) . '</div>' : '' . $displayVisitCounter .'' ) . '</div>';
                }

                $company = pods( 'data_customization', ['orderby' => 'date DESC', 'limit'   => 1] );
                $status = 'show';
                while ( $company->fetch() ):
                    $status = $company->display('visit_counter');
                endwhile;
                if ($status == 'hide') {
                    $visitorCounterStatus = 'hide';
                } else {
                    $visitorCounterStatus = 'show';
                }

                $loanName = ($result->loan_name) ? '<div class="loan-name">' . $result->loan_name . '</div>' : '';
                $pixel_impression = Util::trackingUrl($podInner->display('offer_tracking_url'), $podInner->display('impression_tracking_url'), $result->offer_tracking_url, $result->impression_tracking_url);

                $readReviews = ($slider->getMinimalStatus() != 'true') ? '<a href="' . get_permalink( $result->ID ) . '#read-reviews">' . __( 'Read', 'fs' ) . '&nbsp;' . $result->total_rating . ' ' . __( 'reviews.', 'fs' ) . '</a>' : '';

                $items   = [];
                $items[] = new Row( [
                    // Logo
                    new Data( 'logo', $span . '<a class="company-logo" href="' . get_permalink( $result->ID ) . '">' . '<img title="' . $result->title . '" src="' . $podInner->field( 'logo._src' ) . '" />' . '</a>' . '<span class="totalReviews">' . $loanName .
                        self::showStars( $result->ID ) . $readReviews . '</span> ', [ 'class' => 'display-mobile vit company-listing', 'minimal' => $slider->getMinimalStatus() ] ),
                    // Loan Amounts
                    new Data( 'loan_amount', '<span class="mobile-only">' . __( 'Loan amount:', 'fs' ) . '</span> ' . Util::moneyFormat( $slider->getAmount() ) . ' ' . __( 'usd', 'fs' ) .'<br>', [ 'class' => 'loan-amount', 'minimal' => $slider->getMinimalStatus() ] ),
                    // Loan Period
                    new Data( 'loan_period', '<span class="mobile-only">' . __( 'Loan period:', 'fs' ) . '</span> ' . Util::getPeriod( $slider->getPeriod() ), [ 'class' => 'loan-period', 'minimal' => $slider->getMinimalStatus() ] ),
                    // Loan APR
                    new Data( 'nominal_apr', '<span class="mobile-only">' . __( 'Nominal APR:', 'fs' ) . '</span><span class="sort-interest">' . $result->interest_rate . '&nbsp;% ' . ( $result->highest_annual_interest_rate != 0 ? __( 'to', 'fs' ) . ' ' . Util::numberFormat( $result->highest_annual_interest_rate ) . ' %' : '' ) . '</span>', [ 'mobileButtonSpecial' => $mobileButton, 'class' => 'loan-apr loan-mobile-view', 'style' => $remove_style, 'minimal' => $slider->getMinimalStatus() ] ),
                    new Data( 'daily_interest', '<span class="mobile-only">' . __( 'Daily Interest:', 'fs' ) . '</span> ' . Util::calcuateDailyRate( $result->interest_rate ) . '&nbsp;% </span>', [ 'mobileButtonSpecial' => $mobileButton, 'class' => 'loan-apr loan-mobile-view', 'style' => $remove_style, 'minimal' => $slider->getMinimalStatus() ] ),
                    new Data( 'effective_interest_rate', '<span class="mobile-only">' . __( 'Effective Interest Rate:', 'fs' ) . '</span> ' . Util::numberFormat($result->effective_interest_rate) . '% </span>', [ 'mobileButtonSpecial' => $mobileButton, 'class' => 'loan-apr loan-mobile-view ', 'style' => $remove_style, 'minimal' => $slider->getMinimalStatus() ] ),
                    new Data( 'custom_interest_rate', '<span class="mobile-only">' . __( 'Most likely Interest Rate:', 'fs' ) . '</span> ' . Util::numberFormat($result->custom_interest_rate) . '% </span>', [ 'mobileButtonSpecial' => $mobileButton, 'class' => 'loan-apr loan-mobile-view ', 'style' => $remove_style, 'minimal' => $slider->getMinimalStatus() ] ),
                    // Loan Fees
                    new Data( 'monthly_payback', '<span class="mobile-only">' . __( 'Monthly payback:', 'fs' ) . '</span> ' . Util::moneyFormat( $result->total_monthly_payback ) . ' ' . __( 'usd', 'fs' ), [ 'mobileButtonSpecial' => $mobileButton, 'class' => 'loan-mobile-view loan-monthly-payback', 'minimal' => $slider->getMinimalStatus() ] ),
                    // Loan Total Cost
                    new Data( 'total_cost_from', '<span class="mobile-only">' . __( 'Total cost from:', 'fs' ) . '</span>' . Util::moneyFormat( $result->total_cost ) . ' ' . __( 'usd', 'fs' ), [ 'mobileButtonSpecial' => $mobileButton, 'class' => 'loan-total' . ( 0 == $result->total_cost ? ' green' : '' ), 'minimal' => $slider->getMinimalStatus() ] ),
                    // Loan Apply
                    //new Data( 'application', $pixel_impression.'<a href="'.$url_link.'" class="button small applyYellow"  rel="nofollow"> ' . __( 'Application', 'fs' ) . ' </a>' . ( $generalSettings->field( 'show_sponsored_text' ) ? '<div class="sponsored">' . __( 'Sponsored', 'fs' ) . '</div>' : '' . $displayVisitCounter .'' ), [ 'class' => 'company-apply' ] ),
                    new Data( 'application', $pixel_impression.'<a href="'.$url_link.'" target="_blank" class="button small applyYellow"  data-cname="'. get_the_title($result->ID) .'" data-cid="'. $result->ID .'" data-plink="'. get_permalink($result->ID) .'" rel="nofollow"> ' . __( 'Application', 'fs' ) . ' </a>' . ( $generalSettings->field( 'show_sponsored_text' ) ? '<div class="sponsored">' . __( 'Sponsored', 'fs' ) . '</div>' : '' . $displayVisitCounter .'' ), [ 'class' => 'company-apply', 'minimal' => $slider->getMinimalStatus() ] ),
                    /**/
                ], [ 'data-id' => $result->ID, 'class' => 'flex-columns sort-item' . ( $pos % 2 ? ' even' : ' odd' ) . ( $result->ej_partner ? ' greyed' : '' ) . ( $result->favorite ? ' premium' : '' ) . ( $repexample || $output_loan_tags ? ' tag-rep-more' : '' ) ] );

                if(!empty($output_loan_tags) ||  !empty($repexample)) {
                    $items[] = new Row( [
                        //tag
                        new Data( false, ( $output_loan_tags ? '<div class="loan-tags">' . $output_loan_tags . '</div>' : '' ) .
                            ( $repexample ? '<div class="representative-example">' . $repexample . '</div>' : '' ), [ 'class' => 'tag-example-column', 'minimal' => $slider->getMinimalStatus() ] ),
                    ], [ 'class' => 'tag-example' ] );
                }

                if ($slider->getMinimalStatus() != 'true') {
                    $items[] = new Row( [
                        //tags
                        new Data( 'more_information', '<i class="toggle-details fa fa-plus" >' . __( 'More information', 'fs' ) . '</i>', [ 'class' => 'more-information' ] ),
                    ], [ 'class' => 'more-information-row' ] );
                }

                $items[] = new Row( [
                    new Data( 'loan_amount', '<div class="data-description"><strong>' . __( 'Loan amount:', 'fs' ) . '</strong></div><div class="data-result">' . Util::moneyFormat( $slider->getAmount() ) . ' ' . __( 'usd', 'fs' ) . '</div>', [ 'colspan' => 2 , 'class' => 'hide-desktop', 'minimal' => $slider->getMinimalStatus() ] ),
                    new Data( 'loan_period', '<div class="data-description"><strong>' . __( 'Loan period:', 'fs' ) . '</strong></div><div class="data-result">' . Util::getPeriod( $slider->getPeriod() ) . '</div>', [ 'colspan' => 2 , 'class' => 'hide-desktop', 'minimal' => $slider->getMinimalStatus() ] ),
                    new Data( 'nominal_apr', '<div class="data-description"><strong>' . __( 'Nominal APR:', 'fs' ) . '</strong></div><div class="data-result"><span class="sort-interest">' . $result->interest_rate . '</span>&nbsp;% ' . ( $result->highest_annual_interest_rate != 0 ? __( 'to', 'fs' ) . ' ' . Util::numberFormat( $result->highest_annual_interest_rate ) . ' %' : '' ) . '</div>', [ 'colspan' => 2 ,'class' => 'hide-desktop', 'minimal' => $slider->getMinimalStatus() ] ),
                    new Data( 'monthly_payback', '<div class="data-description"><strong>' . __( 'Monthly payback:', 'fs' ) . '</strong></div><div class="data-result">' . Util::moneyFormat( $result->total_monthly_payback ) . ' ' . __( 'usd', 'fs' ) .'</div>', [ 'colspan' => 2 , 'class' => 'hide-desktop', 'minimal' => $slider->getMinimalStatus() ] ),
                    new Data( 'restriction', '<div class="data-description"><strong>' . __( 'Customer type:', 'fs' ) . '</strong></div><div class="data-result">' . $restriction . '</div>', [ 'colspan' => 2, 'class' => 'restriction', 'before' => '<div class="left-details">', 'minimal' => $slider->getMinimalStatus() ] ),
                    new Data( 'borrow_up_to', '<div class="data-description"><strong>' . __( 'Borrow up to:', 'fs' ) . '</strong></div><div class="data-result">' . Util::moneyFormat( $result->amount_range_maximum ) . '&nbsp;' . __( 'usd', 'fs' ) . '</div>', [ 'colspan' => 2, 'minimal' => $slider->getMinimalStatus() ] ),
                    new Data( 'estimated_pay_back', '<div class="data-description"><strong>' . __( 'Estimated pay back:', 'fs' ) . '</strong></div><div class="data-result">' . $date->format( 'd-m-Y' ) . '</div>', [ 'colspan' => 2, 'minimal' => $slider->getMinimalStatus() ] ),
                    new Data( 'bad_credit_history', '<div class="data-description"><strong>' . __( 'Bad credit history:', 'fs' ) . '</strong></div><div class="data-result"><p class="' . ( $result->bad_history ? 'true' : 'false' ) . '" ></p></div>', [ 'colspan' => 2, 'minimal' => $slider->getMinimalStatus() ] ),
                    new Data( 'credit_score', '<div class="data-description"><strong>' . __( 'Credit Score:', 'fs' ) . '</strong></div><div class="data-result">' . $result->credit_score . '</div>', [ 'colspan' => 2, 'minimal' => $slider->getMinimalStatus() ] ),
                    new Data( 'weekend_payout', '<div class="data-description"><strong>' . __( 'Weekend payout:', 'fs' ) . '</strong>&nbsp;</div><div class="data-result"><p class="' . ( $result->weekend_payout ? 'true' : 'false' ) . '"></p></div>', [ 'colspan' => 2, 'minimal' => $slider->getMinimalStatus() ] ),
                    new Data( 'credit_check', '<div class="data-description"><strong>' . __( 'Credit check:', 'fs' ) . '</strong>&nbsp;</div><div class="data-result">' . $podInner->display( 'credit_check' ) . '</div>', [ 'colspan' => 2, 'minimal' => $slider->getMinimalStatus() ] ),
                    new Data( 'minimum_income', '<div class="data-description"><strong>' . __( 'Minimum Income:', 'fs' ) . '</strong>&nbsp;</div><div class="data-result">' . ( $result->minimum_inkomst ? $result->minimum_inkomst. '&nbsp;'.__( 'usd', 'fs' ) : 'n/a' ) . '</div>', [ 'colspan' => 2, 'minimal' => $slider->getMinimalStatus() ] ),
                    new Data( 'banks', '<div class="data-description"><strong>' . __( 'Banks:', 'fs' ) . '</strong>&nbsp;</div><div class="data-result">' . ( $podInner->display( 'banks' ) ? $podInner->display( 'banks', [ 'serial_params' => [ 'and' => __( ', ', 'fs' ) ] ] ) : 'n/a' ) . '</div>', [ 'colspan' => 2, 'class' => 'banks-details', 'minimal' => $slider->getMinimalStatus() ] ),
                    new Data( 'age_minalder', '<div class="data-description"><strong>' . __( 'Age:', 'fs' ) . '</strong>&nbsp;</div><div class="data-result">' . $result->minalder . '</div>', [ 'colspan' => 2, 'after' => '</div>', 'minimal' => $slider->getMinimalStatus() ] ),

                    new Data( 'review_breakdown', '<div class="overall_ratings">'.do_shortcode('[company_ratings id='.$result->ID.']').'</div>', [ 'colspan' => 2, 'class' => 'full-width', 'before' => '<div class="right-details">', 'minimal' => $slider->getMinimalStatus() ] ),
                    //new Data( 'detail_button', '<a href="' . get_permalink( $result->ID ) . '" class="button small blue-border">' . __( 'Details', 'fs' ) . '</a>', [ 'colspan' => 2, 'class' => 'full-width', 'before' => '<div class="right-details">' ] ),
                    new Data( 'reviews', '<a href="' . get_permalink( $result->ID ) . '" class="button small blue-border">' . __( 'Details', 'fs' ) . '</a>', [ 'colspan' => 2, 'class' => 'full-width', 'after' => '</div>', 'minimal' => $slider->getMinimalStatus() ] ),
                ], [ 'class' => 'details' ] );

                $table->addRow( new Item( $items, [
                    'class' => 'company-listing ' .
                        ( $result->favorite ? 'premium' : '' ) . ( $result->ej_partner ? ' np' : '' )
                ] ) );

                $pod->fetch();
            }

            echo $table->render();
            if ($slider->getMinimalStatus() != 'true') {
                $slider_settings = pods( 'slider_settings' );
                if ( $slider_settings->field( 'loan_notice' ) ) {
                    $loan_notice = $slider_settings->field( 'loan_notice' );
                    echo <<<HTML
                    <div class="loan-notice">{$loan_notice}</div>
HTML;
                }
            }
        } else {

            echo '<div class="msg info slider-msg"> ' . __( 'No loan companies found in that search. Try using less filters, or consider the recommended lenders below based on best ratings.', 'fs' ) . '</div>';

            echo do_shortcode( '[top_rated_companies title="Top Rated Companies" limit="5" type="loan_company"]' );
        }


    }
}

?>
