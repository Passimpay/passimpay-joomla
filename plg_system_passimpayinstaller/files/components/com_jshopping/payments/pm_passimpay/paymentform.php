<?php
/**
* @version      4.3.1 13.08.2013
* @author       MAXXmarketing GmbH / passimpay
* @package      Jshopping
*/
defined('_JEXEC') or die('Restricted access');

$lang = JFactory::getLanguage();
$lang->load('com_jshopping_pm_passimpay', dirname(__FILE__), null, true);

$payment_type = isset($pmconfigs['payment_type']) ? (int) $pmconfigs['payment_type'] : 0;
$img_dir = dirname(__FILE__) . '/img/';
$base_url = JUri::root() . 'components/com_jshopping/payments/pm_passimpay/img/';
$logos = array(0 => 'logo.svg', 1 => 'logo_crypto.svg', 2 => 'logo_card.svg');
$logo_file = isset($logos[$payment_type]) ? $logos[$payment_type] : 'logo.svg';
if (!file_exists($img_dir . $logo_file)) {
    $logo_file = 'logo.svg';
}
if (!file_exists($img_dir . $logo_file)) {
    $logo_url = '';
} else {
    $logo_url = $base_url . $logo_file;
}
?>
<div class="pm_passimpay_checkout_info" style="margin: 10px 0;">
    <?php if ($logo_url): ?>
        <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Passimpay" style="max-height: 36px; margin-bottom: 8px;" />
    <?php endif; ?>
    <p class="pm_passimpay_redirect_text" style="margin: 0; color: #666; font-size: 0.95em;">
        <?php echo JText::_('COM_JSHOP_PASSIMPAY_REDIRECT_NOTICE'); ?>
    </p>
</div>
<script type="text/javascript">
function check_pm_paypal(){
    jQuery('#payment_form').submit();
}
</script>
