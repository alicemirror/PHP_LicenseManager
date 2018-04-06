<?php

// Contains the methods to create licenses registration codes
Class LicenseCodeCreator {
	
	// Global preferences instance. Includes  the database 
	// instance opened by the Globals class constructor
	private $prefs;
	
	private $DEBUG = 0;
	
	// Creates the alphabet
	private $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	// private $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
	
	// Number of characters of every token
	private $tokenLen = 4;
	
	// Class loader method to manage the autoload
	private function loader($className) { 
		require_once dirname(__FILE__) ."/". $className . '.php'; 
	}
	
	// When the class is instantiated it is possible to pass a already
	// loaded prefs instance of the Globals class. If it is null (the default)
	// the Globals class is instantiated locally.
	public function __construct(Globals $prefs = null){
		// Register the autoload function
		spl_autoload_register(array($this, 'loader'));
		
		// Check for the already existing Globals class
		if($prefs == null) {
			$prefs = new Globals;
		}
		
        // Assigns the local instance of the preferences and database
		$this->prefs = $prefs;
	} // constructor
	
	/*
	Create a random character included in the range using the crypto methods.
	crypto_rand_secure($min, $max) works as a drop in replacement for rand() 
	or mt_rand. 
	It uses openssl_random_pseudo_bytes to help create a random number 
	between $min and $max.	
	*/
	private function crypto_rand_secure($min, $max) {
        $range = $max - $min;
        if ($range < 0) return $min; // not so random...
        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
    }

	/*
	Generate a random tokeN based on openssl_random_pseudo_bytes of the desired
	length. This method uses a complete alphanumeric alphabet (both upper and
	lowercase characters).
	*/    
    public function createToken($length){
    	// Initializes the token
    	$token = "";
    	
    	// Create the random characters queueing the token string
    	for( $i = 0; $i < $length; $i++) {
    		$token .= $this->codeAlphabet[$this->crypto_rand_secure(0, strlen($this->codeAlphabet))];
    	}
    	
    	return $token;
    }

    /*
    Create a license code in the format ABCD-EFGH-IJKL-MNOP made of random
    characters.
    */
    public function createLicense() {
    	$license = "";
    	$numTokens = 4;
    	
    	// Create the tokens group
    	for ($i = 0; $i < ($numTokens - 1); $i++) {
    		$license .= $this->createToken($this->tokenLen) . "-";
    	}
    	// Add the fourth token
    	$license .= $this->createToken($this->tokenLen);
    	
    	return $license;
    }
    
    /*
    Create an array of token licenses
    */
    public function createLicenseArray($numLicenses) {
    	$licenses = array();
    	
    	for ($i = 0; $i < $numLicenses; $i++)
    		$licenses[$i] = $this->createLicense();
    	
    	return $licenses;
    }
}

?>