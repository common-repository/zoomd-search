<?php

namespace Zoomd\Core;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Settings {
    
     public static function siteUrl(){
        $siteurl = (get_option('zoomd_siteurl') != '') ? get_option('zoomd_siteurl') :  get_option('siteurl');
        return $siteurl;
    }
    
    public static function setSiteUrl($siteUrl){
        update_option('zoomd_siteurl', $siteUrl);
    }

    public static function lastIndex(){
        $lastIndex =  get_option(LASTINDEXED);
        if(!isset($lastIndex) || empty($lastIndex)) 
            $lastIndex = date('1990-01-01 00:00:00');
        
        return $lastIndex;
    }
    public static function setLastIndex($modified) {
        update_option(LASTINDEXED, $modified, true);
    }

    public static function siteId(){
        return get_option('zoomd_siteId');
    }

     public static function setSiteId($siteId){
		update_option('zoomd_siteId', $siteId);
    }
    
    public static function apiHash(){
        return get_option('zoomd_apiHash');
    }
    
    public static function setApiHash($apiHash){
	    update_option('zoomd_apiHash',$apiHash);
    }

    public static function clientId(){
        return get_option('zoomd_clientId');
    }
    public static function setClientId($clientId){
        update_option('zoomd_clientId',  $clientId);
    }

    public static function firstname(){
        $firstname = get_option('zoomd_first_name');      
        return $firstname;

    }

    public static function key1(){
        $key1 = get_option('zoomd_key1');      
        return $key1;

    }

    public static function key2(){
        $key2 = get_option('zoomd_key2');      
        return $key2;

    }

    public static function lastname(){
        $lastname = get_option('zoomd_last_name');      
        return $lastname;

    }
    
    public static function email(){
        $email = get_option('zoomd_emailaddress');      
        if(empty($email))
            $email = wp_get_current_user()->user_email;
        return $email;

    }
    public static function setEmail($email){
        update_option('zoomd_emailaddress',  $email);        
    }


    public static function get_zoomd_option($name, $defaultval, $checkempty = true)
    {
        $options = get_option('zoomd_options');
        if(!isset($options))
            return $defaultval;
        if(isset($options[$name]))
        {
            //error_log('name:'. $name . ' actualval:'.$options[$name]);
            if($checkempty)
                return settings::defaultifempty($options[$name],$defaultval);
            else
                return $option[$name];
        }
        return $defaultval;
    }

    public static function replacesearchbox(){
        return settings::get_zoomd_option('replacesearchbox',0);
    }    

    public static function showfloatingicon(){
        return settings::get_zoomd_option('showfloatingicon',0);
    }

    public static function searchboxhtml(){
        return settings::get_zoomd_option('searchboxhtml',0);
    }

    public static function floatingicontop(){
        return settings::get_zoomd_option('floatingicontop',70,true);
    }

    public static function floatingiconright(){
        return settings::get_zoomd_option('floatingiconright',30,true);
    }

    public static function searchboxvalidhtml()
    {
        return settings::get_zoomd_option('searchboxvalidhtml',0);
    }

    public static function enabletopsearches(){
        return settings::get_zoomd_option('enabletopsearches',0);
    }

    public static function defaultifempty($val,$default)
    {
        return empty($val)?$default:$val;
    }

    public static function searchboxdefaulthtml(){        
        return '<div>'
                .'<input type="text" id="zsbox" zoomdSearch="{\\\'trigger\\\':\\\'OnEnter\\\'}" />'
                .'<input type="button" id="zsbtn" value="Search" zoomdSearch="{\\\'trigger\\\':\\\'OnClick\\\', \\\'forInput\\\':\\\'#zsbox\\\'}" />'
          .'</div>';
        
    }

    public static function searchfloatingbutton()
    {
        return '<button type="button" id="search-btn" zoomdsearch="{ "trigger" : "OnClick" }" zoomdid="zd-id-21881">'
            . '<span id="search-icon" class="material-icons">search</span>'
        . '</button>';
    }

    public static function validatefloatingicontop($input)
    {    
        if(!is_numeric($input))
        {
            add_settings_error('zoomd_options[floatingicontop]', esc_attr( 'settings_failed' ),'Top must have numeric value, rolling back to default','error');
            return 70;
        }
        return $input;
    }

    public static function validatefloatingiconright($input)
    {    
        if(!is_numeric($input))
        {
            add_settings_error('zoomd_options[floatingiconright]', esc_attr( 'settings_failed' ),'Right must have numeric value, rolling back to default','error');
            return 30;
        }
        return $input;
    }

    public static function validatesearchboxhtml($input)
    {
        $checkhtml = htmlentities($input);
        return ($checkhtml == $input)?0:1;
    }    

     public static function environment(){
        if(defined( 'Zoomd_DBG' ))
            return 'Test';
        else
            return 'Production';
    }

    public static function notices(){
        return get_option('zoomd_notices');
    }

    public static function setNotices($notices){
        update_option('zoomd_notices', $notices);
    }

    public static function updateOptions($array,$email,$clientid,$apikey){
    
        self::setClientId($clientid);
        self::setEmail($email);
        self::setSiteId($array["siteId"]);
        self::setApiHash($apikey);
    
    }

    public static function isValid() {
        return !empty(self::clientId()) && !empty(self::siteId()) && !empty(self::email());
    }

    public static function updateAllOptions($clientId,$siteId,$siteUrl,$email,$hash){
    
        self::setClientId($clientId);
        self::setSiteUrl($siteUrl);
        self::setEmail($email);
        self::setSiteId($siteId);
        self::setApiHash($hash);
        
    
    }

    public static function printOptions($logger){
    
        $clientId = self::clientId();
        $siteUrl = self::siteUrl();
        $siteId = self::siteId();
        $email = self::email();

        $msg = 'clientId: ' .$clientId  .' ,siteUrl: ' .$siteUrl .' ,siteId: ' .$siteId  .' ,email: '  .$email  ;
        if(isset($logger)){
            $logger->Info($msg);
        }
    }

    public static function deleteAllOptions(){
        delete_option('zoomd_siteurl');
        delete_option('zoomd_clientId');
        delete_option('zoomd_siteId');
        delete_option('zoomd_emailaddress');
        delete_option('zoomd_apiHash');
        delete_option('zoomd_notices'); 
        delete_option( 'zoomd_options');    
        delete_option( 'zoomd_reg_token');    
        delete_option( 'zoomd_key1');    
        delete_option( 'zoomd_key2');    
        delete_option(LASTINDEXED);
    }
}

?>