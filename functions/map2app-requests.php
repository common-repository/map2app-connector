<?php 

/*
 * Call a generic REST API
 */
function CallAPI($method, $url, $data_array = false)
{
	$curl = curl_init();
	$data=($data_array)?json_encode($data_array):false;
	switch ($method)
	{
		case "POST":
			//curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
			//if ($data)
				//curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			break;
		case "PUT":
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
			break;
		default:
			if ($data)
				$url = sprintf("%s?%s", $url, http_build_query($data));
	}

	// Optional Authentication:
	//curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	//curl_setopt($curl, CURLOPT_USERPWD, "username:password");
	if($data_array){
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data))
	);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	}
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	//echo $data;
	return curl_exec($curl);
}

/*
 * CALL MAP2APP API
 */
function map2App_request($resource,$data=false,$method='POST'){
	$userKey=get_option('map2app_user_key');
	$apiSecret=get_option('map2app_api_secret');
	
	if(!isset($userKey)||!isset($apiSecret)||$apiSecret==''||$userKey=='')
		return false;
	
	$baseurl="https://map2app-platform.appspot.com/api/useraccounts/".$userKey."/";
	$auth_params="?userKey=".$userKey."&apiSecret=".$apiSecret."";
	$url=$baseurl.$resource.$auth_params;
	
	if(MAP2APP_TEST){
		echo '<br/>';
		print_r($url);
		echo '<br/>';
	}
	
	return json_decode(CallAPI($method,$url,$data), true);
}

function get_map2app_user(){
	return map2App_request('',false,'GET');
}

/*
 * "MODELS"
 */


/*
 * GET MAP2APP CATEGORY ARRAY
 */
function map2app_category($title,$langs){
	foreach($langs as $lang){
	$empty_array[]="";
	$title_array[]=$title;
	
	}
	$array=array(
		'rss'=> $empty_array,
		'contents' => null,
		'sizeContents' => 0,
		'languagesBackoffice' => $langs,
		'kmlFileKey' => null,
		'type' => 0,
		'title' => $title_array,
		'description' => $empty_array,
		'iconMediaKey' => null,
		'key' => null,
		'creationDate' => null,
		'updatedDate' => null
	);
	
	return $array;
}
/**
 * Add map2app post
 * @param unknown_type $title
 * @param unknown_type $content
 * @param unknown_type $category_key
 * @param unknown_type $langs
 * @param unknown_type $content_lang
 * @param unknown_type $type
 * @param unknown_type $latitude
 * @param unknown_type $longitude
 */
function map2app_new_post($title,$content,$category_key,$langs,$content_lang,$type=2,$latitude=null,$longitude=null){
	$content_array=array();
	$empty_value_array=array();
	$contentIsPresent=array();
	
	foreach($langs as $lang){
		$empty_array[]="";
		$title_array[]=$title;
		$empty_value_array[]=array("value"=>"");
		$empty_size_array[]=0;
		$boolean_array[]=false;
		$contentIsPresent[] = ($lang == $content_lang ? true : false);
		$content_array[] = ($lang == $content_lang ? array("value"=>$content) : array("value"=>""));
	}
	
	$array=array( 
			"imagesAttached" => null, 
			"audiosAttached" => null, 
			"videosAttached" =>null, 
			"contentIsPresent" => $contentIsPresent, 
			"moreIsPresent" => $boolean_array, 
			"tags" => null, 
			"periodicity" => -1, 
			"expirationDate" => null, 
			"type" => $type, 
			"updatedDateByLang" => null, 
			"lastLoadDate" => null, 
			"icon" => null, 
			"iconMap" => null, 
			"iconMediaKey" => null, 
			"title" => $title_array, 
			"content" => $content_array, 
			"summary" => $empty_array, 
			"telephone" => null, 
			"telephone2" => null, 
			"street" => $empty_array, 
			"city" => $empty_array,
			"zipCode" => "",
			"state" => $empty_array,
			"country" => $empty_array,
			"longaddress" => "",
			"latitude" => $latitude,
			"longitude" => $longitude,
			"averageRating" => 0,
			"voters" => 0,
			"url" => $empty_array,
			"fax" => null,
			"email" => null,
			 "more" => $empty_value_array,
			"price" => $empty_array,
			"hours" => $empty_array,
			"startDate" => null, 
			"endDate" => null,
			"image" => null,
			"audio" => null,
			"video" => null,
			"sizeImage" => $empty_size_array,
			"sizeAudio" => $empty_size_array,
			"sizeVideo" => $empty_size_array,
			"keyCategory"=> $category_key,
			"languagesBackoffice"=>$langs,
			"key" => null,
			"creationDate" => null,
			"updatedDate" => null);
	return $array;
}

function map2app_album($name){
	return array(
		//"userOwner" => null,
		"title" => $name
	);
}

