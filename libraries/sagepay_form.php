<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Sage Pay Form class
 *
 * This CodeIgniter library to integrate the Sage Pay Go Form service
 * http://www.sagepay.com/products_services/sage_pay_go/integration/form
 * 
 * @package   sagepay_form
 * @author    Ollie Rattue, Too many tabs <orattue[at]toomanytabs.com>
 * @copyright Copyright (c) 2010, Ollie Rattue
 * @license   http://www.opensource.org/licenses/mit-license.php
 * @link      https://github.com/ollierattue/codeigniter-sagepay-form
 */

class sagepay_form
{
	var $protocol_version = "2.23";
	var $config;
	var $VendorTxCode;
	var $submit_btn = '';		// Image/Form button
	
	var $CI;
	
	/**
	 * Constructor
	 *
	 * @access	public
	 */

	public function __construct()
	{
		if (!isset($this->CI))
		{
			$this->CI =& get_instance();
		}

		$this->CI->load->helper('url');
		$this->CI->load->helper('form');
		$this->CI->load->config('sagepay_form_config');
		
		log_message('debug', "Sage Pay Form Class Initialized");
		
		$this->button('Proceed to payment');
	}

	// --------------------------------------------------------------------
	
	function generate_form($form_name = 'SagePayForm')
	{
		$strCrypt = build_form_crypt();
		
		$str = '<form action="'.$strPurchaseURL.'" method="POST" id="SagePayForm" name="'.$form_name.'">' . "\n";
		
		$str .= form_hidden('navigate', "") . "\n";
		$str .= form_hidden('VPSProtocol', $strProtocol) . "\n";
		$str .= form_hidden('TxType', $strTransactionType) . "\n";
		$str .= form_hidden('Vendor', $strVendorName) . "\n";
		$str .= form_hidden('Crypt', $strCrypt) . "\n";
											
		$str .= $this->submit_btn;
		$str .= form_close() . "\n";

		return $str;
	}

	// --------------------------------------------------------------------
	
	// This function actually generates an entire HTML page consisting of
	// a form with hidden elements which is submitted to Sage Pay via the 
	// BODY element's onLoad attribute.  We do this so that you can validate
	// any POST vars from your custom form before submitting to Sage Pay.  
	
	// You would have your own form which is submitted to your script
	// to validate the data, which in turn calls this function to create
	// another hidden form and submit to Sage Pay.
	
	function generate_auto_form()
	{
		$this->button('Click here if you\'re not automatically redirected...');

		echo '<html>' . "\n";
		echo '<head><title>Processing Payment...</title></head>' . "\n";
		echo '<body onLoad="document.forms[\'paypal_auto_form\'].submit();">' . "\n";
		echo '<p>Please wait, your order is being processed and you will be redirected to our payment partner.</p>' . "\n";
		echo $this->generate_form('sagepay_auto_form');
		echo '</body></html>';
	}

	// --------------------------------------------------------------------
	
	function button($value = NULL)
	{
		// changes the default caption of the submit button
		$this->submit_btn = form_submit('sagepay_submit', $value);
	}

	// --------------------------------------------------------------------
	
