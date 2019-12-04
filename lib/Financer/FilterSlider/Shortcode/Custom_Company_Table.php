<?php

namespace Financer\FilterSlider\Shortcode;


use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Plugin;
use Financer\FilterSlider\Table\CompanyTable;

/**
 * Class Custom_Company_Table
 * @package Financer\FilterSlider\Shortcode
 */
class Custom_Company_Table extends Shortcode {

	private static $_instance = 0;

	/**
	 * @inheritDoc
	 */
	public static function register() {
		parent::register();
		add_action(
			'wp_ajax_' . self::$id, [
				get_called_class(),
				'renderAjax',
			]
		);
		add_action(
			'wp_ajax_nopriv_' . self::$id, [
				get_called_class(),
				'renderAjax',
			]
		);
	}

	public static function renderAjax() {
		if ( ! empty( $_GET['params'] ) ) {
			try {
				$json = Crypto::decryptWithPassword( $_GET['params'], SECURE_AUTH_KEY );
			} catch ( \Exception $e ) {
				exit;
			}
			$atts = json_decode( $json, true );
			if ( ! $atts ) {
				exit;
			}
			echo self::render( $atts, null, null, true );
			exit;
		}
	}

	/**
	 * @param array       $atts
	 *
	 * @param null|string $content
	 * @param string      $tag
	 * @param bool        $ajax
	 *
	 * @return mixed|string
	 */
	public static function render( $atts, string $content = null, string $tag = null, $ajax = false ): string {

		$filters    = preg_grep( '/filter_field_[0-9+]/', array_keys( $atts ) );
		$filterList = [];
		if ( ! isset( $atts['limit'] ) ) {
			$atts['limit'] = false;
		}
		if ( isset( $atts['hide_non_affiliate'] ) ) {
			$atts['hide_non_affiliate'] = (int) $atts['hide_non_affiliate'];
		}
		if ( $atts['limit'] ) {
			$atts['limit'] = (int) $atts['limit'];
		}

		if ( ! empty( $atts['filter_field'] ) ) {
			$field        = $atts['filter_field'];
			$compare = ! empty( $atts['filter_compare'] ) ? $atts['filter_compare'] : '=';
			$compareValue = $atts['filter_field_value'];
			if ( in_array(
				strtolower( $compare ), [
					'in',
					'not in',
				]
			) ) {
				$compareValue = explode( ',', $compareValue );
				array_walk( $compareValue, 'trim' );
			}
			$filterList[] = [
				'key'     => $field,
				'value'   => $compareValue,
				'compare' => $compare,
			];
		}



		foreach ( $filters as $filter ) {
			preg_match( '/filter_field_([0-9+])/', $filter, $matches );
			$field        = $atts[ 'filter_field_' . $matches[1] ];
			$compare_key  = 'filter_field_compare_' . $matches[1];
			$compare      = ! empty( $atts[ $compare_key ] ) ? $atts[ $compare_key ] : '=';
			$compareValue = $atts[ 'filter_field_value_' . $matches[1] ];
			if ( in_array(
				strtolower( $compare ), [
					'in',
					'not in',
				]
			) ) {
				$compareValue = explode( ',', $compareValue );
				array_walk( $compareValue, 'trim' );
			}
			$filterList[] = [
				'key'     => $field,
				'value'   => $compareValue,
				'compare' => $compare,
			];
		}


		$non_affiliate = [];
		if ( $atts['hide_non_affiliate'] == 1 ){
			$non_affiliate[] = [
				'key'   => 'ej_partner',
				'value' => 0
			];
			$filterList = array_merge($non_affiliate,$filterList );
		}

		$class    = self::$id;
		$instance = self::$_instance;
		ob_start();
		$pod         = pods(
			'company_single', [
				'limit'   => - 1,
				'select'  => [
					't.post_title as title',
					't.post_name as name',
					't.ID as ID',
					'ej_partner',
					'visits',
					'bad_history',
					'helgutbetalning ',
					'favorite',
					'minalder',
					'custom_representative_example',
					'overall_rating as rating'
				],
				'where'   => array_merge( [
					[
						'key'   => 'loan_datasets.post_status',
						'value' => 'publish'
					],
				], $filterList ),
				'orderby' => 'rating DESC',
				'expires' => Slider::CACHE_PERIOD,
			]
		);
		$show_ui     = $atts['limit'] && $pod->total() > $atts['limit'];
		if ( ! $ajax && $show_ui ) {
			wp_enqueue_script( $class, Plugin::GetUri( 'js/dummy.js' ), [ 'jquery' ] );
            $hide_text        = __( 'Collapse Items', 'fs' );
			$show_text        = __( 'Show All', 'fs' );
			$url              = admin_url( 'admin-ajax.php' );
			$json             = Crypto::encryptWithPassword( wp_json_encode( $atts ), SECURE_AUTH_KEY );
			$pod->rows        = array_slice( $pod->rows, 0, $atts['limit'] );
			$pod->total       = $atts['limit'];
			$pod->total_found = $atts['limit'];
			$loader_image     = home_url( '/wp-content/themes/financer/graph/loader.gif' );
			wp_add_inline_script( $class, <<<JS
(function () {
    (function init() {
        if (typeof jQuery !== 'undefined' && typeof jQuery.ui !== 'undefined') {
            jQuery(function ($) {
            	function starRating(){
					var ratings0 = $('.item-table .rating0');
					for (var i = 0; i < ratings0.length; i++) {
		              var r = new SimpleStarRating(ratings0[i]);
		            }
				}
                $('#{$class}-{$instance}').on('click', 'a.show-all', function () {
                    var self = this;
                    if ($('#{$class}-{$instance}').data('show_all')) {
                        $('#{$class}-{$instance} .item-table .item-row:not(.tag-example):gt(6)').parent('.sortable-item').fadeToggle(400, 'swing', function () {
                            if ($('#{$class}-{$instance} .item-table .item-row:not(.tag-example):gt(6)').parent('.sortable-item').is(':visible')) {
                               $(self).text('{$hide_text}')
                            }	
                            else {
                               $(self).text('{$show_text}')
                            }
                        });
                    }
                    else {
                        $('.status_bar').fadeIn();
                        $.get('$url', {params: '$json', action: '$class'}, function (html) {
                            $('.status_bar').fadeOut();
                            $('#{$class}-{$instance}').html(html);
                            $('#{$class}-{$instance} .item-table .item-row:gt({$atts['limit']})').hide().fadeIn();
                            starRating();
                            $(document).on('click', '.table_cont .fa', function () {
						        var detail = $(this).closest('.item-row').next('.details');
						        detail.toggleClass('expanded');
						        $(this).toggleClass('fa-plus', !detail.hasClass('expanded'));
						        $(this).toggleClass('fa-minus', detail.hasClass('expanded'));
						    });
                        });
                        $('#{$class}-{$instance}').data('show_all', true);
                    }

                })
            });
        } else {
            setTimeout(init, 50);
        }
    })
    ();

})();
JS
			);
			echo <<<HTML
	<div class="status_bar">
	  <img class="loader"
			 src="$loader_image" width="248" height="248">
	   </div>
HTML;
		}

		if ( ! $ajax ):
			?>

            <h2 class="secondtitle"<?php echo( ! empty( $atts['anchor'] ) ? ' id="' . $atts['anchor'] . '"' : '' ) ?>><?= $atts['title'] ?></h2><i class="arrow"></i>
            <div id="<?= $class; ?>-<?= $instance ?>" class="tw-bs table_cont tabs_inner fN  slider-results">
		<?php endif; ?>
		<?php CompanyTable::build( $pod, null ); ?>
		<?php if ( $show_ui ): ?>
            <div class="show-all-holder"><a class="show-all button small"><?php _e( 'Show All', 'fs' ); ?></a></div>
		<?php endif; ?>
		<?php if ( ! $ajax ): ?>
            </div>
		<?php endif; ?>
		<?php
		self::$_instance ++;

		return ob_get_clean();
	}
}
