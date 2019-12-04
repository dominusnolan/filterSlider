<?php


namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;

/**
 * Class Loan_Amounts
 * @package Financer\FilterSlider\Shortcode
 */
class Loan_Amounts extends Shortcode {

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
		ob_start();
		$_posts = get_posts( 'post_type=page&tag=loan-amount&numberposts=-1' );
		usort( $_posts, function ( $a, $b ) {
			if ( $a->ID == $b->ID ) {
				return 0;
			}
			if ( ! preg_match( '/([0-9]+)/', $a->post_name, $matches ) ) {
				$a_num = 0;
			} else {
				$a_num = (int) $matches[1];
			}

			if ( ! preg_match( '/([0-9]+)/', $b->post_name, $matches ) ) {
				$b_num = 0;
			} else {
				$b_num = (int) $matches[1];
			}

			if ( $a_num == $b_num ) {
				return 0;
			}

			return ( $a_num < $b_num ) ? - 1 : 1;
		} );
		?>
        <select onchange="window.location.href = this.options[this.selectedIndex].value;">
            <option><?php _e( 'Choose your amount', 'fs' ); ?></option>
			<?php foreach ( $_posts as $_post ): ?>
                <option value="<?php echo get_permalink( $_post ) ?>"><?php echo get_the_title( $_post ) ?></option>
			<?php endforeach; ?>
        </select>
		<?php
		return ob_get_clean();
	}
}