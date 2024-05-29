<?php

namespace Modules\TelegramNotification\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Auth;
use Modules\TelegramNotifiction\Entities\ConversationObserver;

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

        // Подключаем слушателя при создании диалога
        \App\Conversation::observe(ConversationObserver::class);
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
}
