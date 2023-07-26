<?php

/**
 * Copyright (c) 2023 Payfast (Pty) Ltd
 * You (being anyone who is not Payfast (Pty) Ltd) may download and use this plugin / code in your own website in
 * conjunction with a registered and active Payfast account. If your Payfast account is terminated for any reason,
 * you may not use this plugin / code or part thereof. Except as expressly indicated in this licence, you may not use,
 * copy, modify or distribute this plugin / code or part thereof in any way.
 */

namespace Opencart\Catalog\Model\Extension\Payfast\Payment;

use Opencart\System\Engine\Model;

class Payfast extends Model
{
    public function getMethods($address, $total = null)
    {
        if (!$address) {
            $address['country_id'] = 0;
            $address['zone_id'] = 0;
        }

        $this->load->language('extension/payfast/payment/payfast');

        if ($this->cart->hasSubscription()) {
            $status = false;
        } elseif (!$this->cart->hasShipping()) {
            $status = false;
        } elseif (!$this->config->get('config_checkout_payment_address')) {
            $status = true;
        } elseif (!$this->config->get('payment_payfast_geo_zone_id')) {
            $status = true;
        } else {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX .
                "zone_to_geo_zone WHERE geo_zone_id = '" .
                (int)$this->config->get('payment_payfast_geo_zone_id') .
                "' AND country_id = '" . (int)$address['country_id'] . "' AND ( zone_id = '" .
                (int)$address['zone_id'] . "' OR zone_id = '0' )");

            if ($query->num_rows) {
                $status = true;
            } else {
                $status = false;
            }
        }

        $currencies = array('ZAR');

        if (!in_array(strtoupper($this->session->data['currency']), $currencies)) {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $option_data['payfast'] = [
                'code' => 'payfast.payfast',
                'name' => $this->language->get('text_title')
            ];

            $method_data = [
                'code'       => 'payfast',
                'name'       => $this->language->get('text_title'),
                'option'     => $option_data,
                'sort_order' => $this->config->get('payment_payfast_sort_order')
            ];
        }

        return $method_data;
    }

    public function recurringPayments()
    {
        /*
         * Used by the checkout to state the module
         * supports recurring billing.
         */
        return true;
    }
}
