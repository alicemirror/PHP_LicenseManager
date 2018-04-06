<?php

/*	Create a mail message and send it to the destinations with some predefined parameters
	e.g. blind cc, predefined text etc.
	LicenseMailer class is used to send a variable license list to the destination user(s)

	NOTE: The mail parameters are created to use the PHP / Linux sendmail (with exim4 under
	Debian systems).
*/
Class LicenseMailer {

	private $DEBUG = 0;

	// Predefined mail compnents
	private $MAIL_BCC = "ibiza.techconsulting@gmail.com";
	private $NAME_BCC = "Webmaster";
	private $MAIL_FROM = "license.manager@techinside.es";
	private $NAME_FROM = "License Manager";
	private $MAIL_REPLY = "ibiza.techconsulting@gmail.com";
	private $SUBJECT = "Message from License Generator. ";


	// Constructor.
	public function __construct(){

	}
	
	/*
		Mail sending function. Send the string (i.e. a list or one or more user license(s)
		to the destination address. The method returns 
	*/
	public function mailSend($toAddress, $subjectDetails, $licenseMessage) {
		// Create and array that will contain all the mail headers.
		$headers   = array();
		// Create the complete mail subject.
		$emailSubject = $this->SUBJECT . " " . $subjectDetails;
		
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-type: text/html; charset=iso-8859-1";
		$headers[] = "From: " . $this->NAME_FROM . " <" . $this->MAIL_FROM . ">";
		$headers[] = "Bcc: " . $this->NAME_BCC . " <" . $this->MAIL_BCC . ">";
		$headers[] = "Reply-To: License Manager <" . $this->MAIL_REPLY . ">";
		$headers[] = "Subject: {" . $this->SUBJECT . "}";
		$headers[] = "X-Mailer: PHP/".phpversion();
		
		// Set sendmail in queue mode avoiding long delay in the php for completion
		// $additionalParameters = '-ODeliveryMode=d';
		
		$mailResult = mail($toAddress, $emailSubject, $licenseMessage, implode("\r\n", $headers), $additionalParameters);
		
		if($this->DEBUG == 1) echo '[LicenseMailer]=>mailSend() result = ' . $mailResult;
		
		return $mailResult;
	}
	
}

?>