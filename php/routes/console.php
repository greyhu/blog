<?php

use Illuminate\Foundation\Inspiring;
use App\User;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('initblog {email} {nick} {pwd}', function ($email, $nick, $pwd) {
    $user = User::where('email', $email)->where('id','>', 1)->delete();
//    if($user) $user->delete();
//    User::destroy(1);
//    User::create([
//        'id' => 1,
//        'name' => $nick,
//        'email' => $email,
//        'password' => bcrypt($pwd)
//    ]);
    $user = User::firstOrNew(['id' => 1]);
    $user->name = $nick;
    $user->email = $email;
    $user->password = bcrypt($pwd);
    $user->save();
})->describe('initblog 邮箱 昵称 密码');

Artisan::command('initblog_setapipassword', function (){
    $row = DB::table('oauth_clients')->where('password_client', 1)->latest()->first();
    Storage::put('settings.json', json_encode(['apiclient'=>['id'=>$row->id, 'secret'=>$row->secret], 'title'=>'未设置名称', 'desc'=>'未设置描述']));
    DB::table('oauth_clients')->where('password_client', 1)->where('id', '<', $row->id)->delete();
})->describe('initblog 邮箱 密码');
