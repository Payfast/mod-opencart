<?php
/**
 * admin/view/template/payment/payfast.tpl
 *
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 * 
 * @author     Ron Darby
 * @version    1.1.1
 */

?>

<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/payment.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><?php echo $entry_merchant_id; ?></td>
            <td><input type="text" name="payfast_merchant_id" value="<?php echo $payfast_merchant_id; ?>" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_merchant_key; ?></td>
            <td><input type="text" name="payfast_merchant_key" value="<?php echo $payfast_merchant_key; ?>" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_sandbox; ?></td>
            <td>
              <input type="radio" name="payfast_sandbox" value="1" <?php if ($payfast_sandbox) { ?> checked="checked" <?php }; ?> />
              <?php echo $text_yes; ?>
              <input type="radio" name="payfast_sandbox" value="0" <?php if (!$payfast_sandbox) { ?> checked="checked" <?php }; ?> />
              <?php echo $text_no; ?>
             </td>
          </tr>
          <tr>
            <td><?php echo $text_debug; ?></td>
            <td>
              <input type="radio" name="payfast_debug" value="1" <?php if ($payfast_debug) { ?> checked="checked" <?php }; ?> />
              <?php echo $text_yes; ?>
              <input type="radio" name="payfast_debug" value="0" <?php if (!$payfast_debug) { ?> checked="checked" <?php }; ?> />
              <?php echo $text_no; ?>
             </td>
          </tr>
          <tr>
            <td><?php echo $entry_passphrase; ?>
            <span style="font-weight: bold;font-size:10px;"><?php echo $entry_passphrase_info; ?></span></td>
            <td><input type="text" name="payfast_passphrase" value="<?php echo $payfast_passphrase; ?>" /></td>
          </tr>         
          <tr>
            <td><?php echo $entry_total; ?></td>
            <td><input type="text" name="payfast_total" value="<?php echo $payfast_total; ?>" /></td>
          </tr>          
         
          <tr>
            <td><?php echo $entry_completed_status; ?></td>
            <td><select name="payfast_completed_status_id">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $payfast_completed_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
         
         
          <tr>
            <td><?php echo $entry_failed_status; ?></td>
            <td><select name="payfast_failed_status_id">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $payfast_failed_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_cancelled_status; ?></td>
            <td><select name="payfast_cancelled_status_id">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $payfast_cancelled_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
         
          
         
         
          <tr>
            <td><?php echo $entry_geo_zone; ?></td>
            <td><select name="payfast_geo_zone_id">
                <option value="0"><?php echo $text_all_zones; ?></option>
                <?php foreach ($geo_zones as $geo_zone) { ?>
                <?php if ($geo_zone['geo_zone_id'] == $payfast_geo_zone_id) { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_status; ?></td>
            <td><select name="payfast_status">
                <?php if ($payfast_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_sort_order; ?></td>
            <td><input type="text" name="payfast_sort_order" value="<?php echo $payfast_sort_order; ?>" size="1" /></td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?> 