<?php
namespace Zoomd\Core;
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );



use Zoomd\Core\Settings;
use Zoomd\Core\Logger;
use Zoomd\Core\HttpClient;
use zoomd_indexer;
   
class PostUpdater {
    
    protected static $logger;
    protected static $indexer  ;

    public static function logger(){
         if (NULL == self::$logger) {
              self::$logger = new Logger();
        }
        return self::$logger ;
    }

    public static function indexer(){
         if (NULL == self::$indexer) {
              self::$indexer = new zoomd_indexer();
        }
        return self::$indexer ;
    }
    

 public static function post_updated( $post_id, $post, $update ) {
        
        //self::logger()->Info('postID:: ' . $post_id .' postData:: ' .json_encode($post) .' isUpdate:: '  .$update);
        self::logger()->Info('postID:: ' . $post_id .' isUpdate:: '  .$update);
        if(!isset($post)) return;
        
        $post_arr = array();
        $zpost = self::indexer()->post_to_zoomd_json($post);
        $post_arr[0] = $zpost;
        
        switch($zpost->post_status)
        {
            case 'publish':
                if($update)
                {            
                    self::logger()->Info('post updated: ' . $post_id . ', URL: ' . $zpost->permlink);
                }
                else
                {
                    self::logger()->Info('save post fired: ' . $post_id . ' status: ' . $zpost->post_status . ', URL: ' . $post->permlink);
                }
                self::logger()->Info('save post fired: ' . $post_id . ' status: ' . $zpost->post_status . ', URL: ' . $zpost->permlink);
                $dummy = array();
                self::indexer()->uploadPosts($post_arr,true,$dummy);
                break;

            case 'trash':
                self::logger()->Info('post deleted: ' . $post_id . ' status: ' . $zpost->post_status . ', URL: ' . $zpost->permlink); 
                break;
            default:
                //$this->logger->log('unknown post state');
        }
 
    }
}
    

?>