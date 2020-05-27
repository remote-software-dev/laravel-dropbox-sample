## Steps to upload our files to Dropbox with Laravel
#### 1. Create Dropbox account
Make sure we have Dropbox account, if not, make it one. visit https://www.dropbox.com/developers/apps/create <br/>

![Alt text](https://github.com/remote-software-dev/laravel-dropbox-sample/blob/master/public/image1.PNG)
![Alt text](https://github.com/remote-software-dev/laravel-dropbox-sample/blob/master/public/image2.PNG)
Then hit create button, you will be directed to the page where we can generate Access Token 
#### 2. Creating database model, controller and migration
Run `php artisan make:model Document -mcr` to scaffold model and controller then migrate it to db storage.<br/>
Options:<br/>
- -m for creating migrate
- -c for creating a new controller for the model
- -r for being a resource controller 

#### 3. Installation of required package
Package we rely on is spatie/flysystem-dropbox, 
- Issue `composer require spatie/flysystem-dropbox` to install the package.
- Set `config/filesystem.php` to add dropbox driver, so it looks like
````php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
        ],
        'dropbox' => [
            'driver' => 'dropbox',
            'authorizationToken' => env('DROPBOX_TOKEN')
        ],

    ],

];

````
on the `.env` file, give the generated token value for DROPBOX_TOKEN  
- To get the installed package recognized by our application, we need to create service provider for it by issuing command
`php artisan make:provider DropboxServiceProvider` 
- Add this App\Providers\DropboxServiceProvider::class to the very end **Application Service Provider** 

````php
'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

        App\Providers\DropboxServiceProvider::class,

    ],

````
- Open file **DropboxServiceProvider** from Provider folder, inside the boot function, make it as shown below
````php
use Illuminate\Support\ServiceProvider;
use Storage;

use Spatie\Dropbox\Client as DropboxClient;
use League\Flysystem\Filesystem;
use Spatie\FlysystemDropbox\DropboxAdapter;

...
/**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('dropbox', function ($app, $config) {
            $client = new DropboxClient(
                $config['authorizationToken']
            );

            return new Filesystem(new DropboxAdapter($client));
        });
    }
````

#### 4. 
#### 5. sss
#### 6. sss
#### 7. sss
#### 8. sss


