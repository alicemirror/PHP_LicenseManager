<?php

	// -----------------------------------------------------------------------
	//	Init and constants
	// -----------------------------------------------------------------------
	
	$DEBUG = 0;
	
	// Register the autoloader
	spl_autoload_register('classLoader');
	
	// Parameter names
	$LICENSE_NUM = 'num';	// Number of licenses
	
	// -----------------------------------------------------------------------
	//	Functions
	// -----------------------------------------------------------------------
	
	// Class loadedr
	function classLoader($className) {
		require_once dirname(__FILE__) ."/Classes/". $className . '.php';
	}
	
	// Error manager method
	function customError($errno, $errstr){
		header("HTTP/1.0 500 Internal Server Error");
		print "Error: [$errno] $errstr";
		exit;
	}
	
	// Bad request header
	function badRequest($string){
		header("HTTP/1.0 400 Bad Request");
		print "Error: [$string]";
		exit;
	}
	
	// Correct response header    
	function positiveResponse($string){
		header("HTTP/1.0 202 Accepted");
		print "OK: [$string]";
		exit;
	}
	
	// -----------------------------------------------------------------------
	// Page execution
	// -----------------------------------------------------------------------
	
	if( false == isset($_GET[$LICENSE_NUM])){ 
		badRequest("Specify a number of licenses to create (min = 1)");
	}
	else {
		// -----------------------------------------
		// Go ahead processing the requested action
		// -----------------------------------------
		$numLic = $_GET[$LICENSE_NUM];
		
		echo 'Requested: ' . $numLic . ' license(s).' . '<br>';
		
		// Instantiates the license ckecking class
		$creator = new LicenseCodeCreator();

		// Default mail message texts
		$msgLicenseMono = "The license generator has created for you the following license:<br><br>";
		$msgLicenseMulti = "The license generator has created for you the following licenses list:<br><br>";
		$msgLicenseGreetings = "Best Regards";
		
		// -----------------------------------------
		// Create the licenses
		// -----------------------------------------
		
		// Check for the number of licenses
		if( $numLic == 1 ) {

			// Creates the license
			$licenseCode = $creator->createLicense();

			// Create the license creation message
			$licenseMessage = $msgLicenseMono 
							. "<center><b>" 
							. $licenseCode 
							. "</b></center>";
			echo 'Created the requested license<br>';

		} // Single license
		else {
			echo 'Created an array of ' . $numLic . ' licenses.<br>';

			$lic = array();
			
			$lic = $creator->createLicenseArray($numLic);
			$licenseCode = $msgLicenseMulti;
			
			// Creates the message string array
			for ( $k = 0; $k < $numLic; $k++) {
				$licenseCode .= '[' .  ($k + 1) . '] : <b>' .  $lic[$k] . '</b><br>';
			}
			
			// Create the license creaton message
			$licenseMessage = $licenseCode 
							. "<br>"
							. $msgLicenseGreetings;
		} // Multiple licenses

		if($DEBUG)
			echo 'mail message:<br>' . $licenseMessage . '<br>';
		
		// Compose the mail message
		$mailDest = "enrico.miglino@gmail.com";
		$mailName = "Enrico Miglino";
		$emailSubject = "Message from License Generator";
		$toAddress = $mailName . " <" . $mailDest . ">";

		
		$headers   = array();
		
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-type: text/html; charset=iso-8859-1";
		$headers[] = "From: License Manager <ibiza.techconsulting@gmail.com>";
		$headers[] = "Bcc: Webmaster <ibiza.techconsulting@gmail.com>,<ray@goaheadspace.com>";
		$headers[] = "Reply-To: License Manager <ibiza.techconsulting@gmail.com>";
		$headers[] = "Subject: {$emailSubject}";
		$headers[] = "X-Mailer: PHP/".phpversion();
		
		// Set sendmail in queue mode avoiding long delay in the php for completion
		$additionalParameters = '-ODeliveryMode=d';
		
		$success = mail($toAddress, $emailSubject, $licenseMessage, implode("\r\n", $headers), $additionalParameters);
		// $success = mail($mailDest, $emailSubject, $licenseMessage);
		
		if($DEBUG)
			echo '<br>Licenses sent by mail to ' . $mailDest 
				. ' success result: ' . $success;
		
	} // request is correct


?>

