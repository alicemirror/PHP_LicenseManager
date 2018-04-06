<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php

	// ---------------------------------------------------------------
	// Check if the login was inserted correctly. If the vendor key
	// is wrong, the login page is shown again.
	// ---------------------------------------------------------------
	
	// Class loadedr
	function classLoader($className) {
		require_once dirname(__FILE__) ."/Classes/". $className . '.php';
	}
	
	spl_autoload_register('classLoader');
	
	// Vendor key (should be passed as parameter)
	$VENDOR_KEY = "";
	
	// Debug
	$DEBUG = 0;
	
	// Check if the key parameter was passed to the page
	$VENDOR_KEY = $_POST['access_key'];
	
	// Create the license creation class instance.
	$creator = new CreateLicenses();
	// Get the Vendor name and initializes the vendor variables in the
	// license creator class
	$FIELDS = $creator->checkVendorRecord($VENDOR_KEY);
	
	// If the vendor key is not valid go back to the main page for security reasons
	// avoiding any error message.
	if($FIELDS == null) {
		// Wrong vendor key
		if($DEBUG == 1) {
			echo "controlKey.php : ERROR vendor null";
		}
		else {
			http_redirect( "index.php" );
		}
	} // Vendor name is null
	else {
		// Correct vendor key. POST fields to the license creator page.
		postRequest("http://license-manager.techinside.es/creator.php", $FIELDS);
		// http_redirect( "creator.php?access_key=$VENDOR_KEY" );
	} // vendor is found in the DB
	
	// =============================================================================
	//								PAGE METHODS
	// =============================================================================
	
	// Prepare the data and call the next page sending the parameters via post to
	// avoid passing parameters via GET taht are visible in the url
	function postRequest( $url, $fields, $optional_headers = null ) {
		// http_build_query is preferred but doesn't seem to work!
		// $fields_string = http_build_query($fields, '', '&', PHP_QUERY_RFC3986);
		
		// Create URL parameter string
		foreach( $fields as $key => $value )
			$fields_string .= $key.'='.$value.'&';
			
		$fields_string = rtrim( $fields_string, '&' );

//		echo "controlKey.php : postRequest() : URL = $url";
//		echo "controlKey.php : postRequest() : Fields_string = $fields_string";
		
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt( $ch, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt( $ch, CURLOPT_POST, count( $fields ) );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields_string );
		
		$result = curl_exec( $ch );
		
		curl_close( $ch );
	}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Untitled Document</title>
</head>

<body>
</body>
</html>