<?php

namespace Financer\FilterSlider\Table;


use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Abstracts\Table;
use Financer\FilterSlider\Interfaces\TableInterface;
use Financer\FilterSlider\Surface\Data;
use Financer\FilterSlider\Surface\Item;
use Financer\FilterSlider\Surface\Row;
use Financer\FilterSlider\Surface\Surface;
use Financer\FilterSlider\Util;

/**
 * Class TopRatedCompaniesTable
 * @package Financer\FilterSlider\Table
 */
class TopRatedCompaniesTable extends Table {

	/**
	 * @param null|\Pods $pod
	 *
	 * @param Slider     $slider
	 *
	 * @internal param int $year
	 *
	 * @internal param Slider $slider
	 *
	 * @internal param null $postType
	 *
	 * @internal param array $query
	 */
	public static function build( array $query, \Pods $pod, Slider $slider = null ) {
		if ( ! $query ) {
			$query = [];
		}
		$generalSettings = pods( 'general_settings' );
		$sliderSetting   = pods( 'slider_settings' );
		$remove_apr      = $sliderSetting->field( 'remove_apr' );
		$remove_style    = "";
		if ( $remove_apr == 1 ) {
			$remove_style = "display:none;";
		}

		$table = new Surface( [ 'class' => 'table table-striped' ] );
		$table->setHead( new Row( [
			new Data( 'loan_company', __( 'Loan company', 'fs' ), [ 'title' => __( 'Logo for company', 'fs' ), 'class' => 'vit' ] ),
			new Data( 'ratings', __( 'Ratings', 'fs' ) ),
			new Data( 'total_reviews', __( 'Total Reviews', 'fs' ) ),

		] ) );
		if ( count( $query ) > 0 ) {
			foreach ( $query as $pos => $result ) {
				$args = [
					'post_id'     => $result->ID,
					'post_status' => 'publish',
					'post_type'   => 'company_single',
					'status'      => 'approve',
					'date_query'  => [
						[
							'year' => $result->period,
						]
					]
				];

				$comments      = get_comments( $args );
				$comment_count = count( $comments );

				if ( ! empty( $result->specific_affiliate_url ) ) {
					$url_link = $result->specific_affiliate_url;
				} else {
					$url_link = user_trailingslashit( get_permalink( $result->ID ) . 'redirect' );
				}

				$func = function ( $meta_value, $post_id, $meta_key ) use ( $result ) {
					if ( $post_id == $result->ID ) {
						if ( 'crfp-average-rating' == $meta_key ) {
							$meta_value = ( intval( ( $result->rating * 2 ) + 0.5 ) / 2 );
						}
					}

					return $meta_value;
				};

				add_filter( 'get_post_metadata', $func, 10, 3 );
				$logo_array = get_post_meta( $result->ID, 'logo', true );

				$table->addRow(
					new Item( [
						new Row( [
							// Logo
							new Data( 'logo', '<i class="mega-icon-eraser report"><a href="#" title="' . __( 'Wrong data? Report this item', 'fs' ) . '">&nbsp;</a></i>' . ( ( null !== $slider && ! $slider->isPdf() ) ? ( ! $result->representative_example && ! $output_loan_tags ? '<i title="' . __( 'More information', 'fs' ) . '" class="toggle-details fa fa-plus" ></i>' : '' ) : '' ) . '<a href="' . get_permalink( $result->ID ) . '">' . '<img title="' . $result->title . '" src="' . $logo_array['guid'] . '" />' . '</a>' ),
							// Company Ratings
							new Data( 'company_ratings', self::showStars( $result->ID ) . '<h4>' . round( do_shortcode('[total_rating id='.$result->ID.']'), 1 ) . '/5</h4>' ),

							// Company Total Reviews
							new Data( 'comment-count', $comment_count ),


						], [ 'data-id' => $result->ID, 'data-period' => $result->period, 'class' => ( $pos % 2 ? ' even' : ' odd' ) ] )
					] )
				);
				remove_filter( 'get_post_metadata', $func );
				$pod->fetch();
			}
		} else {
			$table->addRow( new Row( [ new Data( false, __( 'No companies found', 'fs' ), [ 'colspan' => 100 ] ) ] ) );
		}
		echo $table->render();
	}
}
