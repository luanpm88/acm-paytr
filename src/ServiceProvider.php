<?php

namespace Acelle\Paytr;

use Illuminate\Support\ServiceProvider as Base;
use Acelle\Library\Facades\Hook;
use Acelle\Library\Facades\Billing;
use Acelle\Paytr\Paytr;
use Acelle\Paytr\Services\PaytrPaymentGateway;

class ServiceProvider extends Base
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Register views path
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'paytr');

        // Register routes file
        $this->loadRoutesFrom(__DIR__.'/../routes.php');

        // Register translation file
        $this->loadTranslationsFrom(storage_path('app/data/plugins/acelle/paytr/lang/'), 'paytr');

        // Register the translation file against Acelle translation management
        Hook::register('add_translation_file', function() {
            return [
                "id" => '#acelle/paytr_translation_file',
                "plugin_name" => "acelle/paytr",
                "file_title" => "Translation for acelle/paytr plugin",
                "translation_folder" => storage_path('app/data/plugins/acelle/paytr/lang/'),
                "file_name" => "messages.php",
                "master_translation_file" => realpath(__DIR__.'/../resources/lang/en/messages.php'),
            ];
        });

        // register payment
        $paytr = Paytr::initialize();
        if ($paytr->plugin->isActive()) {
            Billing::register(Paytr::GATEWAY, function() use ($paytr) {
                return $paytr->gateway;
            });
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }
}
