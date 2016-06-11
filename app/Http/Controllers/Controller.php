<?php namespace App\Http\Controllers;

use App\Core\ReUserManager;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller extends BaseController
{

    use DispatchesCommands, ValidatesRequests, ReUserManager;

    protected function authCheck(Request $request)
    {
        $this->request = $request;
        if( $username = $this->verifiedUser() ) return $username;
        else return false;
    }

}
