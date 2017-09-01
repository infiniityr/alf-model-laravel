## Alfresco Model For Laravel ##

This package is to add a model for Alfresco with the eloquent syntax for Laravel 5.4.*

### Installation ###
- For Laravel 5.4
```
    composer require infiniityr/alf-model-laravel
```

The next required step is to add the service provider to config/app.php :
```
    Infiniityr\Alfresco\AlfrescoProvider::class,
```

And copy the package config to your local config with the publish command:
```
    php artisan vendor:publish --tag=alf-model-laravel:alfresco
```


