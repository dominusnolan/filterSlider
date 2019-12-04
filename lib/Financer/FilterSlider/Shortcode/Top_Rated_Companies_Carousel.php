<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;

class Top_Rated_Companies_Carousel extends Shortcode {

	/**
	 *
	 * @return mixed|array
	 *
	 */
	public static function post_comments($empty) {
		$args   = [
			'post_type'   => 'company_single',
			'numberposts' => -1,
		];
		$_posts = get_posts( $args );
        $reviewed_posts = [];

		foreach ( $_posts as $index => $_post ) {
			$post_id       = $_post->ID;
			$args_comment  = [ 'post_id' => $post_id ];
			$comment_posts = get_comments( $args_comment );
			$meta_values   = [];
			$pod           = pods( 'company_single', $post_id );
			$ej_partner    = (int) $pod->field( 'ej_partner' );
			$show_on_homepage    = (int) $pod->field( 'show_on_homepage' );

			if ( empty( $show_on_homepage ) ) {
				continue;
			}

			if ( 0 == $ej_partner ) {
				$comment_count = 0;
				foreach ( $comment_posts as $comment_post ) {
					if ( ( (int) ( get_comment_meta( $comment_post->comment_ID, 'crfp-average-rating', true ) ) ) > 0 ) {
						$meta_values[] = (float) get_comment_meta( $comment_post->comment_ID, 'crfp-average-rating', true );
						$comment_count ++;
					}
				}
			}
			if ( ! empty ( $meta_values ) ) {
				$reviewed_posts[ $index ] = [
					'post_id'        => $post_id,
					'comment_rating' => array_sum( $meta_values ) / $comment_count
				];
				continue;
			}
			$reviewed_posts[ $index ] = [ 'post_id' => $post_id, 'comment_rating' => 0 ];

		}

		return $reviewed_posts;
	}

	public static function get_top_companies_by_rating() {
		$_posts = Top_Rated_Companies_Carousel::post_comments(1);
		if( empty($_posts) ){
			$_posts = Top_Rated_Companies_Carousel::post_comments(0);
		}
		array_multisort( array_column( $_posts, 'comment_rating' ), SORT_DESC, SORT_NUMERIC, $_posts );

		return $_posts;
	}

	public static function render( $atts, string $content = null, string $tag = null, $ajax = false ): string {
		extract( shortcode_atts( [
			'companies_count' => 15,
		], $atts ) );
		$top_rated_companies = array_values( Top_Rated_Companies_Carousel::get_top_companies_by_rating() );
		$top_rated_companies = array_slice( $top_rated_companies, 0, $companies_count );
		ob_start();
		?>

        <div class="flexslider carousel top-companies">
            <ul class="slides">
				<?php foreach ( $top_rated_companies as $index => $company ) {
					$pod       = pods( 'company_single', $company['post_id'] );
					$img       = $pod->field( 'logo._src' );
					$permalink = $pod->field( 'permalink' );
					?>
                    <li><a href="<?php echo esc_url( $permalink ); ?>"><img src="<?php echo $img; ?>"/></a></li>
				<?php }
				?>
            </ul>
        </div>


		<?php return ob_get_clean();
	}
}
