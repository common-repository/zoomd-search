<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );



use Zoomd\Core\Settings;
use Zoomd\Core\Logger;
use Zoomd\Core\HttpClient;

   

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
        
        self::logger()->Info('postID:: ' . $post_id .' postData:: ' .json_encode($post) .' isUpdate:: '  .$update);

        if(!isset($post)) return;
        
        $post_arr = array();
        $post_arr[0] = self::indexer()->post_to_zoomd_json($post);
        
        switch($post->post_status)
        {
            case 'publish':
                if($update)
                {            
                    self::logger()->Info('post updated: ' . $post_id);
                }
                else
                {
                    self::logger()->Info('save post fired: ' . $post_id . ' status: ' . $post->post_status);
                }
                self::logger()->Info('save post fired: ' . $post_id . ' status: ' . $post->post_status);
                self::indexer()->uploadPosts($post_arr,true);
                break;

            case 'trash':
                self::logger()->Info('post deleted: ' . $post_id . ' status: ' . $post->post_status); 
                break;
            default:
                //$this->logger->log('unknown post state');
        }
 
    }
}
    

?>