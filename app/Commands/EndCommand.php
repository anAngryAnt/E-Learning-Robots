<?php namespace App\Commands;

use App\Commands\Command;

use App\Http\Controllers\Hack\Utils;
use Carbon\Carbon;
use Illuminate\Contracts\Bus\SelfHandling;

class EndCommand extends Command implements SelfHandling
{

    private $chooseid;
    private $tag;

    public function __construct($tag, $chooseid)
    {
        $this->chooseid = $chooseid;
        $this->tag = $tag;
    }

    public function handle()
    {
        $this->refresh();
        echo 'refresh at ' . Carbon::now()->toDateTimeString();
    }

    private function refresh()
    {
        $ch = curl_init(Utils::REFRESH_STUDY_RESULT . $this->chooseid);

        $u = Utils::getInstance($this->tag);

        $opts = array(
            CURLOPT_COOKIEFILE => $u::$PLAYER_COOKIE_PATH,
            CURLOPT_HTTPHEADER => array(
                'Referer: http://e-learning.neusoft.edu.cn/scorm/player/courseTitle.jsp',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Content-Type: application/x-www-form-urlencoded',
                'Upgrade-Insecure-Requests: 1'
            )
        );

        var_dump($opts);

        curl_setopt_array($ch, $opts);
        curl_exec($ch);

    }

}
