<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_filter( "the_content", array('ContentUpdater' , 'custom_content_after_post' ));

use Zoomd\Core\Settings;

class ContentUpdater {

  public static function custom_content_after_post($content){
        //check if we have legal siteId and Client Id
        if(!Settings::isValid()){
            return $content;
        }

        if (is_single() || is_page()) {
            if(Settings::enabletopsearches()==1){
                $content .= '<div id="zoomdts"></div>';
            }
        }
        return $content;
    }

}
    
?>