	function build_form_crypt()
	{
		$strVendorTxCode = $this->_create_VendorTxCode();
		
		// ** TODO ADD in basket basket support **
		
		// Now to build the Form crypt field.
		$strPost = "VendorTxCode=" . $strVendorTxCode;
		
		// Optional: If you are a Sage Pay Partner and wish to flag the transactions with your unique partner id, it should be passed here
		if (strlen($strPartnerID) > 0)
		{
		    $strPost = $strPost . "&ReferrerID=" . $strPartnerID;			
		}

		$strPost = $strPost . "&Amount=" . number_format($sngTotal,2); // Formatted to 2 decimal places with leading digit
		$strPost = $strPost . "&Currency=" . $strCurrency;
		
		// Up to 100 chars of free format description
		$strPost = $strPost . "&Description=The best DVDs from " . $strVendorName;

		/* The SuccessURL is the page to which Form returns the customer if the transaction is successful 
		** You can change this for each transaction, perhaps passing a session ID or state flag if you wish */
		$strPost = $strPost . "&SuccessURL=" . $strYourSiteFQDN . "/orderSuccessful.php";

		/* The FailureURL is the page to which Form returns the customer if the transaction is unsuccessful
		** You can change this for each transaction, perhaps passing a session ID or state flag if you wish */
		$strPost = $strPost . "&FailureURL=" . $strYourSiteFQDN . "/orderFailed.php";

		// This is an Optional setting. Here we are just using the Billing names given.
		$strPost = $strPost . "&CustomerName=" . $strBillingFirstnames . " " . $strBillingSurname;
		
		/* Email settings:
		** Flag 'SendEMail' is an Optional setting. 
		** 0 = Do not send either customer or vendor e-mails, 
		** 1 = Send customer and vendor e-mails if address(es) are provided(DEFAULT). 
		** 2 = Send Vendor Email but not Customer Email. If you do not supply this field, 1 is assumed and e-mails are sent if addresses are provided. **/
		if ($bSendEMail == 0)
		{
			$strPost=$strPost . "&SendEMail=0";
		}
		else
		{
			if ($bSendEMail == 1) 
			{
		    	$strPost = $strPost . "&SendEMail=1";
		    } 
			else 
			{
		    	$strPost = $strPost . "&SendEMail=2";
		    }

		    if (strlen($strCustomerEMail) > 0)
			{
				$strPost = $strPost . "&CustomerEMail=" . $strCustomerEMail;  // This is an Optional setting
			}
		        
		    if (($strVendorEMail <> "[your e-mail address]") && ($strVendorEMail <> ""))
			{
				$strPost = $strPost . "&VendorEMail=" . $strVendorEMail;  // This is an Optional setting
			}
			    
		    // You can specify any custom message to send to your customers in their confirmation e-mail here
		    // The field can contain HTML if you wish, and be different for each order.  This field is optional
		    $strPost = $strPost . "&eMailMessage=Thank you so very much for your order.";
		}

		// Billing Details:
		$strPost = $strPost . "&BillingFirstnames=" . $strBillingFirstnames;
		$strPost = $strPost . "&BillingSurname=" . $strBillingSurname;
		$strPost = $strPost . "&BillingAddress1=" . $strBillingAddress1;
		
		if (strlen($strBillingAddress2) > 0)
		{
			$strPost=$strPost . "&BillingAddress2=" . $strBillingAddress2;	
		}
		
		$strPost = $strPost . "&BillingCity=" . $strBillingCity;
		$strPost = $strPost . "&BillingPostCode=" . $strBillingPostCode;
		$strPost = $strPost . "&BillingCountry=" . $strBillingCountry;
		
		if (strlen($strBillingState) > 0)
		{
			$strPost=$strPost . "&BillingState=" . $strBillingState;
		}
		
		if (strlen($strBillingPhone) > 0)
		{
			$strPost=$strPost . "&BillingPhone=" . $strBillingPhone;
		}

		// Delivery Details:
		$strPost=$strPost . "&DeliveryFirstnames=" . $strDeliveryFirstnames;
		$strPost=$strPost . "&DeliverySurname=" . $strDeliverySurname;
		$strPost=$strPost . "&DeliveryAddress1=" . $strDeliveryAddress1;
		
		if (strlen($strDeliveryAddress2) > 0)
		{
			$strPost = $strPost . "&DeliveryAddress2=" . $strDeliveryAddress2;
		}
		$strPost = $strPost . "&DeliveryCity=" . $strDeliveryCity;
		$strPost = $strPost . "&DeliveryPostCode=" . $strDeliveryPostCode;
		$strPost = $strPost . "&DeliveryCountry=" . $strDeliveryCountry;
		
		if (strlen($strDeliveryState) > 0)
		{
			$strPost=$strPost . "&DeliveryState=" . $strDeliveryState;	
		}
		
		if (strlen($strDeliveryPhone) > 0)
		{
			$strPost=$strPost . "&DeliveryPhone=" . $strDeliveryPhone;
		}
		
		// For charities registered for Gift Aid, set to 1 to display the Gift Aid check box on the payment pages
		$strPost = $strPost . "&AllowGiftAid=0";
		
		/* Allow fine control over AVS/CV2 checks and rules by changing this value. 0 is Default 
		** It can be changed dynamically, per transaction, if you wish.  See the Server Protocol document */
		if ($strTransactionType!=="AUTHENTICATE")
		{
			$strPost = $strPost . "&ApplyAVSCV2=0";
		}
		
		/* Allow fine control over 3D-Secure checks and rules by changing this value. 0 is Default 
		** It can be changed dynamically, per transaction, if you wish.  See the Form Protocol document */
		$strPost = $strPost . "&Apply3DSecure=0";
		
		// Encrypt the plaintext string for inclusion in the hidden field
		$strCrypt = $this->_base64Encode(SimpleXor($strPost,$strEncryptionPassword));
		
		return $strCrypt;
	}

