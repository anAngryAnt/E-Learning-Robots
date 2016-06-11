<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 6/11/16
 * Time: 2:40 PM
 */

namespace App\Http\Controllers\Hack;


use App\Core\ReUserManager;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redis;

class AuthManager
{

    use ReUserManager;


    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public static function check()
    {
        $cookie = Cookie::get('h_k');

        $username = Redis::hget($cookie, 'username');

        return empty($username) ? false : $username;
    }

    private function authCookieName()
    {
        return 'h_k';
    }


}