<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use Illuminate\support\Facades\validator;

class PostController extends Controller
{
    public function index(){

        //get posts
        $posts = Post::latest()->paginate(5);
        
        //return collection of posts as a resource
        return new PostResource(true, 'List Data Posts', $posts);
    }

    public function store(Request $request){

        // validasi
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'content' => 'required'
        ]);

        //check validasi error
        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hasName());

        //create post
        $post = post::create([
            'image' => $image->hasName(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        //return response
        return new PostResource(true, 'Data Post Berhasil Ditambahkan!', $post);
    }
}
