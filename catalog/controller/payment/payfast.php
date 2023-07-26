<?php

/**
 * Copyright (c) 2023 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in
 * conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason,
 * you may not use this plugin / code or part thereof. Except as expressly indicated in this licence, you may not use,
 * copy, modify or distribute this plugin / code or part thereof in any way.
 */

namespace Opencart\Catalog\Controller\Extension\Payfast\Payment;

use Opencart\System\Engine\Controller;
use Opencart\System\Library\Cart\Customer;

include_once("payfast_common.inc");

class PayFast extends Controller
{
    public string $pfHost = '';
    public const CHECKOUT_ORDER_LITERAL = 'checkout/order';
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->pfHost = ($this->config->get('payment_payfast_sandbox') ? 'sandbox' : 'www') . '.payfast.co.za';
    }

    public function index()
    {
        $this->load->language('extension/payfast/payment/payfast');
        $data[ 'text_sandbox' ] = $this->language->get('text_sandbox');
        $data[ 'button_confirm' ] = $this->language->get('button_confirm');
        $data[ 'sandbox' ] = $this->config->get('payment_payfast_sandbox');
        $data[ 'action' ] = 'https://' . $this->pfHost . '/eng/process';
        $this->load->model(self::CHECKOUT_ORDER_LITERAL);
        $orderInfo = $this->model_checkout_order->getOrder($this->session->data[ 'order_id' ]);
        if ($orderInfo) {
            $orderInfo['currency_code'] = 'ZAR';
            $data['recurring'] = false;
            foreach ($this->cart->getProducts() as $product) {
                if ($product['recurring'] ?? false) {
                    $data['recurring'] = true;
                    if ($product['recurring']['frequency'] == 'month') {
                        $frequency = 3;
                    }

                    if ($product['recurring']['frequency'] == 'year') {
                        $frequency = 6;
                    }

                    $cycles = $product['recurring']['duration'];
                    $recurringAmount = number_format($product['recurring']['price'], 2, '.', '')/100;
                    $customStr3 = $product['recurring']['recurring_id'];
                    $customStr4 = $this->session->data[ 'order_id' ];
                    $customStr5 = $product['product_id'];
                    /** @noinspection PhpUndefinedConstantInspection */
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring` SET `order_id` = '" .
                                      $this->session->data[ 'order_id' ] . "', `reference` = '" .
                                      $this->session->data[ 'order_id' ] . "',
                                      `product_id` = '" . $product['product_id'] . "',
                                      `product_name` = '" . $product['name'] . "', `product_quantity` = '" .
                                      $product['quantity'] . "', `recurring_id` = '" .
                                      $product['recurring']['recurring_id'] . "',
                                      `recurring_name` = '" . $product['recurring']['name'] .
                                      "', `recurring_description` = '" . $product['recurring']['name'] . "',
                                      `recurring_frequency` = '" . $frequency . "', `recurring_cycle` = '1',
                                       `recurring_duration` = '" . $cycles . "',
                                      `recurring_price` = '" . $recurringAmount . "', `status` = '6',
                                       `date_added` = NOW()");
                }
            }

            $merchantId = $this->config->get('payment_payfast_merchant_id');
            $merchantKey = $this->config->get('payment_payfast_merchant_key');
            $passphrase = $this->config->get('payment_payfast_passphrase');
            $returnUrl = $this->url->link('checkout/success');
            $cancelUrl = $this->url->link('checkout/checkout', '', 'SSL');
            $notifyUrl = filter_var(
                $this->url->link('extension/payfast/payment/payfast|callback', '', true),
                FILTER_SANITIZE_URL
            );
            $nameFirst = html_entity_decode($orderInfo[ 'payment_firstname' ], ENT_QUOTES, 'UTF-8');
            $nameLast = html_entity_decode($orderInfo[ 'payment_lastname' ], ENT_QUOTES, 'UTF-8');
            $emailAddress = $orderInfo[ 'email' ];
            $mPaymentId = $this->session->data[ 'order_id' ];
            $amount = filter_var(number_format($orderInfo['total'], 2), FILTER_SANITIZE_NUMBER_INT)/100;
            $itemName = $this->config->get('config_name') . ' - #' . $this->session->data[ 'order_id' ];
            $itemDescription = $this->language->get('text_sale_description');
            $customStr1 = constant('PF_MODULE_NAME') . '_' . constant('PF_SOFTWARE_VER') .
                          '_' . constant('PF_MODULE_VER');
            $payArray = array(
                'merchant_id' => $merchantId, 'merchant_key' => $merchantKey, 'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl, 'notify_url' => $notifyUrl, 'name_first' => $nameFirst,
                'name_last' => $nameLast, 'email_address' => $emailAddress, 'm_payment_id' => $mPaymentId,
                'amount' => $amount, 'item_name' => html_entity_decode($itemName),
                'item_description' => html_entity_decode($itemDescription), 'custom_str1' => $customStr1
            );
            if ($data['recurring']) {
                $payArray['custom_str2'] = date('Y-m-d');
                $payArray['custom_str3'] = $customStr3 ?? '';
                $payArray['custom_str4'] = $customStr4 ?? '';
                $payArray['custom_str5'] = $customStr5 ?? '';
                $payArray['subscription_type'] = '1';
                $payArray['billing_date'] = date('Y-m-d');
                $payArray['recurring_amount'] = $recurringAmount ?? '';
                $payArray['frequency'] = $frequency ?? '';
                $payArray['cycles'] = $cycles ?? '';
            }

            $secureString = '';
            foreach ($payArray as $k => $v) {
                $secureString .= $k . '=' . urlencode(trim($v)) . '&';
                $data[ $k ] = $v;
            }

            if (!empty($passphrase)) {
                $secureString = $secureString . 'passphrase=' . urlencode($passphrase);
            } else {
                $secureString = substr($secureString, 0, -1);
            }

            $securityHash = md5($secureString);
            $data[ 'signature' ] = $securityHash;
            $data[ 'user_agent' ] = 'OpenCart 4.0';


            /** @noinspection PhpUndefinedConstantInspection */
            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') .
                '/template/extension/payfast/payment/payfast')) {
                return $this->load->view(
                    $this->config->get('config_template') . '/template/extension/payfast/payment/payfast',
                    $data
                );
            } else {
                return $this->load->view('extension/payfast/payment/payfast', $data);
            }
        }
    }

    /**
     * callback
     *
     * ITN callback handler
     *
     * @date 07/08/2017
     * @version 2.0.0
     * @access public
     *
     * @author  PayFast
     *
     */
    public function callback()
    {
        if ($this->config->get('payment_payfast_debug')) {
            $debug = true;
        } else {
            $debug = false;
        }
        define('PF_DEBUG', $debug);
        $pfError = false;
        $pfErrMsg = '';
        $pfDone = false;
        $pfData = array();
        $pfParamString = '';
        $orderId = $this->request->post['m_payment_id'] ?? 0;
        pflog('PayFast ITN call received');

        //// Notify PayFast that information has been received
        header('HTTP/1.0 200 OK');
        flush();


        //// Get data sent by PayFast
        pflog('Get posted data');

        // Posted variables from ITN
        $pfData = pfGetData();
        $pfData[ 'item_name' ] = html_entity_decode($pfData[ 'item_name' ]);
        $pfData[ 'item_description' ] = html_entity_decode($pfData[ 'item_description' ]);
        pflog('PayFast Data: ' . print_r($pfData, true));
        if ($pfData === false) {
            $pfError = true;
            $pfErrMsg = PF_ERR_BAD_ACCESS;
        }

        //// Verify security signature
        if (!$pfError && !$pfDone) {
            pflog('Verify security signature');
            $passphrase = empty($this->config->get('payment_payfast_passphrase')) ? null
                                : $this->config->get('payment_payfast_passphrase');
            if (!empty($passphrase) || $this->config->get('payment_payfast_sandbox')) {
                $pfPassphrase = $passphrase;
            } else {
                $pfPassphrase = null;
            }

            // If signature different, log for debugging
            if (!pfValidSignature($pfData, $pfParamString, $pfPassphrase)) {
                $pfError = true;
                $pfErrMsg = PF_ERR_INVALID_SIGNATURE;
            }
        }

        //// Verify source IP (If not in debug mode)
        if (!$pfError && !$pfDone && !PF_DEBUG) {
            pflog('Verify source IP');
            if (!pfValidIP($_SERVER[ 'REMOTE_ADDR' ])) {
                $pfError = true;
                $pfErrMsg = PF_ERR_BAD_SOURCE_IP;
            }
        }
        //// Get internal cart
        if (!$pfError && !$pfDone) {
            // Get order data
            $this->load->model(self::CHECKOUT_ORDER_LITERAL);
            $orderInfo = $this->model_checkout_order->getOrder($orderId);
            pflog("Purchase:\n" . print_r($orderInfo, true));
        }

        //// Verify data received
        if (!$pfError) {
            pflog('Verify data received');
            $pfValid = pfValidData($this->pfHost, $pfParamString);
            if (!$pfValid) {
                $pfError = true;
                $pfErrMsg = PF_ERR_BAD_ACCESS;
            }
        }

        //// Check data against internal order
        if (!$pfError && !$pfDone) {
            pflog('Check data against internal order');
            if (empty($pfData['token']) || strtotime($pfData['custom_str2']) <=
                                           strtotime(gmdate('Y-m-d') . '+ 2 days')) {
                $amount = filter_var(number_format($orderInfo['total'], 2), FILTER_SANITIZE_NUMBER_INT)/100;
            }

            if (!empty($pfData['token']) && strtotime($pfData['custom_str2'])
                                            > strtotime(gmdate('Y-m-d') . '+ 2 days')) {
                $amount = filter_var(number_format($orderInfo['total'], 2), FILTER_SANITIZE_NUMBER_INT)/100;
            }

            // Check order amount
            if (!pfAmountsEqual($pfData[ 'amount_gross' ], $amount)) {
                $pfError = true;
                $pfErrMsg = PF_ERR_AMOUNT_MISMATCH;
            }
        }

        //// Check status and update order
        if (!$pfError && !$pfDone) {
            pflog('Check status and update order');
            if (empty($pfData['token'])) {
                switch ($pfData['payment_status']) {
                    case 'COMPLETE':
                        pflog('- Complete');
                        // Update the purchase status
                        $orderStatusId = $this->config->get('payment_payfast_completed_status_id');


                        break;
                    case 'FAILED':
                        pflog('- Failed');
                        // If payment fails, delete the purchase log
                        $orderStatusId = $this->config->get('payment_payfast_failed_status_id');


                        break;
                    case 'PENDING':
                        pflog('- Pending');
                        // Need to wait for "Completed" before processing

                        break;
                    default:
                        // If unknown status, do nothing (safest course of action)


                        break;
                }

                $this->model_checkout_order->addHistory($orderId, $orderStatusId, '', true);
                return true;
            }

            if (isset($pfData['token']) && $pfData['payment_status'] == 'COMPLETE') {
                $recurring = $this->getOrderRecurringByReference($pfData['m_payment_id']);
                /** @noinspection PhpUndefinedConstantInspection */
                $this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_transaction`
                                  SET `order_recurring_id` = '" . $recurring['order_recurring_id'] . "',
                                   `date_added` = NOW(), `amount` = '" . $pfData['amount_gross'] . "', `type` = '1'");
                //update recurring order status to active
                /** @noinspection PhpUndefinedConstantInspection */
                $this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `status` = 1 WHERE `order_id` = '" .
                                  $pfData['custom_str4'] . "' AND `product_id` = '" . $pfData['custom_str5'] . "'");
                $orderStatusId = $this->config->get('payment_payfast_completed_status_id');
                $this->model_checkout_order->addHistory($orderId, $orderStatusId, '', true);
                return true;
            }
        } else {
            $this->model_checkout_order->addHistory($orderId, $this->config->get('config_order_status_id'), '', true);
            pflog("Errors:\n" . print_r($pfErrMsg, true));
            return false;
        }

        if ($pfData['payment_status'] == 'CANCELLED') {
            $recurring = $this->getOrderRecurringByReference($pfData['m_payment_id']);

            /** @noinspection PhpUndefinedConstantInspection */
            $this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_transaction` SET `order_recurring_id`
                              = '" . $recurring['order_recurring_id'] . "', `date_added` = NOW(), `type` = '5'");

            //update recurring order status to cancelled
            /** @noinspection PhpUndefinedConstantInspection */
            $this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `status` = 3 WHERE `order_recurring_id`
                              = '" . $recurring['order_recurring_id'] . "' LIMIT 1");
        }
    }

    public function getOrderRecurringByReference($reference)
    {
        /** @noinspection PhpUndefinedConstantInspection */
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_recurring` WHERE `reference`
                                   = '" . $this->db->escape($reference) . "'");

        return $query->row;
    }
}
