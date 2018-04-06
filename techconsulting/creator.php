<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php 

	// Class loadedr
	function classLoader($className) {
		require_once dirname(__FILE__) ."/Classes/". $className . '.php';
	}
	
	spl_autoload_register('classLoader');
	
	// Vendor key (should be passed as parameter)
	$VENDOR_KEY = $_POST['reseller_code'];
	$VENDOR_NAME = $_POST['name'];
	$VENDOR_FULL_NAME = $_POST['full_name'];
	
	// Debug
	$DEBUG = 0;
	
	// Form error, initially set to empty	
	$FORM_ERROR = "";
	
	/*
		POST fields manager. postExec() is called when the user POST the form
		creating the two arrays sent with the vendor key to CreateLicenses class.
		
		WARNING: The order of the fields in the array should be the folowing:
			
			$userName, $userFullName, $userAddress,
			$userTown, $userZip, $userCountry, $userVat, 
			$userEmail, $userPhone, $userExtras
			
			$licenseType, $licenseProducts, $licenseQuantity, $licenseExpire		
	*/
	function postExec() {
		if($DEBUG == 1) {
			echo "creator.php : postExec() vendor key = $vendorKey";
		}
		// Catch the post fields of the form
		// and send to the creation function.
		if(isset($_POST)) {
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
		  
		  // If none of the user fields are set, the post has no effect at all
		  $test = "";
		  foreach ($user as $userValue)
		  	if($userValue != "")
				$test = 1;
		 
		  if($test == 1) {
				// Send the fields to the creator
				$Kcreator = new CreateLicenses();
				$result = $Kcreator->createLicenses($user, $license, $_POST['vendor_key']);
				
				// If result is 1 the page is redirected
				if($result == "") {
				  http_redirect("confirm.php");
				} // No error condition
			} // Post has at least some user data
		} // POST is set
	}
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>License Manager for Resellers</title>
<link href="techconsulting.css" rel="stylesheet" type="text/css" />
</head>

<body>
<div class="container">
  <div class="header">Tech Consulting License Creator</div>
  <div class="content">
    <h2>For Authorized Personnel Only</h2>
    <p>Welcome to the License Creator, <b><?php echo $VENDOR_NAME?></b> from <?php echo $VENDOR_FULL_NAME?></p>
    <div class="form">
    <?php echo ('<form action="controlLicense.php" method="post" name="license_data" id="license_data">'); ?>
    <h2 class="content">Client Info
      <input name="vendor_key" type="hidden" id="vendor_key" value="<?php echo $VENDOR_KEY; ?>"/>
    </h2>
    <p>
    <label class="form_text" for="user_name">Name*</label>
        <input name="user_name" class="form_field" type="text" id="user_name" tabindex="1" size="30" maxlength="30">
      </p>
    <p>
        <label class="form_text" for="full_name">Company*</label>
      <input name="full_name" class="form_field" type="text" id="full_name" tabindex="2" size="30" maxlength="30">
      </p>
      <p>
        <label class="form_text" for="address">Address</label>
        <input name="address" class="form_field" type="text" id="address" tabindex="3" size="40" maxlength="40">
      </p>
      <p>
        <label class="form_text" for="town">Town*</label>
        <input name="town" class="form_field" type="text" id="town" tabindex="4" size="30" maxlength="30">
      </p>
      <p>
        <label class="form_text" for="zip">ZIP*</label>
        <input name="zip" class="form_field" type="text" id="zip" value="00000" tabindex="5" size="7" maxlength="6">
        <label class="form_text" for="country">Country*</label>
        <input name="country" class="form_field" type="text" id="country" tabindex="6" size="2" maxlength="2">
      </p>
      <p>
        <label class="form_text" for="vat">VAT</label>
        <input name="vat" class="form_field" type="text" id="vat" tabindex="7" size="25" maxlength="25">
      </p>
      <p>
        <label class="form_text" for="email">E-mail*</label>
        <input name="email" class="form_field" type="text" id="email" tabindex="8" size="30" maxlength="30">
      </p>
      <p>
        <label class="form_text" for="phone">Phone</label>
        <input name="phone" class="form_field" type="text" id="phone" tabindex="9" size="30" maxlength="30">
      </p>
      <h2 class="content">License Data</h2>
      <p>
        <label class="form_text" for="extras">Extras</label>
        <input name="extras" class="form_field" type="text" id="extras" size="55" maxlength="50">
      </p>
      <p>
	  
        <label class="form_text" for="license_type">License Type</label>
        <select name="license_type" class="form_field" tabindex="10" size="1" id="license_type">
          <option value="1">Multiple</option>
          <option value="2">Temporary</option>
          <option value="3">Incremental</option>
          <option value="4">Follow-Up</option>
        </select>
      </p>
      <p>
        <label class="form_text" for="products">Product</label>
        <select name="products" class="form_field" tabindex="11" size="1" id="products">
          <option value="1">Bluetooth Printer Driver</option>
          <option value="2">Printing Notes</option>
          <option value="3">USB Monitor</option>
          <option value="4">Invoice Manager</option>
          <option value="5">Personal Tracking</option>
          <option value="6">USB Printer Driver</option>
          <option value="7">Bt Mobile Printing WebApp</option>
          <option value="8">POS Light Version</option>
        </select>
    </p>
      <p>
        <label class="form_text" for="quantity">Number of Licenses*</label>
        <input name="quantity" class="form_field" type="text" id="quantity" tabindex="12" value="2" size="3" maxlength="3">
    </p>
      <h3>License(s) Creation</h3>
      <h4 class="content">Pressing the <b>Go</b> button below the license manager start creating the requested licenses. A copy of the license activation codes will be sent to your mail inbox. Use the the licenses codes to activate the product licenses on the devices where it is installed the software. </h4>
      <p align="center" >	
        <input class="form_button" name="create" type="submit" id="create" tabindex="14" value="Go">
      </p>
    </form>
	</div>
  </div>
  <div class="footer">Created by Tech Consulting - Spain, 2013 (<a href="mailto:ibiza.techconsulting@gmail.com">ibiza.techconsulting@gmail.com</a>)</div>
</body>
</html>