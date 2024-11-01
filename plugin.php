<?PHP
/*
Plugin name: Zoomd Search
Plugin URI: http://zoomd.com/
Description: A beautiful search experience, that engages your visitors, improves conversion and adds monetization to your site
Author: Zoomd
Text Domain: zoomd-search
Author URI: http://zoomd.com/
License: GPLv2 or later
Version: 2.1.0.37
*/

use Zoomd\Core\Activator;
use Zoomd\Core\Settings;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if ( ! defined( 'Zoomd_Path' ) ) {
	define( 'Zoomd_Path', plugin_dir_path( __FILE__ ) );
}

$debugexists = Zoomd_Path . 'debug';
if (file_exists($debugexists)) 	
{
    if(is_dir($debugexists))
    {
        define( 'Zoomd_DBG', $debugexists);        
    }
}


require_once Zoomd_Path . 'classes/zoomd_classes_map.php';

register_activation_hook( __FILE__,array( 'ZoomdPluginActivator', 'install' )); 
register_deactivation_hook(__FILE__, array( 'ZoomdPluginActivator', 'deactivate' ));
register_uninstall_hook( __FILE__, array( 'ZoomdPluginActivator', 'uninstall' ));
add_action('admin_menu',array( 'ZoomdPluginActivator', 'addmenus' ));


if(session_id() == '')
     session_start(); 



function add_zoomd_debug_scripts()
{
    if(defined( 'Zoomd_DBG' ))
    {        
        wp_register_script( 'zoomd-debug', plugins_url( '/debug/zoomdjson.js', __FILE__ ), array( 'jquery' ) );
    }
}
add_action( 'wp_enqueue_scripts', 'add_zoomd_debug_scripts' );  

add_action('plugins_loaded', 'wan_load_textdomain');
function wan_load_textdomain() {
	load_plugin_textdomain( 'zoomd-search', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
}

function zoomd_admin_notices($msg) {
    //settings_errors();
    $notices= get_option('zoomd_notices');    
    $zoomd_last_error = get_option('zoomd_last_error');    
    
    if ($notices) {       
        if(substr($notices,0,5)=='Error')
        {
             $msg = "<div id='zoomderrmsg' class='notice notice-error'><p>$notices</p>";            
             if($zoomd_last_error)
             {
                $msg .=  "<p><pre><code>$zoomd_last_error</code></pre></p>";
             }
             $msg .=  "</div>";
             echo $msg;
        }
        else
        {
            echo "<div class='notice notice-success is-dismissible'><p>$notices</p></div>";
            delete_option('zoomd_notices');
            delete_option('zoomd_last_error');
        }
    }
    else
    {
        echo "<script>
                var $ = jQuery;
                $(document).ready(function() {
                    $('#zoomderrmsg').hide();
                });
            </script>";
    }
}
add_action('admin_notices', 'zoomd_admin_notices');


if ( defined('WP_CLI') && WP_CLI ) {
	include Zoomd_Path . '/classes/zoomd_cli.php';
	WP_CLI::add_command( 'zoomd', new zoomd_cli() );
}

add_action('admin_init', 'zoomd_admin_init' );
function zoomd_admin_init(){
    
    register_setting( 'zoomd_search_options', 'zoomd_options','validateoptions' );
    $options = get_option('zoomd_options');
    if(empty($options))
    {
        //create default values
        $options =  array();
        $options['enabletopsearches'] = 1;
        $options['replacesearchbox'] = 1;
        $options['showfloatingicon'] = 1;
        $options['searchboxvalidhtml'] = 1;
        $options['floatingicontop'] = 70;
        $options['floatingiconright'] = 30;
        $options['searchboxhtml'] = 0;
        update_option('zoomd_options',$options);
    }   
    register_setting( 'zoomd_registration_options', 'zoomd_siteurl','validateurl');
    register_setting( 'zoomd_registration_options', 'zoomd_key1','validatekey1');
    register_setting( 'zoomd_registration_options', 'zoomd_key2','validatekey2');
}

function validateurl($input)
{
    $url = filter_var($input, FILTER_SANITIZE_URL);       
    if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) === false) {
        return $url;
    }   
    else 
    {   
        $url = "";
        $type = 'error';
        $message = __( 'Url is invalid', 'zoomd-search' );
        add_settings_error('zoomd_siteurl',esc_attr( 'zoomd_registration_options' ), $message,$type);     
        return $url;
    }     
}

