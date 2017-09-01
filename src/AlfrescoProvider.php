<?php

namespace Infiniityr\Providers;

use Infiniityr\Alfresco\Alfresco;
use Infiniityr\Alfresco\API\AlfrescoProcessor;
use Infiniityr\Alfresco\API\ConnectionAPI;
use Infiniityr\Alfresco\API\ConnectionResolver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AlfrescoProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Alfresco::setConnectionResolver($this->app['api']);

        // Config
        $this->publishes([
            __DIR__ . '/../config/alfresco.php' => config_path('alfresco.php'),
        ], 'alf-model-laravel:alfresco');

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        Alfresco::clearBootedModels();

        $this->registerConnectionServices();

        $this->registerStrAdditions();
    }

    public function registerConnectionServices()
    {
        $this->app->singleton('api', function($app){
            return new ConnectionResolver($app, new ConnectionAPI(new AlfrescoProcessor()));
        });
    }

    public function registerStrAdditions()
    {
        Str::macro('removeBetween', function($charBeginning, $charEnd, $string){
            $beginningPos = strpos($string, $charBeginning);
            $endPos = strpos($string, $charEnd);
            if ($beginningPos === false || $endPos === false) {
                return $string;
            }

            $textToDelete = substr($string, $beginningPos, ($endPos + strlen($charEnd)) - $beginningPos);

            return str_replace($textToDelete, '', $string);
        });
    }
}
