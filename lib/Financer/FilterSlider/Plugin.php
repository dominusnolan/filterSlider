<?php

namespace Financer\FilterSlider;

/**
 * Class Plugin
 * @package Financer\FilterSlider
 */
/**
 * Class Plugin
 * @package Financer\FilterSlider
 */
class Plugin extends \WPAutoloader\Abstracts\Plugin {
    /**
     *
     */
    const VERSION = '2.9.0';

    /**
     * @var \AffiliateLogTable
     */

    private static $_affiliateLogTable;

    public static function OnActionPluginsLoaded() {
        load_plugin_textdomain(
            'fs', false, basename(
                dirname(
                    dirname(
                        dirname( __DIR__ )
                    )
                )
            )
        );
    }

    private static function register_post_types_taxonomy() {
        register_post_type(
            'filter_single', [
                'labels'          => [
                    'name'               => __( 'Loan', 'fs' ),
                    'singular_name'      => __( 'Loan', 'fs' ),
                    'add_new'            => __( 'Add New', 'fs' ),
                    'add_new_item'       => __( 'Add New Loan', 'fs' ),
                    'edit_item'          => __( 'Edit Loan', 'fs' ),
                    'new_item'           => __( 'New Loan', 'fs' ),
                    'all_items'          => __( 'All Loans', 'fs' ),
                    'view_item'          => __( 'View Loan', 'fs' ),
                    'search_items'       => __( 'Search Loans', 'fs' ),
                    'not_found'          => __( 'No Loans found', 'fs' ),
                    'not_found_in_trash' => __( 'No Loans found in Trash', 'fs' ),
                    'parent_item_colon'  => '',
                    'menu_name'          => __( 'Loans', 'fs' ),

                ],
                'public'          => false,
                'show_ui'         => true,
                'show_in_menu'    => true,
                'rewrite'         => false,
                'capability_type' => 'post',
                'has_archive'     => false,
                'hierarchical'    => false,
                'menu_position'   => null,
                'supports'        => [ 'title' ],
            ]
        );
        register_post_type(
            'loan_dataset', [
                'labels'          => [
                    'name'               => __( 'Loan Dataset', 'fs' ),
                    'singular_name'      => __( 'Loan Dataset', 'fs' ),
                    'add_new'            => __( 'Add New', 'fs' ),
                    'add_new_item'       => __( 'Add New Loan Dataset', 'fs' ),
                    'edit_item'          => __( 'Edit Loan Dataset', 'fs' ),
                    'new_item'           => __( 'New Loan Dataset', 'fs' ),
                    'all_items'          => __( 'All Loan Datasets', 'fs' ),
                    'view_item'          => __( 'View Loan Dataset', 'fs' ),
                    'search_items'       => __( 'Search Loan Datasets', 'fs' ),
                    'not_found'          => __( 'No Loan Datasets found', 'fs' ),
                    'not_found_in_trash' => __( 'No Loan Datasets found in Trash', 'fs' ),
                    'parent_item_colon'  => '',
                    'menu_name'          => __( 'Loan Datasets', 'fs' ),

                ],
                'public'          => false,
                'show_ui'         => true,
                'show_in_menu'    => true,
                'rewrite'         => false,
                'capability_type' => 'post',
                'has_archive'     => false,
                'hierarchical'    => false,
                'menu_position'   => null,
                'supports'        => [ 'title' ],
            ]
        );
        register_post_type(
            'creditcard', [
                'labels'          => [
                    'name'               => __( 'Credit Card', 'fs' ),
                    'singular_name'      => __( 'Credit Card', 'fs' ),
                    'add_new'            => __( 'Add New', 'fs' ),
                    'add_new_item'       => __( 'Add New Credit Card', 'fs' ),
                    'edit_item'          => __( 'Edit Credit Card', 'fs' ),
                    'new_item'           => __( 'New Credit Card', 'fs' ),
                    'all_items'          => __( 'All Credit Cards', 'fs' ),
                    'view_item'          => __( 'View Credit Card', 'fs' ),
                    'search_items'       => __( 'Search Credit Cards', 'fs' ),
                    'not_found'          => __( 'No Credit Cards found', 'fs' ),
                    'not_found_in_trash' => __( 'No Credit Cards found in Trash', 'fs' ),
                    'parent_item_colon'  => '',
                    'menu_name'          => __( 'Credit Card', 'fs' ),

                ],
                'public'          => true,
                'rewrite'         => [ 'slug' => __( 'creditcard', 'fs' ), 'with_front' => false ],
                'capability_type' => 'post',
                'has_archive'     => false,
                'hierarchical'    => false,
                'menu_position'   => null,
                'supports'        => [ 'title' ],
            ]
        );
        register_post_type(
            'credit_check_company', [
                'labels'             => [
                    'name'               => __( 'Credit Check Company', 'fs' ),
                    'singular_name'      => __( 'Credit Check Company', 'fs' ),
                    'add_new'            => __( 'Add New', 'fs' ),
                    'add_new_item'       => __( 'Add New Credit Check Company', 'fs' ),
                    'edit_item'          => __( 'Edit Credit Check Company', 'fs' ),
                    'new_item'           => __( 'New Credit Check Company', 'fs' ),
                    'all_items'          => __( 'All Credit Check Companies', 'fs' ),
                    'view_item'          => __( 'View Credit Check Company', 'fs' ),
                    'search_items'       => __( 'Search Credit Check Companies', 'fs' ),
                    'not_found'          => __( 'No Credit Check Companies found', 'fs' ),
                    'not_found_in_trash' => __( 'No Credit Check Companies found in Trash', 'fs' ),
                    'parent_item_colon'  => '',
                    'menu_name'          => __( 'Credit Check Companies', 'fs' ),

                ],
                'public'             => false,
                'publicly_queryable' => false,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'query_var'          => false,
                'rewrite'            => false,
                'capability_type'    => 'post',
                'has_archive'        => false,
                'hierarchical'       => false,
                'menu_position'      => null,
                'supports'           => [ 'title' ],
            ]
        );
        register_post_type(
            'company_single', [
                'labels'          => [
                    'name'               => __( 'Company', 'fs' ),
                    'singular_name'      => __( 'Single Company', 'fs' ),
                    'add_new'            => __( 'Add New', 'fs' ),
                    'add_new_item'       => __( 'Add New Company', 'fs' ),
                    'edit_item'          => __( 'Edit Company', 'fs' ),
                    'new_item'           => __( 'New Company', 'fs' ),
                    'all_items'          => __( 'All Companies', 'fs' ),
                    'view_item'          => __( 'View Company', 'fs' ),
                    'search_items'       => __( 'Search Company', 'fs' ),
                    'not_found'          => __( 'No Companies found', 'fs' ),
                    'not_found_in_trash' => __( 'No Companies found in Trash', 'fs' ),
                    'parent_item_colon'  => '',
                    'menu_name'          => __( 'Company', 'fs' ),

                ],
                'public'          => true,
                'rewrite'         => [ 'slug' => __( 'company', 'fs' ), 'with_front' => false ],
                'capability_type' => 'post',
                'has_archive'     => false,
                'hierarchical'    => false,
                'menu_position'   => null,
                'feeds'           => null,
                'supports'        => [
                    'title',
                    'comments',
                    'revisions',
                    'thumbnail',
                ],
            ]
        );
        register_post_type(
            'savings_account', [
                'labels'          => [
                    'name'               => __( 'Savings Account', 'fs' ),
                    'singular_name'      => __( 'Savings Account', 'fs' ),
                    'add_new'            => __( 'Add New', 'fs' ),
                    'add_new_item'       => __( 'Add New Savings Account', 'fs' ),
                    'edit_item'          => __( 'Edit Savings Account', 'fs' ),
                    'new_item'           => __( 'New Savings Account', 'fs' ),
                    'all_items'          => __( 'All Savings Accounts', 'fs' ),
                    'view_item'          => __( 'View Savings Account', 'fs' ),
                    'search_items'       => __( 'Search Savings Accounts', 'fs' ),
                    'not_found'          => __( 'No Savings Accounts found', 'fs' ),
                    'not_found_in_trash' => __( 'No Savings Accounts found in Trash', 'fs' ),
                    'parent_item_colon'  => '',
                    'menu_name'          => __( 'Savings Accounts', 'fs' ),
                ],
                'public'          => false,
                'show_ui'         => true,
                'show_in_menu'    => true,
                'rewrite'         => false,
                'capability_type' => 'post',
                'has_archive'     => false,
                'hierarchical'    => false,
                'menu_position'   => null,
                'supports'        => [ 'title' ],
            ]
        );
        register_post_type(
            'mortgage', [
                'labels'          => [
                    'name'               => __( 'Mortgages', 'fs' ),
                    'singular_name'      => __( 'Mortgage', 'fs' ),
                    'add_new'            => __( 'Add New', 'fs' ),
                    'add_new_item'       => __( 'Add New Mortgages', 'fs' ),
                    'edit_item'          => __( 'Edit Mortgage', 'fs' ),
                    'new_item'           => __( 'New Mortgage', 'fs' ),
                    'all_items'          => __( 'All Mortgages', 'fs' ),
                    'view_item'          => __( 'View Mortgage', 'fs' ),
                    'search_items'       => __( 'Search Mortgages', 'fs' ),
                    'not_found'          => __( 'No Mortgages found', 'fs' ),
                    'not_found_in_trash' => __( 'No Mortgages found in Trash', 'fs' ),
                    'parent_item_colon'  => '',
                    'menu_name'          => __( 'Mortgages', 'fs' ),
                ],
                'public'          => false,
                'show_ui'         => true,
                'show_in_menu'    => true,
                'rewrite'         => false,
                'capability_type' => 'post',
                'has_archive'     => false,
                'hierarchical'    => false,
                'menu_position'   => null,
                'supports'        => [ 'title' ],
            ]
        );
        register_post_type(
            'report', [
                'labels'          => [
                    'name'               => __( 'Report', 'fs' ),
                    'singular_name'      => __( 'Report', 'fs' ),
                    'add_new'            => __( 'Add New', 'fs' ),
                    'add_new_item'       => __( 'Add New Report', 'fs' ),
                    'edit_item'          => __( 'Edit Report', 'fs' ),
                    'new_item'           => __( 'New Report', 'fs' ),
                    'all_items'          => __( 'All Reports', 'fs' ),
                    'view_item'          => __( 'View Report', 'fs' ),
                    'search_items'       => __( 'Search Reports', 'fs' ),
                    'not_found'          => __( 'No Reports found', 'fs' ),
                    'not_found_in_trash' => __( 'No Reports found in Trash', 'fs' ),
                    'parent_item_colon'  => '',
                    'menu_name'          => __( 'Reports', 'fs' ),
                ],
                'public'          => false,
                'show_ui'         => true,
                'show_in_menu'    => false,
                'rewrite'         => false,
                'capability_type' => 'post',
                'has_archive'     => false,
                'hierarchical'    => false,
                'menu_position'   => null,
                'supports'        => false
            ]
        );
        register_taxonomy( 'company_type', [ 'company_single' ], [

                'hierarchical'      => true,
                'labels'            => [
                    'name'          => _x( 'Company Types', 'taxonomy general name', 'fs' ),
                    'singular_name' => _x( 'Company Type', 'taxonomy singular name', 'fs' ),
                ],
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => [ 'slug' => 'company_type' ],
            ]
        );
        register_taxonomy( 'loan_tags', [ 'loan_dataset' ], [

                'hierarchical'      => false,
                'labels'            => [
                    'name'          => _x( 'Loan Tags', 'taxonomy general name', 'fs' ),
                    'singular_name' => _x( 'Loan Tag', 'taxonomy singular name', 'fs' ),
                ],
                'show_ui'           => false,
                'show_admin_column' => false,
                'query_var'         => true,
                'rewrite'           => [ 'slug' => 'loan_tags' ],
            ]
        );
        remove_post_type_support( 'page', 'custom-fields' );
        add_rewrite_endpoint( 'redirect', EP_ALL );
        add_rewrite_endpoint( 'query', EP_PAGES );
    }

