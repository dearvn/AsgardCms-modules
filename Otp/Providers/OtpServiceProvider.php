<?php

namespace Modules\Otp\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Events\LoadingBackendTranslations;
use Modules\Core\Traits\CanPublishConfiguration;
use Modules\Otp\Http\Middleware\LoginMiddleware;
use Modules\Otp\Entities\OneTimePassword;
use Modules\Otp\Repositories\OneTimePasswordRepository;
use Modules\Otp\Repositories\Eloquent\EloquentOneTimePasswordRepository;
use Modules\Opt\Repositories\Cache\CacheOneTimePasswordDecorator;
use Modules\Otp\Entities\OneTimePasswordLog;
use Modules\Otp\Repositories\OneTimePasswordLogRepository;
use Modules\Otp\Repositories\Eloquent\EloquentOneTimePasswordLogRepository;
use Modules\Opt\Repositories\Cache\CacheOneTimePasswordLogDecorator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Arr;

class OtpServiceProvider extends ServiceProvider
{
    use CanPublishConfiguration;
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * @var array
     */
    protected $middleware = [
        'web' => LoginMiddleware::class,
    ];
    
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerBindings();
        
        $this->app['events']->listen(LoadingBackendTranslations::class, function (LoadingBackendTranslations $event) {
            $event->load('otps', Arr::dot(trans('otp::otps')));
        });
    }

    public function boot()
    {
        $this->registerMiddleware();
        $this->publishConfig('otp', 'settings');
        $this->publishConfig('otp', 'config');
        $this->publishConfig('otp', 'permissions');

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        
        
        $this->publishes([__DIR__ . '/../Config/config.php' => config_path('otp.php')], "config");
        
        $this->app['router']->pushMiddlewareToGroup('web', LoginMiddleware::class);

        \Event::listen('Illuminate\Auth\Events\Logout', function ($user) {

            if (config("otp.otp_service_enabled", false) == false) return;
            setcookie("otp_login_verified", "", time() - 3600);
            unset($_COOKIE['otp_login_verified']);
            OneTimePassword::where("user_id", \Auth::user()->id)->get()->each(function ($otp) {
                $otp->discardOldPasswords();
                \Session::forget("otp_service_bypass");
            });
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }
    
    private function registerMiddleware()
    {
        foreach ($this->middleware as $name => $class) {
            $this->app['router']->aliasMiddleware($name, $class);
        }
    }
    
    private function registerBindings()
    {
        $this->app->bind(OneTimePasswordRepository::class, function () {
            $repository = new EloquentOneTimePasswordRepository(new OneTimePassword());

            if (! Config::get('app.cache')) {
                return $repository;
            }

            return new CacheOneTimePasswordDecorator($repository);
        });
        
        $this->app->bind(OneTimePasswordLogRepository::class, function () {
            $repository = new EloquentOneTimePasswordLogRepository(new OneTimePasswordLog());

            if (! Config::get('app.cache')) {
                return $repository;
            }

            return new CacheOneTimePasswordLogDecorator($repository);
        });

    }
}
