<?php
/**
 * Created by PhpStorm.
 * User: WhiteYuan
 * Date: 2018/1/12
 * Time: 16:27
 */

namespace App\Modules\Basic\Providers;


use App\Modules\Basic\Http\Middleware\Authorization;
use App\Modules\Basic\Http\Middleware\JwtAuthorization;
use App\Modules\Basic\Http\Middleware\TeacherAuthorization;
use App\Modules\Basic\Http\Middleware\AdminAuthorization;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'basic.auth' => Authorization::class,
        'jwt.auth' => JwtAuthorization::class,
        'admin.auth' => AdminAuthorization::class,
        'teacher.auth' => TeacherAuthorization::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'basic' => [
            'basic.auth',
        ],
        'jwt' => [
            'jwt.auth',
        ],
    ];

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRouteMiddleware($this->app['router']);
    }


    /**
     * Register the route middleware.
     *
     * @return void
     */
    protected function registerRouteMiddleware(Router $router)
    {
        // register route middleware.
        foreach ($this->routeMiddleware as $key => $middleware) {
            $router->aliasMiddleware($key, $middleware);
        }

        // register middleware group.
        foreach ($this->middlewareGroups as $key => $middleware) {
            $router->middlewareGroup($key, $middleware);
        }
    }
}