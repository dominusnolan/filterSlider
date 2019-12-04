<?php

namespace Financer\FilterSlider;


/**
 * Class Util
 * @package Financer\FilterSlider
 */
/**
 * Class Util
 * @package Financer\FilterSlider
 */
class RepresentativeExampleUtil {
    /**
     * @param $period
     *
     * @return string
     */

    public static function RepresentativeExample($content, $result, $slider) {

        /***

        Shortcode: [rep_example value="" formula=""]


        Example:
        Borrow: [rep_example value="" formula=""]
        Effective rate: [rep_example value="" formula="min-interest-rate x searched-amount"]

        Possible values: searched_amount, searched_period, max_loan_period, loan_fee, loan_fee_flat, loan_fee_percentage, min_interest_rate, max_interest_rate, total_cost_min, total_cost_max, total_cost_custom, company_name, effective_rate_min, effective_rate_max Operators: Group: (), -,+, /, *, Exp: pow(1,2)



         ***/
        //set values



        /*compute for total cost*/

        $total_cost_compute = array(
            'min_interest'=>$result->interest_rate,
            'max_interest'=>$result->highest_annual_interest_rate,
            'custom_interest'=>$result->custom_interest_rate
        );
        $total_cost_done = array();
        foreach($total_cost_compute as $key=>$interest_rate){
            if(empty($interest_rate)){$interest_rate=0;}
            $monthly_rate = (1+(($interest_rate/100) * (1/12)));
            if($monthly_rate>1){
                $annuity = ($slider->getAmount()*POW(($monthly_rate), ($slider->getPeriod()/30))*((1-$monthly_rate)/(1-POW(($monthly_rate),($slider->getPeriod()/30)))));
            }else{
                if($slider->getPeriod()<=30){
                    $compute_l = 0;
                }else{
                    $compute_l = 0/(1-($slider->getPeriod()/30));
                }

                $annuity = ($slider->getAmount()*POW(($monthly_rate), ($slider->getPeriod()/30))*($compute_l));

            }


            //
            $fee = 0;
            if($result->fee_flat == 0 || $result->fee_flat == NULL){
                if($result->fee_percent == 0 || $result->fee_percent == NULL){
                    $fee = 0;
                }else{
                    $fee = ($result->fee_percent/100) * $slider->getAmount();
                }
            }else{
                $fee = $result->fee_flat;
            }

            //
            if($result->monthly_fee == NULL){
                $monthly_feec = 0;
            }else{
                $monthly_feec = $result->monthly_fee;
            }
            if($slider->getPeriod() < 30){
                $period_c = 1;
            }else{
                $period_c = $slider->getPeriod()/30;
            }
            if($annuity==NULL){$annuity_c = 0; $annuity_amount = 0; }
            else{ $annuity_c=$annuity; $annuity_amount = $slider->getAmount(); }
            $total_cost_computed = (($fee) + (($annuity_c)*($period_c)) + ($monthly_feec*$period_c) - $annuity_amount);
            //total cost plus loan fee
            $total_cost_done[$key] = $total_cost_computed;

        }
        //


        /*compute for effective rate*/
        //

        $loan_fee_d = ( $result->fee_flat ? $result->fee_flat : ($slider->getAmount() * ($result->fee_percent/100)) );

        $effective_rate_min = ((pow((1+(($result->interest_rate/100)/($slider->getPeriod()/30))),($slider->getPeriod()/30))-1)+($result->monthly_fee*(12/($slider->getAmount()/2)))+(($loan_fee_d/($slider->getPeriod()/30))*(12/($slider->getAmount()/2))))*100;


        $effective_rate_max = ((pow((1+(($result->highest_annual_interest_rate/100)/($slider->getPeriod()/30))),($slider->getPeriod()/30))-1)+($result->monthly_fee*(12/($slider->getAmount()/2)))+(($loan_fee_d/($slider->getPeriod()/30))*(12/($slider->getAmount()/2))))*100;

        $effective_rate_custom = ((pow((1+(($result->custom_interest_rate/100)/($slider->getPeriod()/30))),($slider->getPeriod()/30))-1)+($result->monthly_fee*(12/($slider->getAmount()/2)))+(($loan_fee_d/($slider->getPeriod()/30))*(12/($slider->getAmount()/2))))*100;

        $period_range_minimum= isset($result->period_range_minimum) ? Util::getPeriod( $result->period_range_minimum ) : '0';
        $period_range_maximum = isset($result->period_range_maximum) ? Util::getPeriod( $result->period_range_maximum ) : '0';
        $age_range_minimum = isset($result->age_range_minimum) ?  $result->age_range_minimum  : 18;
        $age_range_maximum = isset($result->age_range_maximum) ?  $result->age_range_maximum  : 23;

        $new_values = array(
            'company_name'=>ucfirst($result->foretag),
            'searched_amount'=>Util::moneyFormat( $slider->getAmount() ) . ' ' . __( 'usd', 'fs' ),
            'searched_period'=>Util::getPeriod( $slider->getPeriod() ),
            'min_loan_period'=>( $period_range_minimum ),
            'max_loan_period'=>( $period_range_maximum ),
            'min_loan_age'=>( $age_range_minimum ),
            'max_loan_age'=>( $age_range_maximum ),
            'loan_fee'=>( $result->fee_flat!=0 ? Util::moneyFormat( $result->fee_flat ) . ' ' . __( 'usd', 'fs' ) : Util::moneyFormat($slider->getAmount() * ($result->fee_percent/100)) . ' ' . __( 'usd', 'fs' ) ),
			'loan_fee_flat'=>( $result->fee_flat ? Util::moneyFormat( $result->fee_flat ) . ' ' . __( 'usd', 'fs' ) : Util::moneyFormat( 0 ) . ' ' . __( 'usd', 'fs' ) ),
            'loan_fee_percentage'=>Util::moneyFormat($slider->getAmount() * ($result->fee_percent/100)) . ' ' . __( 'usd', 'fs' ),
			'min_interest_rate'=>( $result->interest_rate ? Util::numberFormat( $result->interest_rate ) . '%' : '0%' ),
            'max_interest_rate'=>( $result->highest_annual_interest_rate!=NULL&&$result->highest_annual_interest_rate!=0 ? Util::numberFormat( $result->highest_annual_interest_rate ) . '%' : '0%' ),
            'custom_interest_rate'=>( $result->custom_interest_rate!=NULL&&$result->custom_interest_rate!=0 ? Util::numberFormat( $result->custom_interest_rate ) . '%' : '0%' ),
			'monthly_fee'=>( $result->monthly_fee ? Util::moneyFormat( $result->monthly_fee ) . ' ' . __( 'usd', 'fs' ) : ''),
            'total_cost_min'=> ( $total_cost_done['min_interest'] ? Util::moneyFormat( $total_cost_done['min_interest'] ) . ' ' . __( 'usd', 'fs' ) : '0' . __( 'usd', 'fs' ) ),

            'total_cost_max'=> ( $result->highest_annual_interest_rate!=0 && $total_cost_done['max_interest'] ? Util::moneyFormat( $total_cost_done['max_interest'] ) . ' ' . __( 'usd', 'fs' ) : '0' . __( 'usd', 'fs' ) ),


            'total_cost_custom'=> ( $total_cost_done['custom_interest'] ? Util::moneyFormat( $total_cost_done['custom_interest'] ) . ' ' . __( 'usd', 'fs' ) : '0' . __( 'usd', 'fs' ) ),

            'effective_rate_min'=> ( $effective_rate_min ? Util::numberFormat($effective_rate_min) . __( '%', 'fs' ) : '0' . __( '%', 'fs' ) ),
            'effective_rate_max'=> ( $result->highest_annual_interest_rate!=0 && $effective_rate_max ? Util::numberFormat($effective_rate_max) . __( '%', 'fs' ) : '0' . __( '%', 'fs' ) ),
            'effective_rate_custom'=> ( $effective_rate_custom ? Util::numberFormat($effective_rate_custom) . __( '%', 'fs' ) : '0' . __( '%', 'fs' ) ),
            'custom_text'=> ( $result->representative_example ? $result->representative_example : '0' )

        );

        $new_values_noformat = array(
            'searched_amount'=>$slider->getAmount(),
            'searched_period'=>$slider->getPeriod()/30,
            'max_loan_period'=>$period_range_maximum,
            'min_loan_period'=>$period_range_minimum,
            'max_loan_age'=>$age_range_maximum,
            'min_loan_age'=>$age_range_minimum,
            'loan_fee'=>( $result->fee_flat ? $result->fee_flat : ($slider->getAmount() * ($result->fee_percent/100)) ),
            'loan_fee_flat'=>$result->fee_flat,
            'loan_fee_percentage'=>$slider->getAmount() * ($result->fee_percent/100),
            'min_interest_rate'=>( $result->interest_rate!=0 ? $result->interest_rate/100 : floatval(0.00) ),
            'max_interest_rate'=>( $result->highest_annual_interest_rate!=NULL&&$result->highest_annual_interest_rate!=0 ? $result->highest_annual_interest_rate/100 : '' ),
            'custom_interest_rate'=>( $result->custom_interest_rate!=NULL&&$result->custom_interest_rate!=0 ? $result->custom_interest_rate/100 : '' ),
            'monthly_fee'=>( $result->monthly_fee ? $result->monthly_fee : 0),
            'total_cost_min'=>$total_cost_done['min_interest'],
            'total_cost_max'=>$total_cost_done['max_interest'],
            'total_cost_custom'=>$total_cost_done['custom_interest'],
            'effective_rate_min'=> $effective_rate_min,
            'effective_rate_custom'=> $effective_rate_custom,
            'custom_text'=> ( $result->representative_example ? $result->representative_example : '0' )

        );
        $rep_ex = $content;
        //replace values
        ob_start();
        if ( preg_match_all('/\[rep_example(.*?)\]/', $rep_ex, $values ) ) {

            foreach($new_values as $key=>$new_value){
                $rep_ex = str_replace('value="' . $key . '"', 'value="' . $new_value . '"', $rep_ex);
            }

            foreach($new_values_noformat as $key=>$new_value){
                $rep_ex = str_replace($key, floatval($new_value), $rep_ex);
            }
        }
        //compute formula

        //return
        echo $rep_ex;
        return ob_get_clean();

    }




}
