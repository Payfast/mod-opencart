<?php

/**
 * Copyright (c) 2024 Payfast (Pty) Ltd
 * You (being anyone who is not Payfast (Pty) Ltd) may download and use this plugin / code in your own website in
 * conjunction with a registered and active Payfast account. If your Payfast account is terminated for any reason,
 * you may not use this plugin / code or part thereof. Except as expressly indicated in this licence, you may not use,
 * copy, modify or distribute this plugin / code or part thereof in any way.
 */

// Heading
$_['heading_title'] = 'Payfast';

// Text
$_['text_extension'] = 'Extensions';
$_['text_success']   = 'Success: You have modified your Payfast configuration details!';
$_['text_payfast']   = '<a target="_BLANK" href="https://payfast.io"><img
                                        src="/extension/payfast/admin/view/image/payment/payfast.png"
                                        alt="Payfast" border="0" /></a>';
$_['text_debug']     = 'Debug';
$_['text_edit']      = 'Edit Payfast';

// Entry
$_['entry_payfast_merchant_id']  = 'Payfast Merchant ID:';
$_['entry_payfast_merchant_key'] = 'Payfast Merchant Key:';
$_['entry_payfast_sandbox']      = 'Sandbox Mode:';
$_['entry_payfast_debug']        = 'Debug:';
$_['entry_payfast_passphrase']   = 'Payfast Secure Passphrase:';

$_['entry_geo_zone']   = 'Geo Zone:';
$_['entry_status']     = 'Status:';
$_['entry_sort_order'] = 'Sort Order:';

// Order Status Tab
$_['entry_completed_status'] = 'Payment Completed Status:';
$_['entry_failed_status']    = 'Payment Failed Status:';
$_['entry_cancelled_status'] = 'Payment Cancelled Status:';

// Help
$_['help_payfast_passphrase']   = 'Only insert a value for passphrase if you have
                                        set a passphrase in your Payfast account!';
$_['help_payfast_merchant_id']  = 'Use Sandbox credentials when Sandbox Mode is set to Yes,
                                        else use your Payfast account credentials';
$_['help_payfast_merchant_key'] = 'Use Sandbox credentials when Sandbox Mode is set to Yes,
                                        else use your Payfast account credentials';
$_['help_payfast_debug']        = 'Enable this to turn debug logging on';

// Tab
$_['tab_general'] = 'General';
$_['tab_status']  = 'Order status';

// Error
$_['error_permission']           = 'Warning: You do not have permission to modify the Payfast payment!';
$_['error_payfast_merchant_id']  = 'Merchant ID required!';
$_['error_payfast_merchant_key'] = 'Merchant Key required!';
