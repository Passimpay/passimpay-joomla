# PassimPay для Joomla 3 / JoomShopping 4 — 1.0.1

1. Сохраните резервную копию файлов, базы и настроек оплаты.
2. Установите `passimpay_joomshopping.zip` через JoomShopping → Install & Update. В корне архива находятся `update.php` и `components/`. При обновлении не удаляйте существующий способ оплаты.
3. В настройках PassimPay укажите API KEY и Platform ID своего проекта, включите способ оплаты и проверьте статусы успешной и ожидающей оплаты.
4. Укажите адреса в проекте PassimPay, заменив домен и учитывая подкаталог магазина:

| Поле | Адрес |
| --- | --- |
| Notification URL | `https://shop.example/index.php?option=com_jshopping&controller=checkout&task=step7&act=notify&js_paymentclass=pm_passimpay&no_lang=1` |
| Successful URL | `https://shop.example/index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_passimpay` |
| Fail URL | `https://shop.example/index.php?option=com_jshopping&controller=cart` |

5. Очистите кеш Joomla и выполните контрольную оплату. Нужны PHP cURL и исходящий HTTPS к `https://api.passimpay.io`.

API v2 используется автоматически. Плагин выставляет счёт в USD без автоматической конвертации валюты магазина; переданная сумма заказа должна соответствовать сумме в USD. При ошибке оплаты или связи статус заказа не меняется.

Статус оплаты определяется серверной проверкой платежа. Адрес уведомлений должен принимать POST JSON и заголовок `x-signature` без перенаправлений и проверок браузера. Актуальные IP-адреса отправителей для настройки сервера получите у [поддержки PassimPay](https://passimpay.io/en/contacts); требования описаны в [документации уведомлений](https://passimpay.gitbook.io/passimpay-api/webhook).

Platform ID и API Key доступны в настройках проекта в [кабинете PassimPay](https://account.passimpay.io/).
