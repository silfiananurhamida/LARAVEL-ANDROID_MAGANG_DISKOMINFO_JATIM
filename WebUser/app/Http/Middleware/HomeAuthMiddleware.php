<?php

namespace App\Http\Middleware;

use Closure;
use Cookie;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Session;

class HomeAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $cookie = Cookie::get('access_token');
        if (!is_null($cookie)) {
            return $next($request);
        }
        return redirect('login');
    }
}
