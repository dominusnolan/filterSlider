<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Plugin;
use Financer\FilterSlider\Table\LoanTable;
use Financer\FilterSlider\Util;

/**
 * Class Company_Loans
 * @package Financer\FilterSlider\Shortcode
 */
class Company_Loans extends Shortcode {

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
		wp_enqueue_script( 'dummy-company-loans', Plugin::GetUri( 'js/dummy.js' ), [
			'jquery',
			'jquery-ui-core',
			'jquery-ui-tabs',
		] );
		wp_add_inline_script( 'dummy-company-loans', self::_renderJs() );
		ob_start();
		echo <<<HTML
<style type="text/css">
    #tabs, #tabs ul {
        display: inline;
        background: transparent;
        border: none;
        padding: 0;
    }
</style>
HTML;
		$settings     = pods( 'slider_settings' );
		$amounts_list = $settings->field( 'loan_amounts' );
		$amounts_list = array_filter( array_map( 'absint', array_map( 'trim', explode( ',', $amounts_list ) ) ) );
		$periods_list = $settings->field( 'loan_periods' );
		$periods_list = array_filter( array_map( 'absint', array_map( 'trim', explode( ',', $periods_list ) ) ) );
		sort( $periods_list, SORT_NUMERIC );
		$periods_list_named = [];
		$where              = [
			'relation' => 'OR',
		];
		$datasets = [];
		$results            = [];
		foreach ( $amounts_list as $amount ) {
			$where[] = [
				'relation' => 'AND',
				[
					'key'     => 'amount_range_minimum',
					'value'   => $amount,
					'compare' => '<=',
				],
				[
					'key'     => 'amount_range_maximum',
					'value'   => $amount,
					'compare' => '>=',
				]
			];
		}
		foreach ( $periods_list as $period ) {
			$pod = pods(
				'loan_dataset', [
					'select'  => [
						't.ID',
						'amount_range_minimum',
						'amount_range_maximum',
						'period_range_minimum',
						'period_range_maximum',
						'interest_rate',
						'highest_annual_interest_rate',
						'loan_restriction',
					],
					'limit'   => - 1,
					'where'   => array_merge( [
						'company_parent.ID' => $atts['company'],
						"CAST(d.period_range_minimum AS DECIMAL(12,4)) <= {$period}",
						"CAST(d.period_range_maximum AS DECIMAL(12,4)) >= {$period}",
					], [ $where ] ),
					'expires' => Slider::CACHE_PERIOD,
				]
			);

			$data = $pod->data();
			if ( $data ) {
				$periods_list_named[ $period ] = Util::getPeriod( $period );
			} else {
				$data = [];
			}
			foreach ( $data as &$item ) {
				$item->period = $period;
			}
			$datasets = array_merge( $datasets, $data );
		}
		foreach ( $datasets as $dataset ) {
			$results = array_merge( $results, self::_processPeriod( $dataset, $amounts_list ) );
		}
		usort( $results, function ( $a, $b ) {
			if ( $a->amount == $b->amount ) {
				return 0;
			}

			return ( $a->amount < $b->amount ) ? - 1 : 1;
		} );
		echo <<<HTML
<div class="tw-bs table_cont tabs_inner fN">
    <div id="tabs" class="dropdown-tabs">
        <ul>
			<li class="init">[SELECT]</li>
HTML;
		$count = 0;
		foreach ( $periods_list_named as $period => $text ) {
			$count ++;
			if ( $count == 1 ) {
				echo <<<HTML
<li data-period="$period" class="first-list">
    <a href="#tab_content">$text</a>
</li>
HTML;
			} else {
				echo <<<HTML
<li data-period="$period">
    <a href="#tab_content">$text</a>
</li>
HTML;
			}
		}
		echo <<<HTML
</ul>
<div id="tab_content">
HTML;
		LoanTable::build( $results );
		echo <<<HTML
        </div>
    </div>
</div>
HTML;

		return ob_get_clean();
	}

	/**
	 *
	 */
	private static function _renderJs() {
		return <<<JS
(function () {
    (function init() {
        if (typeof jQuery !== 'undefined' && typeof jQuery.ui !== 'undefined') {

            jQuery(function ($) {
                function updateRows(event, ui) {
                    var panel = ui.newPanel || ui.panel;
                    var tab = ui.newTab || ui.tab;
                    var rows = panel.find('.item-table .item-results .item-row');
                    rows.removeClass('animated pulse');
                    var period = tab.data('period');
                    $(rows).each(function (i, v) {
                        if ($(v).data('period') == period) {
                            $(v).addClass('animated pulse');
                            $(v).removeAttr('style');
                        }
                        else {
                             $(v).hide();
                        }
                    });
                    $(rows).filter(':visible').each(function (i, v) {
                         $(v).addClass(i % 2 ? 'even' : 'odd');
                    })
                }

                $('#tabs').tabs({
                    create: updateRows,
                    activate: updateRows
                });
                
                $("#tabs.dropdown-tabs ul").children('li.init').html( $("#tabs.dropdown-tabs ul").children('li.first-list').html() );   
                
                $("#tabs.dropdown-tabs ul").on("click", ".init", function(e) {
                    e.preventDefault();
				    $(this).closest("#tabs.dropdown-tabs ul").children('li:not(.init)').toggle();
				    $("#tabs.dropdown-tabs ul").find('.ui-tabs-active').hide();
				});
				
				var allOptions = $("#tabs.dropdown-tabs ul").children('li:not(.init)');
				$("#tabs.dropdown-tabs ul").on("click", "li:not(.init)", function() {
				    allOptions.removeClass('selected');
				    $("#tabs.dropdown-tabs ul li").show();
				    $(this).addClass('selected');
				    
				    $("#tabs.dropdown-tabs ul").children('.init').html($(this).html());
				    $(this).hide();
				    allOptions.toggle();
				});
            });

        } else {
            setTimeout(init, 50);
        }
    })
    ();

})();
JS;
	}

	/**
	 * @param \stdClass $dataset
	 * @param array     $amounts
	 *
	 * @return array
	 */
	private static function _processPeriod( \stdClass $dataset, array $amounts ) {
		$results = [];
		foreach ( $amounts as $amount ) {
			if ( $amount >= $dataset->amount_range_minimum && $amount <= $dataset->amount_range_maximum ) {
				$results[] = self::_processAmount( $dataset, $amount );
			}
		}

		return $results;
	}

	/**
	 * @param \stdClass $dataset
	 * @param int       $amount
	 *
	 * @return object
	 */
	private static function _processAmount( \stdClass $dataset, int $amount ) {
		$dataset->amount = $amount;
		$pod             = pods(
			'loan_dataset', [
				'select'  => array_merge( [
					't.ID',
					'amount_range_minimum',
					'amount_range_maximum',
					'period_range_minimum',
					'period_range_maximum',
					'interest_rate',
				], array_map( function ( $line ) {
					return str_replace( 'loan_datasets.d.', '', $line );
				}, Slider::generateLoanTotalSql( $amount, $dataset->period, 'loan_datasets.d.' ) ) ),
				'limit'   => - 1,
				'where'   => [ 't.ID' => $dataset->ID ],
				'expires' => Slider::CACHE_PERIOD,
			]
		);
		$data = $pod->data();
		$data = end( $data );
		$result          = (object) array_merge( (array) $dataset, (array) $data );
		foreach ( array_keys( (array) $result ) as $var ) {
			if ( is_numeric( $result->$var ) ) {
				$result->$var = (float) $result->$var;
			}
		}

		return $result;
	}
}