function validatekey1($input)
{
    $validated = empty($input);
    if ($validated) {
        $type = 'error';
        $message = __( 'Please fill Key1  field', 'zoomd-search' );
        add_settings_error('zoomd_key1', esc_attr( 'zoomd_registration_options' ), $message,$type);
    }
    return $input;
}

function validatekey2($input)
{
    $validated = empty( $input );
    if ($validated) {
        $type = 'error';
        $message = __( 'Please fill Key2 field', 'zoomd-search' );
        add_settings_error('zoomd_key2',esc_attr( 'zoomd_registration_options' ),$message,$type);
    }
    return $input;
}

function validateoptions($input)
{    
    $input['floatingicontop'] = Settings::validatefloatingicontop($input['floatingicontop']);
    $input['floatingiconright'] = Settings::validatefloatingiconright($input['floatingiconright']);
    $validhtml = Settings::validatesearchboxhtml($input['searchboxhtml']);
    $input['searchboxvalidhtml'] = $validhtml;
    if(!$validhtml)
    {
        add_settings_error('zoomd_options[searchboxhtml]', esc_attr( 'settings_failed' ),'Update Failed - Searchbox html is not valid','error');
    }
    return $input;
}



function enqueue_zoomd_admin_scripts($hook) {
    if ( 'toplevel_page_zoomd' != $hook  && 'settings_page_zoomd' != $hook) {
        return;
    }

    wp_register_style( 'zoomd-search-css', plugins_url('css/zoomd-search.css', __FILE__) );
    wp_enqueue_style( 'zoomd-search-css');
    wp_enqueue_script( 'acejs', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.6/ace.js', __FILE__ );
    wp_enqueue_script( 'ext-be‌​autifyhtml', 'https://cdnjs.cloudflare.com/ajax/libs/js-beautify/1.6.7/beautify-html.js', __FILE__ );
}
add_action( 'admin_enqueue_scripts', 'enqueue_zoomd_admin_scripts' );


function zoomd_shortcodes_init()
{
    function zoomd_shortcode($atts = [], $content = null)
    {
         if(shortcode_exists('zoomd_ts'))
            {                
                $content .= '<div id="zoomdts"></div>';
                return $content;                                
            }                   
    }
    add_shortcode('zoomd_ts', 'zoomd_shortcode');
}
add_action('init', 'zoomd_shortcodes_init');

class ZoomdPluginActivator {
    static protected $activator ;
    
    static function install(){
       $activator = new Activator();
       $activator->activate();
       
    }
    
    static function uninstall(){
       $activator = new Activator();
       $activator->uninstall(); 
    }

    static function deactivate(){
       $activator = new Activator();
       $activator->deactivate();

    }
    public static function addmenus()
	{	
    	
	    if(defined( 'Zoomd_DBG' ))
		{   
            if(Settings::isValid()) 
                add_menu_page( "ZoomD Plugin settings", __('Zoomd','zoomd'),  'manage_options', 'zoomd',  'zoomd_settingsView::show_settings','dashicons-search',99);
            else            
		        add_menu_page( "ZoomD Plugin settings", __('Zoomd','zoomd'),  'manage_options', 'zoomd',  'zoomd_registrationView::register','dashicons-search',99);
			require_once Zoomd_DBG . '/zoomd_debug_menu.php';
		}
		else
        {
            if(Settings::isValid()) 
		        add_options_page( "ZoomD Plugin settings", __('Zoomd','zoomd'),  'manage_options', 'zoomd',  'zoomd_settingsView::show_settings');            
            else
               add_options_page( "ZoomD Plugin settings", __('Zoomd','zoomd'),  'manage_options', 'zoomd',  'zoomd_registrationView::register');            
        }
	}
}
?>