<?php

namespace Opencart\Catalog\Controller\Extension\Payfast\Payment;

use Opencart\System\Engine\Controller;
use Payfast\PayfastCommon\Aggregator\Request\PaymentRequest as PayfastRequest;

require_once __DIR__ . '/vendor/autoload.php';


/**
 * Payfast class
 *
 * @property mixed|object|null $config
 * @property mixed|object|null $load
 * @property mixed|object|null $language
 * @property mixed|object|null $model_checkout_order
 * @property mixed|object|null $session
 * @property mixed|object|null $cart
 * @property mixed|object|null $db
 * @property mixed|object|null $url
 */
class Payfast extends Controller
{
    public string $pfHost = '';
    public const CHECKOUT_ORDER_LITERAL = 'checkout/order';
    public string $softwareName = '';
    public string $softwareVer = '';
    public string $moduleVer = '';
    public string $softwareModuleName = '';

    /**
     * @param $registry
     */
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->pfHost             = ($this->config->get(
                'payment_payfast_sandbox'
            ) ? 'sandbox' : 'www') . '.payfast.co.za';
        $this->softwareName       = 'OpenCart';
        $this->softwareVer        = '4.0.2.3';
        $this->moduleVer          = '1.2.0';
        $this->softwareModuleName = 'PF_OpenCart';
    }

    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $this->load->language('extension/payfast/payment/payfast');
        $payfast_data                   = [];
        $payfast_data['text_sandbox']   = $this->language->get('text_sandbox');
        $payfast_data['button_confirm'] = $this->language->get('button_confirm');
        $payfast_data['sandbox']        = $this->config->get('payment_payfast_sandbox');
        $payfast_data['action']         = 'https://' . $this->pfHost . '/eng/process';
        $this->load->model(self::CHECKOUT_ORDER_LITERAL);
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $load_view  = '';
        if ($order_info) {
            $order_info['currency_code'] = 'ZAR';
            $payfast_data['recurring']   = false;

            $passphrase = $this->config->get('payment_payfast_passphrase');
            $pay_array  = $this->handleRecurringP($order_info, $payfast_data);

            $secure_string = '';
            foreach ($pay_array as $pay_array_key => $value) {
                $secure_string                .= $pay_array_key . '=' . urlencode(trim($value)) . '&';
                $payfast_data[$pay_array_key] = $value;
            }

            if (!empty($passphrase)) {
                $secure_string = $secure_string . 'passphrase=' . urlencode($passphrase);
            } else {
                $secure_string = substr($secure_string, 0, -1);
            }

            $security_hash              = md5($secure_string);
            $payfast_data['signature']  = $security_hash;
            $payfast_data['user_agent'] = 'OpenCart 4.0';

            if (file_exists(
                DIR_TEMPLATE . $this->config->get('config_template') .
                '/template/extension/payfast/payment/payfast'
            )) {
                $load_view = $this->load->view(
                    $this->config->get('config_template') . '/template/extension/payfast/payment/payfast',
                    $payfast_data
                );
            } else {
                $load_view = $this->load->view('extension/payfast/payment/payfast', $payfast_data);
            }
        }

        return $load_view;
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
     * @author  Payfast
     *
     */
    public function callback(): void
    {
        $debug = $this->initializeDebug();

        define('PF_DEBUG', $debug);
        $pf_error   = false;
        $pf_err_msg = '';
        $order_id   = $this->request->post['m_payment_id'] ?? 0;

        $payfastRequest = new PayfastRequest($debug);
        $payfastRequest->pflog('Payfast ITN call received');

        // Notify Payfast that information has been received
        header('HTTP/1.0 200 OK');
        flush();


        // Get data sent by Payfast
        $payfastRequest->pflog('Get posted data');

        // Posted variables from ITN
        $pf_data                     = $payfastRequest->pfGetData();
        $pf_data['item_name']        = html_entity_decode($pf_data['item_name']);
        $pf_data['item_description'] = html_entity_decode($pf_data['item_description']);
        $payfastRequest->pflog('Payfast Data: ' . print_r($pf_data, true));
        if ($pf_data === false) {
            $pf_error   = true;
            $pf_err_msg = $payfastRequest::PF_ERR_BAD_ACCESS;
        }

        $pf_verify_data  = $this->verifySignature($pf_error, $pf_data);
        $pf_error        = $pf_verify_data['pf_error'];
        $pf_param_string = $pf_verify_data['pf_param_string'];

        // Get internal cart
        if (!$pf_error) {
            // Get order data
            $this->load->model(self::CHECKOUT_ORDER_LITERAL);
            $order_info = $this->model_checkout_order->getOrder($order_id);
            $payfastRequest->pflog("Purchase:\n" . print_r($order_info, true));
        }

        // Verify data received
        if (!$pf_error) {
            $payfastRequest->pflog('Verify data received');

            $moduleInfo = [
                "pfSoftwareName"       => $this->softwareName,
                "pfSoftwareVer"        => $this->softwareVer,
                "pfSoftwareModuleName" => $this->softwareModuleName,
                "pfModuleVer"          => $this->moduleVer,
            ];


            $pf_valid = $payfastRequest->pfValidData($moduleInfo, $this->pfHost, $pf_param_string);
            if (!$pf_valid) {
                $pf_error   = true;
                $pf_err_msg = $payfastRequest::PF_ERR_BAD_ACCESS;
            }
        }

        // Check data against internal order
        if (!$pf_error) {
            $payfastRequest->pflog('Check data against internal order');
            if (empty($pf_data['token']) || strtotime($pf_data['custom_str2']) <=
                                            strtotime(gmdate('Y-m-d') . '+ 2 days')) {
                $preAmount = $this->currency->format(
                    $order_info['total'],
                    $order_info['currency_code'],
                    $order_info['currency_value'],
                    false
                );
                $amount    = number_format($preAmount, 2, '.', '');
            }

            if (!empty($pf_data['token']) && strtotime($pf_data['custom_str2'])
                                             > strtotime(gmdate('Y-m-d') . '+ 2 days')) {
                $amount = filter_var(number_format($order_info['total'], 2), FILTER_SANITIZE_NUMBER_INT) / 100;
            }

            // Check order amount
            if (!$payfastRequest->pfAmountsEqual($pf_data['amount_gross'], $amount)) {
                $pf_error   = true;
                $pf_err_msg = $payfastRequest::PF_ERR_AMOUNT_MISMATCH;
            }
        }

        $this->updateOrder($pf_error, $pf_data, $order_id, $pf_err_msg);
    }


    /**
     * Initialize debug
     *
     * @return bool
     */
    public function initializeDebug(): bool
    {
        if ($this->config->get('payment_payfast_debug')) {
            $debug = true;
        } else {
            $debug = false;
        }

        return $debug;
    }

    /**
     * Verify the signature from Payfast
     *
     * @param $pf_error
     * @param $pf_data
     *
     * @return array
     */
    public function verifySignature($pf_error, $pf_data): array
    {
        // Verify security signature
        $debug          = $this->initializeDebug();
        $payfastRequest = new PayfastRequest($debug);
        if (!$pf_error) {
            $payfastRequest->pflog('Verify security signature');
            $passphrase = empty($this->config->get('payment_payfast_passphrase')) ? null
                : $this->config->get('payment_payfast_passphrase');
            if (!empty($passphrase) || $this->config->get('payment_payfast_sandbox')) {
                $pf_passphrase = $passphrase;
            } else {
                $pf_passphrase = null;
            }

            // If signature different, log for debugging
            if (!$payfastRequest->pfValidSignature($pf_data, $pf_param_string, $pf_passphrase)) {
                $pf_error   = true;
                $pf_err_msg = $payfastRequest::PF_ERR_INVALID_SIGNATURE;
                $payfastRequest->pflog("Errors:\n" . print_r($pf_err_msg, true));
            }
        }

        return [
            'pf_error'        => $pf_error,
            'pf_param_string' => $pf_param_string
        ];
    }

    /**
     * Update the order status
     *
     * @param $pf_error
     * @param $pf_data
     * @param $order_id
     * @param $pf_err_msg
     *
     * @return bool|void
     */
    public function updateOrder($pf_error, $pf_data, $order_id, $pf_err_msg)
    {
        // Check status and update order
        $debug          = $this->initializeDebug();
        $payfastRequest = new PayfastRequest($debug);

        if (!$pf_error) {
            $payfastRequest->pflog('Check status and update order');
            if (empty($pf_data['token'])) {
                switch ($pf_data['payment_status']) {
                    case 'COMPLETE':
                        $payfastRequest->pflog('- Complete');
                        // Update the purchase status
                        $order_status_id = $this->config->get('payment_payfast_completed_status_id');


                        break;
                    case 'FAILED':
                        $payfastRequest->pflog('- Failed');
                        // If payment fails, delete the purchase log
                        $order_status_id = $this->config->get('payment_payfast_failed_status_id');


                        break;
                    case 'PENDING':
                        $payfastRequest->pflog('- Pending');
                        // Need to wait for "Completed" before processing

                        break;
                    default:
                        // If unknown status, do nothing (safest course of action)


                        break;
                }

                $this->model_checkout_order->addHistory($order_id, $order_status_id, '', true);

                return true;
            }

            if ($pf_data['payment_status'] == 'COMPLETE') {
                $recurring = $this->getOrder($pf_data['m_payment_id']);
                $this->db->query(
                    'INSERT INTO `' . DB_PREFIX . "order_recurring_transaction`
                                  SET `order_recurring_id` = '" . $recurring['order_recurring_id'] . "',
                                   `date_added` = NOW(), `amount` = '" . $pf_data['amount_gross'] . "', `type` = '1'"
                );
                //update recurring order status to active
                $this->db->query(
                    'UPDATE `' . DB_PREFIX . "order_recurring` SET `status` = 1 WHERE `order_id` = '" .
                    $pf_data['custom_str4'] . "' AND `product_id` = '" . $pf_data['custom_str5'] . "'"
                );
                $order_status_id = $this->config->get('payment_payfast_completed_status_id');
                $this->model_checkout_order->addHistory($order_id, $order_status_id, '', true);

                return true;
            }
        } else {
            $this->model_checkout_order->addHistory($order_id, $this->config->get('config_order_status_id'), '', true);
            $payfastRequest->pflog("Errors:\n" . print_r($pf_err_msg, true));

            return false;
        }

        if ($pf_data['payment_status'] == 'CANCELLED') {
            $recurring = $this->getOrder($pf_data['m_payment_id']);

            $this->db->query(
                'INSERT INTO `' . DB_PREFIX . "order_recurring_transaction` SET `order_recurring_id`
                              = '" . $recurring['order_recurring_id'] . "', `date_added` = NOW(), `type` = '5'"
            );

            //update recurring order status to cancelled
            $this->db->query(
                'UPDATE `' . DB_PREFIX . "order_recurring` SET `status` = 3 WHERE `order_recurring_id`
                              = '" . $recurring['order_recurring_id'] . "' LIMIT 1"
            );
        }
    }

    /**
     * Get the order recurring reference
     *
     * @param $reference
     *
     * @return mixed
     */
    public function getOrder($reference): mixed
    {
        $query = $this->db->query(
            'SELECT * FROM `' . DB_PREFIX . "order_recurring` WHERE `reference`
                                   = '" . $this->db->escape($reference) . "'"
        );

        return $query->row;
    }

    /**
     *
     * Handle recurring products
     *
     * @param array $order_info
     * @param array $payfast_data
     *
     * @return array
     */
    private function handleRecurringP(array $order_info, array $payfast_data): array
    {
        $recurring_data = [];

        foreach ($this->cart->getProducts() as $product) {
            if ($product['recurring'] ?? false) {
                $payfast_data['recurring'] = true;
                if ($product['recurring']['frequency'] == 'month') {
                    $frequency = 3;
                }

                if ($product['recurring']['frequency'] == 'year') {
                    $frequency = 6;
                }

                $cycles           = $product['recurring']['duration'];
                $recurring_amount = number_format($product['recurring']['price'], 2, '.', '') / 100;
                $custom_str3      = $product['recurring']['recurring_id'];
                $custom_str4      = $this->session->data['order_id'];
                $custom_str5      = $product['product_id'];

                $recurring_data = [
                    'recurring_amount' => $recurring_amount,
                    'custom_str3'      => $custom_str3,
                    'custom_str4'      => $custom_str4,
                    'custom_str5'      => $custom_str5,
                    'cycles'           => $cycles,
                ];
                $this->db->query(
                    'INSERT INTO `' . DB_PREFIX . "order_recurring` SET `order_id` = '" .
                    $this->session->data['order_id'] . "', `reference` = '" .
                    $this->session->data['order_id'] . "',
                                      `product_id` = '" . $product['product_id'] . "',
                                      `product_name` = '" . $product['name'] . "', `product_quantity` = '" .
                    $product['quantity'] . "', `recurring_id` = '" .
                    $product['recurring']['recurring_id'] . "',
                                      `recurring_name` = '" . $product['recurring']['name'] .
                    "', `recurring_description` = '" . $product['recurring']['name'] . "',
                                      `recurring_frequency` = '" . $frequency . "', `recurring_cycle` = '1',
                                       `recurring_duration` = '" . $cycles . "',
                                      `recurring_price` = '" . $recurring_amount . "', `status` = '6',
                                       `date_added` = NOW()"
                );
            }
        }

        return $this->buildPayArray($order_info, $payfast_data, $recurring_data);
    }

    /**
     * Build the payment array
     *
     * @param array $order_info
     * @param array $payfast_data
     * @param array|null $recurring_data
     *
     * @return array
     */
    private function buildPayArray(array $order_info, array $payfast_data, array $recurring_data = null): array
    {
        $merchant_id      = $this->config->get('payment_payfast_merchant_id');
        $merchant_key     = $this->config->get('payment_payfast_merchant_key');
        $return_url       = $this->url->link('checkout/success');
        $cancel_url       = $this->url->link('checkout/checkout', '', 'SSL');
        $notify_url       = filter_var(
            $this->url->link('extension/payfast/payment/payfast|callback', '', true),
            FILTER_SANITIZE_URL
        );
        $name_first       = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
        $name_last        = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
        $email_address    = $order_info['email'];
        $m_payment_id     = $this->session->data['order_id'];
        $preAmount        = $this->currency->format(
            $order_info['total'],
            $order_info['currency_code'],
            $order_info['currency_value'],
            false
        );
        $amount           = number_format($preAmount, 2, '.', '');
        $item_name        = $this->config->get('config_name') . ' - #' . $this->session->data['order_id'];
        $item_description = $this->language->get('text_sale_description');
        $custom_str1      = $this->softwareName . '_' . $this->softwareVer .
                            '_' . $this->moduleVer;
        $pay_array        = [
            'merchant_id'      => $merchant_id,
            'merchant_key'     => $merchant_key,
            'return_url'       => $return_url,
            'cancel_url'       => $cancel_url,
            'notify_url'       => $notify_url,
            'name_first'       => $name_first,
            'name_last'        => $name_last,
            'email_address'    => $email_address,
            'm_payment_id'     => $m_payment_id,
            'amount'           => $amount,
            'item_name'        => html_entity_decode($item_name),
            'item_description' => html_entity_decode($item_description),
            'custom_str1'      => $custom_str1
        ];

        if ($payfast_data['recurring']) {
            $pay_array['custom_str2']       = date('Y-m-d');
            $pay_array['custom_str3']       = $recurring_data['custom_str3'] ?? '';
            $pay_array['custom_str4']       = $recurring_data['custom_str4'] ?? '';
            $pay_array['custom_str5']       = $recurring_data['custom_str5'] ?? '';
            $pay_array['subscription_type'] = '1';
            $pay_array['billing_date']      = date('Y-m-d');
            $pay_array['recurring_amount']  = $recurring_data['recurring_amount'] ?? '';
            $pay_array['frequency']         = $recurring_data['frequency'] ?? '';
            $pay_array['cycles']            = $recurring_data['cycles'] ?? '';
        }

        return $pay_array;
    }

}
