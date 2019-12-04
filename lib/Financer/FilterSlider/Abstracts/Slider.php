<?php
/**
 * Base Slider
 *
 * PHP version 5.4
 *
 * @category Slider
 *
 * @package Slider
 *
 * @author Derrick Hammer <derrick@derrickhammer.com>
 *
 * @license Propietary https://en.wikipedia.org/wiki/Proprietary_software
 *
 * @link http://www.financer.com
 */

namespace Financer\FilterSlider\Abstracts;

use Dompdf\Dompdf;
use Financer\FilterSlider\Plugin;
use Financer\FilterSlider\Shortcode\Social;

use Financer\FilterSlider\Util;

/**
 * Class Slider
 *
 * @category Slider
 *
 * @package Slider
 *
 * @author Derrick Hammer <derrick@derrickhammer.com>
 *
 * @license Propietary https://en.wikipedia.org/wiki/Proprietary_software
 *
 * @link http://www.financer.com
 */
abstract class Slider {

    /**
     * Period to cache a db query
     * @var int
     */
    const DEFAULT_SLIDER = 'SliderLoanTable';
    const CACHE_PERIOD = HOUR_IN_SECONDS;
    const LOAN_TOTAL_SQL = [
        '(1+(({PREFIX}interest_rate/100) * (1/12))) monthly_rate',
        '({AMOUNT} * POW((SELECT monthly_rate), ({PERIOD} / 30))*((1-(SELECT monthly_rate))/(1-POW((SELECT monthly_rate),({PERIOD} / 30))))) annuity',
        '(IF({PREFIX}fee_flat = 0 OR {PREFIX}fee_flat IS NULL, IF ({PREFIX}fee_percent = 0 OR {PREFIX}fee_percent IS NULL, 0, ({PREFIX}fee_percent / 100) * {AMOUNT}), {PREFIX}fee_flat)) fee',
        '((SELECT fee) + (IF((SELECT annuity) IS NULL, 0,(SELECT annuity)) * ({PERIOD}/30)) +  (IF ({PREFIX}monthly_fee IS NULL, 0, monthly_fee) *  IF({PERIOD} < 30, 1, {PERIOD}/30)) - IF((SELECT annuity) IS NULL or (SELECT annuity) = 0, 0,{AMOUNT})) total_cost',
        '(((SELECT total_cost) + {AMOUNT})/ IF({PERIOD} < 30, 1, {PERIOD}/30) ) total_monthly_payback'
    ];
    /**
     * @var int
     */
    protected static $instance = 0;
    /**
     * Period to cache a db query
     * @var array
     */
    protected $query = [];
    /**
     * Slider amount
     * @var float
     */
    protected $amount = 0;
    /**
     */
    protected $singleCompany = false;
    protected $param_single_company_name = false;
    /**
     * Slider period
     * @var int
     */
    protected $period = 0;
    /**
     * Class to use for rendering result table
     * @var string
     */
    protected $tableClass = 'SliderLoanTable';
    /**
     * WordPress post type to query
     * @var string
     */
    protected $postType = 'company_single';
    /**
     * Allowed custom parameters
     * @var array
     */
    protected $paramsList = [ 'amount', 'period', 'age', 'maxAmount', 'maxPeriod', 'maxAge', 'filters', 'excludeFilters', 'newsletterGroup', 'limit', 'show_limit', 'show_all', 'title', 'tags', 'credit_score' ];

    /**
     * Allowed custom parameters that can be seen in the query url
     * @var array
     */
    protected $shareableParamsList = [ 'amount', 'period', 'age', 'limit', 'tags' ];
    /**
     * @var array
     */
    protected $paramsNoSanitize = [ 'title' ];

    protected $disableList = [];

    protected $minimal = false;

    protected $fullUrl = 'not';

    protected $creditScoreOptions =  [
        'excellent' => ['name' => 'Excellent (750-850)', 'value' => 'excellent', 'max' => 850, 'min' => 750 ],
        'good' =>  ['name' => 'Good (700-749)', 'value' => 'good', 'max' => 749, 'min' => 700 ],
        'fair' =>  ['name' => 'Fair (640-699)', 'value' => 'fair', 'max' => 699, 'min' => 640 ],
        'needs_work' =>  ['name' => 'Needs Work (639 or less)', 'value' => 'needs_work', 'max' => 639, 'min' => 0 ]
    ];
    /**
     * Fields to query
     * @var array
     */
    protected $fields = [];
    /**
     * Extra joins to add
     * @var array
     */
    protected $joins = [];
    /**
     * If the post type has a company relation
     * @var bool
     */
    protected $hasCompany = true;
    /**
     * Limit results
     * @var int
     */
    protected $limit = 50;
    /**
     * Maximum amount to show in slider options
     * @var int
     */
    protected $maxAmount = 0;
    /**
     * Minimum amount to show in slider options
     * @var int
     */
    protected $maxPeriod = 0;
    /**
     * HTML labels for translation
     * @var array
     */
    protected $htmlLabels = [];
    /**
     * List of filters to show on the UI
     * @var array|string
     */
    protected $filters = '';
    /**
     * List of filters to be checked by default
     * @var array
     */
    protected $filtersEnabled = [];
    /**
     * List of filters to exclude on the UI
     * @var array|string
     */
    protected $excludeFilters = '';
    /**
     * Pod instance
     * @var \Pods
     */
    protected $pod = null;
    /**
     * Array of all possible filters
     * @var array|string
     */
    protected $filterList = [
        'interest_free',
        'filter_gov',
        'bad_history',
        'filter_age_20',
        'weekend_payout',
        'loan_broker_filter_active',
        'non_affiliate',
        'free_early_payback',
        'quick_payout'
    ];
    /**
     * @var
     */
    protected $newsletterGroup;
    /**
     * @var array
     */
    protected $limitList = [
        10  => false,
        20  => false,
        50  => true,
        100 => false,
        -1 => false
    ];
    /**
     * @var bool
     */
    protected $limitsEnabled = false;
    /**
     * @var bool
     */
    protected $sortEnabled = true;
    /**
     * @var bool
     */
    protected $tagsEnabled = null;
    protected $credit_score_Enabled = null;
    /**
     * @var string
     */
    protected $sort = 'total-cost';
    /**
     * @var string
     */
    protected $enewsletter_groupname = "lenderupdate";
    /**
     * @var int
     */
    protected $totalResults = 0;
    /**
     * @var int
     */
    protected $total = 0;
    /**
     * @var bool
     */
    protected $isPdf = 0;
    /**
     * @var integer
     */
    protected $post_id = null;
    /**
     * @var string
     */
    protected $id = null;
    /**
     * @var string
     */
    protected $instanceId = null;
    /**
     * Pod setting storing slider comparison run count
     * @var string
     */
    protected $compareCounterSetting = null;
    /**
     * Pod instance for slider_settings
     * @var \Pods
     */
    protected $sliderSettings = null;
    /**
     * Is debug enabled
     * @var \Pods
     */
    protected $debug = false;
    /**
     * @var
     */
    protected $show_limit;
    /**
     * @var
     */
    protected $title;
    /**
     * @var bool
     */
    protected $isAjax = false;
    /**
     * @var
     */
    protected $interest_free;
    /**
     * @var
     */
    protected $filter_gov;
    /**
     * @var
     */
    protected $bad_history;
    /**
     * @var
     */
    protected $filter_age_20;
    /**
     * @var
     */
    protected $loan_broker_filter_active;
    /**
     * @var
     */
    protected $free_early_payback;
    /**
     * @var
     */
    protected $quick_payout;
    /**
     * @var
     */
    protected $non_affiliate;
    /**
     * @var
     */
    protected $loan_tags_param;
    /**
     * @var
     */
    protected $weekend_payout;
    protected $loan_company;
    protected $loan_tags;
    protected $steps = [
        'amount',
        'age',
        'period',
        'filters',
        'limits_sort',
        'submit',
    ];

    protected $show_all;

    protected $s_atts;

    protected $tags;
    protected $age = 18;
    protected $credit_score;

    protected $prefix = 'loan_datasets.d.';
    protected $prefixCompanyParentMain = '';
    protected $prefixCompanyParentTable =  't.';

