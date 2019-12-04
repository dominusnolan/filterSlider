<?php


namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Abstracts\Slider;

use Financer\FilterSlider\Table\TopCompaniesTable;

/**
 * Class Top_Companies
 * @package Financer\FilterSlider\Shortcode
 */
class Top_Companies extends Shortcode {

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
		if ( empty( $atts['year'] ) ) {
			$atts['year'] = date( 'Y' );
		}
		if ( ! isset( $atts['limit'] ) ) {
			$atts['limit'] = - 1;
		}
		$atts['limit'] = (int) $atts['limit'];
		$where         = [
			[
				'key'     => 'review.comment_date',
				'value'   => [ ( new \DateTime( 'first day of january ' . $atts['year'] ) )->format( 'Y-m-d' ), ( new \DateTime( '11:59:59 last day of december ' . $atts['year'] ) )->format( 'Y-m-d H:i:s' ) ],
				'compare' => 'BETWEEN'
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
		];
		if ( ! empty( $atts['type'] ) ) {
			$where['company_type.slug'] = $atts['type'];
		}
		ob_start();
		$pod = pods(
			'company_single', [
				'limit'   => $atts['limit'],
				'select'  => [
					't.ID as ID',
					't.post_title as title',
					't.post_name as name',
					't.post_status',
					'AVG(CAST(`review_rating`.`meta_value` AS DECIMAL(12, 4))) as rating',
				],
				'join'    => [
					'LEFT JOIN `@wp_comments` AS `review` ON `review`.`comment_post_ID` = `t`.`ID`',
					"LEFT JOIN `@wp_commentmeta` AS `review_rating` ON `review_rating`.`comment_id` = `review`.`comment_ID` AND `review_rating`.`meta_key` = 'crfp-average-rating'",
				],
				'where'   => $where,
				'orderby' => 'rating DESC',
				'groupby' => 't.ID',
				'having'  => [ 'rating IS NOT NULL', 'count( `review`.`comment_ID`) >= 5' ],
				'expires' => Slider::CACHE_PERIOD,
			]
		);
		?>
		<?php if ( ! empty( $atts['title'] ) ): ?>
            <h2 class="secondtitle"><?= $atts['title'] ?></h2><i class="arrow"></i>
		<?php endif; ?>
        <div class="tw-bs table_cont tabs_inner fN">
			<?php TopCompaniesTable::build( $pod, null ); ?>
        </div>
		<?php
		return ob_get_clean();
	}
}