<?php
$db = JFactory::getDbo();

$config = "api_key=111-222-333-444-555\nplatform_id=0\ntransaction_end_status=6\ntransaction_pending_status=1\ntransaction_failed_status=3";

$data = array(
    'payment_code'=> 'passimpay',
    'payment_class'=> 'pm_passimpay',
    'scriptname'=> 'pm_passimpay',
    'payment_type'=> 2,
    'payment_params' => $config    
);

$db->setQuery('SELECT language FROM `#__jshopping_languages`');

foreach($db->loadObjectList() as $language)
{
    $data["name_".$language->language] = 'Passimpay';    
}

$addon = JTable::getInstance('addon', 'jshop');

$addon->installPayment($data);

$addon->loadAlias('addon_pm_passimpay');
$addon->set('name', 'Passimpay');
$addon->set('version', '1.0.0');   
$addon->store();
