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
class Util {

    /**
     * @param $company
     * @param $loan_dataset
     * @param bool $impressionFlag
     * @return string|null
     */
    public static function trackingUrl($companyTrackingUrl, $companyTrackingImpression = null, $loanDatasetTrackingUrl = null, $loanDatasetTrackingImpression = null) {

        $trackingUrl = false;
        if (!empty($loanDatasetTrackingImpression)) {
            $pixelTrackingUrl = $loanDatasetTrackingImpression;
        } elseif (!empty($loanDatasetTrackingUrl)) {
            $pixelTrackingUrl = $loanDatasetTrackingUrl;
            $trackingUrl = true;
        } elseif (!empty($companyTrackingImpression)) {
            $pixelTrackingUrl = $companyTrackingImpression;
        } elseif (!empty($companyTrackingUrl)) {
            $pixelTrackingUrl = $companyTrackingUrl;
            $trackingUrl = true;
        }

        if ($pixelTrackingUrl) {
            if ($trackingUrl == true) {
                $pixelTrackingUrl = str_replace('.com/', '.com/impression/', $pixelTrackingUrl);
            }

            $impression_tracking_url = '<img src="'. $pixelTrackingUrl .'" class="hidden">';
        }
        return $impression_tracking_url;
    }

    /**
     * @param $atm_fee_unit
     * @return string
     */
    public static function atmUnit($atm_fee_unit) {
        if ($atm_fee_unit) {
            return $atm_fee_unit;
        }
        //default
        return '%';
    }

    /**
     * @param $period
     *
     * @return string
     */
    public static function getPeriod( int $period ): string {
        if ( 0 === $period % 365 ) {
            $years           = ( $period / 365 );
            $interval_format = 'P' . floor( $years ) . 'Y';
        } else if ( 0 === $period % 360 ) {
            $years           = ( $period / 360 );
            $interval_format = 'P' . floor( $years ) . 'Y';
        } else if ( 0 === $period % 30 ) {
            $months          = ( $period / 30 );
            $interval_format = 'P' . floor( $months ) . 'M';
        } else {
            $interval_format = 'P' . floor( $period ) . 'D';
        }
        if ( is_float( $period ) ) {
            $interval_format .= 'T' . floor( ( $period - ( floor( $period ) ) ) * 24 ) . 'H';
        }
        $interval    = ( new \DateTime() )->diff( ( new \DateTime() )->add( new \DateInterval( $interval_format ) ) );
        $period_list = [];
        if ( 28 <= $interval->d ) {
            $interval->m ++;
            $interval->d = 0;
        }
        if ( 12 == $interval->m ) {
            $interval->y ++;
            $interval->m = 0;
        }
        if ( 0 < $interval->y ) {
            $period_list[] = sprintf( $interval->format( '%y %%s' ), 1 < $interval->y ? __( 'Years', 'fs' ) : __( 'Year', 'fs' ) );
        }
        if ( 0 < $interval->m ) {
            $period_list[] = sprintf( $interval->format( '%m %%s' ), 1 < $interval->m ? __( 'Months', 'fs' ) : __( 'Month', 'fs' ) );
        }
        if ( 0 < $interval->d && 0 == $interval->y && 0 == $interval->m ) {
            $period_list[] = sprintf( $interval->format( '%d %%s' ), 1 < $interval->d ? __( 'Days', 'fs' ) : __( 'Day', 'fs' ) );
        }

        return pods_serial_comma( $period_list, [ 'and' => ' ' . __( ', ', 'fs' ) . ' ' ] );
    }

    /**
     * @param $amount
     *
     * @return string
     */
    public static function moneyFormat( $amount ): string {
        $amount = self::numberFormat( $amount, 2 );
        if ( strpos( $amount, '.' ) !== false ) {
            return number_format_i18n( $amount, 2 );
        } else {
            return number_format_i18n( $amount );
        }
    }

    /**
     * @param $interest_rate
     * @return string
     */
    public static function calcuateDailyRate( $interest_rate ): string {

        //$dailyRate = (1+(($interest_rate/100) * (1/360)));
        $dailyRate = ( $interest_rate / 360 );
        //return number_format((float)$dailyRate, 1);
        return self::moneyFormat($dailyRate);

    }

    public static $altFeatures = ['companyinfo', 'borrow', 'saving'];


