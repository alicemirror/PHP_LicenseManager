<?php

// Include the database tables definitions
require_once dirname(__DIR__) . "/include/database/licenses.php";
require_once dirname(__DIR__) . "/include/database/products.php";
require_once dirname(__DIR__) . "/include/database/users.php";
require_once dirname(__DIR__) . "/include/database/license_types.php";
require_once dirname(__DIR__) . "/include/database/devices.php";

// Contains the methods to check licenses on the database
// generating return information.
Class CheckLicenses {
	
	// Global preferences instance. Includes  the database 
	// instance opened by the Globals class constructor
	private $prefs;
	// Local replica of the database instance
	private $db;
	
	private $DEBUG = 0;

	// The users field names
	private $userFieldList = array(
		USERS_USER_ID,
		USERS_FULL_NAME,
		USERS_ADDRESS,
		USERS_TOWN,
		USERS_ZIP,
		USERS_COUNTRY,
		USERS_EMAIL,
		USERS_PHONE,
		USERS_VAT,
		USERS_EXTRAS );
	
	// The product field names
	private $productLicensesFieldList = array (
		PRODUCTS_PRODUCT,
		LICENSES_DATE,
		LICENSES_UPDATE,
		LICENSES_LICENSES,
		LICENSES_ACTIVE_LICENSES,
		LICENSES_EXPIRE,
		TYPES_TYPE );
	
	// The license field names
	private $licenseFieldList = array(
		LICENSES_LICENSE_ID,
		USERS_USER,
		USERS_USER_ID,
		LICENSES_DATE,
		LICENSES_UPDATE,
		LICENSES_LICENSES,
		LICENSES_SUSPEND, 
		LICENSES_IS_ACTIVE,
		LICENSES_ACTIVE_LICENSES,
		LICENSES_EXPIRE,
		PRODUCTS_PRODUCT,
		PRODUCTS_PRODUCT_ID, 
		TYPES_TYPE_CODE,
		TYPES_TYPE );

	// The user license field names	
	private $userLicensesFieldList = array (
		USERS_USER,
		LICENSES_DATE,
		LICENSES_UPDATE,
		LICENSES_LICENSES,
		LICENSES_ACTIVE_LICENSES,
		LICENSES_EXPIRE,
		PRODUCTS_PRODUCT, 
		TYPES_TYPE );

	// The device profile field names
	private $devicesLicensesFieldList = array (
		DEVICES_DEVICE_ID,
		DEVICES_LICENSE_ID,
		DEVICES_LICENSE_NUM,
		DEVICES_USER_ID,
		DEVICES_UPDATE,
		DEVICES_UUID,
		DEVICES_IMEI,
		DEVICES_WLANMAC,
		DEVICES_BTADDRESS,
		DEVICES_SDK,
		DEVICES_ANDROID,
		DEVICES_BOARD,
		DEVICES_BOOTLOADER,
		DEVICES_BRAND,
		DEVICES_DEVICE,
		DEVICES_FINGERPRINT,
		DEVICES_MANUFACTURER,
		DEVICES_MODEL,
		DEVICES_PRODUCT );
	
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
			$prefs= new Globals;
		}
		
        // Assigns the local instance of the preferences and database
		$this->prefs = $prefs;
		$this->db = $this->prefs->db;
	} // constructor
	
	/*
		Check for the device, if it is registered on the databse for a specific
		user and product key.
		User and product key limitation should be set because on the same device
		may be present more products registered with the same user key or different.
	*/
	public function checkDevice( $uuid, $imei, $mac, $bluetooth ) {
		// Initializes the query to extract the selected license-code
		// associated records.
		$q = 'SELECT ' . DEVICES_ID .  ' AS ' . DEVICES_DEVICE_ID .
			',' . DEVICES_LICENSE_ID . 
			',' . DEVICES_LICENSE_NUM . 
			',' . DEVICES_USER_ID . 
			',' . DEVICES_UPDATE . 
			',' . DEVICES_UUID . 
			',' . DEVICES_IMEI . 
			',' . DEVICES_WLANMAC . 
			',' . DEVICES_BTADDRESS . 
			',' . DEVICES_SDK . 
			',' . DEVICES_ANDROID . 
			',' . DEVICES_BOARD . 
			',' . DEVICES_BOOTLOADER . 
			',' . DEVICES_BRAND . 
			',' . DEVICES_DEVICE . 
			',' . DEVICES_FINGERPRINT . 
			',' . DEVICES_MANUFACTURER . 
			',' . DEVICES_MODEL . 
			',' . DEVICES_PRODUCT . 

			' FROM ' . DB_TABLE_DEVICES .
				
			' WHERE ' . DEVICES_UUID . '=\'' . $uuid . '\'' .
			' AND ' . DEVICES_IMEI . '=\'' . $imei . '\'' .
			' AND ' . DEVICES_WLANMAC . '=\'' . $mac . '\'' .
			' AND ' . DEVICES_BTADDRESS . '=\'' . $bluetooth . '\'';
			
		if($this->DEBUG == 1)
			echo 'CheckLicenses->checkDevice() query = ' . $q . '<br>';
		
		// Prepare the query and executes.
		$pdoStatement = $this->db->prepare($q);
		$pdoStatement->execute();

		$r = null;

		// If the query returned the record
		if( $row = $pdoStatement->fetch(PDO::FETCH_ASSOC) ) {
			// Loop on th fields array
			foreach ($this->devicesLicensesFieldList as $key) {
                $r["$key"] = $row["$key"];
			} // Loop on fields
		} // Record found
		
		$pdoStatement->closeCursor();
		
		if( $r == null )
			$r[DEVICES_DEVICE_ID] = null;
		
		return $r;
	}

	/*
		Check all the products licenses related to the specified user
	*/
	public function checkGlobal($user) {
		// Initializes the query to extract the selected license-code
		// associated records.
		$q = 'SELECT t1.' . USERS_NAME . ' AS ' . USERS_USER . 
			',t2.' . LICENSES_DATE .
			',t2.' . LICENSES_UPDATE . 
			',t2.' . LICENSES_LICENSES . 
			',t2.' . LICENSES_ACTIVE_LICENSES .
			',t2.' . LICENSES_EXPIRE .
			',t3.' . PRODUCTS_DESCRIPTION . ' AS ' . PRODUCTS_PRODUCT . 
			',t4.' . TYPES_TYPE .

			' FROM ' . DB_TABLE_USERS . ' t1' .
			
			' INNER JOIN ' . DB_TABLE_LICENSES . ' t2 ON t1.' . USERS_ID . 
				' = t2.' . LICENSES_USER_ID .

			' INNER JOIN ' . DB_TABLE_PRODUCTS . ' t3 ON t3.' . PRODUCTS_ID . 
				' = t2.' . LICENSES_PRODUCT_ID .
				
			' INNER JOIN ' . DB_TABLE_TYPES . ' t4 ON t4.' . TYPES_ID . 
				' = t2.' . LICENSES_TYPE .
				
			' WHERE t1.' . USERS_LICENSE_CODE . '=\'' . $user . '\'';

			
		IF($this->DEBUG == 1)
			echo 'CheckLicenses->checkGlobal() query = ' . $q . '<br>';
		
		// Prepare the query and executes.
		$pdoStatement = $this->db->prepare($q);
		$pdoStatement->execute();

		$r = null;

		// If the query returned the record
		if( $row = $pdoStatement->fetch(PDO::FETCH_ASSOC) ) {
			// Loop on th fields array
			foreach ($this->userLicensesFieldList as $key) {
                $r["$key"] = $row["$key"];
			} // Loop on fields
		} // Record found
		
		$pdoStatement->closeCursor();
		
		return $r;
	} // checkGlobal
	
	/*
		Check the licenses for a specific product and user searching if
		the devices requiring the registration already exist
	*/
	function checkProduct(	$license, $product ) {
		// Initializes the query to extract the selected license-code
		// associated records.
		$q = 'SELECT t1.' . USERS_NAME . ' AS ' . USERS_USER . 
			',t1.' . USERS_ID . ' AS ' . USERS_USER_ID .
			',t2.' . LICENSES_ID . ' AS ' . LICENSES_LICENSE_ID .
			',t2.' . LICENSES_DATE . 
			',t2.' . LICENSES_UPDATE . 
			',t2.' . LICENSES_LICENSES . 
			',t2.' . LICENSES_SUSPEND . 
			',t2.' . LICENSES_IS_ACTIVE . 
			',t2.' . LICENSES_ACTIVE_LICENSES .
			',t2.' . LICENSES_EXPIRE .
			',t3.' . PRODUCTS_DESCRIPTION . ' AS ' . PRODUCTS_PRODUCT . 
			',t3.' . PRODUCTS_ID . ' AS ' . PRODUCTS_PRODUCT_ID . 
			',t4.' . TYPES_ID . ' AS ' . TYPES_TYPE_CODE .
			',t4.' . TYPES_TYPE .

			' FROM ' . DB_TABLE_USERS . ' t1' .
			
			' INNER JOIN ' . DB_TABLE_LICENSES . ' t2 ON t1.' . USERS_ID . 
				' = t2.' . LICENSES_USER_ID .

			' INNER JOIN ' . DB_TABLE_PRODUCTS . ' t3 ON t3.' . PRODUCTS_ID . 
				' = t2.' . LICENSES_PRODUCT_ID .
				
			' INNER JOIN ' . DB_TABLE_TYPES . ' t4 ON t4.' . TYPES_ID . 
				' = t2.' . LICENSES_TYPE .
				
			' WHERE t1.' . USERS_LICENSE_CODE . '=\'' . $license . '\'' .
			' AND t3.' . PRODUCTS_KEY . '=\'' . $product . '\'';
			
		if($this->DEBUG == 1)
			echo 'CheckLicenses->checkProduct() query = ' . $q . '<br>';
		
		// Prepare the query and executes.
		$pdoStatement = $this->db->prepare($q);
		$pdoStatement->execute();

		$r = null;

		// If the query returned the record
		if( $row = $pdoStatement->fetch(PDO::FETCH_ASSOC) ) {
			// Loop on th fields array
			foreach ($this->licenseFieldList as $key) {
                $r["$key"] = $row["$key"];
			} // Loop on fields
		} // Record found
		
		$pdoStatement->closeCursor();
		
		if( $r == null )
			$r[LICENSES_LICENSE_ID] = null;

		return $r;
	}
	
	/*
		Check the user information and profile data and return 
		a json string.
	*/
	function checkUser($user) {
		// Initializes the query to extract the selected license-code
		// associated records.
		$q = 'SELECT ' .
			USERS_ID . ' AS ' . USERS_USER_ID . ',' .
			USERS_FULL_NAME . ',' .
			USERS_ADDRESS . ',' .
			USERS_TOWN . ',' .
			USERS_ZIP . ',' .
			USERS_COUNTRY . ',' .
			USERS_EMAIL . ',' .
			USERS_PHONE . ',' .
			USERS_VAT . ',' .
			USERS_EXTRAS .
			
			' FROM ' . DB_TABLE_USERS . 
				
			' WHERE ' . 
				DB_TABLE_USERS . '.' . 
				USERS_LICENSE_CODE . '=\'' . $user . '\'';
			
		if($this->DEBUG == 1)
			echo 'CheckLicenses->checkUser() query = ' . $q . '<br>';
		
		// Prepare the query and executes.
		$pdoStatement = $this->db->prepare($q);
		$pdoStatement->execute();

		$r = null;

		// If the query returned the record
		if( $row = $pdoStatement->fetch(PDO::FETCH_ASSOC) ) {
			// Loop on th fields array
			foreach ($this->userFieldList as $key) {
                $r["$key"] = $row["$key"];
			} // Loop on fields
		} // Record found
		
		$pdoStatement->closeCursor();
		
		if( $r == null )
			$r[USERS_USER_ID] = null;

		return $r;
	}
	
}
?>