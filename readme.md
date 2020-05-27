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

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

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
        ...
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

#### 4. Modify controller file
Of course we can extend the functionality), for simplicity I kept it short
````php
<?php

namespace App\Http\Controllers;

use App\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->dropbox = Storage::disk('dropbox')->getDriver()->getAdapter()->getClient();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $docs = Document::all();
        return view('welcome', compact('docs'));
    }

 
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $document = new Document;
        $document->name = $request->input('name');
        $file = $request->file('picture');
        
        $filename = str_random(5).$file->getClientOriginalName();
        $file->storeAs('/public/uploads/', $filename, 'dropbox');
        $document->picture = '/public/uploads/'.$filename;
        $document->save();
        echo "Save Succeeded";
    }
}
````
#### 5. Modify view
````html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <!-- Styles -->
    <style>
        html,
        body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 84px;
        }

        .links>a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <div class="flex-center position-ref full-height">
        @if (Route::has('login'))
        <div class="top-right links">
            @auth
            <a href="{{ url('/home') }}">Home</a>
            @else
            <a href="{{ route('login') }}">Login</a>

            @if (Route::has('register'))
            <a href="{{ route('register') }}">Register</a>
            @endif
            @endauth
        </div>
        @endif

        <div class="content">
            <div class="title m-b-md">
                Laravel
            </div>

            <div class="links">
                <div class="card my-5">
                    <div class="card-body">
                        <form method="post" action="{{route('file.store')}}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label>Title your image</label>
                                <input name="name" type="text" class="form-control" required autofocus>
                                <label>Choose Your Image</label>
                                <input name="picture" type="file" class="form-control @error('berkas') is-invalid @enderror" required autofocus>

                            </div>
                            <button class="mt-3 btn btn-primary btn-block " type="submit">Upload</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
````

#### 6. Add route
````php
Route::get('/', function () {
    return view('welcome');
});

Route::get('/', 'DocumentController@index')->name('file.index');
Route::post('/', 'DocumentController@store')->name('file.store');
````
#### 7. Done


