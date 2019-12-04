<?php


namespace Financer\FilterSlider\Surface;



/**
 * Class Row
 * @package Financer\FilterSlider\Surface
 */
class Row extends \Surface\Row {
	/**
	 * Render the row
	 *
	 * @return string
	 */
	public function render() {
		$data = $this->_data;

		$attributes = $this->getAttributes();

		if ( ! isset( $attributes['class'] ) ) {
			$attributes['class'] = '';
		}
		$attributes['class'] = implode( ' ', array_merge( [ Element::TR ], array_filter( explode( ' ', $attributes['class'] ) ) ) );


		return $this->_renderElement( 'div', function () use ( $data ) {

			$html = '';

			foreach ( $data as $d ) {
				$html .= $d->render();
			}

			return $html;
		},
			$attributes );
	}

	/**
	 * Set the row data
	 *
	 * The array of data may contain a mixture of Surface\Data objects and
	 * native data types
	 *
	 * All data will be converted to Surface\Data objects
	 *
	 * @array $data
	 * @param array $data
	 */
	public function setData( array $data ) {
		foreach ( $data as $i => $d ) {
			if ( ! $d instanceof Data ) {
				$data[ $i ] = new Data( 'false', $d );
			}
		}
		$this->_data = $data;
	}
}