    /**
     *
     */
    public static function OnActionInit() {
        self::register_post_types_taxonomy();
        add_action( 'wp_criticalcss_multisite_before_rewrites', function () {
            self::register_post_types_taxonomy();
        } );

        if( function_exists('rocket_clean_post') ){
            add_action( 'pods_api_post_save_pod_item', function ( $pieces, $is_new_item, $id ) {
                $homepage_settings = pods( 'homepage_settings' );
                $homepage_settings_field = $homepage_settings->data->api->pod_data['fields'] ;

                foreach( $homepage_settings_field as $key => $value ){
                    if( $value['type'] == 'pick' ){
                        $postID_array = $homepage_settings->field( $value['name'] );
                        rocket_clean_post($postID_array['ID']);
                    }
                }
                rocket_clean_post($id);
            }, 10, 3 );
        }
    }


    public static function OnActionWPEnqueueScripts() {
        wp_register_script( 'filter_slider-jquery-counterup', self::GetUri( 'js/jquery.counterup.min.js' ), [ 'jquery' ] );
        wp_enqueue_script( 'filter_slider-jquery-counterup' );
        wp_register_script( 'filter_slider-array-polyfill', self::GetUri( 'js/array.polyfill.js' ) );
        wp_enqueue_script( 'filter_slider-array-polyfill' );
        wp_register_script( 'filter_slider-jquery-flexslider', self::GetUri( 'assets/js/jquery.flexslider.js' ), [ 'jquery' ] );
        wp_enqueue_script( 'filter_slider-jquery-flexslider' );
        wp_register_script( 'filter_slider-jquery-custom', self::GetUri( 'assets/js/custom.js' ), [ 'jquery' ] );
        wp_enqueue_script( 'filter_slider-jquery-custom' );
        wp_register_style( 'filter_slider-flex-slider', self::GetUri( 'assets/css/flexslider.css' ) );
        wp_enqueue_style( 'filter_slider-flex-slider' );

    }

