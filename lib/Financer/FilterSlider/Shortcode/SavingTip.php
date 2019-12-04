<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
/**
 * [savingtip]
 * Class Item
 * @package Financer\FilterSlider\Shortcode
 */
class SavingTip extends Shortcode {

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
		 $savings_posts = query_posts(array( 
        'post_type' => 'saving_tip',
        'showposts' => -1
    	) );
		 
		global $wp_query; 
		ob_start();
		echo $wp_query->found_posts;
		?>
		
		<?php
	return ob_get_clean();

	}
/***/

}
