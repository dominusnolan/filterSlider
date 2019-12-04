<?php


namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Abstracts\Slider;
use Financer\FilterSlider\Plugin;
use Financer\FilterSlider\Slider\GenericLoan;

/**
 * Class Loan_Slider
 * @package Financer\FilterSlider\Shortcode
 */
class Loan_Slider extends Shortcode {

	/**
	 * @param array       $atts
	 *
	 * @param null|string $content
	 *
	 * @param string      $tag
	 * @param bool        $ajax
	 *
	 * @return mixed|string
	 */
	static function render( $atts, string $content = null, string $tag = null, $ajax = false ): string {
		$atts = shortcode_atts( [
			'title'          => '',
			'page'           => pods_field( 'general_settings', null, 'main_loan_page.ID' ),
			'default_amount' => 0,
			'default_period' => 0,
			'default_age' => 18,
            'minimal' => 'true'
		], $atts );
		ob_start();
		$deps = [
			'jquery-ui-widget',
			'jquery-ui-mouse',
			'jquery-ui-slider',
			'jquery-ui-tabs',
			'jquery-ui-dialog',
			'jquery-touch-punch',
			'jquery-effects-core',
		];
		foreach ( $deps as $script ) {
			wp_enqueue_script( $script );
		}
		$slider = new GenericLoan( [] );
		$slider->setAmount( (int) $atts['default_amount'] );
		$slider->setPeriod( (int) $atts['default_period'] );
		$slider->setAge( (int) $atts['default_age'] );
		$slider->setMinimalStatus( $atts['minimal'] );
		$mapOutput = $slider->renderJsMaps();

		wp_enqueue_script( 'dummy', Plugin::GetUri( 'js/dummy.js' ), [ 'jquery' ] );
		wp_add_inline_script( 'dummy', $mapOutput . "\n" . self::_renderJs( $slider, $atts ) );
		echo <<<HTML
		<div id="{$slider->getInstanceId()}_form">
HTML;
		$slider->setSteps( [ 'amount', 'period', 'submit' ] );
		$slider->runSteps();
		echo <<<HTML
		</div>
HTML;
		$slider::increaseInstanceCounter();

		return ob_get_clean();
	}

	/**
	 * @param Slider $slider
	 * @param array  $atts
	 *
	 * @return string
	 * @internal param string $instanceId
	 *
	 */
	private static function _renderJs( Slider $slider, array $atts ): string {
		$url         = get_the_permalink( $atts['page'] );
		$preloaderJs = $slider->renderPreloaderJs();

		$sliderInitJs = $slider->renderSliderInitJs();
		$sliderDataJs = $slider->renderJsData();

		///Slider.php:1673 same as
		return <<<JS
jQuery(function ($) {
    $sliderInitJs;
    $('#{$slider->getInstanceId()}_form .get_results').click(function (e) {
        var expDate = new Date();
    	var gdprStatus = 'Rejected';

		expDate.setTime(expDate.getTime() + ( 525600 * 60 * 1000)); // add 1 
    	$('.stepFilters input').each(function(){
    		if ( $(this).is(':checked')) {
	            $(this).val(1);
	            $(this).attr('checked');
	            $.cookie( $(this).attr('name') , 1, { path: '/', expires: expDate });
	        }else{
	        	$(this).val(0);
	            $(this).removeAttr('checked');
	            $.cookie( $(this).attr('name') , 0, { path: '/', expires: expDate });
	        }
    	});
 		
        //generate_table_this->instanceId( $(this).attr('data-toggle') );
        $('#{$slider->getInstanceId()} .show-all-holder').remove();
        $('.counterWrapper').parent().addClass('hidden');
        //var data = $('#{this->instanceId}_form input:checkbox').map(function() {
	    var data = $('.stepFilters input:checkbox').map(function() {
	        value = this.checked ? this.value : "0";
	        if (value != '0') {
	            value = 1
	         return { name: this.name, value: value };   
	        }
		});
        if($(this).attr('data-toggle')){
        	data.push({name: "param_sort", value: $(this).attr('data-toggle')});
    	}


        $('#{$slider->getInstanceId()}_form .ui-slider').each(function () {
            var name = $(this).data('name');
            var value = $(this).slider('value');
            var value_object = window['{$slider->getInstanceId()}_' + name.replace('param_', '') + 'Map'][value];
            data.push({
                name: name,
                value: value_object ? value_object.value : 0
            });
        });
        
        data = $.param(data);

        var url = window.location.href;
        var urlParts = window.location.href.split('query');
        var loanType = 'false';
        if (urlParts.length > 1) {
            loanType = urlParts[1];
            var paramList = loanType.split('/')
            loanType = paramList[1];
            
            url = urlParts[0];
        }
        
        
        var fullurl = $(this).data("slider-url");
        
        if (fullurl) {                
            if (fullurl.indexOf("query") === -1) {
                fullurl = fullurl + 'query/';
            }
            
            url = fullurl + data.replace(/[\&\=]/g, '/');
            //console.log('full', url);
            window.history.pushState("", "", url);
            window.open(url,"_self")
        } else {
            if (url.indexOf("query") === -1) {
                url = url + 'query/';
            }
            
            url = url + loanType + '/' + data.replace(/[\&\=]/g, '/');
            window.history.pushState("", "", url);
        }
        
        
        e.preventDefault();
        return false;
    }).closest('.widget').addClass('widget_filter_slider_loan');
});
$preloaderJs
JS;

	}

}
