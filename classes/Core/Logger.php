<?php
namespace Zoomd\Core;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Zoomd\Core\Settings;
const defaultloglevel = 1;

class Logger
    {
        
        public $logLevel = 1; //0=info , 1=Warn , 2=Error

        public function __construct(){                    
            $this->logLevel = defaultloglevel;
            $lvl = get_transient('zoomd_loglevel');                                   

                         
            if(isset($lvl) && is_numeric($lvl))
            {                    
                $this->logLevel = $lvl;                
            }  
        }

        public function Info($msg){            
            if($this->logLevel == 0)
                $this->log($msg,3);
        }

        public function Warn($msg){            
            if($this->logLevel <= 1)
                $this->log($msg,4);
        }

        public function Error($msg){
            if($this->logLevel <= 2)
                $this->log($msg,5);        
        }

        public function log($msg,$level = 3)
        {
            try
            {
                $data_string = $msg;
                if(!$this->isJson($msg))
                {
                    $data = array("message" => $msg);
                    $data_string = json_encode($data);                  
                }
                $data_string = $this->enrichlogmsg($data_string,$level);
                $this->post_async(loggerUrl,$data_string);

                $siteUrl = Settings::siteUrl();
            }
            catch (Exception $e) 
	        {
                
            }
        }

        private function post_async($url, $post_string)
        {
            $res = "";
            $parts=parse_url($url);
            $fp = fsockopen("ssl://".$parts['host'],443,$errno, $errstr, 30);
            
            $out = "POST ".$url." HTTP/1.1\r\n";
            $out.= "Host: ".$parts['host']."\r\n";
            $out.= "Content-Type: application/json\r\n";
            $out.= "Content-Length: ".strlen($post_string)."\r\n";
            $out.= "Connection: Close\r\n\r\n";
            if (isset($post_string)) $out.= $post_string;
            fwrite($fp, $out);
            fclose($fp);            
        }

        private function enrichlogmsg($msg,$lvl)
        {
            $result = json_decode("{}");
            $result->privateKey = "98d36d56-a8ff-75a6-1fd2-389a76edf768";
            $result->applicationName = Settings::environment();            
            $result->subsystemName = "Wordpress";
            $result->computerName = gethostname();
            
            $le = array(
                 array(
                           "timestamp" => microtime(true) * 1000,
                           "severity" => $lvl,
                           "siteid" => Settings::siteId(),
                           "url" => Settings::siteUrl(),
                           "clientid" => Settings::clientId(),
                           "lastindexed" => Settings::lastIndex(),
                           "environment" => Settings::environment(),
                           "email" => Settings::email(),                           
                           "text" => json_decode($msg)
                 )
              );     
            
            $result->logEntries = $le;
            return json_encode($result);
        }

        private function isJson($string) {
           return ((is_string($string) &&
            (is_object(json_decode($string)) ||
            is_array(json_decode($string))))) ? true : false;
        }

       
    }
?>