	// --------------------------------------------------------------------

	/* Specification - parameters
	
	VendorTxCode - site creates this unique code
	Amount - with 2 decimal places where relevant
	Currency - 3 characters 
	Description - Max 100 characters
	SuccessURL - Max 2000 characters
	FailureURL - Max 2000 characters
	
	Optional
	 
	CustomerName - Max 100 characters
	CustomerECustomerEMail - Max 255 characters 
	
	VendorEMail - Max 255 characters 
				-> If provided, an e-mail will be sent to this address when each 
				transaction completes (successfully or otherwise).

	SendEMail - 
	0 = Do not send either customer or vendor e- 
	mails 
	1 = Send customer and vendor e-mails if 
	addresses are provided (DEFAULT) 
	2 = Send vendor e-mail but NOT the customer e-mail 
	
	If you do not supply this field, 1 is assumed and e-mails are sent 
	if addresses are provided. 

	// A message to the customer which is inserted into the successful transaction e-mails only.
	// If provided this message is included toward the top of the
	eMailMessage - Max 7500 characters (HTML)
	
	
	BillingSurname - Max 20 characters
	BillingFirstnames - Max 20 characters
	BillingAddress1 - Max 100 characters
	BillingAddress2 (optional) Max 100 characters
	BillingCity - Max 40 characters
	BillingPostCode  Max 10 characters
	BillingCountry - 2 characters ISO 3166-1 country code
	BillingState (optional) - Max 2 characters State code for US customers only
	BillingPhone (optional) - Max 20 characters
	DeliverySurname - Max 20 characters 
	DeliveryFirstnames - Max 20 characters
	DeliveryAddress1 - Max 100 characters
	DeliveryAddress2 (optional) - Max 100 characters
	DeliveryCity - Max 40 characters
	DeliveryPostCode - Max 10 characters
	DeliveryCountry - 2 characters ISO 3166-1 country code
	DeliveryState (optional) - Max 2 characters State code for US customers only
	DeliveryPhone (optional) - Max 20 characters
	
	// You can use this field to supply details of the customer’s order.  
	// This information will be displayed to you in My Sage Pay. 
	Basket (optional) - Max 7500 characters 
	
	// 0 = No Gift Aid Box displayed (default) 
	// 1 = Display Gift Aid Box on payment screen. This flag allows the gift aid acceptance box to appear for this 
	AllowGiftAid (optional)
	ApplyAVSCV2 
	Apply3DSecure
	BillingAgreement

	*/
	
	// --------------------------------------------------------------------
	
	// Creates a unique string
	function _create_VendorTxCode()
	{
		$timestamp = date("y-m-d-H-i-s", time());
		$random_number = rand(0,32000)*rand(0,32000);
		$VendorTxCode = "{$timestamp}-{$random_number}";

		return $VendorTxCode;
	}

