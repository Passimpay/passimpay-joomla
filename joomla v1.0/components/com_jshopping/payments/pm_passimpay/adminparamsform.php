<?php
/**
* @version      4.18.1 11.09.2018
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die();
?>
<div class="col100">
<fieldset class="adminform">
<table class="admintable" width = "100%" >
 <tr>
   <td  class="key">API KEY</td>
   <td>
     <input type = "text" class = "inputbox" name = "pm_params[api_key]" value = "<?php echo $params['api_key']?>" />
   </td>
 </tr>
 <tr>
   <td  class="key">Platform ID</td>
   <td>
     <input type = "text" class = "inputbox" name = "pm_params[platform_id]" value = "<?php echo $params['platform_id']?>" />
   </td>
 </tr>
 <tr>
   <td  class="key">URL notifications</td>
   <td>
     <?php echo JURI::root()."index.php?option=com_jshopping&controller=checkout&task=step7&act=notify&js_paymentclass=pm_passimpay&no_lang=1"; ?>
   </td>
 </tr>
 <tr>
   <td  class="key">URL return</td>
   <td>
     <?php echo JURI::root()."index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_passimpay"; ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     <?php echo _JSHOP_TRANSACTION_END;?>
   </td>
   <td>
     <?php              
     print JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_end_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_end_status'] );
     echo " ".JHTML::tooltip(_JSHOP_PAYPAL_TRANSACTION_END_DESCRIPTION);
     ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     <?php echo _JSHOP_TRANSACTION_PENDING;?>
   </td>
   <td>
     <?php 
     echo JHTML::_('select.genericlist',$orders->getAllOrderStatus(), 'pm_params[transaction_pending_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_pending_status']);
     echo " ".JHTML::tooltip(_JSHOP_PAYPAL_TRANSACTION_PENDING_DESCRIPTION);
     ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     <?php echo _JSHOP_TRANSACTION_FAILED;?>
   </td>
   <td>
     <?php 
     echo JHTML::_('select.genericlist',$orders->getAllOrderStatus(), 'pm_params[transaction_failed_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_failed_status']);
     echo " ".JHTML::tooltip(_JSHOP_PAYPAL_TRANSACTION_FAILED_DESCRIPTION);
     ?>
   </td>
 </tr>
</table>
</fieldset>
</div>
<div class="clr"></div>