    /**
     * @param     $number
     * @param int $decimals
     *
     * @return string
     */
    public static function numberFormat( $number, $decimals = 2 ): string {
        $number       = round( (float) $number, $decimals );
        $number_parts = explode( '.', $number );
        if ( 1 < count( $number_parts ) ) {
            if ( 0 == absint( $number_parts[1] ) ) {
                $number = absint( $number );
            }
        }

        return (string) $number;
    }

    public static function constructSingleCompanyFeatures($postId) {
        $company = get_post( $postId );

        foreach (self::$altFeatures as $key => $altF) {
            $var = 'feature_1_' . $altF;
            if ($company->{$var}) {
                for ( $i = 1; $i <= 4; $i ++ ) {
                    $fName = "feature_" . $i . '_' . $altF;
                    if ( $company->{$fName} ) {
                        $features[] = $company->{$fName};
                    }
                }
                break;
            }
        }


        if ( ! empty( $features ) ) {
            $features_html = "";
            $features_html .= '<ul>';
            foreach ( $features as $feature ) {
                $features_html .= '<li>' . $feature . '</li>';
            }
            $features_html .= '</ul>';
        }

        if ( empty( $features_html ) ) {
            $features_html = "";
        }

        return $features_html;

    }

    public static function constructFeatures($postId) {
        $repeatable_fields = get_post_meta( $postId, 'repeatable_fields', true );
        //$features = array();

        foreach ( $repeatable_fields as $fields ) {
            $atts = array(
                'id' => $fields['select'],
                'features' => array(
                    ($fields['feature'] ? $fields['feature'] : ''),
                    ($fields['feature2'] ? $fields['feature2'] : ''),
                    ($fields['feature3'] ? $fields['feature3'] : ''),
                    ($fields['feature4'] ? $fields['feature4'] : ''),
                ),
                'tags' => $fields['item_tags'],
            );
        }

        if ($atts['features'][0]) {
            for ( $i = 0; $i <= 3; $i ++ ) {
                $var = $atts['features'][$i];
                if ( $var ) {
                    $features[] = $var ;
                }
            }
        } else {

            $type             = get_post_type( $atts['id'] );
            $item             = pods( $type, $atts['id'] );
            foreach (self::$altFeatures as $key => $altF) {
                $var = 'feature_1_' . $altF;
                if ($item->field( $var )) {
                    for ( $i = 1; $i <= 4; $i ++ ) {
                        $fName = "feature_" . $i . '_' . $altF;
                        if ( $item->field( $fName ) ) {
                            $features[] = $item->field( $fName );
                        }
                    }
                    break;
                }
            }
        }

        if ( ! empty( $features ) ) {
            $features_html = "";
            $features_html .= '<ul>';
            foreach ( $features as $feature ) {

                $features_html .= '<li>' . $feature . '</li>';

            }
            $features_html .= '</ul>';

        } else {
            if ( $type != 'creditcard' ) {
                $features_html = "";
            }
        }
        if ( empty( $features_html ) ) {
            $features_html = "";
        }

        return $features_html;
    }

    public static $mobileSpecialDataField = 'mobile_display_price';

    public static function dataCustomizationCopmanyPage($tag) {

        //$flagMobile = Util::dynamicDataCustomization($tag);
        //$flag = Util::specialDynamicDataCustomization($tag);

        $data_customization   = pods( 'data_customization' );
        $status = $data_customization->field( $tag );
        if ($status == 'hide') {
            return 'hide';
        } else {
            return 'show';
        }

    }

