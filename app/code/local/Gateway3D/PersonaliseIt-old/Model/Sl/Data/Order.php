<?php

class Gateway3D_PersonaliseIt_Model_Sl_Data_Order
{
	const STATUS_UNKNOWN		= 0;
	const STATUS_ITEMS_READY	= 1;
	const STATUS_BATCH_READY	= 2;
	const STATUS_IN_PRODUCTION	= 4;
	const STATUS_DISPATCHED		= 8;
	const STATUS_REPRINT		= 16;
	const STATUS_REJECTED		= 32;
	
	const STATUS_SUPPLIER_DISPATCHED_NOTIFIED = 64;
	
	const STATUS_CANCELLED		= 128;
	const STATUS_ON_HOLD		= 256;
	
	public $id						= 0;
	public $ref						= '';
	public $external_ref			= '';
	public $additional_info			= '';
	public $company_ref_id			= 0;
	public $status					= 0;
		
	public $sale_datetime			= '';
	public $completion_datetime		= '';
	public $creation_datetime		= '';
		
	public $has_been_completed		= false;
		
	public $shipping_address_1		= '';
	public $shipping_address_2		= '';
	public $shipping_address_3		= '';
	public $shipping_address_4		= '';
	public $shipping_address_5		= '';
	public $shipping_postcode		= '';
	public $shipping_country		= '';
	public $shipping_country_code	= '';
		
	public $shipping_method			= '';
	public $shipping_carrier		= '';
	public $shipping_tracking		= '';
		
	public $billing_address_1		= '';
	public $billing_address_2		= '';
	public $billing_address_3		= '';
	public $billing_address_4		= '';
	public $billing_address_5		= '';
	public $billing_postcode		= '';
	public $billing_country			= '';
		
	public $payment_trans_id		= '';
		
	public $customer_name			= '';
	public $customer_email			= '';
		
	public $has_error				= false;
	public $error_message			= '';
	
	public $items					= array();
}