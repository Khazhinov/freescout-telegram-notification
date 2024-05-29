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
            $url = sprintf('https://api.telegram.org/bot%s/sendMessage', $settings['telegram_notification.token']);
            $data = [
                'chat_id' => $settings['telegram_notification.chat_id'],
                'text' => $message,
                'parse_mode' => 'html',
                'disable_web_page_preview' => true,
                'disable_notification' => false
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $content = curl_exec($ch);

            if (is_resource($ch)) {
                curl_close($ch);
            }
        }
    }
}