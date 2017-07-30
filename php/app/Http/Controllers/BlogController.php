<?php

namespace App\Http\Controllers;

use App\Catalog;
use App\Tag;
use App\User;
use Illuminate\Http\Request;
use App\Article;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request['catalog']) {
            $articleids = Catalog::find($request['catalog'])->articles;;//Article::where('catalog_id', '=', $request['catalog']);
        } elseif($request['tag']) {
            $articleids = Tag::find($request['tag'])->articles()->orderBy('created_at','desc')->select('id')->get();
        } else {
            $articleids = Article::orderBy('created_at','desc')->select('id')->get();
        }
        $settings = json_decode(Storage::get('settings.json'));
        return [
            'stat' => 0,
            'articleids'=>$articleids->map(function ($row) {return $row->id;}),
            'catalogs'=>Catalog::withCount('articles')->get(),
            'tags'=>Tag::withCount('articles')->get(),
            'logopath' => asset('storage/logo.jpg'),
            'settings' => $settings
        ];
    }
    public function reg(Request $request)
    {
        if(User::count() < 1) {
            return ['stat'=>1,'msg'=>'博客尚未启用，暂时不能注册'];
        }
        $valid = validator($request->only('email', 'name', 'password'), [
            'name' => 'required|string|max:32|unique:users',
            'email' => 'required|string|max:32|unique:users',
            'password' => 'required|string|min:6|',
        ]);

        if ($valid->fails()) {
            return ['stat'=>2,'msg'=>join( $valid->errors()->all())];
        }

        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => bcrypt($request['password'])
        ]);
        return ['stat'=>0];
    }
    public function saveLogo(Request $request)
    {
        $path = $request->file('file')->storeAs(
            'public', 'logo.jpg'
        );
        return ['stat'=>0, 'path'=>asset('storage/logo.jpg')];
    }
    public function settings(Request $request)
    {
        if(Auth::user()->id != 1) {
            return ['stat'=>1,'msg'=>'非拥有者不能设置！'];
        }

        $config = json_decode(Storage::get('settings.json'));
        $config->title = $request['title'];
        $config->desc = $request['desc'];
        Storage::put('settings.json', json_encode($config));

        if(file_exists(public_path('index.html'))) {
            @file_put_contents(public_path('index.html'),
                preg_replace('/<meta name="keywords".*?>/', '<meta name="keywords" content="'.$config->title.'">',
                    preg_replace('/<meta name="description".*?>/', '<meta name="description" content="'.$config->desc.'">',
                        preg_replace('/<title>.*?<\/title>/', '<title>'.$config->title.'</title>',
                            file_get_contents(public_path('index.html'))
                        )
                    ))
            );
        }
        return ['stat'=>0];
    }
    public function upload(Request $request)
    {
        $path = $request->file('upload')->store(
            'public/imgs_upload'
        );
        $filename = basename($path);
        return [
            'uploaded' => 1,
            'fileName' => $filename,
            'url' => asset('storage/imgs_upload/'.$filename)
        ];

    }
}
