@extends('app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Home</div>

                    <div class="panel-body">

                        <?php
                            $username = \App\Http\Controllers\Hack\AuthManager::check();
                            $message = \Illuminate\Support\Facades\Redis::get('no_course_msg_'.$username);
                        ?>

                        @if( empty($message ))
                            Hacker Robots are working so hard for you ...
                            <a href="{{route('refresh')}}">Submit</a>
                        @else
                            {{$message}} <br>
                            <a href="http://e-learning.neusoft.edu.cn/nou/sel_course/studentselcourse.jsp"> Click here
                                to choose course </a> <br>
                            After that , you have to re-login this robot for an amazing function! <a
                                    href="{{url('auth/logout')}}">Logout</a>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
