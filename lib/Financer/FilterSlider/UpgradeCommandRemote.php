<?php

namespace Financer\FilterSlider;
error_reporting( E_ALL );
/**
 * Implements FilterSliderUpgradeCommandRemote command.
 */

/**
 * Class UpgradeCommandRemote
 * @package Financer\FilterSlider
 */
class UpgradeCommandRemote extends \WP_CLI_Command {
	/**
	 * @var bool
	 */
	private $_upgraded = false;
	/**
	 * @var null
	 */
	private $_version = null;
	/**
	 * @var int
	 */
	private $_blog = 0;
	/**
	 * @var string
	 */
	private $_podJson = false;

	/**
	 *
	 */
	public static function register() {
		\WP_CLI::add_command( 'finance_filter_slider_upgrade_remote', get_called_class() );
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
     *     wp finance_filter_slider_upgrade_remote <json>
     *
     * @when after_wp_load
     */
	function __invoke( $args, $assoc_args ) {
		global $wpdb;
		$this->_blog = ! empty( $assoc_args['blog_id'] ) ? $assoc_args['blog_id'] : null;
        $this->_podJson = $args[0] ? $args[0] : false;

		if ($this->_podJson == false) {
            die(":::: pod json is invalid ::::\n");
        } else {

		    if (json_decode($this->_podJson) != NULL && count($this->_podJson) > 10) {
		        die("works:: {$this->_podJson}\n");
            }

            die("not:: {$this->_podJson}\n");
        }

		if ( empty( $this->_blog ) ) {
			$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = '{$wpdb->siteid}' AND spam = '0' AND deleted = '0' AND archived = '0' ORDER BY registered DESC", ARRAY_A );
			foreach ( (array) $blogs as $details ) {
				\WP_CLI::log( \WP_CLI::launch_self( 'finance_filter_slider_upgrade', [], [ 'blog_id' => $details['blog_id'] ], false, true )->stdout );
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
			if ( $this->_upgraded ) {
				\WP_CLI::success( sprintf( 'Upgraded site %s%s from %s to %s', $blog_info->domain, $blog_info->path, $this->_version, Plugin::VERSION ) );
			} else {
				\WP_CLI::log( sprintf( 'No upgrade required for site %s%s', $blog_info->domain, $blog_info->path, $this->_version, Plugin::VERSION ) );

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
		if ( ! class_exists( 'Pods_Migrate_Packages' ) ) {
			$_GET['toggle'] = 1;
			pods_components()->toggle( 'migrate-packages' );
			pods_components()->load();
		}
		if ( ! class_exists( 'Pods_Advanced_Relationships' ) ) {
			$_GET['toggle'] = 1;
			pods_components()->toggle( 'advanced-relationships' );
			pods_components()->load();
		}
		pods_api()->import_package( file_get_contents( Plugin::GetDir( 'pods.json' ) ) );
		pods_api()->save_field( [ 'pod' => 'loan_dataset', 'name' => 'company_parent', 'sister_id' => pods_api()->load_field( [ 'pod' => 'company_single', 'name' => 'loan_datasets' ] )['id'] ] );
		pods_api()->cache_flush_pods();
		if ( defined( 'PODS_PRELOAD_CONFIG_AFTER_FLUSH' ) && PODS_PRELOAD_CONFIG_AFTER_FLUSH ) {
			pods_api()->load_pods();
		}
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
