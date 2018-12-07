# LaravelAuth
This is an extended provider of [Laravel Socialite ](https://laravel.com/docs/5.7/socialite) for 
ColoredScience authentication using the [SocialiteProviders Manager](https://github.com/SocialiteProviders/Manager).

Installation
------------

```
composer require laravel/socialite
```

```
composer require coloredscience/laravel-auth
```

Create your app on [Colored User](https://user.coloredscience.com/devs) to obtain API keys.


Configuration
------------

* Add a configurable service for Laravel Socialite connection:

    ```
        'coloredsci' => [
            'client_id' => env('CS_CLIENT_ID'),
            'client_secret' => env('CS_CLIENT_SECRET'),
            'redirect' => env('APP_URL').env('CS_REDIRECT'),
        ],
    ```

* Remove `Laravel\Socialite\SocialiteServiceProvider` from your `providers[]` array in `config\app.php` if you have added it already.

* Register this provider instead

    ```
        'providers' => [
            // a whole bunch of providers
            // remove 'Laravel\Socialite\SocialiteServiceProvider',
            \SocialiteProviders\Manager\ServiceProvider::class, // add
        ];
    ```

* Add `SocialiteProviders\Manager\SocialiteWasCalled` event to your `listen[]` array in `app/Providers/EventServiceProvider`

* Add the Colored Science listener to the `SocialiteProviders\Manager\SocialiteWasCalled[]` that you just created

    ```
        protected $listen = [
            \SocialiteProviders\Manager\SocialiteWasCalled::class => [
                'ColoredScience\\LaravelAuth\\ColoredScienceExtendSocialite@handle',
            ],
        ];
    ```

* Create your Authentication Controller to manage the authentication flow
    ```
        php artisan make:controller Auth\\CSAuthController
    ```

* Register your Auth Routes
    ```
    Route::get('/login', 'Auth\CSAuthController@login' )->name( 'login' );
    Route::get('/callback', 'Auth\CSAuthController@handleCallback' )->name( 'callback' );
    Route::get('/logout', 'Auth\CSAuthController@logout' )->name( 'logout' )->middleware('auth');
    ```

* Create a login and logout function to handle the CS Socialite authentication and logout, more usage options are available via [Laravel Socialite ](https://laravel.com/docs/5.7/socialite)
    ```
        public function login()
        {
            return Socialite::with('coloredsci')->redirect();
        }

        public function logout()
        {
            \Auth::logout();
            return  \Redirect::intended('/');
        }
    ```
* Create a callback function to handle the redirect callback, more usage options are available via [Laravel Socialite ](https://laravel.com/docs/5.7/socialite)
    ```
        public function handleCallback()
        {
            $user = Socialite::driver('coloredsci')->user();

            //optional
            $authUser = $this->updateOrCreateUser($user);
            Auth::login($authUser, true);
            return redirect('/home');
        }

        //optional
        public function updateOrCreateUser($user) {
            return $authUser  = User::updateOrCreate(
                ['email' => $user->email],
                [
                    'fname' => $user->fname,
                    'lname' => $user->lname,
                    'nickname' => $user->nickname,
                    'avatar' => $user->avatar,
                    'phone' => $user->phone,
                    'email_verified_at' => $user->email_verified_at,
                ]
            );
        }
    ```

## Reference

* [Laravel docs about events](http://laravel.com/docs/5.0/events)
* [Laracasts video on events in Laravel 5](https://laracasts.com/lessons/laravel-5-events)
* [Laravel Socialite Docs](https://github.com/laravel/socialite)  
* [Laracasts Socialite video](https://laracasts.com/series/whats-new-in-laravel-5/episodes/9)