    /**
     * @param $name
     */
    public static function OnActionActivatedPlugin( $name ) {
        if ( 'w3-total-cache/w3-total-cache.php' !== $name ) {
            return;
        }
        $sites = get_sites( [ 'fields' => 'ids' ] );
        foreach ( $sites as $site ) {
            switch_to_blog( $site );
            pods_api()->cache_flush_pods();
            restore_current_blog();
        }
    }

    /**
     * @param $name
     */
    public static function OnActionDeactivatedPlugin( $name ) {
        if ( 'w3-total-cache/w3-total-cache.php' !== $name ) {
            return;
        }
        $sites = get_sites( [ 'fields' => 'ids' ] );
        foreach ( $sites as $site ) {
            switch_to_blog( $site );
            pods_api()->cache_flush_pods();
            restore_current_blog();
        };
    }

    /**
     * @param $blog_id
     */
    public static function OnActionWpmuNewBlog( $blog_id ) {
        switch_to_blog( $blog_id );
        pods_init()->setup( $blog_id );
        pods_init()->load_components();
        pods_components()->get_components();
        pods_components()->load();
        if ( ! class_exists( 'Pods_Migrate_Packages' ) ) {
            $_GET['toggle'] = 1;
            pods_components()->toggle( 'migrate-packages' );
            pods_components()->load();
        }
        pods_api()->import_package( file_get_contents( Plugin::GetDir( 'pods.json' ) ) );
        pods_api()->cache_flush_pods();
        if ( defined( 'PODS_PRELOAD_CONFIG_AFTER_FLUSH' ) && PODS_PRELOAD_CONFIG_AFTER_FLUSH ) {
            pods_api()->load_pods();
        }
        update_option( 'financer_filter_slider_version', self::VERSION );
        restore_current_blog();
    }

    /**
     *
     */
    public static function OnActionTemplateRedirect() {
        /*if ( 'creditcard' === get_post_type() && '' === get_query_var( 'redirect' ) && empty($_GET['s']) ) {
            wp_redirect( user_trailingslashit( get_the_permalink() . 'redirect' ) );
            exit();
        }*/
    }

    public static function OnActionAdminInit() {
        global $plugin_page, $pagenow, $page_hook, $hook_suffix;
        if ( is_network_admin() && 'company-affiliate-log' == $plugin_page ) {
            $page_hook   = get_plugin_page_hook( $plugin_page, $pagenow );
            $hook_suffix = $page_hook;
            set_current_screen();
            self::$_affiliateLogTable = new AffiliateLogTable( [] );
            add_screen_option( 'per_page', true );
            add_action( 'network_admin_notices', function () {
                ?>
                <h1>Company Affiliate Log</h1>
                <?php
            }, 0 );
        }
    }

    public static function OnActionNetworkAdminMenu() {
        add_menu_page( 'Company Affiliate Log', 'Company Affiliate Log', 'manage_network', 'company-affiliate-log', [ __CLASS__, 'runAffiliateLog' ] );
    }

    public static function runAffiliateLog() {
        self::$_affiliateLogTable->prepare_items();
        self::$_affiliateLogTable->display();
    }

    public static function OnActionWidgetsInit() {
        if ( is_dir( __FILE__ . '/Widget' ) ) {
            $dir = new \DirectoryIterator( dirname( __FILE__ ) . '/Widget' );
            foreach ( $dir as $file ) {
                if ( $file->isFile() ) {
                    register_widget( '\Financer\FilterSlider\Widget\\' . $file->getBasename( '.php' ) );
                }
            }
        }
    }

    /**
     * @param array $query_vars
     *
     * @return array
     */
    public static function OnFilterQueryVars( array $query_vars ): array {
        $query_vars[] = 'query';

        return $query_vars;
    }

    /**
     *
     */
    public static function OnActionWP() {
        $query = get_query_var( 'query' );
        if ( ! empty( $query ) ) {
            $query_vars  = [];
            $query_parts = explode( '/', $query );

            //skip the NUMBER query/<NUMBER>/param_cmount
            if (in_array($query_parts[0], ['1', '2', '3', '4'])) {
                array_shift($query_parts);
            }

            foreach ( $query_parts as $index => $part ) {
                if ( 1 == $index % 2 ) {
                    $query_vars[ $query_parts[ $index - 1 ] ] = urldecode( $part );
                }
            }
            $_GET = array_merge( $_GET, $query_vars );
            add_filter( 'su_meta_robots', function ( $commands ) {
                $commands[] = 'noindex';

                return array_unique( $commands );
            } );
        }
    }

