<?php namespace App\Http\Middleware;

use App\Core\ReUserManager;
use Closure;

class ReUserAuthenticate
{

    use ReUserManager;


    public function handle($request, Closure $next)
    {
        $this->request = $request;

        return $this->verifiedUser() ? $next($request) : redirect()->guest('auth/login');
    }

}
