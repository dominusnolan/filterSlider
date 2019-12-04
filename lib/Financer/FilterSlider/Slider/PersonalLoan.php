<?php

namespace Financer\FilterSlider\Slider;

/**
 * Class PersonalLoan
 * @package Financer\FilterSlider\Slider
 */
class PersonalLoan extends GenericLoan {

	protected function labels() {
		parent::labels();
		$this->htmlLabels = [
			                    'interest_free' => __( 'show only interest free loans', 'fs' ),
			                    'bad_history'   => __( 'personal loan with bad credit history', 'fs' ),
			                    'filter_gov'    => __( 'personal loan without credit check', 'fs' ),
			'guide_1'           => __( 'Drag the sliders on the left to find your personal loan', 'fs' ),
			                    'slider_type'        => __( 'personal loan', 'fs' ),
			                    'slider_type_plural'     => __( 'personal loans', 'fs' ),

		                    ] + $this->htmlLabels;
	}

    protected function stepFilters()
    {
        parent::stepFilters($type = 'personal');
    }

}