    /**
     * Constructor
     *
     * @param array $atts Shortcode attributes
     */
    public function __construct( $atts ) {

        global $wp_query;
        $postType;
        if ($wp_query->query_vars) {
            $postType = $wp_query->query_vars['post_type'];
        }

        if ($postType == 'company_single' || $atts['param_single_company_name']) {
            if ($this->pod) {
                $this->pod->singleCompany = true;
            }

            $this->singleCompany = true;
        }

        if (isset($atts['disable'])) {
            $disable = explode(',', $atts['disable']);
            $this->disableList = $disable;
        }

        if (isset($atts['minimal'])) {
            if ($atts['minimal'] == 'true') {
                $this->minimal = 'true';               // hack for ajax
            } elseif ($atts['minimal'] == 'single' || (strpos($_SERVER['HTTP_REFERER'], '/query/') !== false)) {
                $this->minimal = 'single';
            }
        }

        foreach ( $atts as $key => $val ) {
            if ( strpos( $key, 'param_' ) === 0 ) {
                $param = str_replace( 'param_', '', $key );
                if ( in_array(
                    strtolower( $param ), array_map(
                        'strtolower',
                        $this->paramsList
                    )
                ) ) {
                    if ( is_array( $val ) ) {
                        array_walk( $val, 'trim' );
                        array_walk( $val, 'sanitize_key' );
                    } else if ( preg_match( '/[\w]+,[\w]+,?/', $val ) ) {
                        $val = explode( ',', $val );
                        array_walk( $val, 'trim' );
                        array_walk( $val, 'sanitize_key' );
                    } else {
                        if ( in_array( $param, $this->paramsNoSanitize ) ) {
                            $val = wp_strip_all_tags( $val );

                        } else {
                            $val = sanitize_key( $val );
                        }
                    }
                    $this->__set( $param, $val );

                }
            }
        }


        $this->instanceId = strtolower( ( new \ReflectionClass( $this ) )->getShortName() ) . '_' . self::$instance;
        $this->id = strtolower( ( new \ReflectionClass( $this ) )->getShortName() );
        if ( isset( $atts['post_id'] ) ) {
            $this->post_id = (int) $atts['post_id'];
        }
        $this->sliderSettings = pods( 'slider_settings' );
        $this->labels();

        $this->processFilters( $atts );


        if ( isset( $_GET['slider_debug'] ) && 1 === (int) $_GET['slider_debug'] && pods_is_admin( 'pods' ) ) {
            $this->debug = true;
        }

        if ( ! $this->limitList[ $this->limit ] ) {
            foreach ( $this->limitList as &$limit ) {
                $limit = false;
            }
            $this->limitList[ $this->limit ] = true;
        }

        if(!empty($atts['param_single_company_name'])){
            $this->param_single_company_name = urldecode($atts['param_single_company_name']);
        }

        if(!empty($atts['param_age'])){
            $this->age = $atts['param_age'];
        } else {
            $ageFlag = Util::specialDynamicDataCustomization('age');
            $default_loan_age_slider = (int) $this->sliderSettings->field( 'default_loan_age_slider' );
            if ($ageFlag == 'show' && $default_loan_age_slider > 0) {
                $this->age = $default_loan_age_slider;
            }
        }

        if(!empty($atts['param_credit_score'])){
            $this->credit_score = $atts['param_credit_score'];
        }

        if(!empty($atts['param_tags'])){
            $this->tags = $atts['param_tags'];
        }

        if( !empty($atts['param_sort'])){
            $this->sort = $atts['param_sort'];
        }else{
            if($this->sliderSettings->field( 'default_sorting' )){
                $this->sort = $this->sliderSettings->field( 'default_sorting' );
            }
        }
        $this->s_atts = $atts;
        $this->isAjax         = defined( 'DOING_AJAX' ) && DOING_AJAX;


        $this->sortEnabled = $this->sliderSettings->field( 'alphabetical_sorting' );
        if($this->sortEnabled){
            $this->sort = "company-name";
        }

        $this->tagsEnabled = $this->sliderSettings->field( 'show_tags_of_loan_dataset_as_dropdown' );

        $this->credit_score_Enabled = $this->sliderSettings->field( 'show_credit_score_dataset_as_dropdown' );

        if(!empty($atts['param_loan_tags'])){
            $this->loan_tags_param = $atts['param_loan_tags'];
        }

        $this->buildQuery();
    }

    /**
     *
     */
    protected function labels() {
        $this->htmlLabels = [
            'compare_now'           => __( 'How much would you like to borrow?', 'fs' ),
            'amount'                 => __( 'Loan amount', 'fs' ),
            'period'                 => __( 'Loan period', 'fs' ),
            'age'                 	 => __( 'Choose your age', 'fs' ),
            'filters'                => __( 'Optional filters:', 'fs' ),
            'interest_free'          => __( 'interest free loans', 'fs' ),
            'bad_history'            => __( 'lenders accepting bad credit history', 'fs' ),
            'loan_broker_filter_active' => __( 'Lenders only, exclude loan brokers', 'fs' ),
            'free_early_payback' => __( 'Free early payback', 'fs' ),
            'quick_payout' => __( 'Quick Payout', 'fs' ),
            'non_affiliate' => __( 'exclude non-partners', 'fs' ),
            'credit_score'           => __( 'credit score', 'fs' ),
            'filter_gov'             => __( 'loans without credit check', 'fs' ),
            'filter_age_20'          => __( 'lenders accepting age of 18-20', 'fs' ),
            'weekend_payout'         => __( 'lenders with weekend payout', 'fs' ),
            'step4'                  => __( 'Compare the best loans', 'fs' ),
            'newsletter'             => __( 'Let me know about great loan offers', 'fs' ),
            'newsletter_email'       => __( 'Enter your email', 'fs' ),
            'newsletter_name'        => __( 'Enter your name', 'fs' ),
            'display'                => __( 'Results', 'fs' ),
            'display_10'             => __( 'Top 10', 'fs' ),
            'display_20'             => __( 'Top 20', 'fs' ),
            'display_50'             => __( 'Top 50', 'fs' ),
            'display_100'            => __( 'Top 100', 'fs' ),
            'display_-1'             => __( 'All loans', 'fs' ),
            'sort'                   => __( 'Choose sorting', 'fs' ),
            'sort_company'           => __( 'Company name', 'fs' ),
            'sort_total'             => __( 'Total cost', 'fs' ),
            'sort_interest'          => __( 'Interest rate', 'fs' ),
            'sort_score'             => __( 'Best score', 'fs' ),
            'submit'                 => __( 'Compare offers', 'fs' ),
            'difference_text'        => __( 'Difference', 'fs' ),
            'total_loan_count'       => __( 'Results', 'fs' ),
            'tags'       			 => __( 'Tags', 'fs' ),
            'pdf_text'               => __( 'Download PDF', 'fs' ),
            'counter_header'         => __( 'Your comparison is completed', 'fs' ),
            'compared_help'          => __( 'We have helped people', 'fs' ),
            'compared_text'          => __( 'times to find', 'fs' ),
            'slider_type'            => __( 'loan', 'fs' ),
            'slider_type_plural'     => __( 'loans', 'fs' ),
            'better_loan_comparison' => __( 'Comparison service', 'fs' ),
            'your_loan_search'       => __( 'Your loan search', 'fs' ),
            'find_loan_for_you'      => __( 'Compare', 'fs' ),
            'refine_search'    		 => __( 'Refine your search', 'fs' ),
            'about_page'             => __( 'About', 'fs' ),
            'charity_notice'         => __( '50 cents per loan to charity', 'fs' ),
            'loan_company'           => __( 'Loan Company', 'fs' ),
            'guide_1'           => __( 'Drag the sliders on the left to find your loan', 'fs' ),
            'guide_2'           => __( 'Customize your search with the filters (optional)', 'fs' ),
            'guide_3'           => __( 'Click', 'fs' ),
            'guide_4'           => __( 'to see the best companies', 'fs' ),
            'guide_5'           => __( 'Apply by clicking on the "View offer" button.', 'fs' ),
            'how_to_compare'           => __( 'How to compare', 'fs' ),
            'loan_quiz_text'           => __( 'Loan quiz - find out if you should take a loan first', 'fs' ),
            'total-cost'           => __( 'Total-cost', 'fs' ),
            'interest-rate'           => __( 'Interest-rate', 'fs' ),
            'best-rating'           => __( 'Best-rating', 'fs' ),
            'company-name'           => __( 'Company-name', 'fs' ),
            'most-like-interest-rate'           => __( 'Most-like-interest-rate', 'fs' ),
            'yearly-best-review'           => __( 'Yearly-best-review', 'fs' ),
            'show_additional_filters' => __( 'Show additional filters', 'fs' ),
            'hide_additional_filters' => __( 'Hide additional filters', 'fs' ),
        ];

        if ($this->getMinimalStatus() == 'single') {
            $this->htmlLabels['compare_now'] = __( 'Compare loans - find a loan customized to your needs', 'fs' );
        }
    }

    /**
     * @param array $atts
     */
    protected function processFilters( array $atts ) {


        $_non_affiliate = $this->sliderSettings->field( 'filter_non_affiliate' );
        if($_non_affiliate !='1'){
            unset( $this->filterList['non_affiliate'] );
        }


        if ( true == empty( $this->filters ) ) {
            $this->filters = $this->filterList;
        } else {
            if ( ! is_array( $this->filters ) ) {
                $this->filters = array_filter( explode( ',', $this->filters ) );
            }
            $this->filters = array_filter( $this->filters,
                function ( $filter ) {
                    return in_array( $filter, $this->filterList );
                } );
        }
        $this->filters = array_unique( $this->filters );

        if ( ! empty( $this->excludeFilters ) ) {
            if ( ! is_array( $this->excludeFilters ) ) {

                $this->excludeFilters = array_filter( explode( ',', $this->excludeFilters ) );

            }


            $this->filters = array_filter( $this->filters,
                function ( $filter ) {
                    return ! in_array( $filter, $this->excludeFilters );
                } );


        }
        $this->filters = array_values( $this->filters );

        foreach ( $this->filters as $filter ) {
            if ( isset( $atts[ 'filter_' . $filter ] ) ) {
                $this->$filter = sanitize_key( $atts[ 'filter_' . $filter ] );
            }
        }
    }

