<?php

namespace Modules\TelegramNotification\Entities;

class TelegramSender
{
    public static function send(string $message): void
    {
        $settings = \Option::getOptions([
            'telegram_notification.active',
            'telegram_notification.token',
            'telegram_notification.chat_id',
        ]);

        if ($settings['telegram_notification.active'] === 'on') {
            $data = [
                'chat_id' => $settings['telegram_notification.chat_id'],
                'text' => $message,
                'parse_mode' => 'html',
                'disable_web_page_preview' => true,
                'disable_notification' => false
            ];

            $ch = curl_init(sprintf('https://api.telegram.org/bot%s/sendMessage', $settings['telegram_notification.token']));
            curl_setopt_array($ch, array(
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $data
            ));
            curl_exec($ch);
            curl_close($ch);
        }
    }
}