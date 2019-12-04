<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;

/**
 * Class Company_List
 * @package Financer\FilterSlider\Shortcode
 */
class Company_List extends Shortcode {

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
		$company = pods( 'company_single', [ 'limit' => - 1 ] );
		ob_start();
		while ( $company->fetch() ) :
			?>
			<div class="companyBox">
				<a href="<?php echo esc_url( apply_filters( 'the_permalink', get_permalink( $company->id() ) ) ); ?>"
				   rel="bookmark"
				   title="<?php the_title_attribute( [ 'post' => get_post( $company->id() ) ] ); ?>"><img
						src="<?php echo pods_field( 'company_single', $company->id(), 'logo', true ); ?>"></a>

				<p><strong><?php _e( 'Company', 'fs' ); ?>
						:</strong> <?php $company->display( 'foretag' ); ?></p>

				<p><strong><?php _e( 'Min. age', 'fs' ); ?>
						:</strong> <?php $company->display( 'minalder' ); ?></p>

				<p><strong><?php _e( 'Weekend payout', 'fs' ); ?>:</strong>
					<?php if ( $company->field( 'helgutbetalning' ) ) : ?>
						<?php _e( 'Yes', 'fs' ); ?>.
					<?php else : ?>
						<?php _e( 'No', 'fs' ); ?>.
						<?php
					endif ?></p>

				<p><strong><?php _e( 'Monday - friday', 'fs' ); ?>
						:</strong> <?php echo $company->display( 'mandag-fredag' ); ?>
				</p>

				<p><strong><?php _e( 'Saturday', 'fs' ); ?>
						:</strong> <?php echo $company->display( 'lordag' ); ?></p>

				<p><strong><?php _e( 'Sunday', 'fs' ); ?>
						:</strong> <?php echo $company->display( 'sondag' ); ?>
				</p>

				<p><strong><?php _e( 'Telephone', 'fs' ); ?>
						:</strong> <?php echo $company->display( 'telefon' ); ?></p>
				<a href="<?php echo esc_url( apply_filters( 'the_permalink', get_permalink( $company->id() ) ) ); ?>"
				   class="button blue small"><?php _e( 'Go to company', 'fs' ); ?> &rarr;</a>
			</div>
			<?php if ( $company->position() - 1 % 3 == 0 ) : ?>
			<div class="cboth"></div>
			<?php
		endif;
		endwhile;

		return ob_get_clean();
	}
}
