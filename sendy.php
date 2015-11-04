<?php 

class Sendy {

	public $api_key;
	public $site_url;
	public $database;

	/*
	|--------------------------------------------------------------------------
	| USE cURL TO POST TO SENDY ENDPOINTS
	|
	| PARAMETERS:
	| * url
	| * fields (array)
	|
	| RESPONSE: String with status
	|--------------------------------------------------------------------------
	*/
	public function curlPost($url, $fields) 
	{
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_POSTFIELDS => http_build_query($fields)
		));
		$result = curl_exec($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$result = array('result'=>$result,'status'=>$http_status);
		curl_close($ch);
		return $result;
	}

	/*
	|--------------------------------------------------------------------------
	| CREATE AND SEND A CAMPAIGN
	|
	| PARAMETERS:
	| * api_key
	| * from_name
	| * from_email
	| * reply_to
	| * subject
	| * plain_text
	| * html_text
	| * list_ids (comma-separated)
	| * brand_id (required if you are creating a draft, send_campaign set to 0 or left as default)
	| * send_campaign (set to 1 if you want to send the campaign)
	|
	| RESPONSE: String with status
	|--------------------------------------------------------------------------
	*/
	public function createCampaign($data) 
	{
		$url = $this->site_url . '/api/campaigns/create.php';
		$data['api_key'] = $this->api_key;
		$campaign = $this->curlPost($url, $data);
	}

	/*
	|--------------------------------------------------------------------------
	| GET ACTIVE SUBSCRIBER COUNT FOR A LIST
	|
	| PARAMETERS:
	| * api_key
	| * list_id
	|
	| RESPONSE: Integer with active subscriber count
	|--------------------------------------------------------------------------
	*/
	public function subscriberCount($list_id) 
	{
		$url = $this->site_url . '/api/subscribers/active-subscriber-count.php';
		$subscriber_count = $this->curlPost(
			$url, 
			$fields = array(
				'api_key' => $this->api_key, 
				'list_id' => $list_id
				)
			);
		return $subscriber_count['result'];
	}

	/*
	|--------------------------------------------------------------------------
	| SUBSCRIBER STATUS FOR A LIST
	|
	| PARAMETERS:
	| * api_key
	| * email
	| * list_id
	|
	| RESPONSE: String with subscriber status
	|--------------------------------------------------------------------------
	*/
	public function subscriberStatus($email, $list_id) 
	{
		$url = $this->site_url . '/api/subscribers/subscription-status.php';
		$subscriber_status = $this->curlPost(
			$url, 
			$fields = array(
				'api_key' => $this->api_key, 
				'email' => $email, 
				'list_id' => $list_id
			)
		);
		return $subscriber_status['result'];
	}

	/*
	|--------------------------------------------------------------------------
	| SUBSCRIBE
	|
	| PARAMETERS:
	| * name (optional)
	| * email
	| * list
	| * boolean (Set this to "true" so that you'll get a plain text response)
	| * (custom fields can also be added here by name)
	|
	| RESPONSE: String with subscriber status
	|--------------------------------------------------------------------------
	*/
	public function subscribe($name, $email, $list_id, $boolean, $custom_fields) 
	{
		$url = $this->site_url . '/subscribe';
		$fields = array(
			'api_key' => $this->api_key,
			'name' => $name,
			'email' => $email, 
			'list' => $list_id,
			'boolean' => $boolean
		);
		$custom_field_keys = array_keys($custom_fields);
		$i = 0;
		foreach( $custom_fields as $custom_field ) {
			$fields[$custom_field_keys[$i]] = $custom_field;
			$i++;
		}
		$subscribe = $this->curlPost($url, $fields);
		return $subscribe['result'];
	}

	/*
	|--------------------------------------------------------------------------
	| UNSUBSCRIBE
	|
	| PARAMETERS:
	| * name (optional)
	| * email
	| * list
	| * boolean (Set this to "true" so that you'll get a plain text response)
	|
	| RESPONSE: String with status
	|--------------------------------------------------------------------------
	*/
	public function unsubscribe($email, $list_id, $boolean) 
	{
		$url = $this->site_url . '/unsubscribe';
		$unsubscribe = $this->curlPost($url, $fields = array(
			'api_key' => $this->api_key, 
			'email' => $email, 
			'list' => $list_id
		));
		return $unsubscribe['result'];
	}

	/*
	|--------------------------------------------------------------------------
	|--------------------------------------------------------------------------
	| UNOFFICIAL API / PULLING SENDY DATA FROM ITS MYSQL DATABASE
	|
	| The methods below provide additional function pull directly from the 
	| Sendy MySQL database and do not use the official Sendy API.
	|--------------------------------------------------------------------------
	|--------------------------------------------------------------------------
	*/

	/*
	|--------------------------------------------------------------------------
	| CONNECT TO THE SENDY DATABASE
	| Required for all methods below.
	|--------------------------------------------------------------------------
	*/
	public function connect($host, $database, $user, $pass) 
	{
		try {
		  $this->database = new PDO('mysql:host='.$host.';dbname='.$database,$user,$pass);
		} catch (PDOException $e) {		    
		  return "Could not connect to database.";
		}
	}

	/*
	|--------------------------------------------------------------------------
	| SELECT AND RETURN ASSOCIATIVE ARRAY FROM DATABASE
	|--------------------------------------------------------------------------
	*/
	public function fetch($sql, $all=TRUE) 
	{
		$stmt = $this->database->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->execute();
		if( $all == TRUE ) {
			$result = $stmt->fetchAll();
		} else {
			$result = $stmt->fetch();
		}
		return $result;
	}

	/*
	|--------------------------------------------------------------------------
	| GET BRANDS
	|--------------------------------------------------------------------------
	*/
	public function getBrands($id=NULL, $user_id=NULL) 
	{
		// Get all brands
		if ( $id == NULL && $user_id == NULL ) {
			$sql = 'SELECT * FROM apps';
			return $this->fetch($sql);
		// Get brand by ID
		} elseif ( is_numeric($id) && $user_id == NULL ) {
			$sql = 'SELECT * FROM apps WHERE id = '.$id;
			return $this->fetch($sql, $all=FALSE);
		// Get brands associated with a user ID
		} elseif ( is_numeric($user_id) && $id == NULL ) {
			$sql = 'SELECT * FROM apps WHERE userID = '.$user_id;
			return $this->fetch($sql);
		}
	}

	/*
	|--------------------------------------------------------------------------
	| GET USERS
	|--------------------------------------------------------------------------
	*/
	public function getUsers($id=NULL) 
	{
		if ( $id == null ) {
			// Get all users
			$sql = 'SELECT * FROM login';
			return $this->fetch($sql);
			// Get user by ID
		} elseif ( is_numeric($id) ) {
			$sql = 'SELECT * FROM login WHERE id = '.$id;
			return $this->fetch($sql, $all=FALSE);
		}
	}

	/*
	|--------------------------------------------------------------------------
	| GET LISTS
	|--------------------------------------------------------------------------
	*/
	public function getLists($id=NULL, $brand_id=NULL, $user_id=NULL) 
	{
		// Get all lists
		if ( $id == NULL && $brand_id == NULL && $user_id == NULL ) {
			$sql = 'SELECT * FROM lists';
			return $this->fetch($sql);
		// Get list by ID
		} elseif ( is_numeric($id) && $brand_id == NULL && $user_id == NULL ) {
			$sql = 'SELECT * FROM lists WHERE id = '.$id;
			return $this->fetch($sql, $all=FALSE);
		// Get lists associated with a brand ID
		} elseif ( $id == NULL && is_numeric($brand_id) && $user_id == NULL ) {
			$sql = 'SELECT * FROM lists WHERE app = '.$brand_id;
			return $this->fetch($sql);
		// Get lists associated with a user ID
		} elseif ( $id == NULL && $brand_id == NULL && is_numeric($user_id) ) {
			$sql = 'SELECT * FROM lists WHERE app = '.$user_id;
			return $this->fetch($sql);
		}
	}

	/*
	|--------------------------------------------------------------------------
	| GET SUBSCRIBERS
	|--------------------------------------------------------------------------
	*/
	public function getSubscribers($id=NULL, $list_id=NULL, $user_id=NULL) 
	{
		// Get all subscribers
		if ( $id == NULL && $list_id == NULL && $user_id == NULL ) {
			$sql = 'SELECT * FROM subscribers';
			return $this->fetch($sql);		
		}
		// Get subscriber by ID
		elseif ( is_numeric($id) && $list_id == NULL && $user_id == NULL ) {
			$sql = 'SELECT * FROM subscribers WHERE id = '.$id;
			return $this->fetch($sql, $all=FALSE);	
		} 
		// Get subscribers associated with a list ID
		elseif ( $id == NULL && is_numeric($list_id) && $user_id == NULL ) {
			$sql = 'SELECT * FROM subscribers WHERE list = '.$list_id;
			return $this->fetch($sql);
		} 
		// Get subscribers associated with a user ID
		elseif ( $id == NULL && $list_id == NULL && is_numeric($user_id) ) {
			$sql = 'SELECT * FROM subscribers WHERE userID = '.$user_id;
			return $this->fetch($sql);
		}
	}

	/*
	|--------------------------------------------------------------------------
	| GET TEMPLATES
	|--------------------------------------------------------------------------
	*/
	public function getTemplates($id=NULL, $brand_id=NULL, $user_id=NULL) 
	{
		// Get all templates
		if ( $id == NULL && $brand_id == NULL && $user_id == NULL ) {
			$sql = 'SELECT * FROM template';
			return $this->fetch($sql);
		}
		// Get a template by ID
		elseif ( is_numeric($id) && $brand_id == NULL && $user_id == NULL ) {
			$sql = 'SELECT * FROM template WHERE app = '.$id;
			return $this->fetch($sql, $all=FALSE);
		}
		// Get templates associated with a brand ID
		elseif ( $id == NULL && is_numeric($brand_id) && $user_id == NULL ) {
			$sql = 'SELECT * FROM template WHERE app = '.$brand_id;
			return $this->fetch($sql);
		}
		// Get templates associated with a user ID
		elseif ( $id == NULL && $brand_id == NULL && is_numeric($user_id) ) {
			$sql = 'SELECT * FROM template WHERE userID = '.$user_id;
			return $this->fetch($sql);
		}
	}

	/*
	|--------------------------------------------------------------------------
	| GET CAMPAIGNS
	|--------------------------------------------------------------------------
	*/
	public function getCampaigns($id=NULL, $brand_id=NULL, $user_id=NULL) 
	{
		// Get all tempaltes
		if ( $id == NULL && $brand_id == NULL && $user_id == NULL ) {
			$sql = 'SELECT * FROM campaigns';
			return $this->fetch($sql);
		}
		// Get template by ID
		elseif ( is_numeric($id) && $brand_id == NULL && $user_id == NULL ) {
			$sql = 'SELECT * FROM campaigns WHERE id = '.$id;
			return $this->fetch($sql, $all=FALSE);
		}
		// Get templates associated with a brand ID
		elseif ( $id == NULL && is_numeric($brand_id) && $user_id == NULL ) {
			$sql = 'SELECT * FROM campaigns WHERE app = '.$brand_id;
			return $this->fetch($sql);
		}
		// Get tempalte by ID
		elseif ( $id == NULL && $brand_id == NULL && is_numeric($user_id) ) {
			$sql = 'SELECT * FROM campaigns WHERE userID = '.$user_id;
			return $this->fetch($sql);
		}
	}

	/*
	|--------------------------------------------------------------------------
	| GET LINKS / CLICKS
	|--------------------------------------------------------------------------
	*/
	public function getLinks($id=NULL, $campaign_id=NULL) 
	{
		// Get all links
		if ( $id == NULL && $campaign_id == NULL ) {
			$sql = 'SELECT * FROM links';
			return $this->fetch($sql);
		}
		// Get link by ID
		elseif ( is_numeric($id) && $campaign_id == NULL ) {
			$sql = 'SELECT * FROM links WHERE id = '.$id;
			return $this->fetch($sql, $all=FALSE);
		}
		// Get links associated with campaign ID
		elseif ( $id == NULL && is_numeric($campaign_id) ) {
			$sql = 'SELECT * FROM links WHERE campaign_id = '.$campaign_id;
			return $this->fetch($sql);
		}
	}

}

?>