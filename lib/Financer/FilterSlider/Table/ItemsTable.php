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
 * Class ItemsTable
 * @package Financer\FilterSlider\Table
 */
class ItemsTable extends Table{

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
		

		$table = new Surface( [ 'class' => 'table table-striped custom-items table_cont', 'id'=>'products'] );

		if ( count( $query ) > 0 ) {
			foreach ( $query as $pos => $result ) {
				$type = get_post_type( $result->ID );
				$features_html = Util::constructSingleCompanyFeatures($result->ID);
				$link = get_permalink( $result->ID );
				$url_link      = get_the_permalink( $result->ID ) . 'redirect?b=i';
				$applyurl  = "window.open('".$url_link."')";
				$total_comments = do_shortcode('[total_rating id='.$result->ID.']');

				$read_reviews = ' <a href="' . get_permalink( $result->ID ) . '#reviews">' . __( 'Read', 'fs' ) . '&nbsp;' . $total_comments . ' ' . __( 'reviews.', 'fs' ) . '</a>';
				$stars    = Table::showStars( $result->ID );

                $items    = [];
                if( $type == 'creditcard' ){
                	$item = pods( $type, $result->ID);
                	$features_html = $item->field( 'card_details' );
                	$items [] =
					new Row(
						[
							// Logo
							new Data( 'logo', '<a href="'.$link .'" class="company-logo"><img title="' . $result->title . '" src="' . $item->field( 'logo._src' ) . '" /></a>', [ 'class' => 'vit logo-column company-listing' ] ),
							// Features
							new Data( 'features', $features_html, [ 'class' => 'item-features' ] ),
							// Button
							new Data( 'application', '<a href="#" onclick="'.$applyurl.'" class="button small applyYellow" data-cname="'. get_the_title($result->ID) .'" data-cid="'. $result->ID .'" data-plink="'. get_permalink($result->ID) .'" rel="nofollow"> ' . __( 'Application', 'fs' ) . ' </a>', [ 'class' => 'loan-apply' ] ),
						], [ 'data-id' => $result->ID, 'class' => 'flex-columns sort-item ' ]  );
                }else{
					$items [] =
					new Row(
						[
							// Logo
							new Data( 'logo', '<a href="'.$link .'" class="company-logo"><img title="' . $result->title . '" src="' . $pod->field( 'logo._src' ) . '" /></a><span class="totalReviews">' . $stars . $read_reviews .'</span>', [ 'class' => 'vit logo-column company-listing' ] ),
							// Features
							new Data( 'features', $features_html, [ 'class' => 'item-features' ] ),
							// Button
							new Data( 'application', '<a href="#" onclick="'.$applyurl.'" class="button small applyYellow" data-cname="'. get_the_title($result->ID) .'" data-cid="'. $result->ID .'" data-plink="'. get_permalink($result->ID) .'" rel="nofollow"> ' . __( 'Application', 'fs' ) . ' </a>', [ 'class' => 'loan-apply' ] ),
						], [ 'data-id' => $result->ID, 'class' => 'flex-columns sort-item ' ]  );
				}
				$items [] = new Row( [
						//tags
						new Data( 'more_information', '<a href="'.$link .'" class="toggle-details fa fa-plus">' . __( 'Read Reviews', 'fs' ) . '</a>', [ 'class' => 'more-information' ] ),
					], [ 'class' => 'more-information-row ' ] );

				$table->addRow( new Item( $items, [ 'class' => 'taglist' ] ) );


				$pod->fetch();
			}
		} else {
			$table->addRow( new Row( [ new Data( false, __( 'No companies found', 'fs' ), [ 'colspan' => 100 ] ) ] ) );
		}
		echo $table->render();
	}
}