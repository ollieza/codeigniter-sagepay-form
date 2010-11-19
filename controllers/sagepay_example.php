<?php

/*
| -------------------------------------------------------------------------
| Sagepay library example
| -------------------------------------------------------------------------
*/

class Sagepay_example extends Controller
{
	public function __construct()
    {
        parent::__construct();
		
		$this->load->library('sagepay_form');
  	}

	// --------------------------------------------------------------------
	
    function payment()
	{
		$this->sagepay_form->add_data('total', '15.00'); // with 2 decimal places where relevant
		$this->sagepay_form->add_data('description', 'My instructional DVD'); // The description of goods purchased is displayed on the Sage Pay Max 100
				
		// The domain name and protocol (http or https) is defined in sagepay_form_config. DO NOT INCLUDE HERE.
		$this->sagepay_form->add_data('success_url', 'sagepay_example/payment_status/success');
		$this->sagepay_form->add_data('failure_url', 'sagepay_example/payment_status/failure');
		
		// Billing address
		$this->sagepay_form->add_data('billing_first_names', "Jo"); // Max 20 characters
		$this->sagepay_form->add_data('billing_surname', "Blogs"); // Max 20 characters
		$this->sagepay_form->add_data('billing_address1', "Jo's place"); // Max 100 characters
		$this->sagepay_form->add_data('billing_address2', ""); // Optional Max 100 characters
		$this->sagepay_form->add_data('billing_city', "London"); // Max 40 characters
		$this->sagepay_form->add_data('billing_postcode', "EC8 8RH"); // Max 10 characters
		$this->sagepay_form->add_data('billing_country', "UK"); // 2 characters ISO 3166-1 country code
		$this->sagepay_form->add_data('billing_state', ""); // US customers only Max 2 characters State code
		$this->sagepay_form->add_data('billing_phone', "01205581818"); // Optional Max 20 characters
		                               
		// Can be the same as billing  
		$this->sagepay_form->add_data('delivery_first_names', "Jo"); // Max 20 characters
		$this->sagepay_form->add_data('delivery_surname', "Blogs"); // Max 20 characters
		$this->sagepay_form->add_data('delivery_address1', "Jo's place"); // Max 100 characters
		$this->sagepay_form->add_data('delivery_address2', ""); // Optional Max 100 characters
		$this->sagepay_form->add_data('delivery_city', "London"); // Max 40 characters
		$this->sagepay_form->add_data('delivery_postcode', "EC8 8RH"); // Max 10 characters
		$this->sagepay_form->add_data('delivery_country', "UK"); // 2 characters ISO 3166-1 country code
		$this->sagepay_form->add_data('delivery_state', ""); // US customers only Max 2 characters State code
		$this->sagepay_form->add_data('delivery_phone', "077974899"); // Optional Max 20 characters
		
		// Optional values
		// $this->sagepay_form->add_data('send_email', ''); // Flag Consult the Form Protocol document
		// $this->sagepay_form->add_data('currency', ''); // 3 characters 
		// $this->sagepay_form->add_data('customer_email', ''); // Max 255 characters 
		// $this->sagepay_form->add_data('vendor_email', ''); // Set in config. You can do a per transaction override Max 255 characters 
		// $this->sagepay_form->add_data('email_message', ''); // A message to the customer which is inserted into the successful transaction e-mails only Max 7500 characters
		
		// Advanced fine control. Consult the Form Protocol document
		$this->sagepay_form->add_data('allow_gift_aid', 0); // For charities registered for Gift Aid, set to 1 to display the Gift Aid check box on the payment pages
		$this->sagepay_form->add_data('apply_avscv2', 0); // Allow fine control over AVS/CV2 checks and rules by changing this value
		$this->sagepay_form->add_data('apply_3d_secure', 0); // Allow fine control over 3D-Secure checks and rules by changing this value
		$this->sagepay_form->add_data('billing_agreement', 0); // For PAYPAL REFERENCE transactions 
		
		echo $this->sagepay_form->form();
	}

	// --------------------------------------------------------------------
	
	// Redirected to from SagePay Form
	function payment_status($type = NULL, $crypt = NULL)
	{
		switch ($type)
		{
			case 'success':
				echo 'Payment successfully made';
			break;
			
			case('failure'):
				echo 'Payment failed';
			break;
			
			default:
				redirect();
			break;
		}
	}
	
	// --------------------------------------------------------------------
}
?>