<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php

	// ---------------------------------------------------------------
	// Control the license form data. If the license is ok, it is created
	// and the confirmation page is shown else shows the error page.
	// ---------------------------------------------------------------
	
	// Class loadedr
	function classLoader($className) {
		require_once dirname(__FILE__) ."/Classes/". $className . '.php';
	}
	
	spl_autoload_register('classLoader');
	
	// retrieve the user data
	$user = array();
	  
	$user[] = $_POST['user_name'];
	$user[] = $_POST['full_name'];
	$user[] = $_POST['address'];
	$user[] = $_POST['town'];
	$user[] = $_POST['zip'];
	$user[] = $_POST['country'];
	$user[] = $_POST['vat'];
	$user[] = $_POST['email'];
	$user[] = $_POST['phone'];
	$user[] = $_POST['extras'];
	
	// Retrieve the license data
	$license = array();
	
	$license[] = $_POST['license_type'];
	$license[] = $_POST['products'];
	$license[] = $_POST['quantity'];
	$license[] = $_POST['expire'];
	
	$VENDOR_KEY = $_POST['vendor_key'];
	
	echo "Vendor Key = $VENDOR_KEY <br>";
  
	// Send the fields to the creator
	$Kcreator = new CreateLicenses();
	$result = $Kcreator->createLicenses($user, $license, $VENDOR_KEY);
	
	// If result is 1 the page is redirected
	if($result == "") {
	  http_redirect("confirm.php");
	} // License created
	else {
	  http_redirect("error.php");
	}
	
	
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