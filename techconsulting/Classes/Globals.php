<?php 

// Licensing system Globals class
// Globals defines the global variables, dynamically loaded when the class
// is instantiated depending on the market name
class Globals {
	
	public $root_site;
	public $db;        //PDO connection
	public $DEBUG;
	
	// Globals class constructor
	function __construct(){
		
		$this->DEBUG = 0;

		// Creates the relative path pointers
		$this->root_site = dirname(__FILE__) . "/../";
		
		$this->root_site = realpath($this->root_site) . "/" ;
		
		$myPath = dirname(__DIR__) . "/Config/config.ini.php";
		
		if($this->DEBUG == 1) {
		echo "myPath = " . $myPath . "<br>";
		}
		
		// Load the configuration parameters
		$ini_array = parse_ini_file($myPath);
		$this->openDbConnection($ini_array);
		$this->loadVars($ini_array);
		
		if($this->DEBUG == 1){
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}

	}
	
	/**
	* \fn __destruct()
	* 
	* The class destructor closes the PDO database connection
	*/
	function __destruct() {
		$this->db = null;
	}
	
	/**
	* \fn loadVars($ini_array)
	* 
	* Loader of the global variables from the ini file array, after parsing
	* \c loadVars loop on the input ini_array and for each existing property
	* set the value in the key.
	* 
	* @param $ini_array The initialization file data packaed array
	*/
	function loadVars($ini_array) {
		foreach ($ini_array as $key => $value) {
			if( property_exists($this, $key) ){
				$this->$key = $value;
			}
		}
		
	} // loadVars
	
	/**
	* \fn openDbConnection($ini_array)
	* 
	* Open the PDO database connection. The ini_array is expected
	* containing the pairs name-value with the needed credentials
	* to open the database.
	* 
	* @param $ini_array
	*/
	
	function openDbConnection($ini_array) {
		$db_host = $ini_array['db_host'];
		$db_name = $ini_array['db_name'];
		$db_user = $ini_array['db_user'];
		$db_pass = $ini_array['db_pass'];
		
		$this->db = new PDO("mysql:host=" . $db_host . ";dbname=" . $db_name, $db_user, $db_pass);
	
		if($this->DEBUG == 1) {
			echo "openDbConnection => mysql:host  " . $db_host . " - dbname = " . $db_name . "<br>";
			}

	} // openDbConnection
	
	}
	?>