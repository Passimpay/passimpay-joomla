# PassimPay для Joomla 5 / JoomShopping 5 — 1.0.1

1. Сохраните резервную копию файлов, базы и настроек оплаты.
2. Установите `passimpay_joomla5.zip` через System → Install → Extensions. Для обновления используйте тот же установщик, не удаляя существующий способ оплаты.
3. Откройте JoomShopping → Options → Payment methods → PassimPay. Укажите API KEY, Platform ID, Payment options и статусы успешной и ожидающей оплаты. Активируйте способ оплаты. Для карт включите Cards / Bank transfer в проекте PassimPay.
4. Скопируйте URL notifications и URL return из настроек в проект PassimPay. Для магазина в корне домена:

| Поле | Адрес |
| --- | --- |
| Notification URL | `https://shop.example/index.php?option=com_jshopping&controller=checkout&task=step7&act=notify&js_paymentclass=pm_passimpay&no_lang=1` |
| Successful URL | `https://shop.example/index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_passimpay` |
| Fail URL | `https://shop.example/index.php?option=com_jshopping&controller=cart` |

5. Очистите кеш Joomla и выполните контрольную оплату.

Включать системный плагин установщика после установки не требуется. Нужны PHP cURL и исходящий HTTPS к `https://api.passimpay.io`. API v2 используется автоматически; передаётся валюта заказа, сумма в HUF округляется до целого.

При ошибке оплаты или связи статус заказа не меняется. Поле неуспешного статуса в настройках не применяется обработчиком уведомлений. Подробное логирование платёжных уведомлений включайте только на время диагностики.

Статус оплаты определяется серверной проверкой платежа. Адрес уведомлений должен принимать POST JSON и заголовок `x-signature` без перенаправлений и проверок браузера. Актуальные IP-адреса отправителей для настройки сервера получите у [поддержки PassimPay](https://passimpay.io/en/contacts); требования описаны в [документации уведомлений](https://passimpay.gitbook.io/passimpay-api/webhook).

Platform ID и API Key доступны в настройках проекта в [кабинете PassimPay](https://account.passimpay.io/).
