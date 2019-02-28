# Sendy Extended PHP API Wrapper

Easily integrate Sendy into other applications with this extended PHP wrapper for the [Sendy API](https://sendy.co/api). This class provides methods to access Sendy's official API via cURL as well as methods to access the Sendy MySQL database directly for additional functionality.

## Official API Examples

    require_once('sendy.php');

    $sendy = new Sendy();
    $sendy->site_url = 'http://yoursite.com';
    $sendy->api_key = 'your_api_key';

### Create & Send a Campaign
Returns a string with status.

	$data = array(
		'from_name' = > 'Joe Schmoe',
    	'from_email' => 'name@domain.com',
    	'reply_to' => 'name@domain.com',
    	'subject' => 'This is a subject',
    	'plain_text' => 'Hello World.',
    	'html_text' => '<strong>Hello World.</strong>',
    	'list_ids' => 'd0v7I4h1LOQVQIsyk4f9, 02v0e9vbe8QVQIsykp9s',
    	'brand_id' => 1,
    	'send_campaign' => 0
	);

	$sendy->createCampaign($data);

### Subscriber Count
Returns an integer.

    $sendy->subscriberCount($list_id='d0v7I4h1LOQVQIsyk4f9');

### Subscriber Status
Returns string with status.

    $sendy->subscriberStatus(
        $email='name@domain.com, 
        $list_id='d0v7I4h1LOQVQIsyk4f9'
    );

### Subscribe
Subscribe an email address to a list. "Boolean" set to "true" will result in a plain text response, as opposed to an HTML document. Although this field is called "boolean," it actually expects a string value.

Pass in an associative array for your custom fields with the name of the key matching the name of your custom field. These are case sensitive and must be defined in Sendy prior to using them here.

This API function appears to also work for updating fields related to an email address, but will return with "already subscribed" upon updating field values.

    $sendy->subscribe(
        $name = 'John Doe', 
        $email = 'name@domain.com', 
        $list_id = , $
        boolean = 'true', 
        $custom_fields = array(
            'Company' => 'ABC Corp.',
            'Title' => 'CEO'
        )
    );

### Unsubscribe
Regardless of the list ID that you provide for an account, this API function appears to globally unsubscribe an email address from all lists within an account.

Like the subscribe method, the "boolean" field here also expects a string, not an actual boolean.

    $sendy->unsubscribe(
        $email = 'name@domain.com',
        $list_id = 'd0v7I4h1LOQVQIsyk4f9',
        $boolean = 'true'
    );

## Extended Database Methods

If you need to access Sendy data beyond what the API allows, the extra methods below will let you access raw data from MySQL. 

These methods are currently compatible with and tested on version 2.0.4.

### Connect to the Sendy MySQL Database

    $sendy->connect(
        $host='localhost', 
        $database='sendy', 
        $user='root', 
        $pass='root'
    );
    
### Get Brands

    $sendy->getBrands($id, $user_id);

### Get Users

    $sendy->getUsers();

### Get Lists

    $sendy->getLists($id, $brand_id, $user_id);

### Get Subscribers

    $sendy->getSubscribers($id, $list_id, $user_id);

### Get Templates

    $sendy->getTemplates($id, $brand_id, $user_id);

### Get Campaigns

    $sendy->getCampaigns($id, $brand_id, $user_id);

### Get Links/Clicks

    $sendy->getLinks($id, $campaign_id);






