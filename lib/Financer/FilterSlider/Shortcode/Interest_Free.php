<?php


namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Plugin;
use Financer\FilterSlider\Table\InterestFreeTable;

/**
 * Class Interest_Free
 * @package Financer\FilterSlider\Shortcode
 */
class Interest_Free extends Shortcode {

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
			$json = base64_decode( $_GET['params'] );
			if ( ! $json ) {
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
	 *
	 * @param string      $tag
	 * @param bool        $ajax
	 *
	 * @return mixed|string
	 */
	static function render( $atts, string $content = null, string $tag = null, $ajax = false ): string {
		if ( ! isset( $atts['limit'] ) ) {
			$atts['limit'] = false;
		}
		ob_start();
		$class    = self::$id;
		$instance = self::$_instance;

		$pod      = pods(
			'company_single', [
				'limit'   => - 1,
				'select'  => [
					't.post_title as title',
					't.post_name as name',
					't.ID as ID',
					'ej_partner',
					'bad_history',
					'favorite',
					'minalder',
					'loan_datasets.d.amount_range_maximum AS amount_range_maximum',
					'loan_datasets.d.amount_range_minimum AS amount_range_minimum',
					'CAST(loan_datasets.d.period_range_maximum AS DECIMAL(12,4)) AS period_range_maximum',
					'CAST(loan_datasets.d.period_range_minimum AS DECIMAL(12,4)) AS period_range_minimum',
				],
				'join'    => [
					'LEFT JOIN `@wp_postmeta` AS `meta_crfp-average-rating` ON `meta_crfp-average-rating`.`post_id` = `t`.`ID`',
				],

				'where'   => [
					[
						'key'   => 'meta_crfp-average-rating.meta_key',
						'value' => 'crfp-average-rating'
					],

					[
						'key'   => 'loan_datasets.d.interest_rate',
						'value' => '0',
						'compare' => '<='
					],

					[
						'key'   => 'loan_datasets.d.fee_flat',
						'value' => '0',
						'compare' => '<='
					],

					[
						'key'   => 'loan_datasets.d.fee_percent',
						'value' => '0',
						'compare' => '<='
					],

					'loan_datasets.post_status'     => 'publish'
				],
				'orderby' => 'CAST(meta_crfp-average-rating.meta_value AS DECIMAL(10,2)) DESC',
				'expires' => Slider::CACHE_PERIOD,
			]
		);


		$show_ui  = $atts['limit'] && $pod->total() > $atts['limit'];
		if ( ! $ajax && $show_ui ) {
			wp_enqueue_script( $class, Plugin::GetUri( 'js/dummy.js' ), [ 'jquery' ] );
			$hide_text        = __( 'Collapse Items', 'fs' );
			$show_text        = __( 'Show All', 'fs' );
			$url              = admin_url( 'admin-ajax.php' );
			$json             = base64_encode( wp_json_encode( $atts ) );
			$pod->rows        = array_slice( $pod->rows, 0, $atts['limit'] );
			$pod->total       = $atts['limit'];
			$pod->total_found = $atts['limit'];
			$loader_image     = home_url( '/wp-content/themes/financer/graph/loader.gif' );
			wp_add_inline_script( $class, <<<JS
(function () {
    (function init() {
        if (typeof jQuery !== 'undefined' && typeof jQuery.ui !== 'undefined') {
            jQuery(function ($) {
                              $('#{$class}-{$instance}').on('click', 'a.show-all', function () {
                    var self = this;
                    if ($('#{$class}-{$instance}').data('show_all')) {
                        $('#{$class}-{$instance} .item-table .item-row:gt({$atts['limit']})').fadeToggle(400, 'swing', function () {
                            if ($('#{$class}-{$instance} .item-table .item-row:gt({$atts['limit']})').is(':visible')) {
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
			<?php if ( ! empty( $atts['title'] ) ): ?>
            <h2 class="secondtitle"><?= $atts['title'] ?></h2><i class="arrow"></i>
		<?php endif; ?>
            <div id="<?= $class; ?>-<?= $instance ?>" class="tw-bs table_cont tabs_inner fN">
		<?php endif; ?>
		<?php InterestFreeTable::build( $pod, null ); ?>
		<?php if ( $show_ui ): ?>
            <div class="show-all-holder"><a class="show-all"><?php _e( 'Show All', 'fs' ); ?></a></div>
		<?php endif; ?>
		<?php if ( ! $ajax ): ?>
            </div>
		<?php endif; ?>
		<?php
		self::$_instance ++;

		return ob_get_clean();
	}
}
