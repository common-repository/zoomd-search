<?php
    defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
    use Zoomd\Core\Activator;
    use Zoomd\Core\Settings; 

    class zoomd_registrationView {
	
        public static function register() {   
            $siteurl = Settings::siteUrl();
            $emailaddress  = Settings::email();            
            $key1  = Settings::key1();                
            $key2  = Settings::key2();               
            $siteid =  Settings::siteId();
            $adminUrl = "http://www.zoomd.com/#login";

            if(empty($emailaddress))
                $emailaddress = wp_get_current_user()->user_email;


            $registration_errors =  get_settings_errors();               
            if(!empty($registration_errors) && $registration_errors>0 && $registration_errors[0]['type']=='error')
            {
                 $registration_errors = 'errors';
            }                        
            
            if (isset( $_GET['settings-updated']) && $registration_errors!='errors' ) {                
                $updateAdminLink = false;               
                Settings::setClientId($key1); 
                //validate registration keys
                if (!$siteid) {
                    
                    $clientId  = Settings::clientId();
                    $activator = new Activator();
                    $siteid = $activator->validate($clientId,$key2);
                    
                    if(empty($siteid))
                    {            
                        update_option('zoomd_notices', __("Error registering site with Zoomd - Please contact Zoomd support at support@zoomd.com","zoomd-search") );
                        do_action( 'zoomd_admin_notices', '' );
                        echo '<p style="color:red">' . __("Error registering site with Zoomd - Please contact Zoomd support at support@zoomd.com","zoomd-search") . '</p>';
                        die;                
                    }
                    else
                    {
                        $activator->start();
                        update_option('zoomd_notices', __("Zoomd Search is ready","zoomd-search") );
                        do_action( 'zoomd_admin_notices', '' );
                        delete_option('zoomd_notices');
                        zoomd_settingsView::show_settings();
                    }
                }
            }//end registration
            else{                
                delete_option('zoomd_notices');
                
?>

<form method="post" action="options.php"> 
    <?php 
        settings_fields('zoomd_registration_options');         
        settings_errors();
    ?>    
    <div class="zdpage">
        <!--Header-->
        <div>
            <!--<h1>Zoomd Settings</h1>-->
            <img align="right" src="<?php echo plugins_url('../images/logo.png', __FILE__)?>" alt="Zoomd" >
        </div>      
        <h1 class="zoomd-h1"><?php _e('Please go to <a target="_blank" href='. RegistrationEndPoint . $siteurl . '>Zoomd</a>, Create account and fill the form below with API keys received after registration ','zoomd-search');?></h1>  
        <div id="accordion" >
            <!--Account Info-->
            <!--<h4 class="zdaccordion-toggle"><?php _e( 'Account Details', 'zoomd-search' );?></h4>-->
            <div class="zdaccordion-content default">            
                <table width="100%" cellpadding="0">                
                <tr>
                    <td class="zdtdlable">
                        <label><?php _e( 'Site Url', 'zoomd-search' );?>: </label>
                    </td>
                    <td>
                        <span>
                            <input style="width:500px" type="text" name="zoomd_siteurl" value='<?php echo $siteurl ?>'>                            
                        </span>
                    </td>
                </tr>                   
                <tr>
                    <td class="zdtdlable">
                        <label><?php _e( 'key1', 'zoomd-search' );?>: </label>
                    </td>
                    <td>
                        <span>
                            <input style="width:500px" type="text" name="zoomd_key1" value='<?php echo $key1 ?>'>                            
                        </span>
                    </td>
                </tr>                
                <tr>
                    <td class="zdtdlable">
                        <label><?php _e( 'key2', 'zoomd-search' );?>: </label>
                    </td>
                    <td>
                        <span>
                            <input style="width:500px" type="text" name="zoomd_key2" value='<?php echo $key2 ?>'>                            
                        </span>
                    </td>
                </tr>       
            </table>
            </div>
        </div> 
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
        </div>
</form>

<?php
            }
    }//end register
}//end class
?>