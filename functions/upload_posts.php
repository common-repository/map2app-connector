<?php
function upload_post($post_id,$lang){
	//foreach selected post: clear the content from images, store post in map2app, store images in map2app, attach them to the post
	
		$category=map2app_get_category();
		$map2app_category=$category['category_id'];
		$album=map2app_get_album();
		$map2app_album_key=$album['map2app_album_key'];
		$map2app_user=get_map2app_user();
		$post = get_post($post_id);
		$content = $post->post_content;
		$title = $post->post_title;
		//clear the content from images and store them in a array
		$DOM = new DOMDocument();
		$DOM->loadHTML('<?xml encoding="UTF-8"><div>'.$content.'</div>');
		$xpath = new DOMXPath($DOM);
		$html_imgs = $xpath->query('//img');
		$imgs_to_upload=array();
		$media_attached=array();
		if (!is_null($html_imgs)) {
			foreach ($html_imgs as $img) {
				$imgs_to_upload[]=$img->getAttribute('src');
				$img->parentNode->removeChild($img);
	
	
			}
			//print_r($imgs_to_upload);
		}
		$root=$DOM->documentElement;
		$map2app_content_dirty=$DOM->saveHTML($root);
		$map2app_content=substr ($map2app_content_dirty,6,strlen($map2app_content_dirty)-6);
		$map2app_mapping=get_post_map2app_status($post_id);
		$map2app_posted;
		$map2app_posted_key;
		if($map2app_mapping!=null&&$map2app_mapping->map2app_id!=''&&$map2app_mapping->map2app_id!=null){
	
			$map2app_posted=map2App_request('categories/'.$map2app_category['key'].'/contents/'.$map2app_mapping->map2app_id,false,'GET');
	
		}
	
	
	
		if(isset($map2app_posted)&&isset($map2app_mapping)){
	
			$map2app_post=map2app_updated_post($title,$map2app_content,$lang,$map2app_posted);
	
			$json = json_decode('{"imagesAttached":null, "audiosAttached":null, "videosAttached":null, "contentIsPresent":[true,false], "moreIsPresent":[false,false], "tags":[], "periodicity":-1, "expirationDate":null, "type":2, "updatedDateByLang":["2013-12-19T11:25:19.240Z","2013-12-19T11:25:19.240Z"], "lastLoadDate":null, "icon":null, "iconMap":null, "iconMediaKey":null, "title":["Ciao mondo!!","Ciao mondo!!"], "content":[{"value":"<div>\n\tBenvenuto in WordPress. Questo è il tuo primo articolo. Modificalo o cancellalo e inizia a creare il tuo blog! dfsdfdsf</div>\n"},{"value":""}], "summary":["",""], "telephone":null, "telephone2":null, "street":["",""], "city":["",""], "zipCode":"", "state":["",""], "country":["",""], "longaddress":"", "latitude":null, "longitude":null, "averageRating":0, "voters":0, "url":["",""], "fax":null, "email":null, "more":[{"value":""},{"value":""}], "price":["",""], "hours":["",""], "startDate":null, "endDate":null, "image":[], "audio":[], "video":[], "sizeImage":[0,0], "sizeAudio":[0,0], "sizeVideo":[0,0], "keyCategory":"ahJzfm1hcDJhcHAtcGxhdGZvcm1yJAsSC1VzZXJBY2NvdW50GIXniQkMCxIIQ2F0ZWdvcnkY8ZMJDA", "languagesBackoffice":["en","it"], "key":"ahJzfm1hcDJhcHAtcGxhdGZvcm1yMwsSC1VzZXJBY2NvdW50GIXniQkMCxIIQ2F0ZWdvcnkY8ZMJDAsSB0NvbnRlbnQYwe0aDA", "creationDate":"2013-12-19T11:25:19.240Z", "updatedDate":"2013-12-19T11:25:19.240Z"}');
	
	
			$map2app_posted=map2App_request('categories/'.$map2app_category['key'].'/contents/'.$map2app_mapping->map2app_id,$map2app_post,'PUT');
			$map2app_posted_key=$map2app_mapping->map2app_id;
				
		}
		else{
			$map2app_post=map2app_new_post($title,$map2app_content,$map2app_category['key'],$map2app_user['languagesBackoffice'],$_POST['lang']);
			$map2app_posted=map2App_request('categories/'.$map2app_category['key'].'/contents',$map2app_post,'POST');
			$map2app_posted_key=$map2app_posted['key'];
		}
	
		//insert posted article id in db
	
		update_post_status($post->ID,$map2app_posted_key,$map2app_category['key']);
	
	
	
		$attached_media = get_attached_media( 'image',$post->ID );
		$to_be_attached=array();
		foreach ($attached_media as $img) {
	
			$map2app_endpoint=map2App_request('albums/'.$map2app_album_key.'/upload',null,'GET');
				
				
			//$data=file_get_contents($img);
			$map2app_album=map2App_request('albums/'.$map2app_album_key.'/medias',null,'GET');
				
			$file=get_attached_file($img->ID);
			$name=$img->guid;
			$found=false;
			$media_key='';
			foreach($map2app_album as $media){
				if($media['originalName']==$name){
					$found=true;
					//$media_key=$media['key'];
					$media_key=$name;
				}
			}
			if(!$found){
				map2app_send_file($file,$name,$map2app_endpoint,$img->post_mime_type);
				/*foreach($map2app_album as $media){
				 if($media['originalName']==$name){
				$found=true;
				$media_key=$media['key'];
				}
				}*/
				$found=true;
				$media_key=$name;
			}
			if(!$found){
				echo '<div id="message" class="error fade"><p><strong>'.__('Error uploading image ','map2app').'</strong><strong>'.$name.'</strong></p></div>';
			}
			else $to_be_attached[]=$media_key;
				
				
				
	
		}
	
		if($to_be_attached){
				
			$result=map2App_request('categories/'.$map2app_category['key'].'/contents/'.$map2app_posted_key.'/requestAttach/image',array('fileName'=>$to_be_attached),'POST');
			/*echo 'ATTACH_QUERY   ';
			 print_r('categories/'.$map2app_category['key'].'/contents/'.$map2app_posted_key.'/requestAttach/image');
			echo 'JSON   ';
			echo json_encode(array('fileName'=>$to_be_attached));*/
				
		}
	
		return $map2app_posted_key;
	
}