	// --------------------------------------------------------------------
	
	// Filters unwanted characters out of an input string.  Useful for tidying up FORM field inputs.
	function _cleanInput($strRawText,$strType) 
	{

		if ($strType=="Number") {
			$strClean="0123456789.";
			$bolHighOrder=false;
		}
		else if ($strType=="VendorTxCode") {
			$strClean="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
			$bolHighOrder=false;
		}
		else {
	  		$strClean=" ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.,'/{}@():?-_&£$=%~<>*+\"";
			$bolHighOrder=true;
		}

		$strCleanedText="";
		$iCharPos = 0;

		do
			{
	    		// Only include valid characters
				$chrThisChar=substr($strRawText,$iCharPos,1);

				if (strspn($chrThisChar,$strClean,0,strlen($strClean))>0) { 
					$strCleanedText=$strCleanedText . $chrThisChar;
				}
				else if ($bolHighOrder==true) {
					// Fix to allow accented characters and most high order bit chars which are harmless 
					if (bin2hex($chrThisChar)>=191) {
						$strCleanedText=$strCleanedText . $chrThisChar;
					}
				}

			$iCharPos=$iCharPos+1;
			}
		while ($iCharPos<strlen($strRawText));

	  	$cleanInput = ltrim($strCleanedText);
		return $cleanInput;

	}

	// --------------------------------------------------------------------
	
	/* Base 64 Encoding function **
	** PHP does it natively but just for consistency and ease of maintenance, let's declare our own function **/

	function _base64Encode($plain) {
	  // Initialise output variable
	  $output = "";

	  // Do encoding
	  $output = base64_encode($plain);

	  // Return the result
	  return $output;
	}

	// --------------------------------------------------------------------
	
	/* Base 64 decoding function **
	** PHP does it natively but just for consistency and ease of maintenance, let's declare our own function **/

	function _base64Decode($scrambled) {
	  // Initialise output variable
	  $output = "";

	  // Fix plus to space conversion issue
	  $scrambled = str_replace(" ","+",$scrambled);

	  // Do encoding
	  $output = base64_decode($scrambled);

	  // Return the result
	  return $output;
	}

	// --------------------------------------------------------------------

	/*  The SimpleXor encryption algorithm                                                                                **
	**  NOTE: This is a placeholder really.  Future releases of Form will use AES or TwoFish.  Proper encryption      	  **
	**  This simple function and the Base64 will deter script kiddies and prevent the "View Source" type tampering        **
	**  It won't stop a half decent hacker though, but the most they could do is change the amount field to something     **
	**  else, so provided the vendor checks the reports and compares amounts, there is no harm done.  It's still          **
	**  more secure than the other PSPs who don't both encrypting their forms at all                                      */

	function _simpleXor($InString, $Key) 
	{
	  // Initialise key array
	  $KeyList = array();
	  // Initialise out variable
	  $output = "";

	  // Convert $Key into array of ASCII values
	  for($i = 0; $i < strlen($Key); $i++){
	    $KeyList[$i] = ord(substr($Key, $i, 1));
	  }

	  // Step through string a character at a time
	  for($i = 0; $i < strlen($InString); $i++) {
	    // Get ASCII code from string, get ASCII code from key (loop through with MOD), XOR the two, get the character from the result
	    // % is MOD (modulus), ^ is XOR
	    $output.= chr(ord(substr($InString, $i, 1)) ^ ($KeyList[$i % strlen($Key)]));
	  }

	  // Return the result
	  return $output;
	}

	// --------------------------------------------------------------------
	
	// Function to check validity of email address entered in form fields
	function _is_valid_email($email)
	{
	  $result = TRUE;
	  if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email)) {
	    $result = FALSE;
	  }
	  return $result;
	}
	
	// --------------------------------------------------------------------
}

/* End of file sagepay_form.php */
/* Location: ./application/libraries/sagepay_form.php */