    /**
     *
     */
    protected function init() {
        $dir = new \DirectoryIterator( dirname( __FILE__ ) . '/Shortcode' );
        foreach ( $dir as $file ) {
            if ( $file->isFile() ) {
                ( new \ReflectionClass( '\Financer\FilterSlider\Shortcode\\' . $file->getBasename( '.php' ) ) )->getMethod( 'register' )->invoke( null );
            }
        }
        add_filter(
            'crfp_display_rating_field',
            function ( $html ) {
                return <<<HTML
<div class="ratings">$html</div>
HTML;
            }
        );
        add_filter( 'crfp_display_post_rating_comment', function ( $html, $group, $comment_id, $comment ) {
            $entities = get_html_translation_table( HTML_ENTITIES );
            unset( $entities['"'] );
            unset( $entities['<'] );
            unset( $entities['>'] );
            unset( $entities['&'] );
            $entities         = array_values( $entities );
            $comment_entities = array_map( function ( $item ) {
                return '<!-- ' . $item . ' -->';
            }, $entities );
            $html             = str_replace( $entities, $comment_entities, $html );
            $comment          = str_replace( $entities, $comment_entities, $comment );
            $html             = htmlqp( wpautop( $html ) );
            $html->find( 'p' )->detach();
            $comment = wpautop( $comment );
            $dom     = htmlqp( $html );
            $div     = $dom->find( 'body' );
            $div->append( htmlqp( '<div class="rating-container"><div></div></div>' )->find( 'div > div' )->append( $comment )->parent() );

            return str_replace( $comment_entities, $entities, $dom->find( 'body' )->innerHTML() );
        }, 10, 4 );
        add_filter(
            'manage_loan_dataset_posts_columns',
            function () {
                return [
                    'cb'                   => true,
                    'title'                => _x( 'Title', 'column name' ),
                    'company_parent'       => 'Company',
                    'monthly_fee'          => 'Monthly Fee',
                    'fee_flat'             => 'Fee Flat',
                    'fee_percent'          => 'Fee Percent',
                    'fee_custom'           => 'Fee Custom',
                    'interest_rate'        => 'Interest Rate',
                    'amount_range_minimum' => 'Amount Range Minimum',
                    'amount_range_maximum' => 'Amount Range Maximum',
                    'period_range_minimum' => 'Period Range Minimum',
                    'period_range_maximum' => 'Period Range Maximum',
                    'credit_score'         => 'Credit Score',
                    'date'                 => __( 'Date' ),
                    'last_updated'         => __( 'Last Updated' ),
                ];
            }
        );
        add_filter(
            'manage_company_single_posts_columns',
            function () {
                return [
                    'cb'           => true,
                    'title'        => _x( 'Title', 'column name' ),
                    'credit_check' => 'Credit Check Company',
                    'banks'        => 'Banks',
                    'company_type' => 'Company Type',
                    'date'         => __( 'Date' ),
                    'last_updated' => __( 'Last Updated' ),
                ];
            }
        );
        add_filter(
            'manage_savings_account_posts_columns',
            function () {
                return [
                    'cb'    => true,
                    'title' => _x( 'Title', 'column name' ),
                    'bank'  => 'Bank',
                    'date'  => __( 'Date' ),
                ];
            }
        );
        add_filter(
            'manage_filter_single_posts_columns',
            function () {
                return [
                    'cb'             => true,
                    'title'          => __( 'Title' ),
                    'company_parent' => 'Company',
                    'date'           => __( 'Date' ),
                ];
            }
        );
        add_filter(
            'manage_mortgage_posts_columns',
            function () {
                return [
                    'cb'    => true,
                    'title' => _x( 'Title', 'column name' ),
                    'bank'  => 'Bank',
                    'date'  => __( 'Date' ),
                ];
            }
        );
        add_filter(
            'manage_report_posts_columns',
            function () {
                return [
                    'cb'           => true,
                    'title'        => __( 'Title' ),
                    'date'         => __( 'Date' ),
                    'item'         => __( 'Item Type', 'fs' ),
                    'item_id'      => __( 'Item', 'fs' ),
                    'report_count' => __( 'Reports', 'fs' ),
                ];
            }
        );

        add_filter( 'manage_edit-company_single_sortable_columns', function( $columns ){
            $columns['last_updated'] = 'last_updated';

            return $columns;

        } );

        add_action( 'request', function( $vars ){
            if ( isset( $vars["orderby"] ) && "last_updated" == $vars["orderby"] ) {
                $vars = array_merge( $vars, array(
                    "orderby" => "modified"
                ) );
            }
            return $vars;
        } );

        add_filter( 'request', function ( $vars ) {

            global $wp;
            $urlParam = explode('/', $wp->request);
            if (count($urlParam) == 3) {
                $originalProductName = str_replace('-', ' ', $urlParam[2]);
                $urlType = strtolower(urldecode(__($originalProductName,'fs')));
                if ($urlParam[0] == __('company','fs')) {

                    Util::isTranslated($originalProductName, $urlType);

                    $product = false;
                    if ($urlType == strtolower(__('loan products','fs'))) {
                        /*translators: ***Do NOT translate that one. Translate the it in the THEME ONLY *** */
                        $product = __('loan products','fs');
                        $originalProductName = 'loan products';
                    } elseif ($urlType == strtolower(__('saving products','fs'))) {
                        /*translators: ***Do NOT translate that one. Translate the it in the THEME ONLY *** */
                        $product = __('saving products','fs');
                        $originalProductName = 'saving products';
                    } elseif ($urlType == strtolower(__('card products','fs'))) {
                        /*translators: ***Do NOT translate that one. Translate the it in the THEME ONLY *** */
                        $product = __('card products','fs');
                        $originalProductName = 'card products';
                    } elseif ($urlType == strtolower(__('mortgage products','fs'))) {
                        /*translators: ***Do NOT translate that one. Translate the it in the THEME ONLY *** */
                        $product = __('mortgage products','fs');
                        $originalProductName = 'mortgage products';
                    }

                    if ($product) {
                        $pod = pods( 'company_single', [ 'where' => [ [ 'key' => 'post_name', 'value' => $urlParam[1], 'compare' => '=' ] ] ] );
                        $pod->fetch();
                        $postId = $pod->ID();
                        if (!$postId) {
                            global $wp_query;
                            $wp_query->set_404();
                            status_header( 404 );
                            get_template_part( 404 ); exit();
                        }

                        set_query_var('postId', $postId);
                        set_query_var('urlType', $product);
                        set_query_var('originalProductName', $originalProductName);
                        get_template_part('single', 'company_single');
                        status_header(200);
                        exit;
                    }
                }
            }

            return $vars;
        } );

        add_filter( 'request', function ( $vars ) {
            if ( isset( $vars['redirect'] ) ) {
                $vars['redirect'] = true;
            }

            return $vars;
        } );

        add_filter( 'query_vars', function ( $vars ) {
            $vars[] = "id";
            $vars[] = "b";
            return $vars;
        } );

        add_filter( 'template_include', function ( $template ) {

            if ( get_query_var( 'redirect' ) ) {

                //$new_template = locate_template( 'templates' . DS . get_post_type() . '-redirect.php' );
                $new_template = locate_template( 'templates' . DS . 'company_single-redirect.php' );
                //die('templates' . DS . get_post_type() . '-redirect.php' );
                if ( '' !== $new_template ) {
                    $template       = $new_template;
                    $GLOBALS['pod'] = $pod = pods( get_post_type(), get_the_ID() );
                    $url            = false;
                    switch ( get_post_type() ) {
                        case 'company_single':
                            if ( $pod->field( 'ej_partner' ) ) {
                                $url = $pod->field( 'hemsida' );
                            } else {
                                $url = $pod->display( 'url' );
                                $pid = url_to_postid(wp_get_referer());

                                if ( isset( $pid ) ) {
                                    $page_item_id   = $pid;
                                    $page_item_type = get_post_type( $page_item_id );
                                    if ( ! empty( $page_item_type ) ) {
                                        if ( 'page' == $page_item_type ) {
                                            $repeatable_fields = get_post_meta( $page_item_id, 'repeatable_fields', true );
                                            if( !empty($repeatable_fields)  ){
                                                foreach ( $repeatable_fields as $fields ) {
                                                    if ( $fields['select'] == get_the_ID() ) {
                                                        $url = $fields['specific_url'];
                                                        if( empty($url) ){
                                                            $url = $pod->display( 'url' );
                                                        }else{
                                                            $specific_url =  $fields['specific_url'];
                                                            $items_url = $fields['specific_url'];
                                                        }
                                                    }
                                                }
                                            }

                                        }elseif ( 'company_single' == $page_item_type ) {
                                            $url = $pod->display( 'url' );

                                        } else {
                                            $podld = pods( $page_item_type, $page_item_id );
                                            if ( 'savings_account' == $page_item_type ) {
                                                $url = $podld->display( 'specific_affiliate_url_account' );
                                            } else {
                                                if( get_post_type() != 'company_single' ){
                                                    $url = $podld->display( 'specific_affiliate_url' );
                                                }
                                            }
                                        }

                                        if ( 'company_single' != $page_item_type ) {
                                            $pid = get_the_ID();
                                            if ( 'company_single' == get_post_type( $pid ) ) {
                                                $loan_datasets = pods(
                                                    'loan_dataset', [
                                                        'select'  => [
                                                            't.ID as ID',
                                                            'specific_affiliate_url',
                                                            'company_parent.d.ID as pid'
                                                        ],
                                                        'limit'     => -1,
                                                        'where'  => [
                                                            [
                                                                'key'   => 'post_status',
                                                                'value' => 'publish',
                                                            ],
                                                            [
                                                                'key'   => 'company_parent.d.ID',
                                                                'value' => get_the_ID(),
                                                            ],
                                                        ],
                                                    ]
                                                );

                                                $data_query = $loan_datasets->data();

                                                if ( count( $data_query ) > 0 ) {
                                                    foreach ( $data_query as $pos => $result ) {
                                                        $specific_url = $result->specific_affiliate_url;
                                                    }
                                                }

                                                if( !empty($specific_url) ){
                                                    $url = $specific_url;
                                                }else{

                                                    if( !empty($items_url) ){
                                                        $url = $items_url; // fall for items.php
                                                    }else{
                                                        $url = $pod->display( 'url' );
                                                    }

                                                }
                                            }

                                        }

                                        if( !empty($_GET['id']) ){ // for special loan dataset
                                            if( get_post_type( $_GET['id'] ) == 'loan_dataset' ){
                                                $podld = pods( 'loan_dataset', $_GET['id'] );
                                                $url = $podld->display( 'specific_affiliate_url' );
                                            }else{
                                                $podld = pods( 'savings_account', $_GET['id'] );
                                                $url = $podld->display( 'specific_affiliate_url_account' );
                                            }

                                        }

                                        if( !empty($_GET['tid']) ){   // For items
                                            $itemid = substr($_GET['tid'], 4);
                                            $repeatable_fields = get_post_meta( url_to_postid(wp_get_referer()) , 'repeatable_fields', true );
                                            if( !empty($repeatable_fields)  ){
                                                $url = $repeatable_fields[$itemid]['specific_url'];
                                                if( empty($url) ){
                                                    $url = $pod->display( 'url' );
                                                }
                                            }
                                        }
                                    }
                                }


                            }

                            break;
                        case 'creditcard':
                            $url = $pod->display( 'url' );
                            break;
                    }

                    if( !empty($_GET['pid'])  ){
                        $passID = str_replace('/', '', $_GET['pid']);
                        if( !empty($passID) ){
                            $mortgage = pods( 'mortgage', $passID );
                            if( !empty($mortgage) && !empty( $mortgage->field( 'url' ) ) ){
                                $url = $mortgage->field( 'url' );
                            }else{
                                $url = $pod->display( 'url' );
                            }
                        }
                    }

                    add_filter( 'su_meta_robots', function ( $commands ) {
                        $commands[] = 'noindex';

                        return array_unique( $commands );
                    } );

                    if ( false !== $url ) {
                        $GLOBALS['redirect_url'] = $url;
                    }

                }
            }

            return $template;
        } );
        add_filter( 'nonce_life', function () {
            return 3 * DAY_IN_SECONDS;
        } );
        add_action(
            'manage_loan_dataset_posts_custom_column',
            function ( $column, $post_id ) {
                static $pod = [];
                if ( empty( $pod[ $post_id ] ) ) {
                    $pod[ $post_id ] = pods( 'loan_dataset', $post_id );
                }
                /** @noinspection PhpUndefinedMethodInspection */
                if ( 'last_updated' == $column ) {
                    $post_modified = get_post_field( 'post_modified', $post_id );
                    if ( ! $post_modified ) {
                        $post_modified = '' . __( 'undefined', 'fs' ) . '';
                    }
                    echo __( 'Modified', 'fs' ) . ' <br/>' . date( 'Y-m-d', strtotime( $post_modified ) );
                } else {
                    echo $pod[ $post_id ]->display( $column );
                }


            }, 10, 2
        );
        add_action(
            'manage_company_single_posts_custom_column',
            function ( $column, $post_id ) {
                static $pod = [];
                if ( empty( $pod[ $post_id ] ) ) {
                    $pod[ $post_id ] = pods( 'company_single', $post_id );
                }
                /** @noinspection PhpUndefinedMethodInspection */
                if ( 'last_updated' == $column ) {
                    $post_modified = get_post_field( 'post_modified', $post_id );
                    if ( ! $post_modified ) {
                        $post_modified = '' . __( 'undefined', 'fs' ) . '';
                    }
                    echo __( 'Modified', 'fs' ) . ' <br/>' . date( 'Y-m-d', strtotime( $post_modified ) );
                } else {
                    echo $pod[ $post_id ]->display( $column );
                }
            }, 10, 2
        );

        add_action(
            'manage_savings_account_posts_custom_column',
            function ( $column, $post_id ) {
                static $pod = [];
                if ( empty( $pod[ $post_id ] ) ) {
                    $pod[ $post_id ] = pods( 'savings_account', $post_id );
                }
                /** @noinspection PhpUndefinedMethodInspection */
                echo $pod[ $post_id ]->display( $column );
            }, 10, 2
        );
        add_action(
            'manage_mortgage_posts_custom_column',
            function ( $column, $post_id ) {
                static $pod = [];
                if ( empty( $pod[ $post_id ] ) ) {
                    $pod[ $post_id ] = pods( 'mortgage', $post_id );
                }
                /** @noinspection PhpUndefinedMethodInspection */
                echo $pod[ $post_id ]->display( $column );
            }, 10, 2
        );
        add_action(
            'manage_filter_single_posts_custom_column',
            function ( $column, $post_id ) {
                static $pod = [];
                if ( empty( $pod[ $post_id ] ) ) {
                    $pod[ $post_id ] = pods( 'filter_single', $post_id );
                }
                /** @noinspection PhpUndefinedMethodInspection */
                echo $pod[ $post_id ]->display( $column );
            }, 10, 2
        );
        add_action(
            'manage_report_posts_custom_column',
            function ( $column, $post_id ) {
                static $pod = [];
                if ( empty( $pod[ $post_id ] ) ) {
                    $pod[ $post_id ] = pods( 'report', $post_id );
                }
                /** @noinspection PhpUndefinedMethodInspection */
                switch ( $column ) {
                    case 'item_id':
                        $item_id = $pod[ $post_id ]->field( $column );
                        if ( empty( $pod[ $item_id ] ) ) {
                            $pod[ $item_id ] = pods( get_post_type( $item_id ), $item_id );
                            echo $pod[ $item_id ]->display( 'post_title' );
                        }
                        break;
                    case 'item':
                        $item_id = $pod[ $post_id ]->field( 'item_id' );
                        echo get_post_types( [], [ 'output' => 'objects' ] )[ get_post_type( $item_id ) ]->label;
                        break;
                    default:
                        echo $pod[ $post_id ]->display( $column );
                        break;
                }
            }, 10, 2
        );
        add_action(
            'pods_meta_groups',
            function ( $type, $name ) {
                if ( 'post_type' == $type ) {
                    switch ( get_post_type() ) {
                        case 'company_single':
                            //General Fields
                            pods_group_add(
                                get_post_type(), 'General data',
                                [
                                    ['name' => 'content'],
                                    'links',
                                    'feature_1_companyinfo',
                                    'feature_2_companyinfo',
                                    'feature_3_companyinfo',
                                    'feature_4_companyinfo',
                                    'logo',
                                    'url',
                                    'offer_tracking_url',
                                    'impression_tracking_url',
                                    'custom_cta_text',
                                    'foretag',
                                    'adress',
                                    'telefon',
                                    'swift_code',
                                    'hemsida',
                                    'open_weekdays',
                                    'close_weekdays',
                                    'open_saturday',
                                    'close_saturday',
                                    'open_sunday',
                                    'close_sunday',
                                    'mandag_fredag',
                                    'lordag',
                                    'sondag',
                                    'favorite',
                                    'ej_partner',
                                    'email',
                                    'last_updated',
                                    'company_ranking',
                                    'show_on_homepage',
                                    'lowest_interest_from',
                                    'overall_rating',
                                    'customer_support',
                                    'interest_loan_costs',
                                    'flexibility_loan_terms',
                                    'website_functionality',
                                    'total_review'
                                ]
                            );
                            //Company Fields
                            pods_group_add(
                                get_post_type(), 'Lender specific data',
                                [
                                    'feature_1_borrow',
                                    'feature_2_borrow',
                                    'feature_3_borrow',
                                    'feature_4_borrow',
                                    'bad_history',
                                    'credit_check',
                                    'banks',
                                    'national_bank',
                                    'national_phone',
                                    'minimum_inkomst',
                                    'helgutbetalning',
                                    'fornya_lan',
                                    'minalder',
                                    'payment_times',
                                    'eleg',
                                    'free_early_payback',
                                    'quick_payout',
                                    'loan_broker',
                                    'approval_rate',
                                    'loan_amount_range',
                                    'loan_period_range',
                                    'custom_representative_example',
                                ]
                            );
                            //Bank Fields
                            pods_group_add(
                                get_post_type(), 'Bank specific data', [
                                    'feature_1_saving',
                                    'feature_2_saving',
                                    'feature_3_saving',
                                    'feature_4_saving',
                                    'savings_url',
                                    'bank_id',
                                    'governmental_guarantee',
                                ]
                            );
                            //Other Meta Data
                            pods_group_add(
                                get_post_type(), 'Products', [
                                    'company_content',
                                    'borrow_content',
                                    'borrow_meta_title',
                                    'borrow_meta_description',
                                    'save_content',
                                    'savings_meta_title',
                                    'savings_meta_description',
                                    'mortgage_content',
                                    'mortgages_meta_title',
                                    'mortgages_meta_description',
                                    'creditcard_content',
                                    'creditcards_meta_title',
                                    'creditcards_meta_description',
                                ],
                                'normal',
                                'high'
                            );
                            break;
                        case 'data_customization' :
                            pods_group_add(
                                get_post_type(), 'General', Util::$data_customization_general
                            );
                            pods_group_add(
                                get_post_type(), 'Loans', Util::$data_customization_loans
                            );
                            pods_group_add(
                                get_post_type(), 'Credit Cards', Util::$data_customization_credit_cards
                            );

                            pods_group_add(
                                get_post_type(), 'Mortgage', Util::$data_customization_mortgage
                            );

                            pods_group_add(
                                get_post_type(), 'Saving Accounts', Util::$data_customization_saving_accounts
                            );

                            break;
                    }
                }
            }
            , 10, 2 );
        add_filter( 'pods_meta_group_add_post_type_company_single', function ( $group ) {
            $group['priority'] = 'high';
            return $group;
        } );

        add_action( 'save_post_company_single', function ( $post_id, $post, $update ) {

            $filter = pods(
                'company_single',
                [
                    'select'  => [
                        //'MIN(CAST(minalder AS SIGNED)) AS min',
                        'MAX(CAST(minalder AS SIGNED)) AS max',
                    ],
                ]
            );
            if ( $filter->total() > 0 ) {
                $min = 18; //$filter->field( 'min' );
                $max = $filter->field('max');

                $postAgeValue = $_POST['pods_meta_minalder'];

                if ($postAgeValue > $max) {
                    $max = $postAgeValue;
                }

                $data = array(
                    'age_range_maximum' => $max,
                    'age_range_minimum' => $min
                );
                $pod = pods('slider_settings');
                $pod->save($data);
            }
        }, 100, 2 );

        add_action( 'update_option_wp_language_locale', function () {
            if ( function_exists( 'rocket_clean_domain' ) ) {
                add_action( 'setup_theme', function () {
                    rocket_clean_domain();
                } );
            } else {
                add_action( 'setup_theme', 'flush_rewrite_rules' );
                if ( function_exists( 'pods_api' ) ) {
                    pods_api()->cache_flush_pods();
                }
            }
        } );
        add_action( 'update_option_wp_language_locale_front', function () {
            if ( function_exists( 'rocket_clean_domain' ) ) {
                add_action( 'setup_theme', function () {
                    rocket_clean_domain();
                } );
            } else {
                add_action( 'setup_theme', 'flush_rewrite_rules' );
                if ( function_exists( 'pods_api' ) ) {
                    pods_api()->cache_flush_pods();
                }
            }
        } );
        $after_rocket_clean_domain_func = function () use ( &$after_rocket_clean_domain_func ) {
            flush_rewrite_rules();
            pods_api()->cache_flush_pods();
            remove_action( 'after_rocket_clean_domain', $after_rocket_clean_domain_func );
            rocket_clean_domain();
        };
        add_action( 'after_rocket_clean_domain', $after_rocket_clean_domain_func, 10, 2 );
        add_action( 'registered_post_type', function ( $post_type ) {
            if ( 'company_single' == $post_type ) {
                global $wp_post_types;
                $wp_post_types['company_single']->rewrite['slug'] = __( 'company', 'fs' );
            }
        } );
        add_filter( 'wp_post_revision_meta_keys', function ( array $fields ): array {
            $fields [] = 'url';

            return array_unique( $fields );
        } );
        /*
         * Hack to reset \PodsMeta::$current_field_pod
         */
        add_action( 'wp_save_post_revision_post_has_changed', function ( bool $post_has_changed, \WP_Post $last_revision, \WP_Post $post ) {
            if ( 'publish' == get_post_status( $post ) && 'company_single' == get_post_type( $post ) ) {
                $pod = pods( 'company_single', [ 'where' => [ [ 'key' => 'ID', 'value' => $post->ID, 'compare' => '!=' ] ] ] );
                $pod->fetch();
                pods_meta()->get_meta( 'post_type', null, $pod->id(), 'null' );
            }

            return $post_has_changed;
        }, 9, 3 );
        add_action( 'wp_save_post_revision_post_has_changed', function ( bool $post_has_changed, \WP_Post $last_revision, \WP_Post $post ) {
            if ( 'publish' != get_post_status( $post ) && 'company_single' == get_post_type( $post ) ) {
                $post_has_changed = false;
            }

            return $post_has_changed;
        }, 11, 3 );
        /*
         * Hack to reset \PodsMeta::$current_pod
         */
        add_action( 'pods_meta_save_post_company_single', function ( array $data, \Pods $pod, int $id ) {
            global $wp_current_filter;
            if ( 'pods_meta_save_post_company_single' != $wp_current_filter[ count( $wp_current_filter ) - 2 ] ) {
                $pod = pods( 'company_single', [ 'where' => [ [ 'key' => 'ID', 'value' => $id, 'compare' => '!=' ] ] ] );
                $pod->fetch();
                pods_meta()->save_post( $id, get_post( $id ) );
            }

        }, 10, 3 );
        add_action( 'pre_post_update', function ( int $post_id ) {
            if ( 'company_single' == get_post_type( $post_id ) ) {
                $revisions = wp_get_post_revisions( $post_id );

                if ( 1 == count( $revisions ) && empty( get_post_meta( end( $revisions )->ID, 'url', true ) ) ) {
                    update_metadata( 'post', end( $revisions )->ID, 'url', get_post_meta( $post_id, 'url', true ) );
                }
            }
        } );
        add_action( '_wp_put_post_revision', function ( int $revision_id ) {
            global $wpdb;
            $data    = [
                'post_id'    => null,
                'old_url'    => null,
                'new_url'    => null,
                'blog_id'    => null,
                'blog_title' => null,
                'date'       => null,
            ];
            $post_id = wp_get_post_parent_id( $revision_id );

            if ( 'company_single' != get_post_type( $post_id ) ) {
                return;
            }

            $revisions = wp_get_post_revisions( $post_id );
            array_shift( $revisions );
            if ( count( $revisions ) > 0 ) {
                $previous_revision = array_shift( $revisions );
                $data['old_url']   = get_post_meta( $previous_revision->ID, 'url', true );
                if ( is_array( $data['old_url'] ) ) {
                    $data['old_url'] = end( $data['old_url'] );
                }
            }
            $data['new_url'] = get_post_meta( $revision_id, 'url', true );
            if ( is_array( $data['new_url'] ) ) {
                $data['new_url'] = end( $data['new_url'] );
            }
            if ( ( isset( $previous_revision ) && ( $data['old_url'] == $data['new_url'] || empty( get_post_meta( $previous_revision->ID ) ) ) ) || ( ! isset( $previous_revision ) && empty( $data['new_url'] ) ) ) {
                return;
            }
            $data['post_id']    = $revision_id;
            $data['blog_id']    = get_current_blog_id();
            $data['blog_title'] = get_site()->blogname;
            $data['date']       = current_time( 'mysql' );

            $wpdb->insert( $wpdb->base_prefix . 'company_affiliate_log', $data, [ '%d', '%s', '%s', '%d', '%s', '%s' ] );
        }, 11 );
        add_action( 'wp_delete_post_revision', function ( int $revision_id, $revision ) {
            global $wpdb;
            if ( 'company_single' != get_post_type( wp_get_post_parent_id( $revision ) ) ) {
                return;
            }
            $wpdb->delete( $wpdb->base_prefix . 'company_affiliate_log', [ 'post_id' => $revision_id ] );
        }, 10, 2 );
        remove_action( 'post_updated', 'wp_save_post_revision' );
        add_action( 'save_post', function ( int $post_ID, \WP_Post $post, bool $update ) {
            if ( $update ) {
                wp_save_post_revision( $post_ID );
            }
        }, 11, 3 );
        add_filter( 'set-screen-option', function ( $value, string $option, int $new_value ) {
            if ( 'toplevel_page_company_affiliate_log_network_per_page' == $option ) {
                $value = $new_value;
            }

            return $value;
        }, 10, 3 );
        foreach ( [ 'wp_ajax_nopriv_record_company_visit', 'wp_ajax_record_company_visit' ] as $hook ) {
            add_action( $hook, function () {
                if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }
                $company = 0;
                if ( ! empty( $_POST['company'] ) ) {
                    $company = absint( $_POST['company'] );
                }
                $transient = "company_single_visit_{$company}_{$ip}";
                $post      = get_post( $company );
                if ( ! empty( $post ) && 'company_single' === get_post_type( $post ) && 'publish' === get_post_status( $post ) && ! get_transient( $transient ) ) {
                    pods( 'company_single', $company )->add_to( 'visits', 1 );
                    set_transient( $transient, true, HOUR_IN_SECONDS );
                }
            } );
        }

