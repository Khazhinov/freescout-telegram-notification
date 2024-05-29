<?php

namespace Modules\TelegramNotification\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Auth;

class TelegramNotificationServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->hooks();
    }

    /**
     * Module hooks.
     */
    public function hooks()
    {
        \Eventy::addFilter('settings.sections', function($sections) {
            $sections['telegram_notification'] = ['title' => __('TelegramNotification'), 'icon' => 'user', 'order' => 700];

            return $sections;
        }, 30);

        // Section settings
        \Eventy::addFilter('settings.section_settings', function($settings, $section) {

            if ($section != 'telegram_notification') {
                return $settings;
            }

            $settings = \Option::getOptions([
                'telegram_notification.active',
                'telegram_notification.token',
                'telegram_notification.chat_id',
            ]);

            return $settings;
        }, 20, 2);

        // Section parameters.
        \Eventy::addFilter('settings.section_params', function($params, $section) {
            if ($section != 'telegram_notification') {
                return $params;
            }

            $params = [
                'template_vars' => [],
                'validator_rules' => [],
            ];

            return $params;
        }, 20, 2);

        // Settings view name
        \Eventy::addFilter('settings.view', function($view, $section) {
            if ($section != 'telegram_notification') {
                return $view;
            } else {
                return 'telegram_notification::index';
            }
        }, 20, 2);

        \Eventy::addAction('conversation.created_by_customer', function($conversation, $thread, $customer) {
            \Log::info(sprintf("[TELEGRAM-NOTIFICATION] Реакция на событие (%s)", 'conversation.created_by_customer'));

            $conversation_link = route('conversations.view', ['id' => $conversation->number]);
            $message = <<<MESSAGE
Поступило новое обращение #{$conversation->number}
<pre>
{$conversation->subject}
{$conversation->body}
</pre>

<a href="{$conversation_link}">[ЧАТ]</a>
MESSAGE;
            $this->sendToTelegram($message);
        }, 10, 3);

        \Eventy::addAction('conversation.customer_replied', function($conversation, $thread, $customer) {
            \Log::info(sprintf("[TELEGRAM-NOTIFICATION] Реакция на событие (%s)", 'conversation.customer_replied'));

            $conversation_link = route('conversations.view', ['id' => $conversation->number]);
            $message = <<<MESSAGE
Поступило новое сообщение в обращении #{$conversation->number}
<pre>
{$conversation->subject}
{$conversation->body}
</pre>

<a href="{$conversation_link}">[ЧАТ]</a>
MESSAGE;

            $this->sendToTelegram($message);
        }, 10, 3);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerTranslations();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('telegram_notification.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'telegram_notification'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/telegram_notification');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/telegram_notification';
        }, \Config::get('view.paths')), [$sourcePath]), 'telegram_notification');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $this->loadJsonTranslationsFrom(__DIR__ .'/../Resources/lang');
    }

    /**
     * Register an additional directory of factories.
     * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    protected function sendToTelegram(string $message): void
    {
        $settings = \Option::getOptions([
            'telegram_notification.active',
            'telegram_notification.token',
            'telegram_notification.chat_id',
        ]);

        if ($settings['telegram_notification.active'] === 'on') {
            \Log::info(sprintf("[TELEGRAM-NOTIFICATION] Попытка отправки сообщения (%s)", $message));
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

            \Log::info(sprintf("[TELEGRAM-NOTIFICATION] Ответ Telegram (%s)", $content));
        }
    }
}