    /**
     * Build pods query
     * @return void
     */
    protected function buildQuery() {

        static::initQuery();

        if ( ! is_array( $this->query['select'] ) ) {
            $this->query['select'] = [];
        }

        if ($this->period == 1 || !isset($this->period)) {
            $this->period = 360;
        }

        $select                 = self::generateLoanTotalSql( $this->amount, $this->period, $this->prefix );

        $this->query['select']  = array_merge( $this->query['select'], $select );

        $prefixCompany = str_replace('.','',$this->prefixCompanyParentTable);

        if ( false == empty( $this->interest_free ) && 1 ==
            $this->interest_free
        ) {
            $this->query['where'][] = [
                'relation' => 'OR',
                [

                    'key'     => "{$this->prefix}interest_rate",
                    'value'   => 0,
                    'compare' => '='
                ],
                [
                    'key'     => "{$this->prefix}interest_rate",
                    'compare' => 'NOT EXISTS'
                ]
            ];

            $this->query['where'][] = [
                'relation' => 'OR',
                [

                    'key'     => "{$this->prefix}fee_flat",
                    'value'   => 0,
                    'compare' => '='
                ],
                [
                    'key'     => "{$this->prefix}fee_flat",
                    'compare' => 'NOT EXISTS'
                ]
            ];

            $this->query['where'][] = [
                'relation' => 'OR',
                [

                    'key'     => "{$this->prefix}fee_percent",
                    'value'   => 0,
                    'compare' => '='
                ],
                [
                    'key'     => "{$this->prefix}fee_percent",
                    'compare' => 'NOT EXISTS'
                ]
            ];

            $this->query['where'][] = [
                'relation' => 'OR',
                [

                    'key'     => "{$this->prefix}monthly_fee",
                    'value'   => 0,
                    'compare' => '='
                ],
                [
                    'key'     => "{$this->prefix}monthly_fee",
                    'compare' => 'NOT EXISTS'
                ]
            ];


        }

        if ( false == empty( $this->tags ) ) {
            $new_tags = str_replace("%2C",",",$this->tags);
            $search_tags = explode(",", $this->tags);
            $where_tags = "";
            foreach($search_tags as $search_tag){
                if($where_tags!=''){$where_tags .= " OR "; }
                $where_tags .= "{$this->prefix}loan_tags.slug = '" . $search_tag . "'";
            }
            $this->query['where'][] = $where_tags;

        }

        if ( false == empty( $this->credit_score ) && (array_key_exists($this->credit_score, $this->creditScoreOptions))
        ) {

            $selected = $this->creditScoreOptions[$this->credit_score];

            $this->query = [
                'where'     => [
                    [
                        'key'     => "loan_datasets.d.credit_score",
                        'value'   => [$selected['min'], $selected['max']],
                        'compare' => 'BETWEEN'
                    ]
                ]
            ];
        }

        if ( false == empty( $this->filter_gov ) && 1 == $this->filter_gov
        ) {
            $this->query['where'][] = "{$this->prefixCompanyParentMain}credit_check.d.government IS NULL or {$this->prefixCompanyParentMain}credit_check.d.government = 0";
        }

        //if period slider is enable then skip the age query
        $ageFlag = Util::specialDynamicDataCustomization('age');
        if (($ageFlag == 'show') && false == empty( $this->age ) && 0 != $this->age) {
            $this->query['where'][] = [
                'key'     => "{$this->prefixCompanyParentMain}d.minalder",
                'value'   => $this->age,
                'compare' => '<=',
                'type'    => 'numeric',
            ];
        } else if (false == empty( $this->filter_age_20 ) && 1 == $this->filter_age_20) {
            $this->query['where'][] = [
                'key'     => "{$this->prefixCompanyParentMain}d.minalder",
                'value'   => 20,
                'compare' => '<=',
                'type'    => 'numeric',
            ];
        }


        if ( false == empty( $this->bad_history ) && 1 ==
            $this->bad_history
        ) {
            $this->query['where'][] = [
                'key'   => "{$this->prefixCompanyParentMain}d.bad_history",
                'value' => '1',
            ];
        }


        if ( empty($_GET)
        ) {
            $this->query['where'][] = [
                'key'   => "{$this->prefixCompanyParentMain}d.ej_partner",
                'value' => '0',
            ];
        }


        if ( '0' == $this->non_affiliate || empty($this->non_affiliate)
        ) {
            $this->query['where'][] = [
                'relation' => 'OR',
                [
                    'key'   => "{$this->prefixCompanyParentMain}d.ej_partner",
                    'value' => '1'
                ],
                [
                    'key'   => "{$this->prefixCompanyParentMain}d.ej_partner",
                    'value' => ''
                ]
            ];
        }


        if ( !empty( $this->non_affiliate ) && 1 ==
            $this->non_affiliate
        ) {
            $this->query['where'][] = [
                'key'   => "{$this->prefixCompanyParentMain}d.ej_partner",
                'value' => '0',
            ];
        }


        if ( false == empty( $this->weekend_payout ) && 1 ==
            $this->weekend_payout
        ) {
            $this->query['where'][] = [
                'key'   => "{$this->prefixCompanyParentMain}d.helgutbetalning",
                'value' => '1',
            ];
        }

        if ( false == empty( $this->loan_broker_filter_active ) && 1 ==
            $this->loan_broker_filter_active
        ) {

            $this->query['where'][] = [
                'key'   => "{$this->prefixCompanyParentMain}d.loan_broker",
                'value' => '0',
            ];
        }

        // $this->quick_payout
        if ( $this->quick_payout == '1' && $this->sliderSettings->field('quick_payout_filter_enabled') ) {
            $this->query['where'][] = [
                'key'   => "{$this->prefixCompanyParentMain}d.quick_payout",
                'value' => '1', //$this->quick_payout,
            ];
        }

        if ( $this->free_early_payback == '1' && $this->sliderSettings->field('free_early_payback_filter_enabled') ) {
            $this->query['where'][] = [
                'key'   => "{$this->prefixCompanyParentMain}d.free_early_payback",
                'value' => '1', //$this->free_early_payback,
            ];
        }


        if ( false == empty( $this->loan_company ) && 1 ==
            $this->loan_company
        ) {
            $this->query['where'][] = [
                'key'     => 'company_type.slug',
                'compare' => '!=',
                'value'   => 'loan_company',
            ];
        }

        /*$this->query['where'][] = [
            'key'   => "{$this->prefix}hide_result",
            'value' => '0',
        ];*/

        if( isset($_COOKIE['loan_tags']) && $_COOKIE['loan_tags'] != 'null' ||  isset($_GET['slug']) ){
            $loan_tags_slug = ( isset($_GET['slug']) ? $_GET['slug'] : $_COOKIE['loan_tags'] );
            $this->query['where'][] = [
                'key'     => "{$this->prefix}loan_tags.slug",
                'value'   => $loan_tags_slug,
            ];
        }

        //disable groupng only for total-cost
        //if( $this->sort != 'total-cost' ){
        //    $this->query['groupby'][] = "{$this->prefixCompanyParentTable}post_name";
        //}
    }

    protected function initQuery() {
        $this->credit_score     = (string) $this->credit_score;
        $this->period     = (float) $this->period;
        $this->limit      = (int) $this->limit;
        $this->show_limit = (int) $this->show_limit;
        $this->age = 	(int) $this->age;

        switch ( $this->sort ) {
            case 'company-name':
                $sort = 'name ASC';
                break;
            case 'interest-rate':
                $sort = 'interest_rate ASC';
                break;
            case 'best-rating':
                $sort = 'rating DESC';
                break;
            case 'total-cost':
                $sort = "total_cost ASC, company_ranking ASC";
                break;
            case 'most-like-interest-rate':
                $sort = "custom_interest_rate DESC";
                break;
            case 'yearly-best-review':
                $sort = "yearlyrating DESC";
                break;
            default:
                $sort = "total_cost ASC, company_ranking ASC";
                break;
        }

        $this->query = [
            'select'    => ! empty( $this->fields ) ? $this->fields : null,
            'limit'     => $this->limit,
            'orderby'   => $sort,
            'join'      => $this->joins,
            'calc_rows' => true,
            'where'     => [
                [
                    'key'     => "{$this->prefix}amount_range_minimum",
                    'value'   => $this->amount,
                    'compare' => '<=',
                ],
                [
                    'key'     => "{$this->prefix}amount_range_maximum",
                    'value'   => $this->amount,
                    'compare' => '>=',
                ],
                /*[
                     'key'     => "{$this->prefixCompanyParentMain}d.minalder",
                     'value'   => 18,
                     'compare' => '<=',
                 ],*/
                //"CAST(period_range_minimum AS DECIMAL(12,4)) <= {$this->period}",
                //"CAST(period_range_maximum AS DECIMAL(12,4)) >= {$this->period}",
                "CAST({$this->prefix}period_range_minimum AS DECIMAL(12,4)) <= {$this->period}",
                "CAST({$this->prefix}period_range_maximum AS DECIMAL(12,4)) >= {$this->period}",
                "{$this->prefixCompanyParentMain}post_status"               => 'publish',
                //"{$this->prefix}post_status"                                => 'publish',       //not 1
                'post_status'                                 => 'publish',        //not 2
                //'company_parent_d.post_status'                              => 'publish',       //not 3
            ]
        ];

    }

    /**
     * @param int $amount
     * @param int $period
     *
     * @return array
     */
    public static function generateLoanTotalSql( int $amount, int $period, $prefixMain ) {
        return array_map( function ( string $line ) use ( $amount, $period, $prefixMain ) {
            return str_replace( [ '{AMOUNT}', '{PERIOD}', '{PREFIX}' ], [ $amount, $period, $prefixMain ], $line );
        }, self::LOAN_TOTAL_SQL );
    }

    public function showResultsTitle($slider =  false, $query = 0, $pod = false) { //TODO Nolan bypass
        if ($this->singleCompany == false) {
            if ($slider->getMinimalStatus() == 'true') {
                echo '<h2 class="slider_results_header">' . __('Top Picks', 'fs') . '</h2>';
                echo '<div class="slider-message">' . __('Showing', 'fs') . ' <span class="found_total">' . count($query) . '</span> ' . __('out of', 'fs') . ' <strong>' . $pod->unfiltered_results . '</strong> ' . __('offers', 'fs') . '</div>';
            } else {
                echo '<div class="slider-message">' . __('Showing', 'fs') . ' <span class="found_total">' . count($query) . '</span> ' . __('offers based on your search.', 'fs') . '</div>';
            }
        }
    }

    public static function increaseInstanceCounter() {
        self::$instance ++;
    }

    public function setMinimalStatus( string $minimal ) {
        $this->minimal = $minimal;
    }

    /**
     * @return string
     */
    public function getMinimalStatus(): string {
        return $this->minimal;
    }

    /**
     * @return string
     */
    public function getInstanceId(): string {
        return $this->instanceId;
    }

    /**
     * @return \Pods
     */
    public function getPod(): \Pods {
        return $this->pod;
    }