        add_filter( 'posts_clauses', function ( $pieces, $query ) {
            if ( $query->get( 'orderby' ) != 'title_number' ) {
                return $pieces;
            }
            global $wpdb;
            $field = $wpdb->posts . '.post_name';

            $pieces['fields']  .= $wpdb->prepare(
                ', LEAST(' . implode( ',', array_fill( 0, 10, 'IFNULL(NULLIF(LOCATE(%s, ' . $field . '), 0), ~0)' ) )
                . ') AS first_int',
                '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
            );
            $pieces['orderby'] = $wpdb->prepare(
                'IF(first_int = ~0, ' . $field . ', CONCAT('
                . 'SUBSTR(' . $field . ', 1, first_int - 1),'
                . 'LPAD(CAST(SUBSTR(' . $field . ', first_int) AS UNSIGNED), LENGTH(~0), %s),'
                . 'SUBSTR(' . $field . ', first_int + LENGTH(CAST(SUBSTR(' . $field . ', first_int) AS UNSIGNED)))'
                . ')) ' . $query->get( 'order' )
                , 0
            );
            return $pieces;
        }, 10, 2 );

        add_action( 'init', function () {
            global $wp_post_types;

            if ( post_type_exists( 'button_tracking' ) || post_type_exists( 'button_tracking_date' ) || post_type_exists( 'reviews' ) || post_type_exists( 'reviews_reply' ) || post_type_exists( 'creditcard' ) || post_type_exists( 'credit_check_company' ) || post_type_exists( 'saving_tip' )  ) {
                // exclude from search results
                $wp_post_types['button_tracking']->exclude_from_search = true;
                $wp_post_types['reviews']->exclude_from_search = true;
                $wp_post_types['reviews_reply']->exclude_from_search = true;
                $wp_post_types['creditcard']->exclude_from_search = true;
                $wp_post_types['credit_check_company']->exclude_from_search = true;
                $wp_post_types['saving_tip']->exclude_from_search = true;
                if ( isset( $wp_post_types['button_tracking_date'] ) ) {
                    $wp_post_types['button_tracking_date']->exclude_from_search = true;
                }
            }
        }, 999 );

        foreach ( [ 'wp_ajax_nopriv_record_post_visit', 'wp_ajax_record_post_visit' ] as $hook ) {
            add_action( $hook, function () {
                if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }
                $postID = 0;
                if ( ! empty( $_POST['postid'] ) ) {
                    $postID = absint( $_POST['postid'] );
                }
                $transient = "post_single_visit_{$postID}_{$ip}";
                $post      = get_post( $postID );

                $counter = get_post_meta( $postID, 'wpb_post_views_count', true );
                if ( $counter ) :
                    $counter++;
                    update_post_meta( $postID, 'wpb_post_views_count', $counter );
                else :
                    update_post_meta( $postID, 'wpb_post_views_count', 1 );
                endif;
                rocket_clean_post($postID);

                wp_die();
            } );
        }
    }
}
