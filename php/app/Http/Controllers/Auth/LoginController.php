<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        $authed = $this->authenticated($request, $this->guard()->user());

        if ($request->expectsJson()) {
            return response()->json(['stat' => 0, 'name'=>$this->guard()->user()->name, 'isadmin'=>User::first()->id==$this->guard()->user()->id]);
        }
        else {
            if($authed) return;
            else return redirect()->intended($this->redirectPath());
        }
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->flush();

        $request->session()->regenerate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['stat' => 0,'token'=>$request->session()->token()]);
        }
        else {
            return redirect('/');
        }
    }
}
