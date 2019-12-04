<?php

namespace Financer\FilterSlider\Shortcode;


use Financer\FilterSlider\Abstracts\Shortcode;
use Financer\FilterSlider\Abstracts\Table;
/**
 * Class Item
 * @package Financer\FilterSlider\Shortcode
 */
class Items extends Shortcode {

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
        global $post;
        $pods = pods( get_post_type( $post->ID ), $post->ID );
        $content = do_shortcode( $content );
        ob_start();

        if( $atts['title'] ){
            ?>
            <h2 class="secondtitle"><?php echo $atts['title']; ?></h2><i class="arrow"></i>

            <?php
        }

        /*tags*/
        $_page = pods( get_post_type(), get_the_ID() );
        if( $_page ){
            $comparison_table = $_page->field( 'comparison_table' );
            if ( is_string( $comparison_table ) )
            {
                if ( preg_match_all('/\[item(.*?)\]/', $comparison_table, $item ) ) {
                    $item = array_key_exists( 1 , $item) ? $item[1] : array();
                    echo Items::itemTags($item);
                }
            }
        }
        ?>

        <div class="item-table table_cont table custom-items" id="products">
            <div class="item-results">
                <?php echo str_replace('<br />', '', $content); ?>
            </div></div>
        <?php
        return ob_get_clean();

    }
    /***/
    public static function itemTagsbyField($pods){
        ob_start();

        $tag_arr=array($pods->field( 'item_1_tags' ), $pods->field( 'item_2_tags' ), $pods->field( 'item_3_tags' ));

        if (!empty($tag_arr)) {
            ?>

            <div class="item-categories">
            <?php
            foreach($tag_arr as $tagname){
                if(!empty($tagname)){
                    $uctags = ucfirst($tagname);
                    $idtags = str_replace(' ', '', $tagname);
                    $idtags = preg_replace('/[^A-Za-z0-9\-]/', '', $idtags);

                    ?>
                    <div class="checkboxFive">
                        <input type="checkbox" name="<?php echo $idtags; ?>" class="tag-check">
                        <label id="<?php echo $idtags; ?>" class="tag-container">
                            <?php echo $uctags; ?>
                        </label>
                    </div>
                    <?php
                }
            }
            ?>
            </div><?php
        }
        ?>

        <?php
        return ob_get_clean();

    }
    /**remove this if comparison table is set out**/
    public static function itemTags($atts){
        ob_start();

        $tag_arr=array();
        foreach($atts as $tag_array){
            $tags = preg_match('/tags="([^"]+)"/', $tag_array, $match);


            if (!empty($match)) {
                $tags_list2 = explode(',', $match[1]);
                foreach($tags_list2 as &$tags){

                    if (!in_array($tags, $tag_arr)) {
                        $tag_arr[]=$tags;
                    }
                }
            }

        }

        if (!empty($tag_arr)) {
            ?>

            <div class="item-categories">
            <?php
            foreach($tag_arr as $tagname){
                $uctags = ucfirst($tagname);
                $idtags = str_replace(' ', '', $tagname);
                $idtags = preg_replace('/[^A-Za-z0-9\-]/', '', $idtags);

                ?>
                <div class="checkboxFive">
                    <input type="checkbox" name="<?php echo $idtags; ?>" class="tag-check">
                    <label id="<?php echo $idtags; ?>" class="tag-container">
                        <?php echo $uctags; ?>
                    </label>
                </div>
                <?php
            }
            ?>
            </div><?php
        }
        ?>

        <?php
        return ob_get_clean();
    }
    /**end itemtags**/
    /***/

    public static function renderTable( $item_array ){
        $atts = $item_array;
        $atts['id'] = $atts['id']['ID'];
        if ( ! empty( $atts['id'] ) ) {
            $type = get_post_type( $atts['id'] );
            $item = pods( $type, $atts['id'] );
        }
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
                        $attachment_id = self::_getAttachmentId2( $logourl );
                        if ( $attachment_id ) {
                            $logohtml = wp_get_attachment_image( $attachment_id, 'full', false, [ 'class' => 'logo-img' ] );
                        }
                    } else {
                        $logohtml = wp_get_attachment_image( $item->field( 'logo.ID' ), 'full', false, [ 'class' => 'logo-img' ] );


                    }
                    $linkurl  = ! empty( $linkurl ) ? $linkurl : get_the_permalink( $atts['id'] );
                    $applyurl = trailingslashit( get_the_permalink( $atts['id'] ) . 'redirect' );
                    for ( $i = 0; $i <= 3; $i ++ ) {
                        $feature = $atts['features'][$i];
                        if ( ! empty( $feature ) ) {
                            $features[] = $feature;
                        }
                    }
                    if ( $item->field( 'ej_partner' ) ) {
                        $item_row_classes [] = 'greyed';
                    } if ( $item->field( 'favorite' ) ) {
                    $item_row_classes [] = 'premium';
                }
                    break;
                case 'creditcard':


                    $name = ! empty( $name ) ? $name : get_the_title( $atts['id'] );
                    if ( ! empty( $logourl ) ) {
                        $attachment_id = self::_getAttachmentId2( $logourl );
                        if ( $attachment_id ) {
                            $logohtml = wp_get_attachment_image( $attachment_id, 'full', false, [ 'class' => 'logo-img' ] );
                        }
                    } else {
                        $logohtml = wp_get_attachment_image( $item->field( 'logo.ID' ), 'full', false, [ 'class' => 'logo-img' ] );
                    }
                    $linkurl       = ! empty( $linkurl ) ? $linkurl : get_the_permalink( $atts['id'] )  . 'redirect';
                    $applyurl      = $linkurl;
                    $features_html = $item->field( 'card_details' );
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


        $tags_list = explode(',', $atts['tags']);
        foreach($tags_list as &$tags_list){
            $classtags = str_replace(' ', '', $tags_list);
            $classtags = preg_replace('/[^A-Za-z0-9\-]/', '', $classtags);

            $item_row_classes [] = $classtags;
        }
        $item_row_classes [] = 'taglist';
        /**end add tags**/

        $item_row_classes = implode( ' ', $item_row_classes );
        $item_row_classes = " class=\"{$item_row_classes}\"";


        echo '
<div' . $item_row_classes . '>
<div class="item-row">
    <div class="item-column vit logo-column">
        ' . $logohtml . '
        ' . $stars . '
    </div>
    <div class="item-column">
        ' . $features_html . '
    </div>
    <div class="item-column">
        <a href="' . $applyurl . '?b=i" target="_blank" rel="nofollow" class="small button applyYellow" data-cname="'.$name.'" data-cid="'.$atts['id'].'" data-plink="'.get_the_permalink($atts['id']).'" >' . $text . '</a>
		<a href="' . $linkurl . '" class="applyNow">' . $readmore . '</a>
    </div>
</div>
</div>
';

    }

    /**
     * @param $url
     *
     * @return mixed
     */
    private static function _getAttachmentId2( $url ) {
        global $wpdb;
        $attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $url ) );

        return $attachment[0];
    }

}
