<?php
/**
 * admin/language/english/payfast.php
 *
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 * 
 * @author     Ron Darby
 * @version    1.1.1
 */
// Heading
$_['heading_title']                  = 'PayFast.co.za';

// Text
$_['text_payment']                   = 'Payment';
$_['text_success']                   = 'Success!';
$_['text_payfast']                   = '<a href="https://www.payfast.co.za" ><img src="' . HTTP_SERVER . 'view/image/payment/payfast.png" border="0" /></a>';
$_['text_debug']                     = 'Debug'; 
// Entry

$_['entry_sandbox']                  = 'Sandbox Mode:';
$_['entry_total']                    = 'Total:<br /><span class="help">The checkout total the order must reach before this payment method becomes active.</span>';
$_['entry_completed_status']         = 'Payment Completed Status:';
$_['entry_failed_status']            = 'Payment Failed Status:';
$_['entry_cancelled_status']         = 'Payment Cancelled Status:';
$_['entry_geo_zone']                 = 'Geo Zone:';
$_['entry_status']                   = 'Status:';
$_['entry_sort_order']               = 'Sort Order:';
$_['entry_merchant_id']              = 'PayFast Merchant ID:';
$_['entry_merchant_key']             = 'PayFast Merchant Key:';
$_['entry_passphrase']             = 'PayFast Secure Passphrase:';
$_['entry_passphrase_info']             = 'ONLY INSERT A VALUE INTO THE SECURE PASSPHRASE IF YOU HAVE SET THIS ON THE INTEGRATION PAGE OF THE LOGGED IN AREA OF THE PAYFAST WEBSITE!!!!!';


// Error
$_['error_permission']               = 'Warning: You do not have permission to modify the PayFast payment!';

?>