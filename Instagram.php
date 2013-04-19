<?php

/*
*
* API wrapper for instagram
* Implements simple file cache based
* on user_id.
*
* @author Ross  
*
*/
class Instagram{
	
	private $token = null;
	private $instagram = null;
	private $user = null;	
		
	/**
	*
	* constructor 
	*
	* @param String $token - instagram api token
	*
	*/
	function __construct($token){
		
		//curl is required
		if(!function_exists('curl_version'))
		{
			die("The CURL extension is required ");
		}
		
		if(is_null($token)){
			die('Error: no Instagram user auth token passed to constructor');
		}
		
		$instagram = new Instagram\Instagram;
		$instagram->setAccessToken($token);
		$this->instagram = $instagram;
		$this->user = $this->instagram->getCurrentUser();
	}
	
	/*
	*
	* Get a the current users large pictures
	* 
    * @param Array $params  - params to include in request(e.g(array('count' => 10)
	* @return Array $return - array of image urls
	*
	**/
	public function getLargePics( array $params = null){
		
		$return = array();
		foreach($this->user->getMedia($params) as $pic){
			$return[] = $pic->images->standard_resolution->url;
		}
		
		return $return;
	}
	
	/*
	*
	* Get a the current users thumbnail pictures
	*
    * @param Array $params  - params to include in request(e.g(array('count' => 10)
	* @return Array $return - array of image urls
	*
	**/
	public function getThumbnails( array $params = null){
		
		$return = array();
		foreach($this->user->getMedia($params) as $pic){
			$return[] = $pic->images->thumbnail->url;
		}
		return $return;
	}
	
	
	
	public function getRawFeed(){
		
		return $this->user->getMedia();
	}
	
	/*
	*
	* Get a users thumbnail pictures (must be a public feed)
	* 
	* @param String $u_id   - user id(http://jelled.com/instagram/lookup-user-id)
    * @param Array $params  - params to include in request(e.g(array('count' => 10)
	*
	*
	* @return Array $return - array of image urls
	*
	**/
	public function getUserThumbnails($u_id,array $params = null){
		
		$return = array();

		//cache file for this user
		$filename =  dirname(__FILE__) . '/'. $u_id . '_thumb.txt';
		
		//read it - if not too old
		if( (file_exists($filename)) && (filemtime($filename) > (time() - 60 * 60))){
			$return = $this->readCache($filename);
		}
		else{
	
			//get a public user timeline
			$user = $this->instagram->getUser($u_id);
			foreach($user->getMedia($params) as $pic){
				$return[] = $pic->images->thumbnail->url;
			}
			//save the cache
			if(!file_put_contents($filename,serialize($return))){
				die('Instagram: failed to write cache file - check permissions');
			}
		
		}
		
		return $return;
	}
	
	
   /*
	*
	* Get a users large pictures (must be a public feed)
	* 
	* @param String $u_id   - user id(http://jelled.com/instagram/lookup-user-id)
    * @param Array $params  - params to include in request(e.g(array('count' => 10)
	*
	*
	* @return Array $return - array of image urls
	*
	**/
	public function getUserLargePics($u_id, array $params = null){
		
		$return = array();

		//cache file for this user
		$filename =  dirname(__FILE__) . '/'. $u_id . '_large.txt';
		
		//read it - if not too old
		if( (file_exists($filename)) && (filemtime($filename) > (time() - 60 * 60))){
			$return = $this->readCache($filename);
		}
		else{
	
			$user = $this->instagram->getUser($u_id);
			foreach($user->getMedia($params) as $pic){
				$return[] = $pic->images->standard_resolution->url;
			}
			//save the cache
			if(!file_put_contents($filename,serialize($return))){
				die('Instagram: failed to write cache file - check permissions');
			}
		}
		
		return $return;
	}
	
	/*
	*
	* Read and unserialise a cache file
	* 
	* @param String $cachefile - file to read
    * @return Boolean - return false if cache file doesnt exist
	*
	**/
	private function readCache($cachefile)
	{
		if(file_exists($cachefile))
		{
			return unserialize(file_get_contents($cachefile));

		}
		else
			return false;
	}
}


// autoload function - needed for the instagram API in this case
 spl_autoload_register(function($class){
	// convert namespace to full file path
	$class =  str_replace('\\', '/', $class) . '.php';
	require_once($class);
});