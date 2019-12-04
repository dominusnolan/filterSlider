<?php

namespace Financer\FilterSlider\Slider;

/**
 * Class CarLoan
 * @package Financer\FilterSlider\Slider
 */
class CarLoan extends GenericLoan {

	protected $maxWhere = 5;

	/**
	 *
	 */
	protected function labels() {
		parent::labels();
		$this->htmlLabels = [
			                    'bad_history' => __( 'Car loan with bad credit history', 'fs' ),
			                    'filter_gov'  => __( 'Car loan without credit check', 'fs' ),
			'guide_1'           => __( 'Drag the sliders on the left to find your car loan', 'fs' ),
			                    'slider_type'            => __( 'car loan', 'fs' ),
			                    'slider_type_plural'     => __( 'car loans', 'fs' ),

		                    ] + $this->htmlLabels;
	}

    protected function stepFilters()
    {
        parent::stepFilters($type = 'car');
    }

}
