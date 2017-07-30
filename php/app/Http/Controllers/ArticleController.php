<?php

namespace App\Http\Controllers;

use App\Article;
use App\Catalog;
use App\Comment;
use App\Tag;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(Auth::user()->id != 1) {
            return ['stat'=>1,'msg'=>'非拥有者不能发表文章！'];
        }
        if($request['catalog']=="") {$request['catalog']="未分类";}
        $catalog = Catalog::firstOrCreate(['name'=>$request['catalog']]);
        $article = Article::create([
            'title'=>$request['title'],
            'catalog_id'=>$catalog?$catalog->id:null,
            'content'=>$request['content'],
        ]);
        foreach (explode(' ',$request['tags']) as $tagname) {
            if(empty($tagname)) continue;
            $tag = Tag::firstOrCreate(['name'=>$tagname]);
            $article->tags()->attach($tag->id);
        }
        if ($request->expectsJson()) {
            $t=$article->tags;
            $t=$article->catalog;
            return ['stat'=>0,'article'=>$article];
        }
        return response()->redirectTo('articles/'.$article->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $article = Article::find($id);
        if(!$article)   return ['stat'=>1,'msg'=>'文章未找到'];
        return ['id'=>$article->id,'title'=>$article->title,'content'=>$article->content,'catalog'=>$article->catalog?$article->catalog->name:'','tags'=>$article->tags->implode('name',' ')];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $article = Article::find($id);
        if(!$article)   return ['stat'=>1,'msg'=>'文章未找到'];
        if(Auth::user()->id != 1) {
            return ['stat'=>1,'msg'=>'非拥有者不能修改文章！'];
        }
        if($request['catalog']=="") {$request['catalog']="未分类";}
        $catalog = Catalog::firstOrCreate(['name'=>$request['catalog']]);
        $article->title = $request['title'];
        $article->catalog_id = $catalog?$catalog->id:null;
        $article->content = $request['content'];
        $article->save();
        $tagidarr = [];
        foreach (explode(' ',$request['tags']) as $tagname) {
            if(empty($tagname)) continue;
            $tag = Tag::firstOrCreate(['name'=>$tagname]);
            $tagidarr[] = $tag->id;
        }
        $article->tags()->sync($tagidarr);
        if ($request->expectsJson()) {
            $t=$article->tags;
            $t=$article->catalog;
            return ['stat'=>0,'article'=>$article];
        }
        return response()->redirectTo('articles/'.$article->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $article = Article::find($id);
        if($article)    {
            if(Auth::user()->id != 1) {
                return ['stat'=>1,'msg'=>'非拥有者不能删除文章！'];
            }
            $article->delete();
        }
        return ['stat'=>0];
    }

    public function getArticles(Request $request) {
        $articles = Article::whereIn('id', explode(',', $request['ids']))->with('catalog')->with('tags')->get();
        return ['stat'=>0,'articles'=>$articles];
    }

    public function getComments($id, Request $request) {
        $comments = Comment::where('article_id','=',$id);
        if(!Auth::user() || Auth::user()->id != 1) {
            $comments->where(function ($query) {
                $query->where('private', '=', false);
                if(Auth::user()) {
                    $query->orWhere('user_id', '=', Auth::user()->id);
                }
            });
            $comments->where('private','=',false);
        }
        $comments = $comments->with('user')->get();
        $comments->each(function ($comment, $key) {
            if($comment->reply_private) {
                if (!Auth::user() || (Auth::user()->id != 1 && $comment->user_id != Auth::user()->id)) {
                    $comment->reply = '';
                }
            }
        });
        return ['stat'=>0, 'comments'=>$comments];
    }

    public function addComment($id, Request $request) {
        $comment = Comment::create([
            'article_id' => $id,
            'user_id' => Auth::user()->id,
            'content' => $request['content'],
            'private' => $request['private'] ?? false,
        ]);
        $comment->user;
        return ['stat'=>0, 'comment'=>$comment];
    }

    public function delComment($id, Request $request) {
        if(Auth::user()->id != 1) {
            return ['stat'=>1,'msg'=>'非拥有者不能删除！'];
        }
        Comment::destroy($id);
        return ['stat'=>0];
    }

    public function reply($id, Request $request) {
        if(Auth::user()->id != 1) {
            return ['stat'=>1,'msg'=>'非拥有者不能回复！'];
        }
        $comment = Comment::find($id);
        if(!$comment)   return ['stat'=>1, 'msg'=>'评论不存在'];
        $comment->reply = $request['reply'];
        $comment->reply_private = $request['reply_private'] ?? false;
        $comment->save();
        return ['stat'=>0];
    }
}
