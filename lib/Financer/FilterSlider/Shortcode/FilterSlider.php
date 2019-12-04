<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;

/**
 * Class FilterSlider
 * @package Financer\FilterSlider\Shortcode
 */
class FilterSlider extends Shortcode {
	/**
	 * @param array       $atts
	 *
	 * @param null|string $content
	 *
	 * @param string      $tag
	 * @param bool        $ajax
	 *
	 * @return string
	 */
	public static function render( $atts, string $content = null, string $tag = null, $ajax = false ): string {
		$fullClassPath = '\Financer\FilterSlider\Slider\\' . $atts['slider'];
		if ( class_exists( $fullClassPath ) ) {
			return ( new \ReflectionClass( $fullClassPath ) )->newInstance( array_merge( $atts, $_GET ) )->render();
		}

		return '';
	}

	/**
	 *
	 */
	public static function register() {
		parent::register();
		$dir = new \DirectoryIterator( dirname( dirname( __FILE__ ) ) . '/Slider' );
		foreach ( $dir as $file ) {
			if ( $file->isFile() ) {
				$sliderId   = $file->getBasename( '.php' );
				$sliderSlug = strtolower( $sliderId );
				add_action(
					'wp_ajax_nopriv_filter_slider_' . $sliderSlug, [
						get_called_class(),
						'renderAjax',
					]
				);
				add_action(
					'wp_ajax_filter_slider_' . $sliderSlug, [
						get_called_class(),
						'renderAjax',
					]
				);
				add_action(
					'wp_ajax_nopriv_filter_slider_pdf_' . $sliderSlug, [
						get_called_class(),
						'renderPdf',
					]
				);
				add_action(
					'wp_ajax_filter_slider_pdf_' . $sliderSlug, [
						get_called_class(),
						'renderPdf',
					]
				);
			}
		}
		add_action(
			'wp_ajax_filter_slider_report', [
				get_called_class(),
				'addReport',
			]
		);
		add_action(
			'wp_ajax_nopriv_filter_slider_report', [
				get_called_class(),
				'addReport',
			]
		);
		add_action(
			'wp_ajax_purge_cache', [
				get_called_class(),
				'clearCache',
			]
		);
		add_action(
			'wp_ajax_nopriv_purge_cache', [
				get_called_class(),
				'clearCache',
			]
		);
	}

	/**
	 *
	 */
	public static function renderAjax() {
		$sliderId = str_replace( 'filter_slider_', '', $_POST['action'] );
		$class    = '';
		$dir      = new \DirectoryIterator( dirname( __DIR__ ) . '/Slider' );
		foreach ( $dir as $file ) {
			if ( $file->isFile() ) {
				$className = $file->getBasename( '.php' );
				if ( strtolower( $className ) == $sliderId ) {
					$class = $className;
					break;
				}
			}
		}
		if ( ! empty( $class ) ) {
			$fullClassPath = '\Financer\FilterSlider\Slider\\' . $class;
			if ( class_exists( $fullClassPath ) ) {
				( new \ReflectionClass( $fullClassPath ) )->newInstance( array_merge( $_GET, $_POST ) )->renderAjax();
				wp_die();
			}
		}
	}

	public static function clearCache() {
		if ( parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_HOST ) == parse_url( home_url(), PHP_URL_HOST ) ) {
			if ( function_exists( 'do_rocket_purge_cron' ) ) {
				do_rocket_purge_cron();
				$post_id = url_to_postid( $_SERVER['HTTP_REFERER'] );
				if ( $post_id ) {
					$post = get_post( $post_id );
					run_rocket_bot_after_clean_post( $post, [], false );
					do_action( 'shutdown' );
				}
				wp_die();
			}
		}
	}

	public static function renderPdf() {
		$run404   = function () {
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            get_template_part( 404 ); exit();
		};
		$sliderId = str_replace( 'filter_slider_pdf_', '', $_GET['action'] );
		$params   = unserialize( base64_decode( $_GET['slider_params'] ) );
		$_REQUEST = array_merge( $params, $_REQUEST );
		if ( empty( $params ) ) {
			$run404();
		}
		$class = '';
		$dir   = new \DirectoryIterator( dirname( __DIR__ ) . '/Slider' );
		foreach ( $dir as $file ) {
			if ( $file->isFile() ) {
				$className = $file->getBasename( '.php' );
				if ( strtolower( $className ) == $sliderId ) {
					$class = $className;
					break;
				}
			}
		}
		if ( ! empty( $class ) ) {
			$fullClassPath = '\Financer\FilterSlider\Slider\\' . $class;
			if ( class_exists( $fullClassPath ) ) {
				( new \ReflectionClass( $fullClassPath ) )->newInstance( $_REQUEST )->renderPdf();
				wp_die();
			}
		} else {
			$run404();
		}
	}

	public function addReport() {
		$id   = (int) $_POST['id'];
		$type = get_post_type( $id );
		if ( ! empty( $type ) ) {
			$reports = pods( 'report', [ 'where' => [ 'item_id' => $id ] ] );
			if ( $reports->total() ) {
				$reports->save( 'report_count', $reports->field( 'report_count' ) + 1 );
			} else {
				$reports->add( [
					'item_type'    => $type,
					'item_id'      => $id,
					'report_count' => 1,
					'status'       => 'publish'
				] );
			}
			wp_die();
		}
	}
}
