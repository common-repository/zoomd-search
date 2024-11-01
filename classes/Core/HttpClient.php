<?php
namespace Zoomd\Core;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
use Zoomd\Core\Settings;

class HttpClient {

    //////////////POST ///////////////////////////
    public function post($url,$data_string,$headers=null){    
            $data_length = strlen($data_string);
             
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            $isEmpty = empty($data_string); 
            if(!$isEmpty)
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            else
                curl_setopt($ch, CURLOPT_HEADER, 0);

            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //Add Headers
            if(!isset($headers) || empty($headers)) {
                $headers =  array(                                                                        
                    'Content-Type: application/json',                                                                                
                    'Content-Length: ' . strlen($data_string));                                                                       
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
            $ret = curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);

            $result = json_decode($ret,true);
            $http_code = $info["http_code"];
            return array("httpCode"=>$http_code,"data"=>$result);
            

    }

    public function isErr($http_code){
        return !isset($http_code) || empty($http_code) || $http_code !=200;
    }

    public function get($url){
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        
        $http_code = $info["http_code"];
        return array("httpCode"=>$http_code,"data"=>$data);

    }
        
}

?>