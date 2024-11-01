<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Zoomd\Core\Settings;
use Zoomd\Core\Logger;
use Zoomd\Core\HttpClient;

class zoomd_indexer
{
	protected $logger;
	protected $httpClient;


	public function __construct(){
        $this->logger = new Logger();
		$this->httpClient = new HttpClient();
    }

	public static function view()
	{
		$indexer = new zoomd_indexer();
		$indexer->index();
	}


	public function index()
	{
		///Set Timeout limit to 10 minutes
		if(!Settings::isValid()){
			$this->logger->Warn("Cannot Index Posts -> Site is invalid , no siteId/clientId is specified");
			return;
		}

		$this->logger->Warn("zoomd_indexer::index Called.");
		
		set_time_limit( 600 );
		//delete_option(LASTINDEXED);
		$fromdatetime = Settings::lastIndex(); 
		
		$this->logger->Info('got from date ' .$fromdatetime);
		

		$post = 1;
		$totalpages = 2;//neeed to be 2 for the below logic of bringing all pages
		$postsperpage = 10;
		$lastdatetime = new DateTime('now');
        $lastdatetime->modify('+1 hour');
		$errIds = $this->getErrorPostsIds();
		
		
		$args = array (				
				'nopaging'               => false,
				'post_status'            => 'publish',
				'post_type'              => array('post','page'),
				'posts_per_page'         => $postsperpage,
				'posts_per_archive_page' => $postsperpage,
				'ignore_sticky_posts'    => false,
				'order'                  => 'ASC',
				'orderby'                => 'post_modified_gmt',
				'paged'					 => 0,
				'offset'				 => 0,
				'date_query'    => array(
                    'relation' => 'AND',
                    array(
                        'column'	=> 'post_modified_gmt',
                        'after'	=> $fromdatetime,                        
                        'inclusive' => false,
                    ),
                    array(
                        'column'	=> 'post_modified_gmt',
                        'before' => $lastdatetime->format('Y-m-d H:i:s'),
                        'inclusive' => false,
                    )
    			),
			);
		
		$sentCount  = 0;
		//Phase 2 : Perform a Query
		for ($page=0; $page < $totalpages; $page++) 
		{			
			$args['paged'] = $page;
			$args['offset'] = ($page * $postsperpage);
			
			// The Query
			$query = new WP_Query( $args );		
			if($page == 0)	$totalpages = $query->max_num_pages;
						
			if ( $query->have_posts() ) {
				$post_count = count($query->posts);
				$this->logger->Info("found " .$post_count ." posts");
				foreach ( $query->posts as $qp) 
				{
					$qp = zoomd_indexer::post_to_zoomd_json($qp);
					//Remove From Error Id's if it exists there
					zoomd_utils::arrRemove($errIds,$qp->ID);
					$article_modified_date_gmt = $qp->post_modified_gmt;	
				}//for each post

				//Send to API
				$this->uploadPosts($query->posts,true,$errIds);
				Settings::setLastIndex($article_modified_date_gmt);
				$sentCount += count($query->posts);

			} else {
				$this->logger->Info("no posts found");
			}
			$this->logger->Info('Sent Total Posts :'  .$sentCount .' error count: ' .count($errIds));
			// Restore original Post Data
			wp_reset_postdata();
			

		}//For each page		

		//Try to recover Error Data
		$this->resendErrorPosts($errIds);
		
		///Save the Error Data if exists
		if(count($errIds) > 0){
			$this->saveErrorPost(json_encode(array_values($errIds)));
		}else {
			$this->clearErrPosts();
		}

		if(isset($article_modified_date_gmt))
			$this->logger->Info('Setting last Saved Date ' .$article_modified_date_gmt );
		else
			$this->logger->Info('Setting last Saved Date ' .Settings::lastIndex());
	}

	private function isAlive(){
		
		for ($x = 0; $x <= 3; $x++) {
			$response = $this->httpClient->get(GetApiEndpoint) ;
			$isValidChannel = $response["httpCode"] == 200;
			if($isValidChannel)
				return true;
			if(!$isValidChannel){
				$this->logger->Warn( "Http resource error. Invalid Get Ping Request");
			}
			
			sleep(5);
		}
		return false;
	}

	public function uploadPosts($post_arr,$saveErr,&$errIds) {
		
		//Send to API
		$tok = Settings::siteId() . ':' . Settings::apiHash();
		$siteurl = Settings::siteUrl();
		$model_data = $this->buildModelData($post_arr);
		$headers = array(
				'Content-Type: application/json',
				'Referer: ' . $siteurl,
				'Content-Length: ' . strlen($model_data),
				'RequestVerificationToken: ' . str_replace('"', "", $tok ));

		$responseArr = $this->httpClient->post(PostApiEndpoint,$model_data,$headers);
		$responseData = json_encode($responseArr["data"]);
		$httpCode = $responseArr["httpCode"];
		$this->logger->Info('indexer::uploadPosts called. HttpResponse : ' .$httpCode .'  response data:' .$responseData  );

		//Save Errors if Neccessary
		if($saveErr == true && $httpCode != 200) {
				//saving each page to its own private post "error" item
				$this->logger->Warn('indexer::uploadPosts called. HttpResponse : ' .$httpCode .'  response data:' .$responseData  );
				foreach ( $post_arr as $qp) {
					if(!in_array($qp->ID,$errIds))
						array_push($errIds,$qp->ID);
				}
			return ""; 
		}
		return $responseData;
	}

