<?php namespace App\Commands;

use App\Http\Controllers\Hack\Utils;
use Carbon\Carbon;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Facades\Queue;

class AutoRefresh extends Command implements SelfHandling
{

    private $courseInfo;

    private $play_timestamp;
    private $play_url;

    private $session_timestamp;
    private $session_url;

    private $stuNo;
    private $tag;
    private $refreshResultTime = 60;

    private $leftHour;
    private $leftMins;

    private $deadLine;


    public function __construct($tag, $courseInfo, $session_timestamp = null, $play_timestamp = null, $stuNo = null)
    {
        $this->stuNo = $stuNo;
        $this->tag = $tag;
        $this->courseInfo = $courseInfo;
        $this->session_timestamp = is_null($session_timestamp) ? time() : $session_timestamp;
        $this->play_timestamp = is_null($play_timestamp) ? time() : $play_timestamp;

        $this->leftHour = $courseInfo['hour'];
        $this->leftMins = $courseInfo['min'];

        $this->deadLine = Carbon::now()->addHours($this->leftHour)->addMinutes($this->leftMins)->timestamp;


    }


    public function handle()
    {
        $couseId = isset($this->courseInfo['couid']) ? $this->courseInfo['couid'] : $this->courseInfo['courseid'];
        $chooseId = $this->courseInfo['chooseid'];

        $playTimeGap = $this->resetPlay($this->stuNo, $chooseId, $couseId);
        $sessionTimeGap = $this->resetSession($chooseId);

        //choose the biggest one
        $timeGap = $playTimeGap >= $sessionTimeGap ? $playTimeGap : $sessionTimeGap;

        $delayTime = Carbon::now()->addSeconds($timeGap);

        if (time() < $this->deadLine) {
            Queue::laterOn('test', $delayTime, $this->copyThis());
        }

//        $refreshResultTime = Carbon::now()->addMinutes(2);
//        Queue::laterOn('end', $refreshResultTime, new EndCommand($this->tag, $chooseId));

    }


    private function resetPlay($stuno, $chooseid, $courseid)
    {
        if (empty($stuno))
            $this->play_url = Utils::PLAYER_ROOT . "refresh.jsp";
        else
            $this->play_url = Utils::PLAYER_ROOT . "refresh.jsp?timestamp=$this->play_timestamp&stuno=$stuno&chooseid=$chooseid&courseid=$courseid";

        echo '  1 play_url is ' . $this->play_url;

        $opts = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_URL => $this->play_url,
            CURLOPT_COOKIEFILE => public_path('cookies') . '/player_cookie_' . $this->tag
        );

        list($this->play_url, $timeGap) = $this->getContent($opts, 0);

        echo '  2 play_url is ' . $this->play_url;

        $this->play_timestamp = Utils::seprateGetParamter($this->play_url)['timestamp'];

        return $timeGap;
    }

    private function resetSession($chooseid)
    {
        $this->session_url = Utils::URL_ROOT . "freshsession.jsp?timestamp=$this->session_timestamp&chooseid=$chooseid";

        echo '  session_url is ' . $this->session_url;

        $opts = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_URL => $this->session_url,
            CURLOPT_COOKIEFILE => public_path('cookies') . '/player_cookie_' . $this->tag
        );

        list($this->session_url, $timeGap) = $this->getContent($opts, 1);


        $this->session_timestamp = Utils::seprateGetParamter($this->session_url)['timestamp'];

        return $timeGap;
    }

    private function getContent($opts, $type)
    {
        $ch = curl_init();

        curl_setopt_array($ch, $opts);
        $res = curl_exec($ch);
        $html = \App\Core\str_get_html($res);
        $meta = $html->find('META')[0];
        $part = explode('; ', $meta->content);
        $refreshTimeGap = $part[0];
        $newUrl = explode('url=', $part[1])[1];

        if ($type == 0) {
            $this->stuNo = Utils::seprateGetParamter($newUrl)['stuno'];
        }

        curl_close($ch);
        return [$newUrl, $refreshTimeGap];
    }

    private function copyThis()
    {
        return new self($this->tag, $this->courseInfo, $this->session_timestamp, $this->play_timestamp, $this->stuNo);
    }

}
