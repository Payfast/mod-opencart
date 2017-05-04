<?php 
/**
 * catalog/model/payment/payfast.php
 *
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 * 
 * @author     Ron Darby
 * @version    1.1.1
 */

class ModelPaymentPayFast extends Model {
    public function getMethod($address, $total) {
        $this->load->language('payment/payfast');
        
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payfast_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
        
        if ($this->config->get('payfast_total') > $total) {
            $status = false;
        } elseif (!$this->config->get('payfast_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }   
        $this->load->model('localisation/currency');

        $supportedCurrencies = $this->model_localisation_currency->getCurrencies();

        $currencies = array_keys($supportedCurrencies);
        
        if (!in_array(strtoupper($this->currency->getCode()), $currencies)) {
            $status = false;
        }           
                    
        $method_data = array();
    
        if ($status) {  
            $method_data = array( 
                'code'       => 'payfast',
                'title'      => $this->language->get('text_pay_method').$this->language->get('text_logo'),
                'sort_order' => $this->config->get('payfast_sort_order')
            );
        }
   
        return $method_data;
    }
}
?>