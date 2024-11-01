<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
    class zoomd_utils
    {
        

        public static function postAsync_TEST($url,$data_str) {
            $async = new \Zoomd\Http\Async();
            $task = \Zoomd\Http\Task::createPost($url,$data_str);
            $promise = $async->attach($task, "postTask");
            
            $async->execute(true);
            return $promise;
        }
        

        public static function arrRemove(&$arr,$val){
            $key = array_search($val, $arr);
            if($key !== false ){
                    unset( $arr[$key]);
                }
            
        }

        public static function arrRemoveBulk(&$arr,$chunk){
            foreach($chunk as $curId){
                zoomd_utils::arrRemove($arr,$curId);
            }
        }

        public static function isEmpty($item)  {
		    return (!isset($item) || strlen($item) == 0);
	    }
      

        public static function arrPrint($arr,$logger){
            if(empty($arr)){
                $logger->Info('arr=[0]');
                return;
            }
                
            $data = json_encode(array_values ($arr));
		    echo '[arr]= ' .$data  . '</br>';
            $logger->Info('[arr]= ' .$data );
        }

        public static function isJson($string) {
           return ((is_string($string) &&
            (is_object(json_decode($string)) ||
            is_array(json_decode($string))))) ? true : false;
        }

        public static function getimageURL($url){
            $isfull = substr($url,0,4);
            if(strtolower($isfull!='http'))
            {
                $url = BASE_URL . $url;                
            }
            return $url;
        }

        
    }
?>