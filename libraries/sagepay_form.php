<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * SagePay form class
 *
 * This CodeIgniter library to integrate the SagePay Go Form service
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

		log_message('debug', "SagePay Form Class Initialized");
	}

	// --------------------------------------------------------------------


	// --------------------------------------------------------------------

}

/* End of file sagepay_form.php */
/* Location: ./application/libraries/sagepay_form.php */