<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;

/**
 * Class Company_Amount
 * @package Financer\FilterSlider\Shortcode
 */
class Loan_Amount_Dropdown extends Shortcode {

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
	public static function render( $atts, string $content = null, string $tag = null, $ajax = false ): string {
		ob_start();

		$args = array(
		    'tag' => 'loan-amount',
		    'orderby' => 'title_number', 
		    'order' => 'ASC'
		);

		$query = new \WP_Query( $args );

		// Check that we have query results.
		if ( $query->have_posts() ) {
			$selected_page = get_option( 'option_key' );
			$pages = get_pages(); 
			?> 
			<div class="dropdown loan-amount-dropdown"> 
				<span class="dropdown-button"><?php _e( 'Select loan amount', 'fs' ); ?></span>
				<div class="dropdown-content">
			 	<?php
			    // Start looping over the query results.
			    while ( $query->have_posts() ) {
			        $query->the_post();
			            $option = '<a href="' . get_the_permalink(get_the_ID()) . '" ';
			            $option .= ( get_the_ID() == $selected_page ) ? 'selected="selected"' : '';
			            $option .= '>';
			            $option .= get_the_title();
			            $option .= '</a>';
			            echo $option;
			    }
			    /* Restore original Post Data */
				wp_reset_postdata();
			    ?>
	    		</div>
		    </div>
		    <?php
		}
		return ob_get_clean();
	}
}
