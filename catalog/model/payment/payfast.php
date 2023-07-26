<?php

/**
 * Copyright (c) 2023 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in
 * conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason,
 * you may not use this plugin / code or part thereof. Except as expressly indicated in this licence, you may not use,
 * copy, modify or distribute this plugin / code or part thereof in any way.
 */

namespace Opencart\Catalog\Model\Extension\Payfast\Payment;

use Opencart\System\Engine\Model;

class PayFast extends Model
{
    public function getMethod($address, $total = null)
    {
        $this->load->language('extension/payfast/payment/payfast');

        /** @noinspection PhpUndefinedConstantInspection */
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX .
                                  "zone_to_geo_zone WHERE geo_zone_id = '" .
                                  (int)$this->config->get('payment_payfast_geo_zone_id') .
                                  "' AND country_id = '" . (int)$address['country_id'] . "' AND ( zone_id = '" .
                                  (int)$address['zone_id'] . "' OR zone_id = '0' )");

        if (!$this->config->get('payment_payfast_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $currencies = array('ZAR');

        if (!in_array(strtoupper($this->session->data['currency']), $currencies)) {
            $status = false;
        }

        $methodData = array();

        if ($status) {
            $methodData = array(
                'code'       => 'payfast',
                'title'      => $this->language->get('text_pay_method'),
                'terms'      => '',
                'sort_order' => $this->config->get('payment_payfast_sort_order')
            );
        }

        return $methodData;
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