    public static function mobileDataCustomization($tag) {

        $flagMobile = 'hide';
        $special_data_customization   = pods( 'special_data_customization' );
        $specialFields = $special_data_customization->fields();
        // this field works specific only for mobile - we remove it from the global specialField array list
        /// mobile
        $specialMobileField = self::$mobileSpecialDataField;
        $mobileData = $specialFields[$specialMobileField];
        $options = $mobileData['options']['pick_custom'];
        $default_value = $mobileData['options']['default_value'];
        $selectedOption = $special_data_customization->field( $specialMobileField );
        $optionList = explode(PHP_EOL, $options);

        if ($selectedOption == null && $tag == $default_value) {
            $flagMobile = 'show';
        } else {
            $valueDelimiter = '|';
            foreach($optionList as $optionName) {
                if (strpos($optionName, $valueDelimiter) !== false) {
                    $optionName = trim(strstr($optionName, $valueDelimiter, true));
                }

                $interest_options = 'interest_options';
                $interestOptionsList = self::getValueFromPodFields($specialFields[$interest_options]);
                $interFlag = false;
                if (in_array( $tag, $interestOptionsList ) && $selectedOption == 'interest_mob_option') {
                    //echo $tag;
                    //die(var_dump($interestOptionsList));
//            if (in_array( $tag, $interestOptionsList ) && $selectedOption == 'interest_mob_option') {
                    //die($tag);
                    //echo $selectedOption .'=='. $interest_options;

                    //apn

                    $selectedOption = $special_data_customization->field( $interest_options );
                    //$field['options']['pick_select_text'];

                    $optionList = $interestOptionsList;

                    $valueDelimiter = '|';

                    $isSelected = false;
                    foreach($optionList as $option) {
                        $optionName = $option;
                        if (strpos($option, $valueDelimiter) !== false) {
                            $optionName = trim(strstr($option, $valueDelimiter, true));
                        }

                        if ($tag == $optionName && $tag == $selectedOption) {
                            //die($tag . '---');
                            $isSelected = true;
                            break;
                        }
                    }

                    //daily
                    //$interFlag = self::specialDynamicDataCustomization($tag);
                    //echo $tag. ' ---' .$interFlag . '<br>';
                    if ($isSelected) {
                        $optionName = $tag;
                        $selectedOption = $tag;
                        $interFlag=true;
                    }
                }

                //die("tag $tag == option $optionName");
                if ($tag == $optionName) {
                    if ($tag == $selectedOption) {
                        $flagMobile = 'show';
                        break;
                    }
                    $flagMobile = 'hide';
                    break;
                }
            }
        }

        /// mobile
        return $flagMobile;
    }

    /**
     * Extract values from pods multi choose options
     *
     * @param $optionList
     * @return array
     */
    public static function getValueFromPodFields($optionList)
    {
        $podsArrayValue = [];
        $optionsTMP = $optionList['options']['pick_custom'];
        $optionList = explode(PHP_EOL, $optionsTMP);
        $valueDelimiter = '|';
        foreach($optionList as $optionName) {
            if (strpos($optionName, $valueDelimiter) !== false) {
                $optionName = trim(strstr($optionName, $valueDelimiter, true));
            }

            $podsArrayValue[] = $optionName;
        }

        return $podsArrayValue;
    }

    public static function specialDynamicDataCustomization($tag) {

        $flag = 'show';
        $special_data_customization   = pods( 'special_data_customization' );
        $specialFields = $special_data_customization->fields();

        //die(var_dump($specialFields['mobile_display_price']['options']['pick_custom']));

        //remove mobile special data field from special data customization
        $specialMobileField = self::$mobileSpecialDataField;
        unset($specialFields[$specialMobileField]);

        foreach($specialFields as $field) {

            //$field = $specialFields['filter_slider_options'];
            //die(var_dump($field));
            $options = $field['options']['pick_custom']; //default_value -
            $default_value = $field['options']['default_value'];
            //die($default_value);
            //default_value - pick_select_text
            $selectedOption = $special_data_customization->field( $field['name'] );
            //$field['options']['pick_select_text'];

            $optionList = explode(PHP_EOL, $options);

            $valueDelimiter = '|';

            $isSelected = false;
            foreach($optionList as $option) {
                $optionName = $option;
                if (strpos($option, $valueDelimiter) !== false) {
                    $optionName = trim(strstr($option, $valueDelimiter, true));
                }

                //var_dump($optionName); &&
                if ($tag == $optionName || $tag == $selectedOption) {
                    //die("tag $tag -- optionName -- $optionName -- selectedOption $selectedOption");
                    $isSelected = true;
                    break;
                }
            }

            foreach($optionList as $option) {
                $optionName = $option;
                if (strpos($option, $valueDelimiter) !== false) {
                    $optionName = trim(strstr($option, $valueDelimiter, true));
                }

                //die("tag $tag --  selected " . var_dump($isSelected));
                /* if ($tag == 'age' && 'age' == $optionName) {
                     die($optionName . 'name');
                     if ($tag == $selectedOption) {
                         echo 'inn';
                         $flag = 'show';
                         echo "flagINN $flag";
                         var_dump($flag);
                         die();
                         break;
                     } else {
                         $flag = 'hide';
                         die('out');
                         break;
                     }
                 }*/

                //var_dump($selectedOption);
                //die("show $tag == $selectedOption || $default_value == $tag && " . var_dump($isSelected));
                if ($tag == $optionName) {
                    if ($tag == $selectedOption || ($default_value == $tag && ($isSelected == false || $selectedOption == null))) {
                        $flag = 'show';
                        break;
                    }
                    $flag = 'hide';
                    break;
                }
            }
        }

        return $flag;
    }

