<?php

namespace Financer\FilterSlider\Table;


use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Abstracts\Table;
use Financer\FilterSlider\Interfaces\TableInterface;
use Financer\FilterSlider\Surface\Item;
use Financer\FilterSlider\Util;
use Financer\FilterSlider\Surface\Data;
use Financer\FilterSlider\Surface\Row;
use Financer\FilterSlider\Surface\Surface;

/**
 * Class SavingsAccountTable
 * @package Financer\FilterSlider\Table
 */
class SavingsAccountTable extends Table implements TableInterface {

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
		$table->setHead( [
			new Data( 'saving_account', __( 'Saving Account', 'fs' ), [ 'title' => __( 'The name of the saving account', 'fs' ) ] ),
			new Data( 'interest_rate', __( 'Interest rate', 'fs' ), [ 'title' => __( 'The interest rate of the saving account', 'fs' ) ] ),
			new Data( 'minimum_savings_time', __( 'Minimum Savings Time', 'fs' ), [ 'title' => __( 'The minimum time the money must be locked', 'fs' ) ] ),
			new Data( 'minimum_savings_amount', __( 'Minimum Savings Amount', 'fs' ), [ 'title' => __( 'The least amount you need to have in the account', 'fs' ) ] ),
			new Data( 'maximum_savings_amount', __( 'Maximum Savings Amount', 'fs' ), [ 'title' => __( 'The maximum amount of money you can save', 'fs' ) ] ),
			new Data( 'free_withdrawals', __( 'Free Withdrawals', 'fs' ), [ 'title' => __( 'The amount of free withdrawals you can do', 'fs' ) ] ),
		] );
		if ( count( $query ) > 0 ) {
			$slider->showResultsTitle($slider, $query, $pod);
			foreach ( $query as $pos => $result ) {
				$period = $result->min_save_time < 1 ? ( $result->min_save_time < 0 ? __( 'N/A', 'fs' ) : Util::getPeriod( $result->min_save_time * 365 ) ) : (float) $result->min_save_time . ' ' . __( 'Years', 'fs' );
				if ( $result->floating_interest_rate ) {
					$small = '<span class="yellowSpan">' . __( 'Floating interest', 'fs' ) . '</span>';
				} else {
					$small = '<span class="greenSpan">' . __( 'Fixed interest', 'fs' ) . '</span>';

				}

				$table->addRow(
					new Item( [
						new Row( [
							// Title
							$result->title,
							( ( ! empty( $result->interest_rate ) || $result->interest_rate == 0 ) ? $result->interest_rate . '%' : '&nbsp;' ) . '<br />' . $small,
							// Period
							0 == $period ? __( 'No Minimum', 'fs' ) : ( - 1 >= $period ? __( 'N/A', 'fs' ) : $period ),
							// Minimum Save Amount
							- 1 >= $result->min_save_amount ? __( 'N/A', 'fs' ) : Util::moneyFormat( $result->min_save_amount ) . ' ' . __( 'usd', 'fs' ),
							// Minimum Save Amount
							- 1 == $result->max_save_amount ? __( 'N/A', 'fs' ) : Util::moneyFormat( $result->max_save_amount ) . ' ' . __( 'usd', 'fs' ),
							// Free Widthdrawls
							$result->free_withdrawals == - 1 ? __( 'N/A', 'fs' ) : $result->free_withdrawals,
						], [ 'class' => ( $pos % 2 ) ? 'even' : 'odd' ] )
					] ) );
			}
		} else {
			$table->addRow( new Row( [ new Data( false, __( 'No savings accounts found in that search. Try using less filters.', 'fs' ), [ 'colspan' => 100 ] ) ] ) );
		}
		echo $table->render();
		?>

		<?php
	}
}
