<?php namespace App\Http\Middleware;

use App\Core\ReUserManager;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;

class RedirectIfAuthenticated
{

    use ReUserManager;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->request = $request;

        if ($this->verifiedUser()) {
            return new RedirectResponse(url('/home'));
        }

        return $next($request);
    }

}