    /**
     * @return float
     */
    public function getAmount(): float {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount( int $amount ) {
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getPeriod(): int {
        return $this->period;
    }

    public function setAge( int $age ) {
        $this->age = $age;
    }

    public function getAge(): int {
        return $this->age;
    }

    /**
     * @param int $period
     */
    public function setPeriod( int $period ) {
        $this->period = $period;
    }

    /**
     * @return array
     */
    public function getQuery(): array {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getPostType(): string {
        return $this->postType;
    }

    /**
     * @return string
     */
    public function render(): string {
        ob_start();
        echo '<div>';
        static::getResults();

        ob_start();
        $counters = ob_get_clean();

        static::header();
        static::renderNewsletter();
        static::table();
        echo $counters;
        static::footer();
        static::status();
        $deps = [
            'jquery-ui-widget',
            'jquery-ui-mouse',
            'jquery-ui-slider',
            'jquery-ui-tabs',
            'jquery-ui-dialog',
            'jquery-touch-punch',
            'jquery-effects-core',
        ];
        foreach ( $deps as $script ) {
            wp_enqueue_script( $script );
        }
        wp_enqueue_script( 'dummy', Plugin::GetUri( 'js/dummy.js' ), [ 'jquery' ] );
        wp_add_inline_script( 'dummy', static::renderJs() );
        self::$instance ++;

        return ob_get_clean();
    }

    /**
     * @return \Pods
     */
    public function getResults() {
        $this->pod          = pods( $this->postType, $this->query );
        $this->total        = $this->pod->total();
        $this->totalResults = $this->pod->total_found();
        static::sort();

        return $this->pod;
    }

    protected function sort() {

    }

    protected function renderCounters() {

        global $wp_query;
        $postType;
        if ($wp_query->query_vars) {
            $postType = $wp_query->query_vars['post_type'];
        }

        if ($postType != 'company_single') {

            if (!$this->isAjax) {
            }
        }
    }

    /**
     *
     */
    protected function renderCounterItems() {
        static::renderDifference();
        static::renderTotalCount();
    }

    /**
     * @return mixed
     */
    abstract protected function renderDifference();

    /**
     *
     */
    protected function renderTotalCount() {

    }

    /**
     * @return void
     */
    protected function renderSharing() {
        $params = [];
        array_walk(
            $this->shareableParamsList,
            function ( $item ) use ( &$slider, &$params ) {
                $params[ 'param_' . $item ] = $this->$item;
            }
        );
        array_walk(
            $this->filters,
            function ( $item ) use ( &$slider, &$params ) {
                $params[ 'filter_' . $item ] = $this->$item;
            }
        );
        $query = str_replace( '=', '/', http_build_query( $params, null, '/' ) );

        echo Social::render( [ 'id' => $this->post_id, 'after' => 'query/' . $query ] );
    }

    /**
     *
     */
    protected function header()
    {
        $limits_sort = '';
        $steps = array_flip($this->steps);


        $sliderClass = '';
        if ($this->minimal == 'true') {
            $sliderClass = ' minimal_slider';
            unset($steps['filters']);
        } elseif ($this->minimal == 'single') {
            $sliderClass = ' single-slider';
            unset($steps['filters']);
        }

        //mortgage, credit cards or saving account should always appear period slider
        if ($this->tableClass != self::DEFAULT_SLIDER) {
            unset($steps['age']);
        } else {
            $periodFlag = Util::specialDynamicDataCustomization('period');
            if ($periodFlag == 'hide') {
                unset($steps['period']);
            }

            $ageFlag = Util::specialDynamicDataCustomization('age');
            if ($ageFlag == 'hide') {
                unset($steps['age']);
            }
        }

        if (!$this->tagsEnabled && isset($steps['loan_tags'])) {
            unset($steps['loan_tags']);
        }
        if ((!$this->credit_score_Enabled && isset($steps['credit_score'])) || ($this->sliderSettings->field('enable_credit_score') != '1')) {
            unset($steps['credit_score']);
        }

        $steps = $this->_disableFilters($steps);

        if (isset($steps['limits_sort'])) {
            ob_start();
            $this->_runStep('limits_sort');
            $limits_sort = ob_get_clean();
            unset($steps['limits_sort']);
            $this->steps = array_keys($steps);
        }
        $form = static::form($sliderClass);
        $preHeader = static::preHeader();
        $afterHeader = static::afterHeader();
        $loanQuiz = static::loanQuiz();
        $counter = $this->sliderSettings->field($this->compareCounterSetting);
        $title = get_the_title();

        global $wp_query;
        $postType;
        if ($wp_query->query_vars) {
            $postType = $wp_query->query_vars['post_type'];
        }

        if ($postType == 'company_single' || $this->param_single_company_name) {
            $this->pod->singleCompany = true;
            $this->singleCompany = true;
        }

        if ($this->singleCompany == false) {
            echo <<<HTML
<style type="text/css">
    .ui-slider {
        opacity: 0;
    }
</style>
<div class="wrap w100{$sliderClass}">
<div class="col-wide">
<div class="blogBox widget widget_text loan-search">
            $preHeader
            <form action="" method="post">
                <div class="fl_l w_70 slider_{$this->id}">
				<div class="slider-form">
				<h3>{$this->htmlLabels['compare_now']}</h3>
                $form
				</div>
            </form>
            $loanQuiz
</div>
</div></div>
</div>
</div>
</div>
</div>
</div>
HTML;
        }
        echo <<<HTML
<section>
<div class="boxWrap compare-results slider-results{$sliderClass}">
<div class="wrap">
<div class="entrySide">
HTML;
        if	($this->minimal != 'true') {
            /*echo <<<HTML
                        <h2 class="secondtitle">
    HTML;
            if (!empty($_GET['slug'])) {
                $slug = get_term_by('slug', $_GET['slug'], 'loan_tags');
                $tag_title = $slug->name;
                echo <<<HTML
                {$tag_title}
    HTML;
            } elseif (!empty($this->title)) {
                echo <<<HTML
                {$this->title}
    HTML;
            } else {
                echo <<<HTML
                {$this->htmlLabels['find_loan_for_you']} {$this->htmlLabels['slider_type_plural']}
    HTML;
            }
            echo <<<HTML
                </h2>
                <i class="arrow"></i>
    HTML;*/
            echo $limits_sort;
        }
    }

    /**
     * @return mixed
     */
    protected function form($sliderClass) {

        ob_start();
        echo <<<HTML
		<div class="stepbox{$sliderClass}" id="{$this->instanceId}_form">
HTML;
        $this->runSteps();
        echo <<<HTML
		</div>
HTML;

        return ob_get_clean();
    }

    /**
     *
     */
    public function runSteps() {
        foreach ( $this->steps as $step ) {
            $this->_runStep( $step );
        }
    }

    private function _disableFilters($steps) {

        if ($this->minimal == 'true') {
            $minimalRemoveList = $this->paramsList;
            //remove amount value
            if (($key = array_search('amount', $minimalRemoveList)) !== false) {
                unset($minimalRemoveList[$key]);
            }

            $this->disableList = array_merge($minimalRemoveList, $this->disableList);
        }

        foreach ($this->disableList as $filter) {
            $filter = trim($filter);
            if (in_array($filter, $this->disableList)) {
                unset($steps[$filter]);
            }
        }
        return $steps;
    }

    /**
     * @param string $step
     */
    private function _runStep( string $step ) {
        $step   = 'step' . ucfirst( $step );
        $header = $step . 'Header';
        $footer = $step . 'Footer';
        if ( method_exists( $this, $header ) ) {
            static::$header();
        } else {
            static::stepHeader( $step );
        }
        if ( method_exists( $this, $step ) ) {
            static::$step();
        }
        if ( method_exists( $this, $footer ) ) {
            static::$footer();
        } else {
            static::stepFooter( $step );
        }
    }

    /**
     * @param string $step
     */
    protected function stepHeader( string $step ) {
        echo <<<HTML
<div class="step $step">
HTML;
    }

    /**
     * @param string $step
     */
    protected function stepFooter( string $step ) {
        echo <<<HTML
</div>
HTML;
    }

    /**
     * @return string
     */
    protected function preHeader(): string {
        return '';
    }

    protected function afterHeader(): string {
        return '';
    }

    protected function loanQuiz(): string {
        return '';
    }

    /**
     * @internal param bool $empty
     *
     * @internal param bool $wrapper
     */
    protected function table() {

        $visitsMost = (object) ['index' => false, 'value' => 0];
        $approval_rateMost = (object) ['index' => false, 'value' => 0];
        $lowestCostMost = (object) ['index' => false, 'value' => 0];
        $ratedMost = (object) ['index' => false, 'value' => 0];

        foreach ($this->pod->rows as $index => $company) {


            $total_ratings = do_shortcode('[total_rating id='.$company->ID.']');
            $company->total_rating = $total_ratings;

            //skip companies with total_reviews less than 5
            if ($company->total_rating < 5) {
                continue;
            }

            $company->open_hours = Util::sortCompanyOpenHours(
                $company->open_weekdays,$company->close_weekdays,
                $company->open_saturday, $company->close_saturday,
                $company->open_sunday, $company->close_sunday
            );

            if (is_numeric($company->rating) && $company->rating > $ratedMost->value) {
                $ratedMost->index = $index;
                $ratedMost->value = $company->rating;
            }

            if (is_numeric($company->approval_rate) && $company->approval_rate > $approval_rateMost->value) {
                $approval_rateMost->index = $index;
                $approval_rateMost->value = $company->approval_rate;
            }
            if (is_numeric($company->visits) && $company->visits > $visitsMost->value) {
                $visitsMost->index = $index;
                $visitsMost->value = $company->visits;
            }
            if (is_numeric($company->total_cost)) {
                if ($lowestCostMost->index === false) {
                    $lowestCostMost->index = $index;
                    $lowestCostMost->value = $company->total_cost;
                } else if ($company->total_cost < $lowestCostMost->value) {
                    $lowestCostMost->index = $index;
                    $lowestCostMost->value = $company->total_cost;
                }
            }
        }

        if ($lowestCostMost->index !== false && $lowestCostMost->value > 0) {
            $this->pod->rows[$lowestCostMost->index]->most_lowest_cost = $lowestCostMost->value;
        }
        if ($visitsMost->index !== false && $visitsMost->value > 0) {
            $this->pod->rows[$visitsMost->index]->most_visited = $visitsMost->value;
        }
        if ($approval_rateMost->index !== false && $approval_rateMost->value > 0) {
            $this->pod->rows[$approval_rateMost->index]->most_approval_rate = $approval_rateMost->value;
        }
        if ($ratedMost->index !== false && $ratedMost->value > 0) {
            $this->pod->rows[$ratedMost->index]->most_overall_rating = $ratedMost->value;
        }


        global $wp_query;
        $this->pod->unfiltered_results = $this->pod->total_found();
        $show_all_ui = ! is_null( $this->show_limit ) && $this->pod->total() > $this->show_limit;
        if ( $show_all_ui && empty( $this->show_all ) && ! $this->isAjax ) {
            $this->pod->rows        = array_slice( $this->pod->rows, 0, $this->show_limit );
            $this->pod->total       = $this->show_limit;
            $this->pod->total_found = $this->show_limit;
        }

        if ($this->minimal == 'single') {
            $this->_runStep('filters');
        }
        if ( ! $this->isAjax ) {
            echo <<<HTML
			<div class="frm_full"></div>
		  <div id="{$this->instanceId}" class="tw-bs table_cont">
HTML;
        }


        $old_in_the_loop       = $wp_query->in_the_loop;
        $wp_query->in_the_loop = true;
        if( $this->loan_tags_param == 1 ){
            $class                 = '\Financer\FilterSlider\Table\\SliderLoanTagsTable';
        }else{
            $class                 = '\Financer\FilterSlider\Table\\' . $this->tableClass;
        }

        /* @var $class \Financer\FilterSlider\Interfaces\TableInterface */
        $class::build( $this->pod, $this );
        $wp_query->in_the_loop = $old_in_the_loop;
        if ( $show_all_ui && $this->singleCompany == false && !empty($this->getMinimalStatus()) && $this->getMinimalStatus() != 'true' && $this->getMinimalStatus() != 'single')  :
            if ( ( $this->isAjax && ! empty( $this->show_all ) ) || ( ! $this->isAjax && empty( $this->show_all ) ) ):
                if ( empty( $this->show_all ) ):
                    ?>
                    <div class="show-all-holder"><a href="<?php echo $this->fullUrl; ?>" class="show-all button small"><?php echo __( 'Show All', 'fs' ) .'&nbsp;'. $this->pod->unfiltered_results .'&nbsp;'.  __( 'Offers', 'fs' ); ?></a></div>
                <?php else: ?>
                    <div class="show-all-holder"><a class="show-all button small"><?php _e( 'Collapse Items', 'fs' ); ?></a></div>
                <?php
                endif;
            endif;
        else :
            if ($this->pod->total > 0 && $this->singleCompany == false && !empty($this->getMinimalStatus()) && $this->getMinimalStatus() != 'single') :
                ?>
                <div class="show-all-holder"><a href="<?php echo $this->fullUrl; ?>" class="show-all button small"><?php echo __( 'Show All', 'fs' ) .'&nbsp;'. $this->pod->unfiltered_results .'&nbsp;'.  __( 'Offers', 'fs' ); ?></a></div>
                <div class="loadmore_loader">Loading...</div>
            <?php
            endif;
        endif;
        echo '</div></div></div></div></div></section>';
    }

    /**
     *
     */
    public function footer() {

        if ( is_front_page() ) {
            echo <<<HTML
<div class="wrap">
    <div class="col-wide">
        <div class="entry">
HTML;
        }
    }

    /**
     *
     */
    protected function status() {
        $label        = __( 'Calculating and preparing your results.', 'fs' );
        $loader_image = home_url( '/wp-content/themes/financer/graph/loader.gif' );
        echo <<<HTML
	<div class="status_bar">
	   <p>
		  $label<img class="loader"
			 src="$loader_image" width="248" height="248">
		  </p>
	   </div>
HTML;
    }

    /**
     *
     */
    public function renderJs(): string {
        $output = static::renderJsMaps();
        $jsInit = static::renderJsInit();
        $jsData = static::renderJsData();
        $jsAjax = static::renderJsAjax();
        /**
         * @var string $output
         */
        /**
         * @var string $jsInit
         */
        /**
         * @var string $jsData
         */
        /**
         * @var string $jsAjax
         */

        $output .= <<<JS
        
	   (function($) {
            $(function ($) {
	          if (typeof jQuery !== 'undefined' && typeof jQuery.ui !== 'undefined') {

				 	function scrollToResults(){
						var results = $('.frm_full').first();
					    if (results.length) {
							if (window.matchMedia('(max-width: 1173px)').matches) {
								$('html, body').animate({
								scrollTop: (parseInt(results.offset().top))
                                        }, 1000,'easeOutQuart');
                            }
					    }
					}

					function toggle_loan_tags(){
					    selected = '';
					    $('#param_tags').on('change', function() {
					        selected = $('#param_tags').val();
					        $( ".sortable-item" ).fadeOut();
					        $( "." + selected ).parents( ".sortable-item" ).fadeIn(400, 'swing');
					    });
					}

					function change_sort(){
					    divs = $(".sortable-item"),
					    selected = '';
					     $('.sort_type_trigger').click(function () {
					        selected = $(this).attr('data-toggle'); //'company', 'total', 'interest', 'score'
					        $('.sort_type_trigger').removeClass('current');
					        $(this).addClass('current');
					    });
					}

					$('.sort-item').each(function( index ) {
				        }).promise()
				        .done( function() {
				            change_sort();
				    });

					function wrap_top_three() {
						$('.sortable-item:lt(3)').wrapAll('<div class="top_options"></div>');
					}
						
					var param;
					$jsInit;
					function generate_table_$this->instanceId(param) {
						$jsData;
						$jsAjax;
					}

					var expDate = new Date();

					expDate.setTime(expDate.getTime() + ( 525600 * 60 * 1000)); // add 1 
					$('.stepFilters input').on('change',function(){
						if ( $(this).is(':checked')) {
				            $(this).attr('checked');
				            $.cookie( $(this).attr('name') , 1, { path: '/', expires: expDate });
				        }else{
				        	$(this).val(0);
				            $(this).removeAttr('checked');
				            $.cookie( $(this).attr('name') , 0, { path: '/', expires: expDate });
				        }
					});

					function starRating(){
						var ratings0 = $('.item-table .rating0');
						for (var i = 0; i < ratings0.length; i++) {
			              var r = new SimpleStarRating(ratings0[i]);
			            }
					}
				 }
				 else {
					setTimeout(init, 50);
				 }
			    });
	    })(jQuery);

JS;

        if ($this->minimal == 'single') {
            $output .= <<<JS
			   (function($) {
				$(function() {
						 if (typeof jQuery !== 'undefined' && typeof jQuery.ui !== 'undefined') {
								var results = $('.frm_full').first();
								if (results.length) {
									if (window.matchMedia('(max-width: 1173px)').matches) {
										$('html, body').animate({
										scrollTop: (parseInt(results.offset().top))
												}, 1000,'easeOutQuart');
									}
								}
							 }
				 else {
					setTimeout(init, 50);
			 }
			});
		})(jQuery);
JS;
        } else if($this->minimal == 'true') {
            $output .= '';
        }

        return $output;
    }

    /**
     * @return string
     */
    public function renderJsMaps(): string {
        $output = '';
        foreach ( static::generateJsMaps() as $name => $map ) {
            $list = [];
            foreach ( $map as $value => $text ) {
                $list[] = [
                    'text'  => $text,
                    'value' => $value,
                ];
            }
            $jsonList = wp_json_encode( $list );
            $output .= <<<JS
	var $name = $jsonList;
JS;
        }

        return $output;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function generateJsMaps( $params = [] ): array {
        $params = array_merge_recursive(
            [
                'select' => [
                    "MAX({$this->prefix}amount_range_maximum) AS loan_amount_range_maximum",
                    "MIN({$this->prefix}amount_range_minimum) AS loan_amount_range_minimum",
                    "MAX(CAST({$this->prefix}period_range_maximum AS DECIMAL(12,4))) AS loan_period_range_maximum",
                    "MIN(CAST({$this->prefix}period_range_minimum AS DECIMAL(12,4))) AS loan_period_range_minimum",
                    // "MAX(CAST({$this->prefix}age_range_maximum AS DECIMAL(12,4))) AS loan_age_range_maximum",
                    //"MIN(CAST({$this->prefix}age_range_minimum AS DECIMAL(12,4))) AS loan_age_range_minimum",
                ],
                'limit'  => - 1,
                'where'  => [
                    [
                        'key'   => 'post_status',
                        'value' => 'publish',
                    ],
                ],
            ], $params
        );

        if ( ! empty( $this->maxAmount ) ) {
            $params['where'][] = [
                'key'     => "{$this->prefix}amount_range_maximum",
                'value'   => $this->maxAmount,
                'compare' => '<=',
            ];
        }
        if ( ! empty( $this->maxPeriod ) ) {
            $params['where'][] = "CAST({$this->prefix}period_range_maximum AS DECIMAL(12, 4)) <= {$this->maxPeriod}";
        }

        if ( ! empty( $this->maxAge ) ) {
            $params['where'][] = "CAST({$this->prefix}period_range_maximum AS DECIMAL(12, 4)) <= {$this->maxPeriod}";
        }

        $pod     = pods( $this->postType, $params );
        $periods = [];
        $amounts = [];
        $ages = [];
        if ( $pod->fetch() ) {
            $amount_min = (int) $pod->field( 'loan_amount_range_minimum' );
            $amount_max = (int) $pod->field( 'loan_amount_range_maximum' );

            $amounts_list = $this->amounts;
            if ( empty( $amounts_list ) ) {
                $amounts_list = $this->sliderSettings->field( 'loan_amounts' );
            }
            if ( ! is_array( $amounts_list ) ) {
                $amounts_list = array_filter( array_map( 'absint', array_map( 'trim', explode( ',', $amounts_list ) ) ) );
            }
            $amounts_list = array_filter( array_map( 'absint', $amounts_list ), function ( $amount ) use ( $amount_min, $amount_max ): bool {
                return $amount <= ( $this->maxAmount ? $this->maxAmount : $amount_max ) && $amount >= $amount_min;
            } );
            foreach ( $amounts_list as $amount ) {
                $amounts[ $amount ] = Util::moneyFormat( $amount ) . ' ' . __( 'usd', 'fs' );
            }
            $period_min = (int) $pod->field( 'loan_period_range_minimum' );
            $period_max = (int) $pod->field( 'loan_period_range_maximum' );

            $age_min = (int) $this->sliderSettings->field( 'age_range_minimum' );
            $age_max = (int) $this->sliderSettings->field( 'age_range_maximum' );

            $periods_list = $this->days;

            if ( empty( $periods_list ) ) {
                $periods_list = $this->sliderSettings->field( 'loan_periods' );
            }
            if ( ! is_array( $periods_list ) ) {
                $periods_list = array_filter( array_map( 'absint', array_map( 'trim', explode( ',', $periods_list ) ) ) );
            }
            $periods_list = array_filter( array_map( 'absint', $periods_list ), function ( $period ) use ( $period_min, $period_max ): bool {
                return $period <= ( ! empty( $this->maxPeriod ) ? $this->maxPeriod : $period_max ) && $period >= $period_min;
            } );
            foreach ( $periods_list as $period ) {
                $periods[ $period ] = Util::getPeriod( $period );
            }

            for ($age = $age_min; $age <= $age_max; $age++) {
                $ages[ $age ] =  $age . ' ' . __( 'Year', 'fs' );
            }

            ksort( $periods );
            ksort( $amounts );
            ksort( $ages );
        }

        return [
            $this->instanceId . '_amountMap' => $amounts,
            $this->instanceId . '_periodMap' => $periods,
            $this->instanceId . '_ageMap' => $ages,
        ];
    }

    /**
     * @return string
     *
     */
    protected function renderJsInit(): string {
        Social::registerJs();
        $sliderInitJs = static::renderSliderInitJs();
        $reportJs     = self::renderReportJs();
        $detailsJs    = self::renderDetailsJs();
        $preloaderJs  = self::renderPreloaderJs();
        $jsData = static::renderJsData();
        $hide_text    = __( 'Collapse Items', 'fs' );
        $show_text    = __( 'Show All', 'fs' );
        $limitJs      = '';
        $url = get_site_url( null, '/wp-admin/admin-ajax.php?' . $this->getParamString() );
        /**
         * @var int $period
         */

        /**
         * @var int $amount
         */

        if ( ! empty( $this->show_limit ) ) {
            $limitJs = <<<JS
$('#{$this->instanceId} .item-table .item-row:not(.details):not(.tag-example):not(.more-information-row):gt({$this->show_limit})').parent('.company-listing').hide();
$('#{$this->instanceId} ~ .msg').hide();
$('#{$this->instanceId}').on('click', '.show-all',function () {
	
    var data = $('.stepFilters input:checkbox').map(function() {
	        value = this.checked ? this.value : "0";
	        if (value != '0') {
	            value = 1
	         return { name: this.name, value: value };   
	        }
		});
        if($(this).attr('data-toggle')){
        	data.push({name: "param_sort", value: $(this).attr('data-toggle')});
    	}


        $('#{$this->instanceId}_form .ui-slider').each(function () {
            var name = $(this).data('name');
            var value = $(this).slider('value');
            var value_object = window['{$this->instanceId}_' + name.replace('param_', '') + 'Map'][value];
            data.push({
                name: name,
                value: value_object ? value_object.value : 0
            });
        });
    
            data = $.param(data);

            
            url = $(this).data("slider-url") + '/' + data.replace(/[\&\=]/g, '/');
        
            console.log('data', data, url);

        //history.replaceState(null, null, url);
        //window.open(url,"_self")

})



JS;
        }

        // if ($this->minimal == false) {
        $query = <<<JS
            
    $('.show-all-holder').click(function (e) {
	   $('.loadmore_loader').show();	
       $("#submitSliders").trigger("click");
        e.preventDefault();
        return false;
    });

    $('.slider-results .stepFilters input').click(function (e) {
       $("#submitSliders").trigger("click");
        //e.preventDefault();
        //return false;
    });


    $('#{$this->instanceId}_form .get_results, .sort_type_trigger').click(function (e) {
    	var expDate = new Date();
    	var gdprStatus = 'Rejected';

		expDate.setTime(expDate.getTime() + ( 525600 * 60 * 1000)); // add 1 
    	$('.stepFilters input').each(function(){
    		if ( $(this).is(':checked')) {
	            $(this).val(1);
	            $(this).attr('checked');
	            $.cookie( $(this).attr('name') , 1, { path: '/', expires: expDate });
	        }else{
	        	$(this).val(0);
	            $(this).removeAttr('checked');
	            $.cookie( $(this).attr('name') , 0, { path: '/', expires: expDate });
	        }
    	});
 		
        generate_table_$this->instanceId( $(this).attr('data-toggle') );
        //$('#{$this->instanceId} .show-all-holder').remove();
        $('.counterWrapper').parent().addClass('hidden');
        //var data = $('#{this->instanceId}_form input:checkbox').map(function() {
	    var data = $('.stepFilters input:checkbox').map(function() {
	        value = this.checked ? this.value : "0";
	        if (value != '0') {
	            value = 1
	         return { name: this.name, value: value };   
	        }
		});
        if($(this).attr('data-toggle')){
        	data.push({name: "param_sort", value: $(this).attr('data-toggle')});
    	}


        $('#{$this->instanceId}_form .ui-slider').each(function () {
            var name = $(this).data('name');
            var value = $(this).slider('value');
            var value_object = window['{$this->instanceId}_' + name.replace('param_', '') + 'Map'][value];
            data.push({
                name: name,
                value: value_object ? value_object.value : 0
            });
        });
        
        data = $.param(data);

        var url = window.location.href;
        var urlParts = window.location.href.split('query');
        var loanType = 'false';
        if (urlParts.length > 1) {
            loanType = urlParts[1];
            var paramList = loanType.split('/')
            loanType = paramList[1];
            
            url = urlParts[0];
        }
        
        
        
        var fullurl = $(this).data("slider-url");
        
        if (fullurl) {
            url = fullurl + data.replace(/[\&\=]/g, '/');
            //console.log('full', url);
            window.history.pushState("", "", url);
            window.open(url,"_self")
        } else {
            url = url + 'query/' + loanType + '/' + data.replace(/[\&\=]/g, '/');
            window.history.pushState("", "", url);
        }
        
        //history.replaceState(null, null, fullurl);

        e.preventDefault();
        return false;
    });
JS;
        //  }

        return <<<JS
jQuery(function ($) {
    $limitJs;
    $sliderInitJs;
    $query;
    $reportJs;
    $detailsJs;
});
(function ($) {
    $(window).load(function () {
        var counterWrapper = $('.counterWrapper');
        var counters = counterWrapper
            .find('div.counter')
             .find('span.counter')
             .parent();
        if (counters.children().length) {
            counterWrapper.parent().removeClass('hidden');
            counters.hide();
            counters
                .show()
                .children('span')
                .fadeIn(function(){
                	$('.navigation-content').removeClass('stick');
    									scrollTableContents();
                });
        }

    });

})(jQuery);

$preloaderJs
JS;
    }

    /**
     * @return string
     */
    public function renderSliderInitJs() {
        return <<<JS
$('#{$this->instanceId}_form .ui-slider').each(function () {
    var self = $(this);
    var defaultValue = parseFloat($(this).data('defaultValue'));

    var name = $(this).data('name').replace('param_', '');
    var map = window['{$this->instanceId}_' + name + 'Map'];
    var display = $(this).data('display');

    map.every(function (v, i) {
        if (parseFloat(v.value) == parseFloat(defaultValue)) {
            defaultValue = map.length > 0 ? i : 0;
            return false;
            
        }
        else if (i == map.length - 1) {
            defaultValue = 0;
        }
        return true;
    });

    $(this).slider({
        range: 'min',
        min: 0,
        max: map.length - 1,
        value: defaultValue,
        slide: function (event, ui) {
            $(self).parent().parent().find('.' + display).html(map[ui.value].text);

        },
        create: function (event, ui) {
            $(event.target).fadeTo(1000, 1);
        }
    });
    $(this).prevAll('.slider_cont').find('.minus_arrow').click(function () {
        self.slider('value', self.slider('value') - self.slider("option", "step"));
        if (map[self.slider('value')]) {
            $(self).parent().parent().find('.' + display).html(map[self.slider('value')].text);
        }
        return false;
    });
    $(this).prevAll('.slider_cont').find('.plus_arrow').click(function () {
        self.slider('value', self.slider('value') + self.slider("option", "step"));
        if (map[self.slider('value')]) {
            $(self).parent().parent().find('.' + display).html(map[self.slider('value')].text);
        }
        return false;
    });
    if (map[defaultValue]) {
        $(self).parent().parent().find('.' + display).html(map[defaultValue].text);
    } 
});
	
JS;
    }

    /**
     * @return string
     */
    public static function renderReportJs(): string {
        $reportConfirmTitle   = __( 'Wrong data', 'fs' );
        $reportConfirmMessage = __( 'Are you sure you wish to report this data?', 'fs' );
        $reportSuccessTitle   = __( 'Data reported', 'fs' );
        $reportSuccess        = __( 'Thank you for your contribution', 'fs' );
        $yesBtn               = __( 'Yes', 'fs' );
        $noBtn                = __( 'No', 'fs' );
        $closeBtn             = __( 'Close', 'fs' );
        $reportUrl            = get_site_url( null, '/wp-admin/admin-ajax.php' );
        $errorJs              = self::renderCacheErrorJs();
        wp_enqueue_script( 'jquery-ui-dialog' );

        return <<<JS
$(document).on('click', '.table_cont .report', function () {
    var self = this;
    $('<div></div>', {
        "class": 'reportModal'
    }).dialog({
        modal: true,
        title: '{$reportConfirmTitle}',
        open: function () {
            $(this).html('{$reportConfirmMessage}');
        },
        buttons: {
            "$noBtn": function () {
                $(this).dialog("close");
            },
            "$yesBtn": function () {
                $(this).dialog("close");
                $.ajax({
                    url: '{$reportUrl}',
                    type: "POST",
                    data: {
                        action: "filter_slider_report",
                        id: $(self).closest('.item-row').data('id')
                    },
                    success: function (data) {

                        if (!data.length) {
                            $('<div></div>', {
                                "class": 'reportModal'
                            }).dialog({
                                modal: true,
                                title: '{$reportSuccessTitle}',
                                open: function () {
                                    $(this).html('{$reportSuccess}');
                                },
                                buttons: {
                                    "$closeBtn": function () {
                                        $(this).dialog("close");
                                    }
                                }
                            });
                        }
                        else {
                            $errorJs
                        }
                    }
                })
            }
        }
    });
    return false;
});
JS;
    }

    /**
     * @return string
     */
    public static function renderCacheErrorJs(): string {
        $clearCacheUrl = get_site_url( null, '/wp-admin/admin-ajax.php' );
        $errorTitle    = __( 'Something went wrong', 'fs' );
        $countDownText = __( 'Reloading the page in %d seconds', 'fs' );

        return <<<JS
$.post('$clearCacheUrl', {
                        'action': 'purge_cache'
                    },
                    function () {
	                    $(".status_bar").fadeOut();
	                    var text = '$countDownText';
	                    var time = 3;
	                    var modal = $('<div></div>');
	                    modal.dialog({
                            modal: true,
                            closeOnEscape: false,
                            title: '{$errorTitle}',
                            create: function () {
		                    $(this).dialog('instance').uiDialogTitlebarClose.remove();
	                    },
                            open: function () {
		                    $(this).text(text.replace('%d', time));
	                    }
                        });
                        (function tick() {
	                        if (-1 == time) {
		                        modal.dialog('instance').uiDialog.fadeTo('slow', 0, function () {
			                        modal.dialog('close');
			                        window.location.reload();
		                        });
	                        }
	                        else {
		                        modal.text(text.replace('%d', time));
		                        setTimeout(tick, 1000);
		                        time--;
	                        }

                        })();
                    }
                );
JS;
    }

    /**
     * @return string
     */
    public static function renderDetailsJs() {
        return <<<JS
$(document).on('click', '.table_cont .fa', function () {
        var detail = $(this).closest('.item-row').next('.details');
        detail.toggleClass('expanded');
        $(this).toggleClass('fa-plus', !detail.hasClass('expanded'));
        $(this).toggleClass('fa-minus', detail.hasClass('expanded'));
    });
JS;

    }

    /**
     * @return string
     */
    public function renderPreloaderJs(): string {
        return <<<JS
jQuery(window).load(function () {
    (function check() {
        if (!jQuery('#{$this->instanceId}_form .get_results').is(':visible')) {
            jQuery('#{$this->instanceId}_form .get_results').fadeIn();
            setTimeout(check)
        }
    })();
});
JS;

    }

    /**
     * @return string
     *
     */
    public function renderJsData(): string {
        return <<<JS
		var data = {};
(function ($) {
    var dataArray = $('.stepFilters :input').serializeArray();
    if ( typeof param !== 'undefined' && param != '' ) {
	    if(param){
	    	dataArray.push({name: "param_sort", value: param});
		}
	}else{
		if($('.current').attr('data-toggle')){
	    dataArray.push({name: "param_sort", value: $('.current').attr('data-toggle')});
		}
	}
	
	var form = jQuery('#{$this->instanceId}_form');
    form.find('.ui-slider').each(function () {
        var name = $(this).data('name');
        var value = $(this).slider('value');
        var value_object = window['{$this->instanceId}_' + name.replace('param_', '') + 'Map'][value];
        dataArray.push({
            name: name,
            value: value_object ? value_object.value : 0
        });
    });
    dataArray.forEach(function (item) {
        data[item.name] = item.value;
    });

    data['action'] = 'filter_slider_{$this->id}';
    data['email'] = form.find('.email').val();
    data['name'] = form.find('.name').val();
})(jQuery);

JS;
    }

    /**
     * @return string
     * @internal param Slider $slider
     *
     */
    public function getParamString(): string {
        $params = [];
        array_walk(
            $this->paramsList,
            function ( $item ) use ( &$slider, &$params ) {
                $params[ 'param_' . $item ] = $this->$item;
            }
        );
        $params['post_id'] = get_the_ID();
        if ( $this->debug ) {
            $params['pods_debug_sql'] = 1;
        }

        return http_build_query( $params );
    }

    /**
     * @return string
     * @internal param $slider
     *
     */
    protected function renderJsAjax(): string {

        if ($this->minimal == 'true') {
            return false;
        }

        $url     = get_site_url( null, '/wp-admin/admin-ajax.php?' . $this->getParamString() );
        $error   = __( 'Error - try another browser !:', 'fs' );
        $errorJs = self::renderCacheErrorJs();
        $admin   = wp_json_encode( current_user_can( 'administrator' ) );
        $js      = <<<JS
(function ($) {
    $.ajax({
        url: '{$url}',
        type: "POST",
        data: data,
        beforeSend: function () {
            $(".status_bar").fadeIn();
        },
        success: function (data) {
            if (typeof data == 'object') {
                $(".status_bar").fadeOut();
                $('#{$this->instanceId}').slideUp(1000, function () {
                    $(".steg4 #newsletter_status").remove();
                    $('#{$this->instanceId} ~ .msg').hide().removeClass('hidden').fadeIn();
                    if (data.newsletter !== false) {
                        $('<p/>', {
                            text: data.newsletter.message,
                            id: 'newsletter_status'
                        }).hide().addClass(data.newsletter.error ? 'error' : 'success').appendTo($(".steg4")).fadeIn().delay(10000).fadeOut();
                    }
                    var counterWrapper = $('.counterWrapper');
                    counterWrapper.parent().removeClass('hidden');
                    var counters = counterWrapper
                        .find('div.counter')
                        .html(data.counters)
                        .find('span.counter')
                        .parent();
                    counters.hide();

                    $(this).siblings('.entry.dashView').find('.sharing').html(data.sharing);
                   
                    $('.blueBar .compare_counter .block-count').html(data.request_counter);
                    $('.stepLoan_tags').html(data.tags);

                    $('#{$this->instanceId}')
                        .html(data.html)
                        .slideDown(1000, function () {
                            if (1000 > $(window).width()) {
                                //$('html, body').animate({scrollTop: $(this).offset().top})
                            }
                            counters
                                .show()
                                .children('span')
                        });
                    $('.blueBar').fadeIn();
                    
                    if ($.parseHTML(data.html).length > 0) {
                        $('.slider-message').html( $.parseHTML(data.html)[0] );
                    }

                    //$('.found_total').html($('.sort-item').length);
                    starRating();
                    scrollToResults();
                	
                });
            }
            else {
                $errorJs
            }
        },
        error: function (msg) {
            if ($admin) {
                alert("$error " + msg);
            } else {
                generate_table_{$this->instanceId}();
            }
        }
    });
})(jQuery);
JS;

        return $js;
    }

    /**
     * @param bool $array
     *
     * @return string
     */
    public function getFilters( $array = false ) {
        return $array ? $this->filters : ( is_array( $this->filters ) ? implode( ',', $this->filters ) : $this->filters );
    }

    /**
     *
     */
    public function renderAjax() {
        header( 'Content-Type: application/json' );
        $newsletter = static::_processEmailSubscription();
        static::getResults();
        /*ob_start();

        $this->stepLoan_tags();
        $tags = ob_get_clean();*/

        ob_start();
        static::renderCounters();
        $counters = ob_get_clean();

        ob_start();

        static::table();
        $html = ob_get_clean();
        $this->pod->reset();
        if( empty( $age ) ){
            $age = 18;
        }
        if( empty( $credit_score ) ){
            $credit_score = '';
        }
        ob_start();
        static::renderSharing();
        $sharing        = ob_get_clean();
        $requestCounter = (int) $this->sliderSettings->field( $this->compareCounterSetting );
        $json           = [
            'html'            => $html,
            'counters'        => $counters,
            'request_counter' => $requestCounter,
            'newsletter'      => $newsletter,
            'sharing'         => $sharing,
            'tags'         	  => $tags,
            'age'    => $age,
            'credit_score'    => $credit_score,
        ];
        if ( empty( $this->show_all ) ) {
            $this->sliderSettings->save( $this->compareCounterSetting, $requestCounter + 1 );
        }
        echo wp_json_encode( $json );
    }

    /**
     * @return bool
     */
    private function _processEmailSubscription() {
        global $email_newsletter, $wpdb;
        if ( ! empty( $_POST['email'] ) && is_email( $_POST['email'] ) ) {
            $_REQUEST['e_newsletter_email'] = sanitize_email( $_POST['email'] );
            $_REQUEST['e_newsletter_name']  = sanitize_text_field( $_POST['name'] );
            if ( false == empty( $this->newsletterGroup ) ) {
                $member = $email_newsletter->get_member_by_email( $_POST['email'] );
                $group  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$email_newsletter->tb_prefix}enewsletter_groups WHERE group_name = %s", $this->newsletterGroup ), "ARRAY_A" );
                if ( false == empty( $member ) ) {
                    if ( empty( $member['unsubscribe_code'] ) ) {
                        $status = [
                            'error'   => true,
                            'message' => __( 'Please check your email for a confirmation email before subscribing to another list',
                                'email-newsletter' )
                        ];
                    } else {
                        $email_newsletter->add_members_to_groups( [ $member['member_id'] ], [ $group['group_id'] ] );
                        $status = [ 'action' => 'new_subscribed', 'error' => false, 'message' => __( 'You have been successfully subscribed!', 'email-newsletter' ) ];
                    }
                } else {
                    $_REQUEST['e_newsletter_groups_id'] = [ $group['group_id'] ];
                    $status                             = $email_newsletter->new_subscribe();
                }
            } else {
                $status = $email_newsletter->add_member(
                    [
                        'email'        => sanitize_email( $_POST['email'] ),
                        'member_fname' => sanitize_text_field( $_POST['name'] ),
                    ],
                    1
                );
            }

            return $status;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getNewsletterGroup(): string {
        return $this->newsletterGroup;
    }

    /**
     * @param $var
     *
     * @return null|string
     */
    public function __get( $var ): string {
        $vars = get_class_vars( get_class( $this ) );
        foreach ( $vars as $key => $value ) {
            if ( strtolower( $var ) == strtolower( $key ) ) {
                return $this->$key;
                break;
            }
        }

        return '';
    }

    /**
     * @param $var
     * @param $value
     *
     * @return void
     */
    public function __set( $var, $value ) {
        $vars = array_keys( get_class_vars( get_class( $this ) ) );
        foreach ( $vars as $prop ) {
            if ( strtolower( $var ) == strtolower( $prop ) ) {
                $this->$prop = $value;
                break;
            }
        }
    }

    /**
     *
     */
    public function renderPdf() {
        $this->isPdf = true;
        static::getResults();
        ob_start();
        $class = '\Financer\FilterSlider\Table\\' . $this->tableClass;
        $class::build( $this->pod, $this );
        $html = ob_get_clean();
        $pdf = new Dompdf();
        $css = file_get_contents( Plugin::GetDir( 'pdf.css' ) );
        $pdf->loadHtml( <<<HTML
<style type="text/css">
{$css}
</style>

HTML
            .

            $html );
        $pdf->render();
        $pdf->stream( 'financer_com_' . $this->id . '_' . date( 'm-d-Y' ) . '.pdf' );
    }

    /**
     * @return boolean
     */
    public function isPdf(): bool {
        return $this->isPdf;
    }

    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * @return bool|\Pods
     */
    public function getDebug(): bool {
        return $this->debug;
    }

    /**
     * @param array $steps
     */
    public function setSteps( array $steps ) {
        $this->steps = $steps;
    }

    /**
     *
     */
    protected function renderNewsletter(){
        /*newletter*/
    }
    protected function stepLimits_sort() {

        if ( $this->singleCompany == false ) {
            echo '<div class="item-heading mob-arrow-down">';
            echo( get_post_meta( $post->ID, 'sorting_title', true ) ? get_post_meta( $post->ID, 'sorting_title', true ) : _e( 'Filter', 'fs' ) );
            echo '</div>';
        }
    }
    protected function stepAge() {
        /*age dropdown for genericloan only*/

        /*      echo '
          <label class="fl_l_m_10"> ' . $this->htmlLabels['amount'] . ' </label>
          <div class="slider_cont">
              <a href="'. $link . '" class="minus_arrow">-</a>
              <span class="fl_l_m_15 amount_display"></span>
              <a href="'. $link . '" class="plus_arrow">+</a>
          </div>
          <div class="ui-slider" data-name="param_amount" data-display="amount_display" data-default-value="'. $this->amount . '"></div>
      ';*/

        $link = get_the_permalink();

        $default_loan_age_slider = (int) $this->sliderSettings->field( 'default_loan_age_slider' );
        if ($default_loan_age_slider == 0) {
            $default_loan_age_slider = $this->age;
        }

        echo <<<HTML
    <label class="fl_l_m_10">{$this->htmlLabels['age']} </label>
    <div class="slider_cont">
        <a href="$link" class="minus_arrow">-</a>
        <span class="fl_l_m_15 age_display"></span>
        <a href="$link" class="plus_arrow">+</a>
    </div>
<div class="ui-slider" data-name="param_age" data-display="age_display" data-default-value="{$default_loan_age_slider}"></div>
HTML;
    }


    /**
     *
     */
    protected function stepCredit_Score() {
        if ( $this->credit_score_Enabled ) {

            $options = $this->creditScoreOptions;
            $checkboxSlider = '';
            if ($this->minimal == 'single') {
                $checkboxSlider = ' checkboxSlider';
            }

            if($options){
                //
                echo <<<HTML
   					<div class="checkboxFive dropdown-select$checkboxSlider">
  				            <div class="inline">{$this->credit_score_Enabled}:</div>
				            <select class="filter_nav" name="param_credit_score" id="param_credit_score">
				            <option value="">---</option>
HTML;
                foreach ( $options as $option ) {

                    //$selected = selected( $this->tags, $term );
                    echo <<<HTML
						    <option value="{$option['value']}">{$option['name']}</option>
HTML;
                }
                echo <<<HTML
				            </select>

				        </div>
HTML;
            }

        }

    }

    protected function stepLoan_tags() {

        /*loan tags dropdown*/
        if ( $this->tagsEnabled ) {

            $terms = get_terms( array(
                'taxonomy' => 'loan_tags',
                'hide_empty' => false,
            ) );

            if($terms){
                //
                echo <<<HTML
   					<div class="checkboxFive dropdown-select">
				            <div class="inline">{$this->tagsEnabled}:</div>
				            <select class="filter_nav" name="param_tags" id="param_tags">
				            <option value="">---</option>
HTML;
                foreach ( $terms as $term ) {
                    //$selected = selected( $this->tags, $term );
                    echo <<<HTML
						    <option value="$term->slug">{$term->name}</option>
HTML;
                }
                echo <<<HTML
				            </select>

				        </div>
HTML;
            }

        }
        /**/
    }
    protected function stepSubmit() {


        $excludeParams = [ 'param_amount', 'param_limit', 'param_show_limit', 'param_show_all', 'param_title' ];
        $allParams = '';
        foreach ($this->s_atts as $key => $value) {
            if (strpos($key, 'param_') === 0 && !in_array($key, $excludeParams)) {
                $allParams = $allParams . "/$key/$value";
            }
        }

        $slilderNumber = 1;
        if ($this->tableClass == 'MortgageSliderTable') {
            $slilderNumber = 2;
        } elseif ($this->tableClass == 'SavingsAccountSliderTable') {
            $slilderNumber = 3;
        } elseif ($this->tableClass == 'CreditcardTable') {
            $slilderNumber = 4;
        }

        $url  = get_bloginfo('url');
        $postName = '';

        $args = array(
            'meta_query'        => array(
                array(
                    'key'       => 'slider',
                    'value'     => 'minimal'
                )
            ),
            'post_type'         => 'page',
            'posts_per_page'    => '1'
        );

        // run query ##
        $posts = get_posts( $args );

        if (is_array($posts) && count($posts) > 0) {
            $page = $posts[0];
            if ($page && $page->post_name) {
                $postName = $page->post_name;
            }
        }

        $filterDynamic = '';
        $this->non_affiliate = $this->sliderSettings->field( 'filter_non_affiliate' );
        if ($this->non_affiliate == '1') {
            $filterDynamic = 'filter_non_affiliate/1/';
        }

        $this->loan_broker_filter_active = $this->sliderSettings->field( 'loan_broker_filter_active' );
        if ($this->loan_broker_filter_active == '1') {
            $filterDynamic .= 'filter_loan_broker_filter_active/1/';
        }

        $pm = "$postName/query/{$slilderNumber}/{$allParams}/";
        $pm = str_replace('//', '/', $pm);
        $this->fullUrl = "$url/$pm/$filterDynamic";

        if ($this->getMinimalStatus() == 'true') {

            $ageFlag = Util::specialDynamicDataCustomization('age');
            $default_loan_age_slider = (int) $this->sliderSettings->field( 'default_loan_age_slider' );
            if ($ageFlag == 'show' && $default_loan_age_slider > 0) {
                $this->fullUrl .= "param_age/$default_loan_age_slider/";
            }

            echo <<<HTML
<a id="submitSliders" href="$this->fullUrl" data-slider-url="$this->fullUrl" class="get_results button small compare">{$this->htmlLabels['submit']}</a>
HTML;
        } else {
            echo <<<HTML
<a href id="submitSliders" class="get_results button small compare">{$this->htmlLabels['submit']}</a>
HTML;
        }

    }
    protected function stepAmountHeader() {
        echo <<<HTML
<div class="step amount">
HTML;
    }

    /**
     *
     */
    protected function stepAmount() {
        $link = get_the_permalink();

        echo '
    <label class="fl_l_m_10"> ' . $this->htmlLabels['amount'] . ' </label>
    <div class="slider_cont">
        <a href="'. $link . '" class="minus_arrow">-</a>
        <span class="fl_l_m_15 amount_display"></span>
        <a href="'. $link . '" class="plus_arrow">+</a>
    </div>
    <div class="ui-slider" data-name="param_amount" data-display="amount_display" data-default-value="'. $this->amount . '"></div>
';

    }

    /**
     *
     */
    /*protected function stepAge() {
        $link = get_the_permalink();
        echo <<<HTML
    <label class="fl_l_m_10">{$this->htmlLabels['age']}: </label>
    <div class="slider_cont2">
    <input type="number" name="param_input_age" min="1" max="99" value="">
    </div>
HTML;

    }*/
    /**
     *
     */
    protected function stepPeriod() {
        $link = get_the_permalink();
        echo <<<HTML
    <label class="fl_l_m_10">{$this->htmlLabels['period']} </label>
    <div class="slider_cont">
        <a href="$link" class="minus_arrow">-</a>
        <span class="fl_l_m_15 period_display"></span>
        <a href="$link" class="plus_arrow">+</a>
    </div>
    <div class="ui-slider" data-name="param_period" data-display="period_display"  data-default-value="{$this->period}"></div>
HTML;

    }

    /**
     * @return void
     */
    protected function stepFilters($tags='loan') {
        // if (in_array('filters', $this->disableList)) {
        $sliderSettings = pods('slider_settings');

        echo <<<HTML
		<div class="additional_filters show" style="display: none;"><a href="javascript:void(0)" class="show_add_filters">{$this->htmlLabels['show_additional_filters']}</a></div>

		<div class="additional_filters hide" style="display: none;"><a href="javascript:void(0)" class="show_add_filters">{$this->htmlLabels['hide_additional_filters']}</a></div>
HTML;

        foreach ($this->filters as $key => $filter) {
            $sliderFlag = $sliderSettings->field($filter . '_filter_enabled');
            if ($sliderFlag != '0') {
                $checked = checked(true, (bool)$this->$filter || in_array($filter, $this->filtersEnabled), false);
                echo <<<HTML
	    <div class="checkboxFive" style="display: none;">
		  <input type="checkbox" id="{$this->instanceId}_$filter" name="filter_$filter" class="$filter" $checked >
		  <label for="{$this->instanceId}_$filter">{$this->htmlLabels[$filter]}</label>
	   </div>
HTML;
            }

        }

        if ($tags == 'generic') {

            $terms = get_terms( array(
                'taxonomy' => 'loan_tags',
                'hide_empty' => false,
            ) );

            if ($this->minimal == 'single' AND (($this->sliderSettings->field('enable_credit_score') == '1') || $terms)) {
                echo '<div class="checkboxSingleSlide">';
            }

            if ($this->sliderSettings->field('enable_credit_score') == '1') {
                $this->stepCredit_score();
            }

            $this->stepLoan_tags();

            if ($this->minimal == 'single' AND (($this->sliderSettings->field('enable_credit_score') == '1') || $terms)) {
                echo '</div>';
            }

        }

        //  }

    }


    /**
     *
     */
    protected function resetQuery() {
        $this->query['where'] = [];
        if ( $this->hasCompany ) {
            $this->query['where'][] = [
                'key'     => 'company_parent',
                'compare' => 'EXISTS',
            ];
        }
        $this->query['where'][] = [
            'key'   => 'post_status',
            'value' => 'publish',
        ];
    }
}
