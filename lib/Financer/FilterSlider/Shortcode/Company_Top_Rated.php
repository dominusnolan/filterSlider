<?php
namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Plugin;
use Financer\FilterSlider\Table\TopRatedCompaniesTable;
use \Datetime;
use \DateInterval;

/**
 * Class Company_Top_Rated
 * @package Financer\FilterSlider\Shortcode
 */
class Company_Top_Rated extends Shortcode {

	private static $_instance = 0;

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

		// Setting the Period Year
		$periods_list_named = array();

		$min_year = '2015';
		$min_year = DateTime::createFromFormat('Y', $min_year);

		$curr_year = new DateTime();

		$diff = $min_year->diff($curr_year);

		for ($i = 0; $i <= $diff->format('%Y'); $i++) {
			$year = clone $min_year;

			$added = $year->add(new DateInterval('P' . $i . 'Y'));
			$added = $added->format('Y');

			array_push($periods_list_named, $added);
		}

		
		ob_start();

		$datasets = [];

		foreach ( $periods_list_named as $period ) {
			$pod = pods(
				'company_single', [
					'select'  => [
						't.ID as ID',
						't.post_title as title',
						't.post_name as name',
						't.post_status',
						'YEAR(review.comment_date) as period',
						'AVG(CAST(`review_rating`.`meta_value` AS DECIMAL(12, 4))) as rating',
					],
					'join'    => [
						'LEFT JOIN `@wp_comments` AS `review` ON `review`.`comment_post_ID` = `t`.`ID`',
						"LEFT JOIN `@wp_commentmeta` AS `review_rating` ON `review_rating`.`comment_id` = `review`.`comment_ID` AND `review_rating`.`meta_key` = 'crfp-average-rating'",
					],
					'limit'   => -1,
					'where'   => [
					    [
							'key'     => 'review.comment_date',
							'value'   => [ ( new \DateTime( 'first day of january ' . $period ) )->format( 'Y-m-d' ), ( new \DateTime( '11:59:59 last day of december ' . $period ) )->format( 'Y-m-d H:i:s' ) ],
							'compare' => 'BETWEEN'
						],
						[
							'key'     => 'company_type.slug',
							'compare' => '==',
							'value'   => 'loan_company'
						],
						[
							'key'     => 'review_rating.meta_value',
							'compare' => 'EXISTS'
						],
						[
							'key'     => 'review_rating.meta_value',
							'value'   => '',
							'compare' => '!='
						],
					] ,
					'orderby' => 'rating DESC',
					'groupby' => 't.ID',
					'having'  => [ 'rating IS NOT NULL', 'count( `review`.`comment_ID`) > 5' ],
					'expires' => Slider::CACHE_PERIOD,
				]
			);
			
			$data = $pod->data();
			if ( $data ) {
				//$periods_list_named[ $period ] = Util::getPeriod( $period );
			} else {
				$data = [];
			}

			$datasets = array_merge( $datasets, $data );
		}


		?>
		<?php if ( ! empty( $atts['title'] ) ): ?>
            <h2 class="secondtitle"><?= $atts['title'] ?></h2><i class="arrow"></i>
		<?php endif; ?>
        <div class="tw-bs table_cont tabs_inner top-rated-table">
        	<div id="tabs" class="dropdown-tabs">
		    	<ul>
		    		<li class="init">[SELECT]</li>
		    		<?php
					foreach ( $periods_list_named as $period ) {
						if ( $period == date('Y') ) {
							?>
							<li data-period="<?php echo $period ?>" class="selected">
    							<a href="#tab_content"><?php echo $period ?></a>
							</li>
							<?php
						} else {
							?>
							<li data-period="<?php echo $period ?>">
							    <a href="#tab_content"><?php echo  $period ?></a>
							</li>
							<?php
						}
					}

					?>
		    	</ul>   
		    	<div id="tab_content">
			    	<?php TopRatedCompaniesTable::build( $datasets, $pod, null ); ?> 
			    </div>
		    </div>
        </div>
		<?php
		return ob_get_clean();
	}


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
					
					// If initial page load, set period to current year
					if (ui.tab) {
						var period = (new Date()).getFullYear();
					} else {
						var period = tab.data('period');
					}

                    $(rows).each(function (i, v) {
                        if ($(v).data('period') == period) {
                            $(v).addClass('animated pulse');
                            $(v).removeAttr('style');
                            $(v).parent('.sortable-item').show();
                        }
                        else {
                            $(v).parent('.sortable-item').hide();
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
              
                $("#tabs.dropdown-tabs ul").children('li.init').html( $("#tabs.dropdown-tabs ul").children('li.selected').html() );   

                $("#tabs.dropdown-tabs ul").on("click", ".init", function(e) {
                    e.preventDefault();
				    $(this).closest("#tabs.dropdown-tabs ul").children('li:not(.init)').toggle();
				    $("#tabs.dropdown-tabs ul").find('.selected').hide();
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
}