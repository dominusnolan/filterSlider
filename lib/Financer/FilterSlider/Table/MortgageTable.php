<?php

namespace Financer\FilterSlider\Table;


use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Abstracts\Table;
use Financer\FilterSlider\Interfaces\TableInterface;
use Financer\FilterSlider\SortUtil;
use Financer\FilterSlider\Surface\Data;
use Financer\FilterSlider\Surface\Item;
use Financer\FilterSlider\Surface\Row;
use Financer\FilterSlider\Surface\Surface;

/**
 * Class MortgageSliderTable
 * @package Financer\FilterSlider\Table
 */
class MortgageTable extends Table implements TableInterface {
	/**
	 * @param null|\Pods $pod
	 *
	 * @param Slider     $slider
	 *
	 * @return void
	 *
	 */
	public static function build( \Pods $pod, Slider $slider = null ) {
		$query = $pod->data();
		if ( ! $query ) {
			$query = [];
		}
		$table = new Surface( [ 'class' => 'table table-striped' ] );
		$table->setHead(
			new Row(
				[
					new Data( false, __( '3 Months', 'fs' ), [ 'title' => __( '3 Months', 'fs' ) ] ),
					new Data( false, __( '1 Year', 'fs' ), [ 'title' => __( '1 Year', 'fs' ) ] ),
					new Data( false, __( '2 Years', 'fs' ), [ 'title' => __( '2 Years', 'fs' ) ] ),
					new Data( false, __( '3 Years', 'fs' ), [ 'title' => __( '3 Years', 'fs' ) ] ),
					new Data( false, __( '4 Years', 'fs' ), [ 'title' => __( '4 Years', 'fs' ) ] ),
					new Data( false, __( '5 Years', 'fs' ), [ 'title' => __( '5 Years', 'fs' ) ] ),
					new Data( false, __( '7 Years', 'fs' ), [ 'title' => __( '7 Years', 'fs' ) ] ),
					new Data( false, __( '10 Years', 'fs' ), [ 'title' => __( '10 Years', 'fs' ) ] ),
				]
			)
		);
        if ( count( $query ) > 0 ) {
            $slider->showResultsTitle($slider, $query, $pod);
            $query = SortUtil::processUnlimited( $query, array_keys( get_object_vars( $query[0] ) ) );
			foreach ( $query as $pos => $result ) {
				$table->addRow(
					new Item( [
						new Row( [
							//3 Months
							_isset( $result->{'3_mir'} ) && $result->{'3_mir'} != '' ? ( - 1 == $result->{'3_mir'} ? __( 'N/A', 'fs' ) : $result->{'3_mir'} . '%' ) : '&nbsp;',
							//1 Year
							_isset( $result->{'1_yir'} ) && $result->{'1_yir'} != '' ? ( - 1 == $result->{'1_yir'} ? __( 'N/A', 'fs' ) : $result->{'1_yir'} . '%' ) : '&nbsp;',
							//2 Years
							_isset( $result->{'2_yir'} ) && $result->{'2_yir'} != '' ? ( - 1 == $result->{'2_yir'} ? __( 'N/A', 'fs' ) : $result->{'2_yir'} . '%' ) : '&nbsp;',
							//3 Years
							_isset( $result->{'3_yir'} ) && $result->{'3_yir'} != '' ? ( - 1 == $result->{'3_yir'} ? __( 'N/A', 'fs' ) : $result->{'3_yir'} . '%' ) : '&nbsp;',
							//4 Years
							_isset( $result->{'4_yir'} ) && $result->{'4_yir'} != '' ? ( - 1 == $result->{'4_yir'} ? __( 'N/A', 'fs' ) : $result->{'4_yir'} . '%' ) : '&nbsp;',
							//5 Years
							_isset( $result->{'5_yir'} ) && $result->{'5_yir'} != '' ? ( - 1 == $result->{'5_yir'} ? __( 'N/A', 'fs' ) : $result->{'5_yir'} . '%' ) : '&nbsp;',
							//7 Years
							_isset( $result->{'7_yir'} ) && $result->{'7_yir'} != '' ? ( - 1 == $result->{'7_yir'} ? __( 'N/A', 'fs' ) : $result->{'7_yir'} . '%' ) : '&nbsp;',
							//10 Years
							_isset( $result->{'10_yir'} ) && $result->{'10_yir'} != '' ? ( - 1 == $result->{'10_yir'} ? __( 'N/A', 'fs' ) : $result->{'10_yir'} . '%' ) : '&nbsp;',

						] )
					], [ 'class' => ( $pos % 2 ) ? 'even' : 'odd' ] ) );
			}
		} else {

            if ($pod->singleCompany) {
                $table->addRow(new Row([new Data(false, __('No mortgages found.', 'fs'), ['colspan' => 100])]));
            } else {
                $table->addRow(new Row([new Data(false, __('No mortgages found in that search.', 'fs'), ['colspan' => 100])]));
            }
		}
		echo $table->render();
	}
}
