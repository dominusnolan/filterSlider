<?php


namespace Financer\FilterSlider\Surface;


use Financer\FilterSlider\Util;

/**
 * Class Data
 * @package Financer\FilterSlider\Surface
 */
class Data extends \Surface\Data {
    protected $_validElementTypes = [
        Element::TH,
        Element::TD
    ];

    private $_tag = false;

    /**
     * @inheritDoc
     */
    public function __construct( $tag, $value, $attributes = [], $elementType = Element::TD ) {
        $this->_tag = $tag;
        parent::__construct( $value, $attributes, $elementType );
    }

    /**
     * Render the data in an element
     *
     * @return string
     */
    public function render()
    {
        $value = $this->_value;
        $tag = isset($this->_tag) ? $this->_tag : false;
        $attributes = $this->getAttributes();
        $sliderType = isset($attributes['minimal']) ? $attributes['minimal'] : false;
        if (isset($attributes['minimal'])) {
            unset($attributes['minimal']);
        }

        // this field works specific only for mobile - we remove it from the global attributes array list
        $mobileButtonSpecial = 'mobileButtonSpecial';
        $mobileContent = false;
        if (array_key_exists($mobileButtonSpecial, $attributes)) {
            $mobileContent = $attributes[$mobileButtonSpecial];
            unset($attributes[$mobileButtonSpecial]);
        }

        $flagMobile = Util::mobileDataCustomization($tag);

        $flag = Util::specialDynamicDataCustomization($tag);

        //disable minimal true
        $minimalSlider = ['loan_amount', 'application', 'more_information', 'apply'];
        $singleSlider = ['loan_amount'];
        if ($sliderType == 'true' && (in_array($tag, $minimalSlider))) {
            $flag = 'hide';
        } elseif (($sliderType == 'single') && (in_array($tag, $singleSlider))) {
            $flag = 'hide';
        }

        $company = pods('data_customization', [
            'orderby' => 'date DESC',
            'limit' => 1,
        ]);
        $status = 'show';
        while ($company->fetch()):
            $status = $company->display($tag);
        endwhile;

        /*if ($tag == 'nominal_apr') {
            die("status: $status and flag: $flag and flagMobile: $flagMobile");
        }*/

        //special hides
        if ($tag == 'credit_score') {
            $sliderSettings = pods('slider_settings');
            $enable_credit_score = $sliderSettings->field('enable_credit_score');
            if ($enable_credit_score=='1') {
                $status = 'show';
            } else {
                $status = 'hide';
            }
        }


        if ($flag == 'hide' || $status == 'hide') {
           return '';
        }

        if ( ! isset( $attributes['class'] ) ) {
            $attributes['class'] = '';
        }
        $attributes['class'] = implode( ' ', array_merge( [ $this->_elementType ], array_filter( explode( ' ', $attributes['class'] ) ) ) );

        if ($flagMobile == 'show') {
            //die("tag $tag status: $status and flag: $flag and flagMobile: $flagMobile");
            $value .= $mobileContent;
            $attributes['class'] .= ' main-column';
        } else {
            //loan-mobile-view
            $attributes['class'] = str_replace('loan-total', '', $attributes['class']);
            $attributes['class'] = str_replace('loan-mobile-view', '', $attributes['class']);
        }

        $before = null;
        $after  = null;

		if ( isset( $attributes['before'] ) ) {
			$before = $attributes['before'];
			unset( $attributes['before'] );
		}
		if ( isset( $attributes['after'] ) ) {
			$after = $attributes['after'];
			unset( $attributes['after'] );
		}

		return $before . $this->_renderElement( 'div', function () use ( $value ) {
				return $value;
			}, $attributes ) . $after;
	}
}
