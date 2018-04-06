<?php

// Include the database tables definitions
require_once dirname(__DIR__) . "/include/database/licenses.php";
require_once dirname(__DIR__) . "/include/database/products.php";
require_once dirname(__DIR__) . "/include/database/users.php";
require_once dirname(__DIR__) . "/include/database/license_types.php";
require_once dirname(__DIR__) . "/include/database/devices.php";

// Contains the methods to register and update licenses on the database
Class UpdateLicenses {
	
	// License data array field names
	public $DATA_S1_FLD_LICENSE_ID = "licenseID";
	public $DATA_S1_FLD_USER_ID = "userID";
	public $DATA_S1_FLD_UPDATE = "updateDate";
	public $DATA_S1_FLD_ACTIVE_LIC = "active_licenses";
	public $DATA_S1_FLD_SUSPEND = "suspendDate";
	public $DATA_S1_FLD_IS_ACTIVE = "is_active";
	public $DATA_S1_FLD_EXPIRE = "expireDate";
	public $DATA_S1_FLD_PRODUCT_ID = "productID";
	public $DATA_S1_FLD_TYPE_CODE = "licenseCode";

	
	// Device data array field names
	public $DATA_S2_FLD_DEVICE_ID = "deviceID";
	public $DATA_S2_FLD_LICENSE_ID = "license_id";
	public $DATA_S2_FLD_LICENSE_NUM = "license_number";
	public $DATA_S2_FLD_USER_ID = "user_id";
	public $DATA_S2_FLD_UPDATE = "updateDate";
	public $DATA_S2_FLD_UUID = "UUID";
	public $DATA_S2_FLD_IMEI = "IMEI";
	public $DATA_S2_FLD_WLANMAC = "WLANMAC";
	public $DATA_S2_FLD_BTADDRESS = "BTADDRESS";
	public $DATA_S2_FLD_SDK = "SDK";
	public $DATA_S2_FLD_ANDROID = "ANDROID";
	public $DATA_S2_FLD_BOARD = "BOARD";
	public $DATA_S2_FLD_BOOTLOADER = "BOOTLOADER";
	public $DATA_S2_FLD_BRAND = "BRAND";
	public $DATA_S2_FLD_DEVICE = "DEVICE";
	public $DATA_S2_FLD_FINGERPRINT = "FINGERPRINT";
	public $DATA_S2_FLD_MANUFACTURER = "MANUFACTURER";
	public $DATA_S2_FLD_MODEL = "MODEL";
	public $DATA_S2_FLD_PRODUCT = "PRODUCT";
	
	// Global preferences instance. Includes  the database 
	// instance opened by the Globals class constructor
	private $prefs;
	// Local replica of the database instance
	private $db;
	
	private $DEBUG = 0;
	
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
		$this->db = $this->prefs->db;
	} // constructor
	
	/*
		Update the license record with the registration data. This method is called every
		time a new license is registered, suspended, unregistered etc. Licenses can only be
		updated necause the license records are created outside of the application context.
	*/
	public function updateLIcense($license) {
		$sql = "UPDATE " . 
				DB_TABLE_LICENSES . 
				" SET " . 
					LICENSES_UPDATE . "='" . $license[$this->DATA_S1_FLD_UPDATE] . "'," .
					LICENSES_SUSPEND . "='" . $license[$this->DATA_S1_FLD_SUSPEND] . "'," .
					LICENSES_IS_ACTIVE . "='" . $license[$this->DATA_S1_FLD_IS_ACTIVE] . "'," .
					LICENSES_ACTIVE_LICENSES . "='" . $license[$this->DATA_S1_FLD_ACTIVE_LIC] . "'," .
					LICENSES_EXPIRE . "='" . $license[$this->DATA_S1_FLD_EXPIRE] . "'" .
				" WHERE " . 
					DB_TABLE_LICENSES . "." . LICENSES_ID . "='" . $license[$this->DATA_S1_FLD_LICENSE_ID] . "'";
					
			if($this->DEBUG == 1)
				echo ' UpdateLicenses->updateLicense() query = ' . $sql  . ' - ';

			$pdoStatement = $this->db->prepare($sql);
			$pdoStatement->execute();
	}
	
	/*
		Update an already registered device with the new device data. This method is used to
		override an already existing device, e.g. when the license was removed or when a follow-up
		license is registered on a new device.
	*/
	public function updateDevice($device) {
		// Prepare the sql query
		$sql = "UPDATE " . 
				DB_TABLE_DEVICES . 
				" SET " . 
					DEVICES_UPDATE . "='" . $device[$this->DATA_S2_FLD_UPDATE] . "'," .
					DEVICES_UUID . "='" . $device[$this->DATA_S2_FLD_UUID] . "'," .
					DEVICES_IMEI . "='" . $device[$this->DATA_S2_FLD_IMEI] . "'," .
					DEVICES_WLANMAC . "='" . $device[$this->DATA_S2_FLD_WLANMAC] . "'," .
					DEVICES_BTADDRESS . "='" . $device[$this->DATA_S2_FLD_BTADDRESS] . "'," .
					DEVICES_SDK . "='" . $device[$this->DATA_S2_FLD_SDK] . "'," .
					DEVICES_ANDROID . "='" . $device[$this->DATA_S2_FLD_ANDROID] . "'," .
					DEVICES_BOARD . "='" . $device[$this->DATA_S2_FLD_BOARD] . "'," .
					DEVICES_BOOTLOADER . "='" . $device[$this->DATA_S2_FLD_BOOTLOADER] . "'," .
					DEVICES_BRAND . "='" . $device[$this->DATA_S2_FLD_BRAND] . "'," .
					DEVICES_DEVICE . "='" . $device[$this->DATA_S2_FLD_DEVICE] . "'," .
					DEVICES_FINGERPRINT . "='" . $device[$this->DATA_S2_FLD_FINGERPRINT] . "'," .
					DEVICES_MANUFACTURER . "='" . $device[$this->DATA_S2_FLD_MANUFACTURER] . "'," .
					DEVICES_MODEL . "='" . $device[$this->DATA_S2_FLD_MODEL] . "'," .
					DEVICES_PRODUCT . "='" . $device[$this->DATA_S2_FLD_PRODUCT] . "'" .
				" WHERE " . 
					DB_TABLE_DEVICES . "." . DEVICES_ID . "='" . $device[$this->DATA_S2_FLD_DEVICE_ID] . "'";
					
			if($this->DEBUG == 1)
				echo ' UpdateLicenses->updateDevice() query = ' . $sql  . ' - ';

			$pdoStatement = $this->db->prepare($sql);
			$pdoStatement->execute();
	}
	
	/*
		Create a new device record associated with the specified license and user IDs
		The function accept as input parameter the updated device record decoded from the
		client data.
	*/
	public function createDevice($device) {
		// Prepare the sql query
		$sql = "INSERT INTO " . 
				DB_TABLE_DEVICES . 
				" (" . 
					DEVICES_LICENSE_ID . "," .
					DEVICES_LICENSE_NUM . "," . 
					DEVICES_USER_ID . "," .
					DEVICES_UPDATE . "," .
					DEVICES_UUID . "," .
					DEVICES_IMEI . "," .
					DEVICES_WLANMAC . "," .
					DEVICES_BTADDRESS . "," .
					DEVICES_SDK . "," .
					DEVICES_ANDROID . "," .
					DEVICES_BOARD . "," .
					DEVICES_BOOTLOADER . "," .
					DEVICES_BRAND . "," .
					DEVICES_DEVICE . "," .
					DEVICES_FINGERPRINT . "," .
					DEVICES_MANUFACTURER . "," .
					DEVICES_MODEL . "," .
					DEVICES_PRODUCT . 
				") VALUES (" . 
					"'" . $device[$this->DATA_S2_FLD_LICENSE_ID] . "'," .
					"'" . $device[$this->DATA_S2_FLD_LICENSE_NUM] . "'," .
					"'" . $device[$this->DATA_S2_FLD_USER_ID] . "'," .
					"'" . $device[$this->DATA_S2_FLD_UPDATE] . "'," .
					"'" . $device[$this->DATA_S2_FLD_UUID] . "'," .
					"'" . $device[$this->DATA_S2_FLD_IMEI] . "'," .
					"'" . $device[$this->DATA_S2_FLD_WLANMAC] . "'," .
					"'" . $device[$this->DATA_S2_FLD_BTADDRESS] . "'," .
					"'" . $device[$this->DATA_S2_FLD_SDK] . "'," .
					"'" . $device[$this->DATA_S2_FLD_ANDROID] . "'," .
					"'" . $device[$this->DATA_S2_FLD_BOARD] . "'," .
					"'" . $device[$this->DATA_S2_FLD_BOOTLOADER] . "'," .
					"'" . $device[$this->DATA_S2_FLD_BRAND] . "'," .
					"'" . $device[$this->DATA_S2_FLD_DEVICE] . "'," .
					"'" . $device[$this->DATA_S2_FLD_FINGERPRINT] . "'," .
					"'" . $device[$this->DATA_S2_FLD_MANUFACTURER] . "'," .
					"'" . $device[$this->DATA_S2_FLD_MODEL] . "'," .
					"'" . $device[$this->DATA_S2_FLD_PRODUCT] . "')";
					
			if($this->DEBUG == 1)
				echo ' UpdateLicenses->createDevice() query = ' . $sql  . ' - ';

			$pdoStatement = $this->db->prepare($sql);
			$pdoStatement->execute();	
	}
	
	/*
		Check if a device with the $lic licenxe id already exists.
		This is for device overwriting when a new follow-up license
		type is registered.
	*/
	public function searchDeviceByLicense($licId, $licNum, $userId) {
		// Initializes the query to extract the selected device record if exist
		$q = "SELECT " .
			DEVICES_ID . "," .
			DEVICES_LICENSE_ID . "," .
			DEVICES_LICENSE_NUM . "," .
			DEVICES_USER_ID . 
			
			" FROM " . DB_TABLE_DEVICES . 
				
			" WHERE " . 
				DB_TABLE_DEVICES . "." . DEVICES_LICENSE_ID . "='" . $licId . "' AND " .
				DB_TABLE_DEVICES . "." . DEVICES_LICENSE_NUM . "='" . $licNum . "' AND " .
				DB_TABLE_DEVICES . "." . DEVICES_USER_ID . "='" . $userId . "'";
			
		if($this->DEBUG == 1) {
			echo ' UpdateLicenses->searchDeviceByLicense() query = ' . $q  . ' - ';
		}
		
		// Prepare the query and executes.
		$pdoStatement = $this->db->prepare($q);
		$pdoStatement->execute();

		// Prepare the output array
		$output = array();
		$r = -1;

		// If the query returned the record get the device ID
		if( $row = $pdoStatement->fetch(PDO::FETCH_ASSOC) ) {
			$r = $row[DEVICES_ID];
		}
		
		$pdoStatement->closeCursor();
		
		return $r;
	}

}
?>