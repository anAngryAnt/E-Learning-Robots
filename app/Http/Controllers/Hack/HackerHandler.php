<?php namespace App\Http\Controllers\Hack;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redis;


/**
 * Created by PhpStorm.
 * User: Liang Guan Quan
 * Date: 2016/6/8
 * Time: 8:29
 */
class HackerHandler extends Controller
{


    public function scratchInfo(Request $request)
    {

        $this->request = $request;

        $this->validate($request, array(
            'username' => 'required',
            'passwd' => 'required'
        ));

        $username = $request->input('username');
        $passwd = $request->input('passwd');

        $post_data = array(
            'loginname' => $username,
            'password' => $passwd,
            'flag' => '0'
        );

        $headers = array(
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Content-Type: application/x-www-form-urlencoded',
            'Referer: http://e-learning.neusoft.edu.cn/nou/online.jsp',
            'Origin: http://e-learning.neusoft.edu.cn',
            'Upgrade-Insecure-Requests: 1'
        );
        $u = Utils::getInstance($username);
        if ($u->login($post_data, $headers)) {

            list($cookieKey, $cookieValue) = $this->bindUser($username, $passwd);

            $infos = array();
            $u->getSelectedCoursesInfo($infos);

            if (count($infos) == 0) {
                Redis::set($this->noCourses() . $username, 'You dont have any course for Hacker Robots!');
            }

            $len = count($infos);

            $this->bindCourse($username, $infos);

            for ($i = 0; $i < $len - 1; $i++) {
                $u->loginCourse($infos['StuNo'], $infos[$i]['chooseid'], $infos[$i]['couid']);
                $u->establishTaskQueue($username, $infos[$i]);
            }
            return redirect(route('home'))->withCookie(Cookie::make($cookieKey, $cookieValue, Carbon::now()->addYear()->toDateTimeString()));
        } else {
            return redirect(url('auth/login'))
                ->withInput($request->only('username'))
                ->withErrors([
                    'username' => 'password or account not matched',
                ]);
        }
    }

    //ajax
    public function refreshTime(Request $request)
    {
        if ($username = $this->authCheck($request)) {
            $couseIds = $this->obtainCourse();
            $u = Utils::getInstance($username);

            $ch = curl_init();

            foreach ($couseIds as $couseId) {
                $opts = array(
                    CURLOPT_URL => Utils::REFRESH_STUDY_RESULT . $couseId,
                    CURLOPT_COOKIEFILE => $u::$PLAYER_COOKIE_PATH,
                    CURLOPT_HTTPHEADER => array(
                        'Referer: http://e-learning.neusoft.edu.cn/scorm/player/courseTitle.jsp',
                        'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36',
                        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                        'Content-Type: application/x-www-form-urlencoded',
                        'Upgrade-Insecure-Requests: 1'
                    )
                );

                curl_setopt_array($ch, $opts);
                curl_exec($ch);
            }

            curl_close($ch);

            return redirect()->back();

        } else
            return redirect()->guest('auth/login');

    }

    public function test(Request $request)
    {
        //startNetg('201310233707744','1522494','en_US_13185');return false

        //startITEC('201310233707744','1522486','Javaduoxian');return false

        ///^[a-zA-Z]*(\([a-zA-Z0-9',]*)/

        //preg_match('/^[a-zA-Z]*(\([a-zA-Z0-9\',]*)/', "startITEC('201310233707744','1522486','Javaduoxian');return false")

//        $partOne = last(preg_split('/^[a-zA-Z]*/', "startITEC('201310233707744','1522486','Javaduoxian');return false"));
//        $partTwo = head(preg_split('/;return false$/', $partOne));
//        $partTwo = str_replace(array('(', ')'), '', $partTwo);
//
//        $parameters = explode(',', $partTwo);
//        for ($i = 0; $i < count($parameters); $i++) {
//            $parameters[$i] = str_replace('\'', '', $parameters[$i]);
//        }
//
//        dd($parameters);

        dd($request->cookies);


    }

}