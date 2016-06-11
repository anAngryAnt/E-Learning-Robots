<?php
/**
 * Created by PhpStorm.
 * User: Liang Guan Quan
 * Date: 2016/6/8
 * Time: 8:41
 */

namespace App\Http\Controllers\Hack;

use App\Commands\AutoRefresh;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class Utils
{
    const URL_ROOT = 'http://e-learning.neusoft.edu.cn/nou/';

    const PLAYER_ROOT = 'http://e-learning.neusoft.edu.cn/scorm/player/';

    const LOGIN_URL = 'http://e-learning.neusoft.edu.cn/nou/login.jsp';

    const MY_COURSE = 'http://e-learning.neusoft.edu.cn/nou/study/mycourses.jsp';

    const COURSE_DETAIL = 'http://e-learning.neusoft.edu.cn/nou/study/detail.jsp?chooseid=1505194&couid=Javabase';

    const REFRESH_SESSION = 'http://e-learning.neusoft.edu.cn/nou/freshsession.jsp?timestamp=1464937492543&chooseid=1505194';

    const REFRESH_PLAYER = 'http://e-learning.neusoft.edu.cn/scorm/player/refresh.jsp?timestamp=1464937492470&stuno=201310233707744&chooseid=1505194&courseid=Javabase';

    const REFRESH_STUDY_RESULT = 'http://e-learning.neusoft.edu.cn/nou/endstudy.jsp?chooseid=';

    //http://e-learning.neusoft.edu.cn/scorm/login.jsp?stuno=201310233707744&chooseid=1522488&courseid=MySQLIO
    const LOGIN_COURSE = 'http://e-learning.neusoft.edu.cn/scorm/login.jsp';

    const LOGIN_SUCCESS_URL = 'http://e-learning.neusoft.edu.cn/nou/gotourl.jsp?gotourl=/nou/group/userhome.jsp';

    const LOGIN_FAILED_URL = 'http://e-learning.neusoft.edu.cn/nou/fail.jsp?errcode=400';

    static $LOGIN_COOKIE_PATH = '';

    static $PLAYER_COOKIE_PATH = '';

    static $COOKIE_LOGIN = '';

    static $COOKIE_PLAYER = '';

    static $instance;

    private $first_item_href = null;

    private function __construct($tag)
    {
        self::$COOKIE_LOGIN = 'login_cookie_' . $tag;
        self::$COOKIE_PLAYER = 'player_cookie_' . $tag;
        self::$LOGIN_COOKIE_PATH = public_path('cookies') . '/' . self::$COOKIE_LOGIN;
        self::$PLAYER_COOKIE_PATH = public_path('cookies') . '/' . self::$COOKIE_PLAYER;
    }


    public static function getInstance($tag)
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self($tag);
        }
        return self::$instance;
    }


    public function loginCourse($stuNo, $chooseid, $courseid)
    {

        $ch = curl_init("http://e-learning.neusoft.edu.cn/nou/study/itecstudy.jsp?stuno=$stuNo&chooseid=$chooseid&courseid=$courseid");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_COOKIEFILE, self::$LOGIN_COOKIE_PATH);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Referer: http://e-learning.neusoft.edu.cn/nou/study/mycourses.jsp',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Content-Type: application/x-www-form-urlencoded',
            'Upgrade-Insecure-Requests: 1'
        ));
        curl_close($ch);

        $ch = curl_init("http://e-learning.neusoft.edu.cn/scorm/login.jsp?stuno=$stuNo&chooseid=$chooseid&courseid=$courseid");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_COOKIEFILE, self::$LOGIN_COOKIE_PATH);
        curl_setopt($ch, CURLOPT_COOKIEJAR, self::$PLAYER_COOKIE_PATH);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Referer: http://e-learning.neusoft.edu.cn/nou/study/mycourses.jsp',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Content-Type: application/x-www-form-urlencoded',
            'Upgrade-Insecure-Requests: 1'
        ));
        curl_exec($ch);
        curl_close($ch);
    }

    public function login($post_fields, $header_fields)
    {
        $ch = curl_init();
        $post_fields = self::createLinkstring($post_fields);
        array_push($header_fields, 'Content-Length:' . strlen($post_fields));
        $opts = array(
            CURLOPT_URL => self::LOGIN_URL,
            CURLOPT_COOKIEJAR => self::$LOGIN_COOKIE_PATH,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_POSTFIELDS => $post_fields,
            CURLOPT_HTTPHEADER => $header_fields,
        );
        if (curl_setopt_array($ch, $opts)) {
            $res = curl_exec($ch);

            $location = ltrim($this->parseHttpHeader($res)['Location']);

            if ($location == self::LOGIN_SUCCESS_URL) {
            } else if ($location == self::LOGIN_FAILED_URL) {
                $res = false;
            }
            curl_close($ch);
            return $res;
        }
    }

    public function getSelectedCoursesInfo(&$infos, $pageNum = 1)
    {
        $ch = curl_init(self::MY_COURSE . "?pageNum=$pageNum");
        curl_setopt($ch, CURLOPT_COOKIEFILE, self::$LOGIN_COOKIE_PATH);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $html = \App\Core\str_get_html($result);
        $result = array();
        $as = $html->find('a');
        foreach ($as as $e) {
            $parseResult = $this->parseOnClickString($e->onclick);
            if (!empty($parseResult)) {
                if (!isset($infos['stuNo'])) {
                    $infos['StuNo'] = $parseResult[0];
                }
            }

            if (preg_match('/^detail.jsp?/', $e->href)) {
                $href = $e->href;
                $time_pair = $this->accessDeatil($href);
                $paramter_str = last(explode('?', $href));
                $paramters = explode('&', $paramter_str);
                $chooseid = last(explode('=', $paramters[0]));
                $couid = last(explode('=', $paramters[1]));
                $inner = array(
                    'href' => $href,
                    'couid' => $couid,
                    'chooseid' => $chooseid
                );
                $result[] = array_merge($inner, $time_pair);
            }

        }

        curl_close($ch);

        if (count($result) > 0) {

            if (!is_null($this->first_item_href) && $result[0]['href'] == $this->first_item_href) {
                //代表上一页已经是最后一页，所以我们在这里作为一个递归出口
                return true;
            } else if ($result[0]['href'] != $this->first_item_href) {
                //只有确定了这一页的first_time_href 是新的，才会赋值
                $this->first_item_href = $result[0]['href'];
                //只有确定了这一页的数据非重复数据，才会装入
                $infos = array_merge($infos, $result);
                //always attempt the next page
                $this->getSelectedCoursesInfo($infos, $pageNum + 1);
            }
        }

    }

    private function parseOnClickString($string)
    {
        if (!empty($string)) {
            $partOne = last(preg_split('/^[a-zA-Z]*/', $string));
            $partTwo = head(preg_split('/;return false$/', $partOne));
            $partTwo = str_replace(array('(', ')'), '', $partTwo);

            $parameters = explode(',', $partTwo);
            for ($i = 0; $i < count($parameters); $i++) {
                $parameters[$i] = str_replace('\'', '', $parameters[$i]);
            }

            return $parameters;
        }
        return array();
    }

    private function accessDeatil($href)
    {
        $result = array(
            'hour' => '',
            'min' => ''
        );
        $href = head(explode('&couna', $href));
        $href = 'http://e-learning.neusoft.edu.cn/nou/study/' . $href;
        $ch = curl_init($href);
        curl_setopt($ch, CURLOPT_COOKIEFILE, self::$LOGIN_COOKIE_PATH);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        $html = \App\Core\str_get_html($res);
        foreach ($html->find('td[style="font-size:8pt;color:red"]') as $e) {
            $time = explode(' ', $e->plaintext);
            if (count($time) > 2) {
                $result['hour'] = $time[0];
                $result['min'] = $time[2];
            } else {
                $result['hour'] = $time[0];
            }
        }
        curl_close($ch);
        return $result;
    }

    public static function parseHttpHeader($header)
    {
        $parameters = explode("\r\n", $header);

        $result = array();

        foreach ($parameters as $parameter) {
            //ensure the item is http header style key-value pair
            if ($pos = stripos($parameter, ': ')) {

                $str_len = strlen($parameter);

                $result[substr($parameter, 0, $pos)] = substr($parameter, $pos + 2, $str_len);
            }
        }

        return $result;
    }

    public static function createLinkstring($para, $devisor = '&')
    {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg .= $key . "=" . $val . $devisor;
        }
        $arg = substr($arg, 0, count($arg) - 2);

        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    public static function seprateGetParamter($parameter_str)
    {
        $getParamters = last(explode('?', $parameter_str));
        $result = array();

        if (!is_null($getParamters)) {
            $sepratedData = explode('&', $getParamters);

            $values = $keys = array();

            $len = count($sepratedData);
            for ($i = 0; $i < $len; $i++) {
                $t = explode('=', $sepratedData[$i]);
                $keys[] = head($t);
                $values[] = last($t);
            }

            $result = array_combine($keys, $values);

        }

        return $result;

    }

    public function establishTaskQueue($tag, $courseInfo)
    {
        Queue::pushOn('test', new AutoRefresh($tag, $courseInfo));
    }

}