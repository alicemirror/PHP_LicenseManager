<?php
/**
 *  users defines the database users table and its record fields
 *  Apr, 1, 2013 - Integrated the extras field
 */

 // Table name
define ("DB_TABLE_USERS", "users");

// Table fields
define ("USERS_ID", "id");
define ("USERS_NAME", "name");
define ("USERS_LICENSE_CODE", "license_code");
define ("USERS_FULL_NAME", "full_name");
define ("USERS_ADDRESS", "address");
define ("USERS_TOWN", "town");
define ("USERS_ZIP", "zip");
define ("USERS_COUNTRY", "country");
define ("USERS_EMAIL", "email");
define ("USERS_PHONE", "phone");
define ("USERS_VAT", "vat");
define ("USERS_EXTRAS", "extras");

// Fields alias
define ("USERS_USER", "user");
define ("USERS_FULL_ADDRESS", "full_address");
define ("USERS_USER_ID", "userID");

?>