	private function buildModelData($post_arr){
		$siteId = Settings::siteId();
		$apiHash = Settings::apiHash();
		$siteurl = Settings::siteUrl();
				
		$upload_data = array("siteId" => $siteId,"hash"=>$apiHash,"posts"=> $post_arr,"siteUrl"=> $siteurl);
		$model_data = json_encode($upload_data);
		return $model_data;
	}

	

	private function saveErrorPost($post_data){
			
			//Clear the previous error post
			$this->clearErrPosts();

			$failDate = date("Y-m-d H:i:s");
			wp_insert_post( array(
								'post_content' => $post_data,
								'post_title'   => $failDate,
								'post_status'  => 'private',
								'post_type'    => 'zoomd_index_err'                                        
                                ));

			$this->logger->Warn('zoomd_utils::saveErrPage called ' .$post_data);

	}

	private function getErrPosts() {
		$args = array (                                                       
                'nopaging'               => true,
                'post_status'            => 'private',
                'post_type'              => 'zoomd_index_err',
				'order'                  => 'DESC',
				'orderby'                => 'ID',
				'posts_per_page'         => -1,
                                                );
        $errquery = new WP_Query( $args );
        if(count($errquery->posts) == 0){
			return array();
		}	
		
		return $errquery->posts;

	}

	private function getErrorPostsIds() {
	    
		$err_posts =  $this->getErrPosts();
		$count = count($err_posts);
		if($count == 0)
			return array();
		$errIdStr =  array_values(json_decode($err_posts[0]->post_content));
		return $errIdStr;


		
	}
	
	private function resendErrorPosts(&$err_Ids) {

		$chunks = array_chunk($err_Ids,15); 
		foreach ( $chunks as $chunk ) {
				$buffer = array();
				$this->logger->Info('Sending ' .count($chunk) .' Items from recovery to API');
				 //prepare the batch data
				 foreach($chunk as $curId){
					 $pq = get_post($curId);
					 if(isset($pq) && !empty($pq)){
						array_push($buffer,$pq);
					 }
				 }
				$dummy = array();
				$batchId= $this->uploadPosts($buffer,false,$dummy);
				$isErr = zoomd_utils::isEmpty($batchId);
				if($isErr == false){
					zoomd_utils::arrRemoveBulk($err_Ids,$chunk);
				}
			}
	}


	public function clearErrPosts() {
		$err_posts =  $this->getErrPosts();
		$err_data = json_encode($err_posts);
		$this->logger->Info('Found Error Post ' .$err_data);
		
		foreach ( $err_posts as $err_post ){
			$this->logger->Info('deleteing  ' .$err_post->ID);
			wp_delete_post( $err_post->ID, true );
		}
	}

	public static function post_to_zoomd_json($post) {
		$article_modified_date = $post->post_modified;						
		$post->post_authorName = zoomd_metadata::$authors[$post->post_author];
		$post->permlink = esc_url( get_permalink($post));
		if(has_post_thumbnail($post->ID))
		{
			$thumb_id = get_post_thumbnail_id($post);
			$thumb_url = wp_get_attachment_image_src($thumb_id,array( 300, 124))[0];
			$featured_url = wp_get_attachment_image_src($thumb_id,'full')[0];
			$post->thumbnail = zoomd_utils::getimageURL($thumb_url);
			$post->featured_image = zoomd_utils::getimageURL($featured_url);
		}
		$post->tags = wp_get_post_tags($post->ID, array( 'fields' => 'names' ));
		$post->categories = wp_get_post_categories($post->ID, array( 'fields' => 'names' ));					
		$att_args = array(
			'post_type' => 'attachment',
			'posts_per_page' => -1,
			'numberposts' => null,
			'post_status' => null,
			'post_parent' => $post->ID,    
		);
		//$post->post_content = json_encode($post->post_content);
		$attachments = get_posts($att_args);
		if ($attachments) {
			$article_attachements = array();
			foreach ($attachments as $attachment) {                            
				$article_attachements['attch_' . $attachment->ID] = zoomd_utils::getimageURL($attachment->guid);
			}
			$post->attachements = $article_attachements;
		}	

		$gallery_images = self::get_gallery_shortcode_images($post);
		if(isset($gallery_images))
		{
			$post->gallery = $gallery_images;
		}
		return $post;
	}


	private static function get_gallery_shortcode_images($post)
	{
		// Make sure the post has a gallery in it
		if( ! has_shortcode( $post->post_content, 'gallery' ) )
			return null;  

		// Retrieve the first gallery in the post
		$gallery = get_post_gallery_images( $post );
		$galimages = array();
		// Loop through each image in each gallery
		$imgnum = 1;
		foreach( $gallery as $image_url ) {
			$galimages['img_' . $imgnum] = zoomd_utils::getimageURL($image_url);
			$imgnum++;
		}
		
		return array_values($galimages);
	}

	

}

?>