<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Util;
/**
 * Class Item
 * @package Financer\FilterSlider\Shortcode
 */
class Rep_Example extends Shortcode {

	/**
	 * @param array       $atts
	 *
	 * @param null|string $content
	 *
	 * @param string      $tag
	 *
	 * @param bool        $ajax
	 *
	 * @return mixed
	 */
	public static function render( $atts, string $content = null, string $tag = null, $ajax = false ): string {
		
		$content = do_shortcode( $content );
		ob_start();
			//
			$show = 0;
			if(isset($atts['value'])&&$atts['value'][0]=='0'&&isset($atts['zerovalue'])&&$atts['zerovalue']=='show'){
				
				$show = 1;
				
			}

			if(isset($atts['value'])&&$atts['value'][0]!='0'){
				$show = 1;
			}
			//

		if(isset($atts['value'])&&$show==1){

				if(isset($atts['before'])){
					echo $atts['before'];
				}
				echo $atts['value'];
				if(isset($atts['after'])){
					echo $atts['after'];
			}
		}

		if(isset($atts['formula'])):
			$calculate = "";
			$calculate = Rep_Example::formula( $atts['formula'] );
		
				if($calculate!=0){
					if(isset($atts['before'])){
					echo $atts['before'];
					}
					echo Util::numberFormat($calculate);
					if(isset($atts['after'])){
					echo $atts['after'];
					}
				}else{
					if(isset($atts['zerovalue'])&&$atts['zerovalue']=='show'){
						if(isset($atts['before'])){
						echo $atts['before'];
						}
						echo "0";
						if(isset($atts['after'])){
						echo $atts['after'];
						}
					}	
				}
			
		endif;

		return ob_get_clean();

	}

	
/**eval function**/
public static function formula( $formula ){

	// Remove whitespaces
	$formula = preg_replace('/\s+/', '', $formula);

	$number = '(?:\d+(?:[,.]\d+)?|pi|π)'; // What is a number
	$functions = '(?:sinh?|cosh?|tanh?|abs|acosh?|asinh?|atanh?|exp|log10|deg2rad|rad2deg|sqrt|ceil|floor|round|pow|float|floatval)'; // Allowed PHP functions
	$operators = '[+\/*\^%-]'; // Allowed math operators
	$regexp = '/^(('.$number.'|'.$functions.'\s*\((?1)+\)|\((?1)+\))(?:'.$operators.'(?2))?)+$/'; // Final regexp, heavily using recursive patterns

	//if (preg_match($regexp, $formula))
	//{
	    $formula = preg_replace('!pi|π!', 'pi()', $formula); // Replace pi with pi function
	    //$formula = preg_replace('!^!', 'pow()', $formula); // Replace exp with pow function
	    eval('$result = '.$formula.';');
	    return $result;
	//}

	   // return false;
}
/****/


}
