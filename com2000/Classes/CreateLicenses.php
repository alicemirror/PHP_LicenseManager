<?php

// Include the database tables definitions
require_once dirname(__DIR__) . "/include/database/licenses.php";
require_once dirname(__DIR__) . "/include/database/products.php";
require_once dirname(__DIR__) . "/include/database/users.php";
require_once dirname(__DIR__) . "/include/database/license_types.php";
require_once dirname(__DIR__) . "/include/database/resellers.php";

// Contains the methods to create licenses on the database
// CreateLicenses should be used from the LicenseManager php form as the
// main class (or any other page able to generate all the fields needed
// for license creation.
Class CreateLicenses {
	
	// Globals class instance. Includes  the database 
	// instance opened by the Globals class constructor
	private $prefs;
	// Local replica of the database instance
	private $db;
	
	// LicensesMailer class instance
	private $mailer = null;
	// LicenseCodeCreator class instance
	private $licenseEncode = null;
	
	private $DEBUG = 0;
	private $DEBUG_SQL_MESSAGE = "";
	
	// Global variables assigned by the functions
	public $VENDOR_NAME = "";
	public $VENDOR_FULL_NAME = "";
	public $VENDOR_ID = 0;
	public $VENDOR_EMAIL = "";
	
	// Field IDs in the user array of createLicense() method.
	private $FLD_USER_NAME = 0;
	private $FLD_USER_FULL_NAME = 1;
	private $FLD_USER_ADDRESS = 2;
	private $FLD_USER_TOWN = 3;
	private $FLD_USER_ZIP = 4;
	private $FLD_USER_COUNTRY = 5;
	private $FLD_USER_VAT = 6;
	private $FLD_USER_EMAIL = 7;
	private $FLD_USER_PHONE = 8;
	private $FLD_USER_EXTRAS = 9;
	
	// Field IDs in the license array of createLicense() method.
	private $FLD_LICENSE_TYPE = 0;
	private $FLD_LICENSE_PRODUCT = 1;
	private $FLD_LICENSE_QUANTITY = 2;
	private $FLD_LICENSE_EXPIRE = 3;
	
	// License types (must reflect the ids in the db table license_types
	// WARNING! If types changes shouldb e updated also the types table and the
	// update php page.
	private $LICENSE_TYPE_UNLIMITED = 1;
	private $LICENSE_TYPE_TEMPORARY = 2;
	private $LICENSE_TYPE_INCREMENTAL = 3;
	private $LICENSE_TYPE_FOLLOWUP = 4;
	
	// Error message string that should be sent to the module
	private $FIELDS_ERROR_MESSAGE = "";
	
	// License mailer predefined messages
	private $msgLicenseSubject = "Ref.: ";
	private $msgLicenseHello = "Hello ";
	private $msgLicenseInit = ",<br> the Tech Consulting license generator has created for the client <i>";
	private $msgLicenseMono = "</i> the following license:<br><br>";
	private $msgLicenseProduct1 = "<br>for the product <b>";
	private $msgLicenseProduct2 = "</b>. ";
	private $msgLicenseMulti = "</i> the single licenses listed below:<br><br>";
	private $msgLicenseExpire1 = "<br>The license expires in <b>";
	private $msgLicenseExpire2 = "</b> days from the activation date and is valid for <b>";
	private $msgLicenseExpire3 = "</b> installations.<br>";
	private $msgLicenseNumber1 = "<br>This license is valid for <b>";
	private $msgLicenseNumber2 = "</b> installations.<br>";
	private $msgClientSummary = "<br><br>Registered client Summary:<br>";
	private $msgLicenseGreetings = "<br>Best Regards";
	
	// Class loader method to manage the autoload
	private function loader($className) { 
		require_once dirname(__FILE__) ."/". $className . '.php'; 
	}
	
	// When the class is instantiated it is possible to pass a already
	// loaded prefs instance of the Globals class. If it is null (the default)
	// the Globals class is instantiated locally.
	public function __construct(Globals $prefs=null){
		// Register the autoload function
		spl_autoload_register(array($this, 'loader'));
		
		// Check for the already existing Globals class
		if($prefs == null) {
			$prefs = new Globals;
		}
		
        // Assigns the local instance of the preferences and database
		$this->prefs = $prefs;
		$this->db = $this->prefs->db;
	}
	
	/*	===========================================================================
								LICENSE(S) CREATION PROCESS
		===========================================================================
		Create the requested licenses if the paraeters are compliant with
		the mandatory data.
		
		Order of the content of the parameters array
			
			$userName, $userFullName, $userAddress,
			$userTown, $userZip, $userCountry, $userVat, 
			$userEmail, $userPhone, $userExtas
			
			$licenseType, $licenseProducts, $licenseQuantity, $licenseExpire
			
		The function return 1 if the license(s) are created correctly or 0 if 
		there is an error
	*/
	public function createLicenses( $userSource, $licenseSource, $vendorKey) {
		// Retrieves the vendor data
		$this->checkVendor($vendorKey);
		
		// If the vendor is not authorized, the licenses are not registered.
		if($this->VENDOR_ID == null) {
			return "The vendor code is not authorized to create new licenses";
		}
		
		// Field controls.
		$this->FIELDS_ERROR_MESSAGE = "";
		
		// Create user and license local arrays trimming the input values from the POST
		// Trimemd fields are in the same order as the original array
		// so the field indexes remain valid.
		$user = array();
		$license = array();
		// Trim user data
		foreach($userSource as $field) {
			$user[] = trim($field);
		}
		// Trim license data
		foreach($licenseSource as $field) {
			$license[] = trim($field);
		}

		// Sanitize fields FILTER_SANITIZE_STRING
		$user[$this->FLD_USER_NAME] = ucwords ($this->secureString(filter_var($user[$this->FLD_USER_NAME],
							FILTER_SANITIZE_STRING, 
							FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH) ));
							
		$user[$this->FLD_USER_ADDRESS] = ucwords ($this->secureString(filter_var($user[$this->FLD_USER_ADDRESS],
							FILTER_SANITIZE_STRING, 
							FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH) ));
							
		$user[$this->FLD_USER_COUNTRY] = strtoupper ($this->secureString(filter_var($user[$this->FLD_USER_COUNTRY],
							FILTER_SANITIZE_STRING, 
							FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH) ));
							
		$user[$this->FLD_USER_FULL_NAME] = ucwords ($this->secureString(filter_var($user[$this->FLD_USER_FULL_NAME],
							FILTER_SANITIZE_STRING, 
							FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH) ));
							
		$user[$this->FLD_USER_PHONE] = strtoupper ($this->secureString(filter_var($user[$this->FLD_USER_PHONE],
							FILTER_SANITIZE_STRING, 
							FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH) ));
							
		$user[$this->FLD_USER_TOWN] = ucwords ($this->secureString(filter_var($user[$this->FLD_USER_TOWN],
							FILTER_SANITIZE_STRING, 
							FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH) ));
							
		$user[$this->FLD_USER_VAT] = strtoupper ($this->secureString(filter_var($user[$this->FLD_USER_VAT],
							FILTER_SANITIZE_STRING, 
							FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH) ));
							
		$user[$this->FLD_USER_ZIP] = strtoupper($this->secureString(filter_var($user[$this->FLD_USER_ZIP],
							FILTER_SANITIZE_STRING, 
							FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH) ));

		// Check for the mandatory fields not empty and content validation
		if($this->checkUserData($user) == 0) {
			return $this->FIELDS_ERROR_MESSAGE;
		}
		if($this->checkLicenseData($license) == 0) {
			return $this->FIELDS_ERROR_MESSAGE;
		}
		
		// Instantiate the license creator class.
		$codeCreator = new LicenseCodeCreator();
		
		// If the license is follow-up, for every license is needed a different key
		// Instead it is created a single license record.
		// For every new license key, a user record is created.
		if($license[$this->FLD_LICENSE_TYPE] == $this->LICENSE_TYPE_FOLLOWUP) {
			$numKeys = $license[$this->FLD_LICENSE_QUANTITY];
		}
		else {
			$numKeys = 1;
		}
		
		// Create the licenses key array
		$lic = array();
		$lic = $codeCreator->createLicenseArray($numKeys);
		
		// Create the user records for the required licenses.
		$this->createUserRecords($user, $lic);
		// Check the created users and retrieve the users id array
		$usersCreatedId = $this->checkCreatedUsers($lic);

		// Creates the license
		$this->createLicenseRecords($usersCreatedId, $license);
	
		// Prepare the mail message 
		if( $license[$this->FLD_LICENSE_TYPE] == $this->LICENSE_TYPE_TEMPORARY ) {
			// Temporary license 
			$licenseMessage = $this->msgLicenseHello
				. $this->VENDOR_NAME
				. $this->msgLicenseInit 
				. $user[$this->FLD_USER_NAME]
				. $this->msgLicenseMono 
				. "<center><b>" 
				. $lic[0] 
				. "</b></center>"
				. $this->msgLicenseProduct1
				. $this->getProductDescription($license[$this->FLD_LICENSE_PRODUCT])
				. $this->msgLicenseProduct2
				. $this->msgLicenseExpire1
				. $license[$this->FLD_LICENSE_EXPIRE]
				. $this->msgLicenseExpire2
				. $license[$this->FLD_LICENSE_QUANTITY]
				. $this->msgLicenseExpire3;
	}
		elseif( $license[$this->FLD_LICENSE_TYPE] == $this->LICENSE_TYPE_FOLLOWUP ) {
			// Follwo-up license
			$index = 1;
			foreach($lic as $licKey) {
				$licenseKeyList .= '[' .  $index++ . '] : <b>' .  $licKey . '</b><br>';
			}
			$licenseMessage = $this->msgLicenseHello
				. $this->VENDOR_NAME
				. $this->msgLicenseInit
				. $user[$this->FLD_USER_NAME]
				. $this->msgLicenseMulti
				. $licenseKeyList
				. $this->msgLicenseProduct1
				. $this->getProductDescription($license[$this->FLD_LICENSE_PRODUCT])
				. $this->msgLicenseProduct2;
		}
		else {
			// Standard license
			$licenseMessage = $this->msgLicenseHello
				. $this->VENDOR_NAME
				. $this->msgLicenseInit
				. $user[$this->FLD_USER_NAME]
				. $this->msgLicenseMono 
				. "<center><b>" 
				. $lic[0] 
				. "</b></center>"
				. $this->msgLicenseProduct1
				. $this->getProductDescription($license[$this->FLD_LICENSE_PRODUCT])
				. $this->msgLicenseProduct2
				. $this->msgLicenseNumber1
				. $license[$this->FLD_LICENSE_QUANTITY]
				. $this->msgLicenseNumber2;
			}
		
		// Close the mail message
		$licenseMessage .= $this->msgClientSummary
				. $user[$this->FLD_USER_NAME] . " - "
				. $user[$this->FLD_USER_FULL_NAME] . "<br>"
				. $user[$this->FLD_USER_ADDRESS] . " "
				. $user[$this->FLD_USER_TOWN] . " "
				. " (" . $user[$this->FLD_USER_ZIP] . ") "
				. $user[$this->FLD_USER_COUNTRY] . "<br>";
		
		if($user[$this->FLD_USER_VAT] != "") {
			$licenseMessage .= "VAT "
				. $user[$this->FLD_USER_VAT] . "<br>";
		}
					
		$licenseMessage .= "E-mail "
			. $user[$this->FLD_USER_EMAIL];

		if($user[$this->FLD_USER_PHONE] != "") {
			$licenseMessage .= " Phone "
				. $user[$this->FLD_USER_PHONE] . "<br>";
		}
		else {
			$licenseMessage .= "<br>";
		}

		$licenseMessage .=  $this->msgLicenseGreetings;
		$subjectDetails = $this->msgLicenseSubject . $user[$this->FLD_USER_NAME];
		
		// Create the mailer instance
		$mailer = new LicenseMailer();
		// Send the mail
		$mailer->mailSend($this->VENDOR_EMAIL, $subjectDetails, $licenseMessage);

		if($this->DEBUG == 1)
			return "Mail message:<br>" . $licenseMessage;		
			
		return "";

	}

	// ==================== LICENSE CREATION PROCESS FINISHED ====================
	
	/*
		Secure a string that should be written in a database field replacing some risky
		characters.
		WARNING: DO NOT USE THIS FUNCTION FOR SECURE EMAIL ADDRESSES !!!
	*/
	private function secureString($str) {
		return strtr($str, array(
			"\0" => "",
			"'"  => "_",
			"\"" => "_",
			"\\" => "-",
			"<"  => ".",
			">"  => ".",
			"#"  => ".",
			"|"  => ".",
			"@"  => " at ",
			"!"  => "."
		));
	}	

	/*
		Create the licenses record in the database. The creation of a single record or multiple
		depends on the license type.
	*/
	private function createLicenseRecords($usersCreatedId, $license) {
		// Get the actual timestamp in MySql date format
		$today = Date("Y-m-d H:i:s", time());
		// If the license is temporary, should be calculated the expire
		// time else it is convetionally set as the cretion date.
		if( $license[$this->FLD_LICENSE_TYPE] == $this->LICENSE_TYPE_TEMPORARY ) {
			$expireDate = Date("Y-m-d H:i:s", time() + (86400 * $license[$this->FLD_LICENSE_EXPIRE]) );
		}
		else {
			$expireDate = $today;
		}
		
		// If the license type is follow-up, should be created as many license records
		// as the number of already created users. In this case the number of licenses
		// is set to 1 for every record else the single license record is initialized to
		// the requested licenses ready to be registered.
		if( $license[$this->FLD_LICENSE_TYPE] == $this->LICENSE_TYPE_FOLLOWUP ) {
			$numberLicenses = 1;	// fixed value for every record			
		}
		else {
			$numberLicenses = $license[$this->FLD_LICENSE_QUANTITY]; // all licenses in on record
		}
		
		// Prepare the SQL base string (fixed part)
		$sqlBase = "INSERT INTO " . 
				DB_TABLE_LICENSES . 
				" (" . 
					LICENSES_PRODUCT_ID . "," .
					LICENSES_RESELLER . "," . 
					LICENSES_DATE . "," .
					LICENSES_LICENSES . "," .
					LICENSES_TYPE . "," .
					LICENSES_EXPIRE . "," .
					LICENSES_USER_ID . 
				") VALUES (" . 
					"'" . $license[$this->FLD_LICENSE_PRODUCT] . "'," .
					"'" . $this->VENDOR_ID . "'," .
					"'" . $today . "'," .
					"'" . $numberLicenses . "'," .
					"'" . $license[$this->FLD_LICENSE_TYPE] . "'," .
					"'" . $expireDate . "',";

		// SQL close query string (added at the end of every query)				
		$sqlClose = ")";
		
		// Now create as many record insertions as the number of ures id present in the loop
		// If there is only one, this means that the license records are alredy set for single
		// license record else it is a follow-up license with as many records as the number of
		// licenses.
		foreach($usersCreatedId as $id) {
			// Create the complete sql string
			$sql = $sqlBase . "'" . $id . "'" . $sqlClose;
		
			$pdoStatement = $this->db->prepare($sql);
			$pdoStatement->execute();
		} // Licenses record creation loop

	} // createLicenseRecords
	
	/*
		Create the requested number of users depending on the number of keys.
		Every user has a different license key code.
	*/
	private function createUserRecords($user, $lic) {
		// Loop on all the license key
		foreach($lic as $licenseCode) {
			// Create the query string for a new user creation
			$sql = "INSERT INTO " . 
				DB_TABLE_USERS . 
				" (" . 
					USERS_NAME . "," .
					USERS_LICENSE_CODE . "," . 
					USERS_FULL_NAME . "," .
					USERS_ADDRESS . "," .
					USERS_TOWN . "," .
					USERS_ZIP . "," .
					USERS_COUNTRY . "," .
					USERS_EMAIL . "," .
					USERS_PHONE . "," .
					USERS_VAT .  "," .
					USERS_EXTRAS .
				") VALUES (" . 
					"'" . $user[$this->FLD_USER_NAME] . "'," .
					"'" . $licenseCode . "'," .
					"'" . $user[$this->FLD_USER_FULL_NAME] . "'," .
					"'" . $user[$this->FLD_USER_ADDRESS] . "'," .
					"'" . $user[$this->FLD_USER_TOWN] . "'," .
					"'" . $user[$this->FLD_USER_ZIP] . "'," .
					"'" . $user[$this->FLD_USER_COUNTRY] . "'," .
					"'" . $user[$this->FLD_USER_EMAIL] . "'," .
					"'" . $user[$this->FLD_USER_PHONE] . "'," .
					"'" . $user[$this->FLD_USER_VAT] . "'," .
					"'" . $user[$this->FLD_USER_EXTRAS] . "'" .
				")";

				$pdoStatement = $this->db->prepare($sql);
				$pdoStatement->execute();
		} // Loop for users creation

	} // Create Users
	
	/*
		Check the user data for validity. If there are wrong data or missed mandatory
		fields, the function update the global error message to be sent to the form.
		The function return 1 if controls are ok else return 0
	*/
	private function checkUserData($user) {
		// Check for mandatory data
		if(	($user[$this->FLD_USER_NAME] == "") ) {
			$this->FIELDS_ERROR_MESSAGE = 
			"The Client field <i>Client Name</i> is missing. All the fields with '*' are mandatory.";
			return 0;
		}
		elseif( ($user[$this->FLD_USER_FULL_NAME] == "") ) {
			$this->FIELDS_ERROR_MESSAGE = 
			"The Client field <i>Full Name / Company</i> is missing. All the fields with '*' are mandatory.";
			return 0;
		}
		elseif( ($user[$this->FLD_USER_TOWN] == "") ) {
			$this->FIELDS_ERROR_MESSAGE = 
			"The Client field <i>Town</i> is missing. All the fields with '*' are mandatory.";
			return 0;
		}
		elseif( ($user[$this->FLD_USER_ZIP] == "") ) {
			$this->FIELDS_ERROR_MESSAGE = 
			"The Client field <i>ZIP</i> is missing. All the fields with '*' are mandatory.";
			return 0;
		}
		elseif( ($user[$this->FLD_USER_COUNTRY] == "") ) {
			$this->FIELDS_ERROR_MESSAGE = 
			"The Client field <i>Country</i> is missing. All the fields with '*' are mandatory.";
			return 0;
		}
		elseif( ($user[$this->FLD_USER_EMAIL] == "") ) {
			$this->FIELDS_ERROR_MESSAGE = 
			"The Client field <i>E-mail</i> is missing. All the fields with '*' are mandatory.";
			return 0;
		}
		
		// At this point at least the mandatory user fields are not empty, 
		// so we can check the content for validation.
		if (filter_var($user[$this->FLD_USER_EMAIL], FILTER_VALIDATE_EMAIL) == FALSE) {
			$this->FIELDS_ERROR_MESSAGE = 
			"The Client <i>E-mail</i> is wrong or malformed.";
			return 0;
		}

	return 1;
		
	}

	/*
		Check the license data for validity.
	*/
	private function checkLicenseData($license) {
	
		// License quantity can't be 0 or null
		if($license[$this->FLD_LICENSE_QUANTITY] < 1) {
			$this->FIELDS_ERROR_MESSAGE = "Need to specify a positive value in the number of licenses (at least 1).";
			return 0;
		} // Negative number of licenses
		else {
			// Validate for integer value
			if(filter_var($license[$this->FLD_LICENSE_QUANTITY], FILTER_VALIDATE_INT) == FALSE) {
				$this->FIELDS_ERROR_MESSAGE = "The number of licenses <b>must</b> be an integer value.";
				return 0;
			} // Non-integer number of licenses
		} // Integer validation
	
		if($license[$this->FLD_LICENSE_TYPE] == $this->LICENSE_TYPE_TEMPORARY) {
			// Temporary license, should have at least 1 expire day
			if($license[$this->FLD_LICENSE_EXPIRE] < 1) {
				$this->FIELDS_ERROR_MESSAGE = 
				"The required license type needs an expiration positive period (number of days). Min accepted value is 1.";
				return 0;
			} // Negative expiration time
			else {
				// Validate for integer value
				if (filter_var($license[$this->FLD_LICENSE_EXPIRE], FILTER_VALIDATE_INT) == FALSE) {
					$this->FIELDS_ERROR_MESSAGE = "The expiration period <b>must</b> be an integer value.";
					return 0;
				} // Non-integer number of licenses
			} // Integer validation
		} // Type = temporary
		
		return 1;		
	}
	
	/*
		Get the product description based on the product id from the database table.
	*/
	private function getProductDescription($id) {
		// Create the search query
		$q = 'SELECT ' .
				PRODUCTS_ID . ',' .
				PRODUCTS_DESCRIPTION . 
			' FROM ' . 
				DB_TABLE_PRODUCTS . 
			' WHERE ' . 
				DB_TABLE_PRODUCTS . '.' . PRODUCTS_ID . '=\'' . $id . '\'';
		
		// Prepare the query and executes.
		$pdoStatement = $this->db->prepare($q);
		$pdoStatement->execute();
		
		$productDescription = "";

		// If the query returned the record
		if( $row = $pdoStatement->fetch(PDO::FETCH_ASSOC) ) {
			$productDescription =  $row[PRODUCTS_DESCRIPTION];
		} // Record found
		// Close the cursor and move to next query
		$pdoStatement->closeCursor();
		
		return $productDescription;
	
	}

	/*
		Check the list of users (last created records) searching by user license key
		checkUsers() return the array with the found IDs. This will be used by the
		other methods to create the other license records.
		The function assumes that there is only one user associated to every different
		license code.
	*/
	
	private function checkCreatedUsers($lic) {
		// Initialize the results array
		$licID = array();
		// Loop on all the license key
		foreach($lic as $licenseCode) {
			// Create the search query
			$q = 'SELECT ' .
					USERS_ID . ',' .
					USERS_LICENSE_CODE . 
				' FROM ' . 
					DB_TABLE_USERS . 
				' WHERE ' . 
					DB_TABLE_USERS . '.' . USERS_LICENSE_CODE . '=\'' . $licenseCode . '\'';
			
		// Prepare the query and executes.
		$pdoStatement = $this->db->prepare($q);
		$pdoStatement->execute();

		// If the query returned the record
		if( $row = $pdoStatement->fetch(PDO::FETCH_ASSOC) ) {
			$licID[] =  $row[USERS_ID];
		} // Record found
		// Close the cursor and move to next query
		$pdoStatement->closeCursor();
		} // License code loop

		// return the result array
		return $licID;
	}
	
	/*
		Check the vendor / reseller data ancd return an array with the fields and their
		values. If the record is wrong or not found, return null.
		This method should be used instead of checkVendor() to retrieve the entire vendor
		record content.
	*/
	public function checkVendorRecord($vendorKey) {
		// The vendors field names
		$vendorsFieldList = array(
			RESELLERS_RESELLER_ID,
			RESELLERS_NAME,
			RESELLERS_FULL_NAME,
			RESELLERS_EMAIL,
			RESELLERS_CODE );

		// Initializes the query to extract the selected license-code
		// associated records.
		$q = 'SELECT ' .
			RESELLERS_ID . ' AS ' . RESELLERS_RESELLER_ID . ',' .
			RESELLERS_NAME . ',' .
			RESELLERS_FULL_NAME . ',' .
			RESELLERS_EMAIL . ',' .
			RESELLERS_CODE . 
			
			' FROM ' . DB_TABLE_RESELLERS . 
				
			' WHERE ' . 
				DB_TABLE_RESELLERS . '.' . RESELLERS_CODE . '=\'' . $vendorKey . '\'';
			
		/*if($this->DEBUG == 1)
			echo 'CreateLicenses->checkVendor() query = ' . $q . '<br>';*/
		
		// Prepare the query and executes.
		$pdoStatement = $this->db->prepare($q);
		$pdoStatement->execute();

		$r = null;

		if( $row = $pdoStatement->fetch(PDO::FETCH_ASSOC) ) {
			// Loop on the fields array
			foreach ($vendorsFieldList as $key) {
                $r["$key"] = $row["$key"];
			} // Loop on fields
		} // Record found
		
		$pdoStatement->closeCursor();
		
		// return $r;
		return $r;
	}

	/*
		Check the vendor / reseller data and assign the global variables.
		The function return the vendor name to the caller.
		If the vendor key is wrong, the function returns null and nothing is set.
		NOTE: This method assumes that - as it should be - every vendor has a
		unique key.
	*/
	public function checkVendor($vendorKey) {
		// Initializes the query to extract the selected license-code
		// associated records.
		$q = 'SELECT ' .
			RESELLERS_ID . ' AS ' . RESELLERS_RESELLER_ID . ',' .
			RESELLERS_NAME . ',' .
			RESELLERS_FULL_NAME . ',' .
			RESELLERS_EMAIL . ',' .
			RESELLERS_CODE . 
			
			' FROM ' . DB_TABLE_RESELLERS . 
				
			' WHERE ' . 
				DB_TABLE_RESELLERS . '.' . RESELLERS_CODE . '=\'' . $vendorKey . '\'';
			
		/*if($this->DEBUG == 1)
			echo 'CreateLicenses->checkVendor() query = ' . $q . '<br>';*/
		
		// Prepare the query and executes.
		$pdoStatement = $this->db->prepare($q);
		$pdoStatement->execute();

		// Initializes the result
		$r = null;

		// If the query returned the record
		if( $row = $pdoStatement->fetch(PDO::FETCH_ASSOC) ) {
			// Set the return value (should be used to identify the user
			$r = "<b>" . $row[RESELLERS_NAME] . "</b> from " .  $row[RESELLERS_FULL_NAME];
			// Assigns the global variables.
			$this->VENDOR_NAME =  $row[RESELLERS_NAME];
			$this->VENDOR_FULL_NAME =  $row[RESELLERS_FULL_NAME];
			$this->VENDOR_ID =  $row[RESELLERS_RESELLER_ID];
			$this->VENDOR_EMAIL = $row[RESELLERS_EMAIL];

		} // Record found
		
		$pdoStatement->closeCursor();
		
		/*if($this->DEBUG == 1)
			echo 'CreateLicenses->checkVendor() Results: ' . $this->VENDOR_NAME
				. '/' . $this->VENDOR_FULL_NAME . ' (ID=' . $this->VENDOR_ID . ')<br>';*/
		
		return $r;
		
	}

}
?>