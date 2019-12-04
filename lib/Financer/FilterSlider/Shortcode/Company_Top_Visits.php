<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Table\TopCompaniesTable;
/**
 * Class Company_Top_Visits
 * @package Financer\FilterSlider\Shortcode
 */
class Company_Top_Visits extends Shortcode {

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
		$company = pods( 'company_single', [ 'limit' => 6, 'orderby' => 'visits DESC' ] );
		ob_start();
		
		while ( $company->fetch() ) :
			?>
				<a class="companyBox" href="<?php echo esc_url( apply_filters( 'the_permalink', get_permalink( $company->id() ) ) ); ?>" rel="bookmark" title="<?php the_title_attribute( [ 'post' => get_post( $company->id() ) ] ); ?>">
				
				<img src="<?php echo $company->display('logo'); ?>">

				</a>
			<?php
		endwhile;
		?>
		
        <?php
		return ob_get_clean();
	}
}
