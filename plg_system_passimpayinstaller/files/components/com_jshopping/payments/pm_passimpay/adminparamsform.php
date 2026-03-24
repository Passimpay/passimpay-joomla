<?php
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$lang = Factory::getLanguage();
$lang->load('com_jshopping_pm_passimpay', __DIR__, null, true);
?>
<div class="col100">
<fieldset class="adminform">
<table class="admintable" width="100%">
  <tr>
    <td class="key">API KEY</td>
    <td><input type="text" class="inputbox" name="pm_params[api_key]" value="<?php echo htmlspecialchars($params['api_key']); ?>" /></td>
  </tr>
  <tr>
    <td class="key">Platform ID</td>
    <td><input type="text" class="inputbox" name="pm_params[platform_id]" value="<?php echo htmlspecialchars($params['platform_id']); ?>" /></td>
  </tr>
  <tr>
    <td class="key"><?php echo Text::_('COM_JSHOP_PASSIMPAY_PAYMENT_TYPE'); ?></td>
    <td>
      <select name="pm_params[payment_type]" class="inputbox" id="pm_passimpay_payment_type">
        <option value="0"<?php echo (isset($params['payment_type']) && (int)$params['payment_type'] === 0) ? ' selected="selected"' : ''; ?>><?php echo Text::_('COM_JSHOP_PASSIMPAY_PAYMENT_TYPE_BOTH'); ?></option>
        <option value="1"<?php echo (isset($params['payment_type']) && (int)$params['payment_type'] === 1) ? ' selected="selected"' : ''; ?>><?php echo Text::_('COM_JSHOP_PASSIMPAY_PAYMENT_TYPE_CRYPTO'); ?></option>
        <option value="2"<?php echo (isset($params['payment_type']) && (int)$params['payment_type'] === 2) ? ' selected="selected"' : ''; ?>><?php echo Text::_('COM_JSHOP_PASSIMPAY_PAYMENT_TYPE_CARD'); ?></option>
      </select>
      <div class="com_jshop_passimpay_card_notice" style="margin-top:8px;padding:10px 12px;background:#fff8e5;border-left:4px solid #f0ad4e;color:#856404;<?php echo (isset($params['payment_type']) && (int)$params['payment_type'] !== 1) ? '' : 'display:none;'; ?>">&#9432; <?php echo Text::_('COM_JSHOP_PASSIMPAY_CARD_NOTICE'); ?></div>
    </td>
  </tr>
  <tr>
    <td class="key">URL notifications</td>
    <td><?php echo JURI::root()."index.php?option=com_jshopping&controller=checkout&task=step7&act=notify&js_paymentclass=pm_passimpay&no_lang=1"; ?></td>
  </tr>
  <tr>
    <td class="key">URL return</td>
    <td><?php echo JURI::root()."index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_passimpay"; ?></td>
  </tr>

  <tr>
    <td class="key"><?php echo Text::_('COM_JSHOP_PASSIMPAY_TRANSACTION_END'); ?></td>
    <td>
      <?php
        echo JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_end_status]', 'class="inputbox" size="1"', 'status_id', 'name', $params['transaction_end_status']);
        echo ' ' . JHTML::tooltip(Text::_('COM_JSHOP_PASSIMPAY_TRANSACTION_END_DESC'));
      ?>
    </td>
  </tr>

  <tr>
    <td class="key"><?php echo Text::_('COM_JSHOP_PASSIMPAY_TRANSACTION_PENDING'); ?></td>
    <td>
      <?php
        echo JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_pending_status]', 'class="inputbox" size="1"', 'status_id', 'name', $params['transaction_pending_status']);
        echo ' ' . JHTML::tooltip(Text::_('COM_JSHOP_PASSIMPAY_TRANSACTION_PENDING_DESC'));
      ?>
    </td>
  </tr>

  <tr>
    <td class="key"><?php echo Text::_('COM_JSHOP_PASSIMPAY_TRANSACTION_FAILED'); ?></td>
    <td>
      <?php
        echo JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_failed_status]', 'class="inputbox" size="1"', 'status_id', 'name', $params['transaction_failed_status']);
        echo ' ' . JHTML::tooltip(Text::_('COM_JSHOP_PASSIMPAY_TRANSACTION_FAILED_DESC'));
      ?>
    </td>
  </tr>
</table>
</fieldset>
</div>
<div class="clr"></div>
<script>
(function(){
  var sel = document.getElementById('pm_passimpay_payment_type');
  var notice = document.querySelector('.com_jshop_passimpay_card_notice');
  if (sel && notice) {
    sel.addEventListener('change', function(){
      var v = parseInt(this.value, 10);
      notice.style.display = (v === 0 || v === 2) ? 'block' : 'none';
    });
  }
})();
</script>
