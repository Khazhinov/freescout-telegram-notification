# freescout-telegram-notification
Простая система оповещений в Telegram для Freescout

Данный модуль предоставляет возможность подключить телеграм бота, который будет уведомлять о получении новых обращений в выбранный чат.

В модуле предусмотрена возможность оповещать о получении нового обращения и получения сообщения на уже открытое ранее обращение.

Для реагирования на новые типы уведомлений смотреть файл ```\Providers\TelegramNotificationServiceProvider.php```

Сообщение будет выглядеть в чате следующим образом:

------------------------------------

Поступило новое обращение: <b>#НОМЕР</b>
Заголовок: <b>Заголовок, указанный в письме</b>

<a href="http://ya.ru">[Открыть диалог]</a>

------------------------------------

## Установка

- загрузите модуль в папку Modules внутри установленного экземпляра FreeScout, название папки модуля должно быть **TelegramNotification**, в ином случае работать **НЕ БУДЕТ**.
- включите модуль в панели администратора FreeScout
- выполните настройку модуля, заполнив требуемые поля (token/chat_id)