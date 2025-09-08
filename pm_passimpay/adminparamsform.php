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