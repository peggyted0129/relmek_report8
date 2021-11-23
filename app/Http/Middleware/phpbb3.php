<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Secuser;

class phpbb3 {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
        /* 
            // ** 測試區 open
            // ** 記得更正 API 抓取路徑 function getCurrentUrl():「resources」→「views」→「layout.blade.php」
            
            // $user = Secuser::where('userno', '=', 'S480')->first();
            // Session::put('mancode', 'S480');

            // $user = Secuser::where('userno', '=', 'S102')->first();
            // Session::put('mancode', 'S102');

            $user = Secuser::where('userno', '=', 'S257')->first();
            Session::put('mancode', 'S257');

            Auth::login($user);
            // dd($user);   
            // dd(Auth::user());
            // dd(Auth::check());
            return $next($request);
        */

        /* 
            if (Auth::check()) {
                echo "Now I'm logged in as ".\Auth::user()->username_utf8."<br />";
                echo "<a href='/logout'>Log out</a>";
            } else {
                echo "I'm still NOT logged in<br />";
            }
        */
    
    
        if (!defined('IN_PHPBB'))
        {
            Auth::logout();
        }

        if (!Auth::check())  
        {
            $cookie_name = DB::table('phpbb_config')->where('config_name', 'cookie_name')->pluck('config_value');
            $cookie_name_string = $cookie_name[0]; // relmek_forum_tes
            // dd($_COOKIE[$cookie_name_string]); 


            if (isset($_COOKIE[$cookie_name_string.'_sid'])) {
                // echo 'Cookie_name id = ['.$_COOKIE[$cookie_name.'relmek_forum_test_sid'].']';
                $forum_sid = $_COOKIE[$cookie_name_string.'_sid'];
                $forum_u = $_COOKIE[$cookie_name_string.'_u'];
                if ((!$forum_sid) || ($forum_u == '1')) {
                    return redirect()->guest(url('../../forum'));
                } else {
                    $user_id = DB::table('phpbb_sessions')->where('session_id', $forum_sid)->pluck('session_user_id');
                    $user_email = DB::table('phpbb_users')->where('user_id', $user_id)->pluck('user_email');
                    if ($user_email and $user_email <> '') {
                        $secuser = DB::table('secuser')->where('email', $user_email)->first();
                        Session::put('mancode', $secuser->userno);
                        $user = Secuser::find($secuser->userno);
                        Auth::login($user);
                    } else {
                        Auth::logout();
                    }
                }
            } else {
                return redirect()->guest(url('../../forum'));
            }
        }
        if( Null == Auth::user() ){
            return redirect()->guest(url('../../forum'));
        }
        //dd(Auth::user());
		return $next($request);
    

	}

}
