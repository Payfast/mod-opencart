<?php

/**
 * Copyright (c) 2024 Payfast (Pty) Ltd
 * You (being anyone who is not Payfast (Pty) Ltd) may download and use this plugin / code in your own website in
 * conjunction with a registered and active Payfast account. If your Payfast account is terminated for any reason,
 * you may not use this plugin / code or part thereof. Except as expressly indicated in this licence, you may not use,
 * copy, modify or distribute this plugin / code or part thereof in any way.
 */

namespace Opencart\Admin\Controller\Extension\Payfast\Payment;

use Opencart\System\Engine\Controller;

/**
 * Payfast class
 *
 * @property mixed|object|null $document
 * @property mixed|object|null $language
 * @property mixed|object|null $load
 * @property mixed|object|null $model_setting_setting
 * @property mixed|object|null $session
 * @property mixed|object|null $response
 * @property mixed|object|null $request
 * @property mixed|object|null $url
 * @property mixed|object|null $config
 * @property mixed|object|null $model_localisation_order_status
 * @property mixed|object|null $model_localisation_geo_zone
 * @property mixed|object|null $db
 * @property mixed|object|null $user
 */
class Payfast extends Controller
{
    private array $error = [];
    private string $tableName = DB_PREFIX . 'payfast_transaction';
    public const LANGUAGE_LITERAL    = 'extension/payfast/payment/payfast';
    public const MARKETPLACE_LITERAL = 'marketplace/extension';
    public const TOKEN_LITERAL       = 'user_token=';
    public const TYPE_LITERAL        = '&type=payment';

