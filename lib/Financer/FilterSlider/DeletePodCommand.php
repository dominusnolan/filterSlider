<?php

namespace Financer\FilterSlider;
error_reporting( E_ALL );
/**
 * Implements FilterSliderDeletePodCommand command.
 */

/**
 * Class DeletePodCommand
 * @package Financer\FilterSlider
 */
class DeletePodCommand extends \WP_CLI_Command {
    /**
     * @var bool
     */
    private $_pod_exists = true;
    /**
     * @var string
     */
    private $_pod_name = false;
    /**
     * @var null
     */
    private $_version = null;
    /**
     * @var int
     */
    private $_blog = 0;

    /**
     *
     */
    public static function register() {
        \WP_CLI::add_command( 'finance_filter_slider_delete_pod', get_called_class() );
    }

    /**
     * Prints a greeting.
     *
     * ## OPTIONS
     *
     * <pod_name>
     * : The name of the pod you want to delete.
     *
     *
     * ## EXAMPLES
     *
     *     wp finance_filter_slider_delete_pod <pod_name>
     *
     * @when after_wp_load
     */
    function __invoke( $args, $assoc_args ) {
        global $wpdb;

        $this->_pod_name = $args[0]; //'exit_intent_popup';//! empty( $assoc_args['pod_name'] ) ? $assoc_args['pod_name'] : false;
        $this->_blog = ! empty( $assoc_args['blog_id'] ) ? $assoc_args['blog_id'] : null;

        if ( empty( $this->_blog ) ) {


            if ($this->_pod_name == false) {
                die(":::: pod name is invalid ::::\n");
            }

            echo ":::: Pod ($this->_pod_name) will be deleted in 8secs ::::\n";
            sleep(8);

            $blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = '{$wpdb->siteid}' AND spam = '0' AND deleted = '0' AND archived = '0' ORDER BY registered DESC", ARRAY_A );
            foreach ( (array) $blogs as $details ) {
                \WP_CLI::log( \WP_CLI::launch_self( 'finance_filter_slider_delete_pod', [], [ 'blog_id' => $details['blog_id'] ], false, true )->stdout );

                sleep(5);

            }
        } else {
            switch_to_blog( $this->_blog );
            add_filter(
                'pods_error_die',
                function () {
                    return true;
                }
            );
            $this->_version = get_option( 'financer_filter_slider_version', '0' );
            $this->_import_packages();
            update_option( 'financer_filter_slider_version', Plugin::VERSION );
            $blog_info = get_blog_details( $this->_blog );
            if ( $this->_pod_exists ) {
                \WP_CLI::log( sprintf( 'Pod (%s) did NOT delete for site %s%s', $this->_pod_name, $blog_info->domain, $blog_info->path ) );
            } else {
                \WP_CLI::success( sprintf( 'Pod (%s) has been deleted for site %s%s', $this->_pod_name, $blog_info->domain, $blog_info->path ) );
            }
            restore_current_blog();

        }
    }

    /**
     *
     */
    private function _import_packages() {
        pods_init()->setup( $this->_blog );
        pods_init()->load_components();
        pods_components()->get_components();
        pods_components()->load();

        pods_api()->delete_pod ([ 'name' => $this->_pod_name]);
        $this->_pod_exists = pods_api()->pod_exists([ 'name' => $this->_pod_name]);

    }


    /**
     * @param $data
     *
     * @return mixed
     */
    private static function val( $data ) {
        return is_array( $data ) ? $data[0] : $data;
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    private static function num_val( $data ) {
        return str_replace( ' ', '', self::val( $data ) );
    }

    /**
     * @param $data
     *
     * @return bool
     */
    private static function bool_val( $data ) {
        return self::val( $data ) == 'on' || self::val( $data ) == 1;
    }

    private function _flush_pods() {
        pods_api()->cache_flush_pods();
        if ( defined( 'PODS_PRELOAD_CONFIG_AFTER_FLUSH' ) && PODS_PRELOAD_CONFIG_AFTER_FLUSH ) {
            pods_api()->load_pods();
        }
    }
}
