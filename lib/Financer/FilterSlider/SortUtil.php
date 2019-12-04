<?php


namespace Financer\FilterSlider;


/**
 * Class SortUtil
 * @package Financer\FilterSlider
 */
class SortUtil {

	/**
	 * @param array $items
	 * @param array $fields
	 */
	public static function processFavorite( array &$items, array $fields = [ 'summ' ] ) {
		$featured = [];
		foreach ( $items as $pos => $result ) {
			if ( $result->favorite ) {
				$featured[] = $result;
				unset( $items[ $pos ] );
			}
		}
		$featured = self::processUnlimited( $featured, $fields );
		$items    = self::processUnlimited( $items, $fields );
		$items    = array_merge( $featured, $items );
	}

	/**
	 * @param array $items
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public static function processUnlimited( array $items, array $fields = [ 'summ' ] ) {
		$unlimited = [];
		foreach ( $items as $pos => $result ) {
			foreach ( $fields as $field ) {
				if ( $result->$field == - 1 ) {
					$unlimited[] = $result;
					unset( $items[ $pos ] );
				}
			}
		}

		return $items + $unlimited;
	}
}