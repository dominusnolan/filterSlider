<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Plugin;

/**
 * Class Social
 * @package Financer\FilterSlider\Shortcode
 */
class Social extends Shortcode {

	private static $_jsEnqueued = false;

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
		self::registerJs();
		if ( ! empty( $atts['id'] ) ) {
			$shareLink = get_permalink( $atts['id'] );
		} else {
			$shareLink = get_permalink();
		}
		if ( ! empty( $atts['after'] ) ) {
			$shareLink .= $atts['after'];
		}
		ob_start();
		?>
        <div class="social-shares">
            <a class="tableBox share button" rel="nofollow" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode( $shareLink ) ?>"><?php _e( 'Share on Facebook', 'fs' ) ?></a>
            <a class="tableBox tweet button" rel="nofollow" target="_blank" href="https://twitter.com/intent/tweet?url=<?php echo urlencode( $shareLink ) ?>"><?php _e( 'Tweet about this', 'fs' ) ?></a>
        </div>
		<?php
		return ob_get_clean();
	}

	/**
	 * @return void
	 */
	public static function registerJs() {
		if ( ! self::$_jsEnqueued ) {
			wp_enqueue_script( 'dummy', Plugin::GetUri( 'js/dummy.js' ), [ 'jquery' ] );
			wp_add_inline_script( 'dummy', self::_renderJs() );
			self::$_jsEnqueued = true;
		}
	}

	/**
	 * @return string
	 */
	private static function _renderJs() {
		$prompt = __( "Please enter your friend\'s email", 'fs' );


	}

}
