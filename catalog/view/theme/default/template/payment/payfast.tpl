<?php
/**
 * catalog/view/theme/default/template/payment/payfast.tpl
 *
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 * 
 * @author     Ron Darby
 * @version    1.1.1
 */

?>

<?php if ($sandbox) { ?>
<div class="warning"><?php echo $text_sandbox; ?></div>
<?php } ?>
<form action="<?php echo $action; ?>" method="post">   
  <input type="hidden" name="merchant_id" value="<?php echo $merchant_id; ?>" />
  <input type="hidden" name="merchant_key" value="<?php echo $merchant_key; ?>" /> 
  <input type="hidden" name="amount" value="<?php echo $amount; ?>" />
  <input type="hidden" name="item_name" value="<?php echo $item_name; ?>" />
  <input type="hidden" name="item_description" value="<?php echo $item_description; ?>" />
  <input type="hidden" name="name_first" value="<?php echo $name_first; ?>" />
  <input type="hidden" name="name_last" value="<?php echo $name_last; ?>" />
  <input type="hidden" name="email_address" value="<?php echo $email_address; ?>" />
  <input type="hidden" name="return_url" value="<?php echo $return_url; ?>" />
  <input type="hidden" name="notify_url" value="<?php echo $notify_url; ?>" />
  <input type="hidden" name="cancel_url" value="<?php echo $cancel_url; ?>" />
  <input type="hidden" name="custom_str1" value="<?php echo $custom_str1; ?>" />
  <input type="hidden" name="m_payment_id" value="<?php echo $m_payment_id; ?>" />
  <input type="hidden" name="signature" value="<?php echo $signature; ?>" />
  <div class="buttons">
    <div class="right">
      <input type="submit" value="<?php echo $button_confirm; ?>" class="button" />
    </div>
  </div>
</form>