    public static $data_customization_general = [
        'visit_counter'
    ];

    public static $data_customization_loans = [
        'bad_credit_history',
        'borrow_up_to',
        'credit_check',
        'estimated_pay_back',
        'interest_rate',
        'interest_free_amount',
        'interest_free_period',
        'loan_amount',
        'loan_period',
        'nominal_apr',
        'monthly_payback',
        'total_cost',
        'total_cost_from',
        'total_monthly_payback',
        'minimum_income',
        'banks',
        'weekend_payout',
    ];

    public static $data_customization_credit_cards = [
        'atm_fee',
        'card_details',
        'card_fee',
        'max_credit',
        'withdrawal',
        'interest_free_days',
        'travel_insurance',
        'credit_card',
        'card_type',
        'interest',
    ];

    public static $data_customization_mortgage = [
        'mortgage_rate',
        'mortgage_fee',
        'estimated_monthly_cost',
    ];

    public static $data_customization_saving_accounts = [
        'maximum_savings_amount',
        'minimum_savings_amount',
        'saving_amount',
        'saving_interest_rate',
        'minimum_savings_time',
        'saving_time',
        'free_withdrawals',
        'saving_profit',
        'governmental_guarantee',
        'saving_account',
    ];

    public static function getDataCustomization() {
        return array_merge(
            Util::$data_customization_general,
            Util::$data_customization_loans,
            Util::$data_customization_credit_cards,
            Util::$data_customization_mortgage,
            Util::$data_customization_saving_accounts
        );
    }

    public static function sortCompanyOpenHours(
        $openWeekdays, $closeWeekdays, $openSaturday, $closeSaturday, $openSunday, $closeSunday
    ) {
        $defaultTimePods = '00:00:00';
        $opens = [];
        $weekdaysList = [
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday"
        ];

        if ($openWeekdays != $defaultTimePods && $closeWeekdays != $defaultTimePods) {
            foreach ($weekdaysList as $key => $day) {
                $opens[$day] = array('open' => $openWeekdays, 'close' => $closeWeekdays);
            }
        }

        if ($openSaturday != $defaultTimePods && $closeSaturday != $defaultTimePods) {
            $opens['Saturday'] = array('open' => $openSaturday, 'close' => $closeSaturday);
        }

        if ($openSunday != $defaultTimePods && $closeSunday != $defaultTimePods) {
            $opens['Sunday'] = array('open' => $openSunday, 'close' => $closeSunday);
        }
        return $opens;
    }

    public static function isCompanyOpenToday($opens) {
        $isopen = false;
        $timenow = date( 'H:i', current_time( 'timestamp', 0 ) );
        $daynow = date( 'l', current_time( 'timestamp', 0 ) );
        if ( ! is_array($opens[$daynow]) && strtolower($opens[$daynow]) == 'closed' ) {
            //$isopen = 'We are closed all day.';
        } else {
            if ( $timenow >= $opens[$daynow]['open'] && $timenow <= $opens[$daynow]['close'] ) {
                $isopen = 'Open right now';
            } else if ($opens[$daynow]['open'] == '24/7'){
                $isopen = 'Open right now';
            } else {
                //$isopen = 'We are closed';
            }
        }
        return $isopen;
    }

    public static function isTranslated($originalProductName, $translatedProduct, $return404 = true, $extra = '') {

        $loanProducts = "loan products$extra";
        $savingProducts = "saving products$extra";
        $cardProducts = "card products$extra";
        $mortgageProducts = "mortgage products$extra";

        // skip only US market
        if (11 != get_current_blog_id() && in_array($originalProductName, [$loanProducts, $savingProducts, $cardProducts, $mortgageProducts])) {
            if (!in_array($translatedProduct, [$loanProducts, $savingProducts, $cardProducts, $mortgageProducts])) {
                if ($return404) {
                    global $wp_query;
                    $wp_query->set_404();
                    status_header( 404 );
                    get_template_part( 404 ); exit();
                } else {
                    return true;
                }
            }
        }
    }

}
