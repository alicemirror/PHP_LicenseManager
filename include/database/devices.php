<?php
/**
 * devices table and its record fields
 */

 // Table name
define ("DB_TABLE_DEVICES", "devices");

// Table fields
define ("DEVICES_ID", "id");
define ("DEVICES_LICENSE_ID", "license_id"); // The product id the license is released
define ("DEVICES_LICENSE_NUM", "license_number"); // The lilcense number created when the device is registered for a certain product
define ("DEVICES_USER_ID", "user_id"); // The associated user for this license
define ("DEVICES_UPDATE", "updateDate"); // Last renewal date of the associated license
define ("DEVICES_UUID", "UUID");
define ("DEVICES_IMEI", "IMEI");
define ("DEVICES_WLANMAC", "WLANMAC");
define ("DEVICES_BTADDRESS", "BTADDRESS");
define ("DEVICES_SDK", "SDK");
define ("DEVICES_ANDROID", "ANDROID");
define ("DEVICES_BOARD", "BOARD");
define ("DEVICES_BOOTLOADER", "BOOTLOADER");
define ("DEVICES_BRAND", "BRAND");
define ("DEVICES_DEVICE", "DEVICE");
define ("DEVICES_FINGERPRINT", "FINGERPRINT");
define ("DEVICES_MANUFACTURER", "MANUFACTURER");
define ("DEVICES_MODEL", "MODEL");
define ("DEVICES_PRODUCT", "PRODUCT");

// Fields alias
define ("DEVICES_DEVICE_ID", "deviceID");

?>