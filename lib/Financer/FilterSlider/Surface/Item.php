<?php


namespace Financer\FilterSlider\Surface;


class Item extends Row {
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
		$attributes['class'] = implode( ' ', array_merge( [ Element::ITEM ], array_filter( explode( ' ', $attributes['class'] ) ) ) );


		return $this->_renderElement( 'div', function () use ( $data ) {

			$html = '';

			foreach ( $data as $d ) {
				$html .= $d->render();
			}

			return $html;
		},
			$attributes );
	}

	public function setData( array $data ) {
		$this->_data = $data;
	}
}