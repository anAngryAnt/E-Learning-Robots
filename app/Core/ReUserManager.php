<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 6/10/16
 * Time: 3:17 PM
 */

namespace App\Core;


use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redis;

trait ReUserManager
{

    /**
     * @var Illuminate\Http\Request;
     */
    protected $request;

    /**
     *
     * bind login-successful user with his machine
     *
     * @param $username e-learning account
     * @param $password
     */
    protected function bindUser($username, $password)
    {

        $token = md5($username . '|&*(' . $password);

        Redis::hmset($token, 'username', $username, 'passwd', $password);

        return [$this->cookieName(), $token];

    }


    protected function verifiedUser()
    {

        $username = Redis::hget($this->request->cookie($this->cookieName()), 'username');

        return is_null($username) ? false : $username;
    }

    protected function bindCourse($username, $courses)
    {
        $len = count($courses) - 1;
        for ($i = 0; $i < $len; $i++)
            Redis::lpush($username, $courses[$i]['chooseid']);

    }

    protected function logout()
    {


        $cookieKey = Cookie::get($this->cookieName());
        $username = Redis::hget($cookieKey, 'username');

        Redis::del($this->noCourses() . $username);
        Redis::del($username);
        Redis::del($cookieKey);
    }

    protected function obtainCourse()
    {
        if ($token = $this->verifiedUser()) {
            return Redis::lrange($token, 0, -1);
        }
    }

    protected function cookieName()
    {
        return 'h_k';
    }

    protected function noCourses()
    {
        return 'no_course_msg_';
    }

}