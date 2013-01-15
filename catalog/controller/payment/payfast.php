<?php
/**
 * catalog/controller/payment/payfast.php
 *
 * Copyright (c) 2009-2012 PayFast (Pty) Ltd
 * 
 * LICENSE:
 * 
 * This payment module is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation; either version 3 of the License, or (at
 * your option) any later version.
 * 
 * This payment module is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
 * License for more details.
 * 
 * @author     Ron Darby
 * @copyright  2009-2012 PayFast (Pty) Ltd
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 */

include('payfast_common.inc');
 
 
class ControllerPaymentPayFast extends Controller {
    var $pfHost = '';  
   function __construct($registry){
        parent::__construct($registry);
        $this->pfHost = ($this->config->get('payfast_sandbox') ? 'sandbox' : 'www') . '.payfast.co.za';
        //$this->pfHost = 'www.payfast.local'; //local testing
    
   }
    
	protected function index() {
	        
	   //session_destroy();
		$this->language->load('payment/payfast');
		
		$this->data['text_sandbox'] = $this->language->get('text_sandbox');		
    	
		$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->data['sandbox'] = $this->config->get('payfast_sandbox');
		
        $this->data['action'] = 'https://'.$this->pfHost.'/eng/process';
		
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if ($order_info) {
		    $secure = '';
            
		    if(!$this->config->get('payfast_sandbox')){
                $this->data['merchant_id'] = $this->config->get('payfast_merchant_id');
                $secure .= 'merchant_id='.urlencode($this->config->get('payfast_merchant_id'));
                $this->data['merchant_key'] = $this->config->get('payfast_merchant_key');
                $secure .= '&merchant_key='.urlencode($this->config->get('payfast_merchant_key'));
            }else{
                $this->data['merchant_id'] = '10000100';
                $secure .= 'merchant_id='.urlencode('10000100');
                $this->data['merchant_key'] = '46f0cd694581a';
                $secure .= '&merchant_key='.urlencode('46f0cd694581a');
            }
            $return = $this->url->link('checkout/success');
            $this->data['return_url'] = $return;
            $secure .= '&return_url='.urlencode($return);
            $cancel = $this->url->link('checkout/checkout');            
			$this->data['cancel_url'] = $cancel;
            $secure .= '&cancel_url='.urlencode($cancel);
            $notify =  $this->url->link('payment/payfast/callback');
			$this->data['notify_url'] = $notify;
            $secure .= '&notify_url='.urlencode($notify);
            
            $name_first = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
            $this->data['name_first'] = $name_first;
            $secure .= '&name_first='.urlencode($name_first);
            $name_last = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
			$this->data['name_last'] =	$name_last;
            $secure .= '&name_last='.urlencode($name_last);
			$this->data['email_address'] = $order_info['email'];
            $secure .= '&email_address='.urlencode($order_info['email']);
            
            $order_id = $this->session->data['order_id'];
			$this->data['m_payment_id'] = $order_id;
            $secure .= '&m_payment_id='.urlencode($order_id);
            $amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
			$this->data['amount'] = $amount;
            $secure .= '&amount='.urlencode($amount);
            $item_name = $this->config->get('config_name') . ' - #' . $this->session->data['order_id'];	
            $this->data['item_name'] = $item_name;
            $secure .= '&item_name='.urlencode($item_name);
            $item_description = $this->language->get('text_sale_description');
			$this->data['item_description'] = $item_description;
            $secure .= '&item_description='.urlencode($item_description);
			$this->data['custom_str1'] = $order_id;
			$secure .= '&custom_str1='.urlencode($order_id);
            
			$securityHash = md5($secure);
            $this->data['signature'] = $securityHash;
		
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/payfast.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/payfast.tpl';
			} else {
				$this->template = 'default/template/payment/payfast.tpl';
			}
	
			$this->render();
		}
	}
	
	public function callback() {
	   
       $pfError = false;
       $pfErrMsg = '';
       $pfDone = false;
       $pfData = array();	   
       $pfParamString = '';
		if (isset($this->request->post['custom_str1'])) {
			$order_id = $this->request->post['custom_str1'];
		} else {
			$order_id = 0;
		}		
		
        
         pflog( 'PayFast ITN call received' );
    
        //// Notify PayFast that information has been received
        if( !$pfError && !$pfDone )
        {
            header( 'HTTP/1.0 200 OK' );
            flush();
        }
    
        //// Get data sent by PayFast
        if( !$pfError && !$pfDone )
        {
            pflog( 'Get posted data' );
        
            // Posted variables from ITN
            $pfData = pfGetData();
        
            pflog( 'PayFast Data: '. print_r( $pfData, true ) );
        
            if( $pfData === false )
            {
                $pfError = true;
                $pfErrMsg = PF_ERR_BAD_ACCESS;
            }
        }
       
        //// Verify security signature
        if( !$pfError && !$pfDone )
        {
            pflog( 'Verify security signature' );
        
            // If signature different, log for debugging
            if( !pfValidSignature( $pfData, $pfParamString ) )
            {
                $pfError = true;
                $pfErrMsg = PF_ERR_INVALID_SIGNATURE;
            }
        }
    
        //// Verify source IP (If not in debug mode)
        if( !$pfError && !$pfDone && !PF_DEBUG )
        {
            pflog( 'Verify source IP' );
        
            if( !pfValidIP( $_SERVER['REMOTE_ADDR'] ) )
            {
                $pfError = true;
                $pfErrMsg = PF_ERR_BAD_SOURCE_IP;
            }
        }
        //// Get internal cart
        if( !$pfError && !$pfDone )
        {
            // Get order data
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($order_id);
    
            pflog( "Purchase:\n". print_r( $order_info, true )  );
        }
        
        //// Verify data received
        if( !$pfError )
        {
            pflog( 'Verify data received' );
        
            $pfValid = pfValidData( $this->pfHost, $pfParamString );
        
            if( !$pfValid )
            {
                $pfError = true;
                $pfErrMsg = PF_ERR_BAD_ACCESS;
            }
        }
        
        //// Check data against internal order
        if( !$pfError && !$pfDone )
        {
           // pflog( 'Check data against internal order' );
    
            // Check order amount
            if( !pfAmountsEqual( $pfData['amount_gross'],$order_info['total'] ) )
            {
                $pfError = true;
                $pfErrMsg = PF_ERR_AMOUNT_MISMATCH;
            }          
            
        }
				
	   //// Check status and update order
        if( !$pfError && !$pfDone )
        {
            pflog( 'Check status and update order' );
    
            
            $transaction_id = $pfData['pf_payment_id'];
    
    		switch( $pfData['payment_status'] )
            {
                case 'COMPLETE':
                    pflog( '- Complete' );
    
                    // Update the purchase status
                    $order_status_id = $this->config->get('payfast_completed_status_id');
                    
                    break;
    
    			case 'FAILED':
                    pflog( '- Failed' );
    
                    // If payment fails, delete the purchase log
                    $order_status_id = $this->config->get('payfast_failed_status_id');
    
        			break;
    
    			case 'PENDING':
                    pflog( '- Pending' );
    
                    // Need to wait for "Completed" before processing
        			break;
    
    			default:
                    // If unknown status, do nothing (safest course of action)
    			break;
            }
             if (!$order_info['order_status_id']) {
				$this->model_checkout_order->confirm($order_id, $order_status_id);
			} else {
				$this->model_checkout_order->update($order_id, $order_status_id);
			}
        }	
	}
}
?>