    /**
     * Index
     *
     * @return void
     */
    public function index(): void
    {
        $this->load->language(self::LANGUAGE_LITERAL);

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_payfast', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect(
                $this->url->link(
                    self::MARKETPLACE_LITERAL,
                    self::TOKEN_LITERAL . $this->session->data['user_token'] . self::TYPE_LITERAL,
                    true
                )
            );
        }

        if (isset($this->error['warning'])) {
            $data_array['error_warning'] = $this->error['warning'];
        } else {
            $data_array['error_warning'] = '';
        }

        if (isset($this->error['payfast_merchant_id'])) {
            $data_array['error_payfast_merchant_id'] = $this->error['payfast_merchant_id'];
        } else {
            $data_array['error_payfast_merchant_id'] = '';
        }

        if (isset($this->error['payfast_merchant_key'])) {
            $data_array['error_payfast_merchant_key'] = $this->error['payfast_merchant_key'];
        } else {
            $data_array['error_payfast_merchant_key'] = '';
        }

        if (isset($this->error['signature'])) {
            $data_array['error_signature'] = $this->error['signature'];
        } else {
            $data_array['error_signature'] = '';
        }

        $data_array['breadcrumbs'] = [];

        $data_array['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link(
                'common/dashboard',
                self::TOKEN_LITERAL . $this->session->data['user_token'],
                true
            )
        ];

        $data_array['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link(
                self::MARKETPLACE_LITERAL,
                self::TOKEN_LITERAL . $this->session->data['user_token'] . self::TYPE_LITERAL,
                true
            )
        ];

        $data_array['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link(
                self::LANGUAGE_LITERAL,
                self::TOKEN_LITERAL . $this->session->data['user_token'],
                true
            )
        ];

        $data_array['action'] = $this->url->link(
            self::LANGUAGE_LITERAL,
            self::TOKEN_LITERAL . $this->session->data['user_token'],
            true
        );

        $data_array['cancel'] = $this->url->link(
            self::MARKETPLACE_LITERAL,
            self::TOKEN_LITERAL . $this->session->data['user_token'] . self::TYPE_LITERAL,
            true
        );

        $data_array = $this->payfastSettings($data_array);

        $data_array['help_total']    = $this->language->get('help_total');
        $data_array['button_save']   = $this->language->get('button_save');
        $data_array['button_cancel'] = $this->language->get('button_cancel');

        $data_array['header']      = $this->load->controller('common/header');
        $data_array['column_left'] = $this->load->controller('common/column_left');
        $data_array['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view(self::LANGUAGE_LITERAL, $data_array));
    }

    /**
     * Adds the payfast_transaction table to the db
     *
     * @return void
     */
    public function install(): void
    {
        $query = <<<QUERY
create table if not exists $this->tableName (
    payfast_transaction_id int auto_increment primary key,
    customer_id int not null,
    order_id int not null,
    payfast_reference varchar(255) not null,
    payfast_data text null,
    payfast_session text null,
    date_created datetime not null,
    date_modified datetime not null
)
QUERY;

        $this->db->query($query);

        $this->load->model('setting/setting');

        $this->model_setting_setting->editValue('config', 'config_session_samesite', 'Lax');
    }

    /**
     * Deletes the Payfast table from the db
     *
     * @return void
     */
    public function uninstall(): void
    {
        $this->db->query("drop table if exists $this->tableName");
    }

    /**
     * Validates the Payfast merchant_id and merchant_key
     *
     * @return bool
     */
    protected function validate(): bool
    {
        if (!$this->user->hasPermission('modify', self::LANGUAGE_LITERAL)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['payment_payfast_merchant_id']) {
            $this->error['payfast_merchant_id'] = $this->language->get('error_payfast_merchant_id');
        }

        if (!$this->request->post['payment_payfast_merchant_key']) {
            $this->error['payfast_merchant_key'] = $this->language->get('error_payfast_merchant_key');
        }

        return !$this->error;
    }

    /**
     * @param $data_array
     *
     * @return mixed
     */
    private function payfastSettings($data_array): mixed
    {
        if (isset($this->request->post['payment_payfast_merchant_id'])) {
            $data_array['payment_payfast_merchant_id'] = $this->request->post['payment_payfast_merchant_id'];
        } else {
            $data_array['payment_payfast_merchant_id'] = $this->config->get('payment_payfast_merchant_id');
        }

        if (isset($this->request->post['payment_payfast_merchant_key'])) {
            $data_array['payment_payfast_merchant_key'] = $this->request->post['payment_payfast_merchant_key'];
        } else {
            $data_array['payment_payfast_merchant_key'] = $this->config->get('payment_payfast_merchant_key');
        }

        if (isset($this->request->post['payment_payfast_passphrase'])) {
            $data_array['payment_payfast_passphrase'] = $this->request->post['payment_payfast_passphrase'];
        } else {
            $data_array['payment_payfast_passphrase'] = $this->config->get('payment_payfast_passphrase');
        }

        if (isset($this->request->post['payment_payfast_sandbox'])) {
            $data_array['payment_payfast_sandbox'] = $this->request->post['payment_payfast_sandbox'];
        } else {
            $data_array['payment_payfast_sandbox'] = $this->config->get('payment_payfast_sandbox');
        }

        if (isset($this->request->post['payment_payfast_debug'])) {
            $data_array['payment_payfast_debug'] = $this->request->post['payment_payfast_debug'];
        } else {
            $data_array['payment_payfast_debug'] = $this->config->get('payment_payfast_debug');
        }

        return $this->storeSettings($data_array);
    }

    /**
     * Builds the store settings array
     *
     * @param $data_array
     *
     * @return mixed
     */
    private function storeSettings($data_array): mixed
    {
        $this->load->model('localisation/order_status');
        $data_array['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['payment_payfast_completed_status_id'])) {
            $data_array['payment_payfast_completed_status_id'] =
                $this->request->post['payment_payfast_completed_status_id'];
        } else {
            $data_array['payment_payfast_completed_status_id'] =
                $this->config->get('payment_payfast_completed_status_id');
        }

        if (isset($this->request->post['payment_payfast_failed_status_id'])) {
            $data_array['payment_payfast_failed_status_id'] = $this->request->post['payment_payfast_failed_status_id'];
        } else {
            $data_array['payment_payfast_failed_status_id'] = $this->config->get('payment_payfast_failed_status_id');
        }

        if (isset($this->request->post['payment_payfast_cancelled_status_id'])) {
            $data_array['payment_payfast_cancelled_status_id'] =
                $this->request->post['payment_payfast_cancelled_status_id'];
        } else {
            $data_array['payment_payfast_cancelled_status_id'] =
                $this->config->get('payment_payfast_cancelled_status_id');
        }

        if (isset($this->request->post['payment_payfast_geo_zone_id'])) {
            $data_array['payment_payfast_geo_zone_id'] = $this->request->post['payment_payfast_geo_zone_id'];
        } else {
            $data_array['payment_payfast_geo_zone_id'] = $this->config->get('payment_payfast_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data_array['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['payment_payfast_status'])) {
            $data_array['payment_payfast_status'] = $this->request->post['payment_payfast_status'];
        } else {
            $data_array['payment_payfast_status'] = $this->config->get('payment_payfast_status');
        }

        if (isset($this->request->post['payment_payfast_sort_order'])) {
            $data_array['payment_payfast_sort_order'] = $this->request->post['payment_payfast_sort_order'];
        } else {
            $data_array['payment_payfast_sort_order'] = $this->config->get('payment_payfast_sort_order');
        }

        return $data_array;
    }
}
