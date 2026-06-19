<?php

namespace App\Http\Middleware;

use App\Utils\Util;
use Closure;

class Superadmin
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
        if (! empty($request->user()) && app(Util::class)->usernameInCsvList($request->user()->username, config('constants.administrator_usernames'))) {
            return $next($request);
        } else {
            abort(403, 'Unauthorized action.');
        }
    }
}
