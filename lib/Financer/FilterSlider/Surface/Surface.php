<?php


namespace Financer\FilterSlider\Surface;


/**
 * Class Table
 * @package Financer\FilterSlider\Surface
 */
class Surface extends \Surface\Surface {
	/**
	 * @inheritDoc
	 */
	public function render() {
		$head = $this->_head;
		$foot = $this->_foot;
		$rows = $this->_rows;

		$attributes = $this->getAttributes();
		if ( ! isset( $attributes['class'] ) ) {
			$attributes['class'] = '';
		}
		$attributes['class'] = implode( ' ', array_merge( [ Element::TABLE ], array_filter( explode( ' ', $attributes['class'] ) ) ) );


		return $this->_renderElement( 'div',
			function () use ( $head, $foot, $rows ) {

				$table = '';

				if ( $head instanceof Row ) {

					$table .= Element::_renderElement( 'div',
						function () use ( $head ) {

							return $head->render();
						}, [ 'class' => Element::THEAD ] );
				}

				if ( $foot instanceof Row ) {

					$table .= Element::_renderElement( 'div',
						function () use ( $foot ) {

							return $foot->render();
						}, [ 'class' => Element::TFOOT ] );
				}

				$table .= Element::_renderElement( 'div',
					function () use ( $rows ) {

						$bodyHtml = '';

						foreach ( $rows as $row ) {
							$bodyHtml .= $row->render();
						}

						return $bodyHtml;
					}, [ 'class' => Element::TBODY ] );

				return $table;
			}, $attributes );
	}

	/**
	 * Add an array of head items to the table
	 *
	 * @param array| Row $head
	 *
	 * @return Surface
	 */
	public function setHead( $head ) {
		if ( ! $head instanceof Row ) {
			$head = new Row( $head );
		}
		foreach ( $head->getData() as $headItem ) {
			$headItem->setElementType( Element::TH );
		}
		$this->_head = $head;

		return $this;
	}

	/**
	 * Add an array of foot items to the table
	 *
	 * @param array|Row $foot
	 *
	 * @return Surface
	 */
	public function setFoot( $foot ) {
		if ( ! $foot instanceof Row ) {
			$foot = new Row( $foot );
		}
		$this->_foot = $foot;

		return $this;
	}

	/**
	 * Add a row to the table
	 *
	 * @param array|Row $row
	 *
	 * @return Surface
	 */
	public function addRow( $row ) {
		if ( ! $row instanceof Row ) {
			$row = new Row( $row );
		}
		$this->_rows[] = $row;

		return $this;
	}
}