function map2app_image($url,$album_key){
	return array(
			
  "type" => 'image',
  "keyAlbum" => $album_key,
	"originalName" => $url
	);
}

function map2app_mediaAttached($post_key,$image_keys){
	$images=array();
	foreach($image_keys as $key){
		$images=array("type" => 'image',
		"keyAlbum" => $album_key);
	}
	return $images;
			
}

function map2app_updated_post($title,$content,$content_lang,$map2app_post){
	
	$langs=$map2app_post["languagesBackoffice"];
	$i=0;
	foreach($langs as $lang){
		
		$title_array[]=($lang == $content_lang ? $title : $map2app_post["title"][$i]);
		$contentIsPresent[] = ($lang == $content_lang ? true : $map2app_post["contentIsPresent"][$i]);
		$content_array[] = ($lang == $content_lang ? array("value"=>$content) : $map2app_post["content"][$i]);
		$i++;
	}

	$map2app_post["contentIsPresent"]=$contentIsPresent;
	$map2app_post["content"]=$content_array;
	$map2app_post["title"]=$title_array;
	return $map2app_post;
}

function map2app_send_file($file,$name,$endpoint,$type='image/jpg'){
	
	
	
	/*$eol = "\r\n";
	$data = '';
	$filedata = file_get_contents($file);
	$mime_boundary="*****";
	
	//$data .= '--' . $mime_boundary . $eol;
	//$data .= 'Content-Disposition: form-data; name="filename"' . $eol . $eol;
	//$data .= $name . $eol;
	//$data .= '--' . $mime_boundary . $eol;
	$data .= 'Content-Disposition: form-data; name="upload"; filename="'.$name.'"' . $eol;
	//$data .= 'Content-Type: image/'.$contenttype . $eol;
	//$data .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
	$data .= $filedata . $eol;
	$data .= "--" . $mime_boundary . "--" . $eol . $eol; // finish with two eol's!!
	
	$params = array('http' => array(
			'method' => 'POST',
			'header' => 'Content-Type: multipart/form-data; boundary=' . $mime_boundary . $eol,
			'content' => $data
	));
	*/
	$POST_DATA  = array(
			'upload[]'=>'@/'.$file.';type='.$type.';filename='.$name,
			);
	$header = array('Content-Type: multipart/form-data');
	// init curl
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $endpoint);
	
	// configure options
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt( $ch, CURLOPT_VERBOSE, 1 );
	// post data
	curl_setopt( $ch, CURLOPT_POST, true );
	
	
	
	curl_setopt($ch, CURLOPT_POSTFIELDS, $POST_DATA);
	
	
	// execute and get return value
	$return = trim( curl_exec( $ch ) );
	//TEST CONNECTION
	$code = curl_getinfo($ch);
	//print_r($code);
	if (MAP2APP_TEST) {
		if (in_array ( $code ['http_code'], array (
				202,
				204 
		) )) {
			print_r ( "Positive response - request ({$code['http_code']})." );
		} else {
			print_r ( "Error issuing - request - ({$code['http_code']})." );
		}
	}
	// cleanup
	curl_close( $ch );
	unset( $ch );
	
	
}

function map2app_get_category($name="My Wordpress"){
	
	$map2app_user=get_map2app_user();
	$result=map2App_request('categories.json',false,'GET');
	$wpCategoryExists=false;
	if(is_array($result)){
		foreach($result as $category){
						$lang=$category['languagesBackoffice']; ?>
						<?php if(isset($category['title'][0])&&($category['title'][0]==$name)){
							$wpCategoryExists=true; 
							$map2app_category=$category;
							}
						 }
					}
					if(!$wpCategoryExists){
						$map2app_category=map2App_request('categories',map2app_category('My Wordpress',$map2app_user['languagesBackoffice']),'POST');
						}
	return array('category_id'=>$map2app_category,'is_new'=>!$wpCategoryExists);										
				
}

function map2app_get_album(){
	
	$albums = map2App_request ( 'albums', false, 'GET' );
	$found = false;
	
	$map2app_album = null;
	if ($albums) {
		foreach ( $albums as $album ) {
			$map2app_album_name = (get_option ( 'map2app_album' ) ? get_option ( 'map2app_album' ) : 'WP-Blog 4 Map2App');
			if ($album ['title'] == $map2app_album_name) {
				$found = true;
				$map2app_album = $album;
				break;
			}
		}
		if (! $found) {
			$albums = array ();
			$map2app_album = map2App_request ( 'albums', map2app_album ( get_option ( 'map2app_album' ) ), 'POST' );
			$found = true;
		}
		$map2app_album_key;
		if (isset ( $map2app_album )) {
			
			$map2app_album_key = $map2app_album ['key'];
		}
		return array (
				'map2app_album_key' => $map2app_album_key,
				'is_new' => ! $found 
		);
	} else
		return false;
}
?>