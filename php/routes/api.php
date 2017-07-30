<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('/index', 'BlogController@index');
Route::post('/reg', 'BlogController@reg');
Route::get('/getArticles', 'ArticleController@getArticles');
Route::get('/articles/{id}/comments', 'ArticleController@getComments');

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return [
            'stat'=> 0,
            'user'=> [
                'isadmin'=>$user->id === 1,
                'nick'=>$user->name,
                'id'=>$user->id
            ]
        ];
    });
    Route::resource('articles', 'ArticleController');
    Route::post('/articles/{id}/comments', 'ArticleController@addComment');
    Route::get('/articles/{id}/allcomments', 'ArticleController@getComments');
    Route::post('/comments/{id}/reply', 'ArticleController@reply');
    Route::delete('/comments/{id}', 'ArticleController@delComment');
    Route::put('/settings', 'BlogController@settings');
    Route::post('/saveLogo', 'BlogController@saveLogo');
    Route::post('/upload', 'BlogController@upload');
});
