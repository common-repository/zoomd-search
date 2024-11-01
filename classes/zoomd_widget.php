<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
use Zoomd\Core\Settings;

add_action('wp_head', 'add_zoomd_plugin');
function add_zoomd_plugin() {    
    //check that we have legal clientId and SiteId
    if( Settings::isValid())
    {
        $clientId  = Settings::clientId();        
        //if(Settings::replacesearchbox()==1) {        
        wp_register_script( 'zoomd_search', '//' . widgetbaseURL . '/zoomd/SearchUi/Script?clientId=' . $clientId, '', '', true );
        wp_enqueue_script('zoomd_search');
        
        if(Settings::showfloatingicon()==1 ) {        
            wp_register_style( 'fonts.googleapis.com-css', 'https://fonts.googleapis.com/icon?family=Material+Icons', __FILE__ );
            wp_register_style( 'zoomd-floating-css', plugins_url('../css/zoomd-floating.css', __FILE__) );
            
            wp_enqueue_style( 'fonts.googleapis.com-css');
            wp_enqueue_style( 'zoomd-floating-css');

            wp_register_script( 'zoomd_floating_button',  plugins_url('../js/zoomd-floating.js', __FILE__) );
            wp_enqueue_script('zoomd_floating_button');

            $floatingicontop = Settings::floatingicontop(); 
            $floatingiconright = Settings::floatingiconright(); 
            
            $custom_inline_style = '#search-btn { top: ' . $floatingicontop . 'px;'
                . ' right: ' . $floatingiconright . 'px;}';
             
            wp_add_inline_style( 'zoomd-floating-css', $custom_inline_style );
        }        
    }
 }

    

function zoomd_search_form( $form ) {
    if( Settings::isValid() && Settings::replacesearchbox()==1) { 
        if(!empty(Settings::searchboxhtml() && Settings::searchboxvalidhtml() == 1))
        {
            echo Settings::searchboxhtml();
        }   
        else
        {    
            echo Settings::searchboxdefaulthtml();
        }        
        return '';
    }
}
add_filter( 'get_search_form', 'zoomd_search_form' );

?>