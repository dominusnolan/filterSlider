<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Abstracts\Table;
use Financer\FilterSlider\Util;

/**
 * Class Item
 * @package Financer\FilterSlider\Shortcode
 */
class Item extends Shortcode {

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
        if ( ! empty( $atts['id'] ) ) {
            $type = get_post_type( $atts['id'] );
            $item = pods( $type, $atts['id']);

        }

        $features_html = Util::constructSingleCompanyFeatures($atts['id']);

        extract( shortcode_atts(
            [
                'name'        => null,
                'logourl'     => null,
                'linkurl'     => null,
                'description' => null,
                'color'       => '',
                'tags'       => '',
            ], $atts
        ) );
        /*
         * @var $name string
         * @var $logourl string
         * @var $linkurl string
         * @var $description string
         * */
        $description      = ! empty( $description ) ? $description : '';
        $logohtml         = '';
        $features         = [];
        $item_row_classes = [ 'sortable-item' ];
        if ( ! empty( $item ) ) {

            switch ( $type ) {
                case 'company_single':
                    $name = ! empty( $name ) ? $name : get_the_title( $atts['id'] );
                    if ( ! empty( $logourl ) ) {
                        $attachment_id = self::_getAttachmentId( $logourl );
                        if ( $attachment_id ) {
                            $logohtml = wp_get_attachment_image( $attachment_id, 'full', false, [ 'class' => 'logo-img' ] );
                        }
                    } else {
                        $logohtml = wp_get_attachment_image( $item->field( 'logo.ID' ), 'full', false, [ 'class' => 'logo-img' ] );
                    }
                    $linkurl  = ! empty( $linkurl ) ? $linkurl : get_the_permalink( $atts['id'] );
                    $applyurl = trailingslashit( get_the_permalink( $atts['id'] ) . 'redirect' );
                    for ( $i = 1; $i <= 4; $i ++ ) {
                        if(!empty($item->field( "feature_" . $i ))){
                            $feature = $item->field( "feature_" . $i );
                            if ( ! empty( $feature ) ) {
                                $features[] = $feature;
                            }
                        }
                    }
                    if ( $item->field( 'ej_partner' ) ) {
                        $item_row_classes [] = 'greyed';
                    } if ( $item->field( 'favorite' ) ) {
                    $item_row_classes [] = 'premium';
                }

                    $comments_count = wp_count_comments($atts['id']);

                    $read_reviews = ' <a href="' . get_permalink( $atts['id'] ) . '#reviews">' . __( 'Read', 'fs' ) . '&nbsp;' . $comments_count->total_comments . ' ' . __( 'reviews.', 'fs' ) . '</a>';
                    $custom_rep_example = ( $item->field( 'custom_representative_example' ) ? $item->field( 'custom_representative_example' ) : "" );

                    break;
                case 'creditcard':
                    $name = ! empty( $name ) ? $name : get_the_title( $atts['id'] );
                    if ( ! empty( $logourl ) ) {
                        $attachment_id = self::_getAttachmentId( $logourl );
                        if ( $attachment_id ) {
                            $logohtml = wp_get_attachment_image( $attachment_id, 'full', false, [ 'class' => 'logo-img' ] );
                        }
                    } else {
                        $logohtml = wp_get_attachment_image( $item->field( 'logo.ID' ), 'full', false, [ 'class' => 'logo-img' ] );
                    }
                    $linkurl       = ! empty( $linkurl ) ? $linkurl : get_the_permalink( $atts['id'] ) . 'redirect';
                    $applyurl      = $linkurl;
                    $features_html = $item->field( 'card_details' );

                    $read_reviews = "";
                    break;
            }
        }
        $text  = __( 'Application', 'fs' );
        $style = '';
        if ( ! empty( $color ) ) {
            $style = " style=\"background:" . $color . ";\"";
        }
        if ( empty( $logohtml ) ) {
            $logohtml = sprintf( '<img src="%s">', $logourl );
        }
        $readmore = __( 'Read more', 'fs' );
        $stars    = Table::showStars( $atts['id'] );

        if ( ! empty( $features ) && empty( $features_html ) ) {
            $features_html = '<ul class="features"><li>' . implode( "</li><li>", $features ) . '</li></ul>';
        }

        /**add tags as class**/
        $tags_list = explode(',', $tags);
        foreach($tags_list as &$tags_list){
            $classtags = str_replace(' ', '', $tags_list);
            $classtags = preg_replace('/[^A-Za-z0-9\-]/', '', $classtags);

            $item_row_classes [] = $classtags;
        }
        $item_row_classes [] = 'taglist';
        /**end add tags**/

        $item_row_classes = implode( ' ', $item_row_classes );
        $item_row_classes = " class=\"{$item_row_classes}\"";
        if(empty($features_html)){
            $features_html = "";
        }
		$plink = get_the_permalink( $atts['id'] );
		
        return <<<HTML
<div{$item_row_classes}>
<div class="item-row">
<div class="item-column vit logo-column company-listing">
<a href="$linkurl" class="company-logo">
        $logohtml
</a>
<span class="totalReviews">
        $stars
        $read_reviews
</span>

    </div>
<div class="item-column item-features">
        $features_html
    </div>
<div class="item-column company-apply">
        <a href="$applyurl?b=i" target="_blank" rel="nofollow" class="small button applyYellow" data-cname="{$name}" data-cid="{$atts['id']}" data-plink="{$plink}">{$text}</a>
    </div>
</div>
<div class="item-row tag-example sort-this">
    <div class="item-column tag-example-column">
        <div class="representative-example">{$custom_rep_example}</div>
    </div>
</div>
<div class="item-row more-information-row">
    <div class="item-column more-information">
        <a href="$linkurl" class="toggle-details fa fa-plus">$readmore</a>
    </div>
</div>
</div>
HTML;

    }

    /**
     * @param $url
     *
     * @return mixed
     */
    private static function _getAttachmentId( $url ) {
        global $wpdb;
        $attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $url ) );

        return $attachment[0];
    }
}
