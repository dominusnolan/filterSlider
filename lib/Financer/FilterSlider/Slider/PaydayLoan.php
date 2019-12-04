<?php

namespace Financer\FilterSlider\Slider;


/**
 * Class PayDayLoan
 * @package Financer\FilterSlider\Slider
 */
class PayDayLoan extends GenericLoan {
	protected function labels() {
		parent::labels();
		$this->htmlLabels = [
			                    'interest_free' => __( 'show only interest free payday loans', 'fs' ),
			                    'bad_history'   => __( 'payday loan with bad credit history', 'fs' ),
			                    'filter_gov'    => __( 'payday loan without credit check', 'fs' ),
			'guide_1'           => __( 'Drag the sliders on the left to find your payday loan', 'fs' ),
			                    'slider_type'        => __( 'payday loan', 'fs' ),
			                    'slider_type_plural'     => __( 'payday loans', 'fs' ),

		                    ] + $this->htmlLabels;
	}

}
