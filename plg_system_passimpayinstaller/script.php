<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Adapter\PluginAdapter;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\Database\DatabaseInterface;

class plgSystemPassimpayinstallerInstallerScript
{
 
    public function install(PluginAdapter $adapter)
    {
        $app = Factory::getApplication();
        $root = JPATH_SITE;

      
        $src  = __DIR__ . '/files/components/com_jshopping/payments/pm_passimpay';
        $dest = $root   . '/components/com_jshopping/payments/pm_passimpay';

        if (!is_dir($src)) {
            $app->enqueueMessage('Passimpay installer: source files not found', 'error');
            return false;
        }

        if (!is_dir($dest)) {
            Folder::create($dest);
        }
        if (!Folder::copy($src, $dest, '', true)) {
            $app->enqueueMessage('Passimpay installer: unable to copy payment files', 'error');
            return false;
        }

  
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('payment_id'))
            ->from($db->quoteName('#__jshopping_payment_method'))
            ->where($db->quoteName('payment_class') . ' = ' . $db->quote('pm_passimpay'));
        $db->setQuery($query);
        $exists = (int) $db->loadResult();

        if (!$exists) {
           
            $params = "api_key=111-222-333-444-555\nplatform_id=0\ntransaction_end_status=6\ntransaction_pending_status=1\ntransaction_failed_status=3";

            $columns = [
                'payment_code','payment_class','scriptname','payment_type','payment_publish','payment_params'
            ];
            $values  = [
                $db->quote('passimpay'),
                $db->quote('pm_passimpay'),
                $db->quote('pm_passimpay'),
                2, // внешний редирект
                1, // опубликован
                $db->quote($params),
            ];
            $insert = $db->getQuery(true)
                ->insert($db->quoteName('#__jshopping_payment_method'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
            $db->setQuery($insert)->execute();

          
            $db->setQuery('SELECT language FROM ' . $db->quoteName('#__jshopping_languages'));
            $langs = $db->loadColumn();
            if ($langs) {
                $id = (int) $db->insertid();
                foreach ($langs as $lang) {
                    $col = 'name_' . $lang;
                    $upd = $db->getQuery(true)
                        ->update($db->quoteName('#__jshopping_payment_method'))
                        ->set($db->quoteName($col) . ' = ' . $db->quote('Passimpay'))
                        ->where($db->quoteName('payment_id') . ' = ' . $id);
                    $db->setQuery($upd)->execute();
                }
            }
        }

        $app->enqueueMessage('Passimpay payment method installed/updated', 'message');
        return true;
    }

   
    // При обновлении копируем файлы, сохраняя существующие настройки оплаты.
    public function update(PluginAdapter $adapter)
    {
        return $this->install($adapter);
    }

    public function postflight($type, $parent)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('enabled') . ' = 0')
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('passimpayinstaller'));
        $db->setQuery($query)->execute();
    }

    public function uninstall(PluginAdapter $adapter)
    {

    }
}