//upload a post array to map2app
function upload_posts(){
foreach($_POST['post_ids'] as $post_id){
	upload_post($post_id,$_POST['lang']);
}
}

//ajax request to upload a single post
add_action( 'wp_ajax_send_recap_email', 'send_recap_email' );
function send_recap_email(){
	$ids=$_POST['ids'];
	$map2app_user=get_map2app_user();
	//send an email to know how many posts were uploaded
		$message="Upload of ".count($ids)." articles from site: ".get_site_url()." by map2app user: ".$map2app_user['email']."\r\nMap2App Id list:\r\n";
		foreach($ids as $id){
			$message.=$id."\r\n";
		}
		if(MAP2APP_TEST)
			$mail="ale@poistory.it";
		else
			$mail="map2app-wordpress@poistory.it";
			
		wp_mail($mail,"[New Wordpress to Map2App Upload]", $message);
		
}

//ajax request to upload a single post
add_action( 'wp_ajax_upload_post', 'upload_post_javascript' );

function upload_post_javascript() { 
	$result=0;
	try{
	$post_id=$_POST['id'];
	$lang=$_POST['lang'];
	$result=upload_post($post_id,$lang);

    echo json_encode(array('result' => ($result?1:0),'id' => $result));
	}catch(Exception $e){
		echo json_encode(array('result' => ($result?1:0),'error'=>print_r($e)));
	}
	die(); // this is required to terminate immediately and return a proper response
}


///OLD CODE
/*echo '<div id="message" class="updated fade"><p><strong>'.__('"My Wordpress" category created on map2app.','map2app').'</strong></p></div>';
	echo '<div id="message" class="updated fade"><p><strong>'.__('You have successfully upload your posts to Map2App.','map2app').'</strong></p></div>';
	*/